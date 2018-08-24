<?php
namespace Bitrix\Crm\Color;
use Bitrix\Main;
use Bitrix\Crm\PhaseSemantics;

class PhaseColorScheme
{
	const PROCESS_COLOR = '#00A9DF';
	const SUCCESS_COLOR = '#9DCF00';
	const FAILURE_COLOR = '#FF5752';

	/** @var string  */
	protected $optionName = '';
	/** @var PhaseColorSchemeElement[]|null  */
	protected $elements = null;
	/** @var bool */
	protected $isPersistent = false;

	/**
	 * @param string $optionName Option name.
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 */
	public function __construct($optionName)
	{
		if(!is_string($optionName))
		{
			throw new Main\ArgumentTypeException('optionName', 'string');
		}

		if($optionName === '')
		{
			throw new Main\ArgumentException('Must be not empty string', 'optionName');
		}

		$this->optionName = $optionName;
	}
	/**
	 * Get default element color by semantic ID.
	 * @param PhaseSemantics $semanticID Semantic ID.
	 * @return string
	 */
	public static function getDefaultColorBySemantics($semanticID)
	{
		if($semanticID === PhaseSemantics::SUCCESS)
		{
			return self::SUCCESS_COLOR;
		}
		elseif($semanticID === PhaseSemantics::FAILURE)
		{
			return PhaseColorScheme::FAILURE_COLOR;
		}
		return self::PROCESS_COLOR;
	}
	/**
	 * Check if scheme is persistent.
	 * @return bool
	 */
	public function isPersistent()
	{
		return $this->isPersistent;
	}
	/**
	 * Add element
	 * @param PhaseColorSchemeElement $element
	 * @return void
	 */
	public function addElement(PhaseColorSchemeElement $element)
	{
		if($this->elements === null)
		{
			$this->elements = array();
		}
		$this->elements[$element->getName()] = $element;
	}
	/**
	 * Get scheme element by name.
	 * @param string $name Item Name.
	 * @return PhaseColorSchemeElement|null
	 */
	public function getElementByName($name)
	{
		return isset($this->elements[$name]) ? $this->elements[$name] : null;
	}
	/**
	 * Reset scheme.
	 */
	public function reset()
	{
		$this->elements = array();
		$this->isPersistent = false;
	}
	/**
	 * Get external representation of this object
	 * @return array
	 */
	public function externalize()
	{
		$results = array();
		foreach($this->elements as $item)
		{
			$item->externalize($results);
		}
		return $results;
	}
	/**
	 * Setup this object from external representation.
	 * @param array $params External params.
	 * @return void
	 */
	public function internalize(array $params)
	{
		foreach($params as $k => $v)
		{
			$element = new PhaseColorSchemeElement();
			$element->internalize($k, is_array($v) ? $v : array());
			$this->elements[$k] = $element;
		}
		$this->isPersistent = true;
	}
	/**
	 * Save scheme to options
	 * @return void
	 */
	public function save()
	{
		Main\Config\Option::set('crm', $this->optionName, serialize($this->externalize()), '');
		if(!$this->isPersistent)
		{
			$this->isPersistent = true;
		}
	}
	/**
	 * Try to load scheme from options
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public function load()
	{
		$s = Main\Config\Option::get('crm', $this->optionName, '', '');
		$params = $s !== '' ? unserialize($s) : null;
		if(!is_array($params))
		{
			return false;
		}

		$this->internalize($params);
		return true;
	}
	/**
	 * Setup scheme by default
	 * @return void
	 */
	public function setupByDefault()
	{
		$this->reset();
	}
	/**
	 * Get default color for element.
	 * @param string $name Element Name.
	 * @return string
	 */
	public function getDefaultColor($name)
	{
		return '';
	}
	/**
	 * Remove scheme from options
	 * @return void
	 */
	public function remove()
	{
		Main\Config\Option::delete('crm', array('name' => $this->optionName));
		if($this->isPersistent)
		{
			$this->isPersistent = false;
		}
	}
	/**
	 * Remove scheme from options
	 * @return void
	 */
	protected static function removeByName($optionName)
	{
		Main\Config\Option::delete('crm', array('name' => $optionName));
	}
}