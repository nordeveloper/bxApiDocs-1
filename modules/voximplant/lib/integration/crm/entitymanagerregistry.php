<?php

namespace Bitrix\Voximplant\Integration\Crm;

use Bitrix\Crm\EntityManageFacility;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Voximplant\Call;
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
	protected static function getWithPhoneNumber($phoneNumber)
	{
		if(!Loader::includeModule('crm'))
			return false;

		if(!is_string($phoneNumber))
			throw new ArgumentException("Phone number should be a string", "phoneNumber");

		$facilityInstance = new \Bitrix\Crm\EntityManageFacility();
		$facilityInstance->setUpdateClientMode(EntityManageFacility::UPDATE_MODE_NONE);
		$facilityInstance->disableAutomationRun();
		$facilityInstance->getSelector()->appendPhoneCriterion($phoneNumber);
		$facilityInstance->getSelector()->search();

		return $facilityInstance;
	}

	/**
	 * Returns EntityManageFacility for the specified entity.
	 * @param string $entityType
	 * @return EntityManageFacility|false
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */

	protected static function getWithEntity($entityType, $entityId)
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

		$facilityInstance = new \Bitrix\Crm\EntityManageFacility();
		$facilityInstance->setUpdateClientMode(EntityManageFacility::UPDATE_MODE_NONE);
		$facilityInstance->disableAutomationRun();
		$facilityInstance->getSelector()->setEntity(
			\CCrmOwnerType::ResolveID($entityType),
			$entityId
		);
		$facilityInstance->getSelector()->search();

		return $facilityInstance;
	}

	/**
	 * Returns EntityManageFacility for the specified call.
	 * @param Call $call
	 * @return EntityManageFacility|false
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getWithCall(Call $call)
	{
		if(static::$instances[$call->getCallId()])
		{
			return static::$instances[$call->getCallId()];
		}

		if ($call->getPrimaryEntityType() != '' && $call->getPrimaryEntityId() > 0)
		{
			$manager = static::getWithEntity($call->getPrimaryEntityType(), $call->getPrimaryEntityId());
		}
		else if($call->getCallerId() != '')
		{
			$manager = static::getWithPhoneNumber($call->getCallerId());
		}
		else
		{
			return false;
		}

		static::$instances[$call->getCallId()] = $manager;
		return $manager;
	}
}