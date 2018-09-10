<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\Value;

use Bitrix\Crm\Format\AddressSeparator;
use Bitrix\Crm\Format\EntityAddressFormatter;
use Bitrix\Crm\Format\RequisiteAddressFormatter;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Requisite;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Value;

class Address extends Value
{
	/**
	 * @param null $modifier
	 * @return string
	 */
	public function toString($modifier = null)
	{
		$options = $this->getOptions($modifier);
		$options['SEPARATOR'] = (int)$options['SEPARATOR'];
		$options['FORMAT'] = (int)$options['FORMAT'];
		return EntityAddressFormatter::format($this->value, $options);
	}

	/**
	 * @return array
	 */
	protected static function getDefaultOptions()
	{
		return [
			'SEPARATOR' => AddressSeparator::Comma,
			'FORMAT' => RequisiteAddressFormatter::getFormatByCountryId(Requisite::getCountryIdByRegion(DataProviderManager::getInstance()->getRegion())),
		];
	}

	protected static function getAliases()
	{
		return [
			'Separator' => 'SEPARATOR',
			'Format' => 'FORMAT',
		];
	}
}