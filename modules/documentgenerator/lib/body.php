<?php

namespace Bitrix\DocumentGenerator;

use Bitrix\DocumentGenerator\Model\FileTable;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Result;
use Bitrix\Main\Text\BinaryString;

abstract class Body
{
	protected static $valuesPattern = '#\{(([a-zA-Z0-9._-]*?)\~?([^\~\r\t\n\<]*))\}#Uu';
	protected $content;
	protected $values = [];
	protected $fields = [];
	protected $storage;
	protected $excludedPlaceholders = [];

	const BLOCK_START_PLACEHOLDER = 'BLOCK_START';
	const BLOCK_END_PLACEHOLDER = 'BLOCK_END';
	const DO_NOT_INSERT_VALUE_MODIFIER = '__SystemDeletePlaceholder';

	/**
	 * Body constructor.
	 * @param string $content
	 */
	public function __construct($content)
	{
		$this->content = $content;
	}

	/**
	 * @param array $fields
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * @param Storage $storage
	 * @param mixed $from
	 * @return static|false
	 */
	public static function readFromStorage(Storage $storage, $from)
	{
		$content = $storage->read($from);
		if($content)
		{
			$body = new static($content);
			return $body->setStorage($storage);
		}

		return false;
	}

	/**
	 * @return Storage
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * @param Storage $storage
	 * @return $this
	 */
	public function setStorage(Storage $storage)
	{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * @param string $filename
	 * @param Storage|null $storage
	 * @return AddResult
	 */
	public function save($filename = '', Storage $storage = null)
	{
		if(!$storage)
		{
			$storage = $this->storage;
		}

		if(!$storage)
		{
			$storage = Driver::getInstance()->getDefaultStorage();
		}

		$result = $storage->write($this->content, ['fileName' => $this->getFileName($filename), 'contentType' => $this->getFileMimeType()]);
		if($result->isSuccess())
		{
			$path = $result->getId();
			$result = FileTable::add([
				'STORAGE_TYPE' => get_class($storage),
				'STORAGE_WHERE' => $path,
			]);
		}

		return $result;
	}

	/**
	 * Parse $content, process commands, fill values.
	 * Returns true on success, false on failure.
	 *
	 * @return Result
	 */
	abstract public function process();

	/**
	 * @return array
	 */
	abstract public function getPlaceholders();

	/**
	 * @return string
	 */
	abstract public function getFileExtension();

	/**
	 * Normalizes content of the body.
	 */
	public function normalizeContent()
	{

	}

	/**
	 * @param string $filename
	 * @return string
	 */
	protected function getFileName($filename = '')
	{
		if(!$filename)
		{
			$filename = randString(5);
		}

		if(BinaryString::getSubstring($filename, -5) !== '.'.$this->getFileExtension())
		{
			$filename = $filename.'.'.$this->getFileExtension();
		}

		return $filename;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param array $values
	 * @return $this
	 */
	public function setValues(array $values)
	{
		$this->values = array_merge($this->values, $values);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFieldNames()
	{
		return array_merge($this->getPlaceholders(), array_keys($this->getFields()));
	}

	/**
	 * Set placeholders list that will not be filled with values.
	 *
	 * @param array $placeholders
	 */
	public function setExcludedPlaceholders(array $placeholders)
	{
		$this->excludedPlaceholders = array_fill_keys($placeholders, true);
	}

	/**
	 * Replaces placeholders on pattern static::$valuesPattern in $content.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function replacePlaceholders($content)
	{
		return preg_replace_callback(static::$valuesPattern, [$this, 'getReplaceValue'], $content);
	}

	protected function getReplaceValue($matches)
	{
		if(isset($matches[2]) && isset($this->values[$matches[2]]))
		{
			if(isset($this->excludedPlaceholders[$matches[2]]))
			{
				return $matches[0];
			}

			return $this->printValue($this->values[$matches[2]], $matches[2], $matches[3]);
		}

		return '';
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public static function getCodeFromPlaceholder($code)
	{
		$matches = static::matchFieldNames($code);
		return reset($matches);
	}

	/**
	 * Returns array of placeholders with TYPE = $type.
	 * If $type is null - returns array of all $placeholders with specified TYPE.
	 *
	 * @param array $types
	 * @return array
	 */
	protected function getTypePlaceholders(array $types = [])
	{
		$placeholders = [];

		foreach($this->fields as $placeholder => $field)
		{
			if(isset($field['TYPE']) && (in_array($field['TYPE'], $types)) || empty($types) && !isset($this->excludedPlaceholders[$placeholder]))
			{
				$placeholders[$placeholder] = $placeholder;
			}
		}

		return $placeholders;
	}

	/**
	 * Parses $content on pattern static::$valuesPattern and returns array of value names.
	 *
	 * @param string $content
	 * @return array
	 */
	protected static function matchFieldNames($content)
	{
		$names = [];
		if(preg_match_all(static::$valuesPattern, $content, $fieldMatches, PREG_SET_ORDER))
		{
			foreach($fieldMatches as $fieldMatch)
			{
				$names[$fieldMatch[2]] = $fieldMatch[2];
			}
		}

		return $names;
	}

	/**
	 * Generates string from value.
	 *
	 * @param mixed $value
	 * @param string $placeholder
	 * @param string $modifier
	 * @return string
	 */
	protected function printValue($value, $placeholder, $modifier = '')
	{
		if(strpos($modifier, static::DO_NOT_INSERT_VALUE_MODIFIER) !== false)
		{
			return '';
		}
		if(is_object($value))
		{
			if($value instanceof Value)
			{
				return $value->toString($modifier);
			}
			elseif(method_exists($value, '__toString'))
			{
				return $value->__toString();
			}
			else
			{
				$value = '';
			}
		}
		elseif(is_array($value))
		{
			return '';
		}

		return $value;
	}

	/**
	 * @return string
	 */
	abstract public function getFileMimeType();

	/**
	 * Returns array of placeholders that starts with $providerName.'.'
	 *
	 * @param $providerName
	 * @return array
	 */
	protected function getLinkedPlaceholders($providerName)
	{
		$linkedPlaceholders = [];
		$placeholders = $this->getPlaceholders();
		foreach($placeholders as $placeholder)
		{
			if(strpos($placeholder, $providerName.'.') === 0)
			{
				$linkedPlaceholders[] = $placeholder;
			}
			elseif(
				isset($this->values[$placeholder]) &&
				is_string($this->values[$placeholder]) &&
				strpos($this->values[$placeholder], $providerName.'.') === 0
			)
			{
				$linkedPlaceholders[] = $placeholder;
			}
		}

		return array_unique($linkedPlaceholders);
	}
}