<?php

namespace Bitrix\Crm\Quote;

use Bitrix\Sale;
use Bitrix\Main;

if (!Main\Loader::includeModule('sale'))
{
	return;
}

/**
 * Class PropertyValueCollection
 * @package Bitrix\Crm\Quote
 */
class PropertyValueCollection extends Sale\PropertyValueCollectionBase
{
	/**
	 * @throws Main\NotImplementedException
	 */
	public static function getRegistryType()
	{
		return REGISTRY_TYPE_CRM_QUOTE;
	}

	/**
	 * @param $primary
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		parent::deleteInternal($primary); // TODO: Change the autogenerated stub
	}

}