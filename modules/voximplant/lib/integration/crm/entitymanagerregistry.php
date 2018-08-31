<?php

namespace Bitrix\Voximplant\Integration\Crm;

use Bitrix\Crm\EntityManageFacility;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Voximplant\Model\CallTable;

class EntityManagerRegistry
{
	/** @var EntityManageFacility[] */
	protected static $instances = array();

	/**
	 * Returns EntityManageFacility for the call with specified callId.
	 * @param string $callId id of the call.
	 * @return EntityManageFacility|false
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getWithPhoneNumber($phoneNumber)
	{
		if(!Loader::includeModule('crm'))
			return false;

		if(!is_string($phoneNumber))
			throw new ArgumentException("Phone number should be a string", "phoneNumber");

		if(isset(static::$instances[$phoneNumber]))
			return static::$instances[$phoneNumber];

		$facilityInstance = new \Bitrix\Crm\EntityManageFacility();
		$facilityInstance->setUpdateClientMode(EntityManageFacility::UPDATE_MODE_NONE);
		$facilityInstance->disableAutomationRun();
		$facilityInstance->getSelector()->appendPhoneCriterion($phoneNumber);
		$facilityInstance->getSelector()->search();

		static::$instances[$phoneNumber] = $facilityInstance;
		return $facilityInstance;
	}

	/**
	 * Returns EntityManageFacility for the specified entity.
	 * @param string $entityType
	 * @return EntityManageFacility|false
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */

	public static function getWithEntity($entityType, $entityId)
	{
		if(!Loader::includeModule('crm'))
		{
			return false;
		}
		if(!is_string($entityType))
		{
			throw new ArgumentException("entityType number should be a string", "entityType");
		}
		if(!is_int($entityId))
		{
			throw new ArgumentException("entityId number should be an integer", "entityId");
		}

		$key = $entityType . "_" . $entityId;
		if(isset(static::$instances[$key]))
		{
			return static::$instances[$key];
		}
		$facilityInstance = new \Bitrix\Crm\EntityManageFacility();
		$facilityInstance->setUpdateClientMode(EntityManageFacility::UPDATE_MODE_NONE);
		$facilityInstance->disableAutomationRun();
		$facilityInstance->getSelector()->setEntity(
			\CCrmOwnerType::ResolveID($entityType),
			$entityId
		);
		$facilityInstance->getSelector()->search();

		static::$instances[$key] = $facilityInstance;
		return $facilityInstance;
	}

	/**
	 * Returns EntityManageFacility for the specified call.
	 * @param array $call
	 * @return EntityManageFacility|false
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getWithCall(array $call)
	{
		if ($call['CRM_ENTITY_TYPE'] != '' && $call['CRM_ENTITY_ID'] > 0)
		{
			return static::getWithEntity($call['CRM_ENTITY_TYPE'], (int)$call['CRM_ENTITY_ID']);
		}
		else if($call['CALLER_ID'] != '')
		{
			return static::getWithPhoneNumber($call['CALLER_ID']);
		}
		else
		{
			return false;
		}
	}
}