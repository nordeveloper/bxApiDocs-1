<?php

namespace Bitrix\DocumentGenerator\Value;

use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Value;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

class DateTime extends Value
{
	/**
	 * DateTime constructor.
	 * @param $value
	 * @param array $options
	 */
	public function __construct($value, array $options = [])
	{
		$options = $this->getOptions($options);
		$format = $options['format'];
		if(!$value instanceof Type\Date)
		{
			$value = Type\DateTime::tryParse($value);
		}
		if(!$value instanceof Type\Date)
		{
			$value = Type\DateTime::tryParse($value, $format);
		}
		if(!$value)
		{
			$value = '';
		}
		parent::__construct($value, $options);
	}

	/**
	 * @param null $modifier
	 * @return string
	 */
	public function toString($modifier = null)
	{
		$date = $this->value;
		$options = $this->getOptions($modifier);
		$format = $options['format'];
		if($date instanceof Type\Date)
		{
			$interfaceLanguage = LANGUAGE_ID;
			$templateLanguage = DataProviderManager::getInstance()->getRegionLanguageId();
			if($templateLanguage != $interfaceLanguage)
			{
				Loc::setCurrentLang($templateLanguage);
			}
			$result = FormatDate($format, $date->getTimestamp());
			if($templateLanguage != $interfaceLanguage)
			{
				Loc::setCurrentLang($interfaceLanguage);
			}

			return $result;
		}

		return $date;
	}

	/**
	 * @return array
	 */
	protected static function getDefaultOptions()
	{
		return ['format' => Type\Date::getFormat(DataProviderManager::getInstance()->getCulture())];
	}

	/**
	 * @param string $modifier
	 * @return array
	 */
	public static function parseModifier($modifier)
	{
		return ['format' => $modifier];
	}
}