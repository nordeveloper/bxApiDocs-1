<?php
namespace Bitrix\Disk\Integration;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class Bitrix24Manager
{
	/**
	 * Tells if module bitrix24 is installed.
	 *
	 * @return bool
	 */
	public static function isEnabled()
	{
		return ModuleManager::isModuleInstalled('bitrix24');
	}

	/**
	 * Tells if user has access to entity by different restriction on B24.
	 *
	 * @param string $entityType Entity type.
	 * @param int $userId User id.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function isAccessEnabled($entityType, $userId)
	{
		if(!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return \CBitrix24BusinessTools::isToolAvailable($userId, $entityType);
	}

	public static function checkAccessEnabled($entityType, $userId)
	{
		if(!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return \CBitrix24BusinessTools::isToolAvailable($userId, $entityType, false);
	}

	/**
	 * Returns true if tariff for this portal is not free.
	 *
	 * @return bool
	 */
	public static function isLicensePaid()
	{
		if(Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::IsLicensePaid();
		}

		return false;
	}

	/**
	 * Init javascript license popup.
	 *
	 * @param string $featureGroupName
	 */
	public static function initLicenseInfoPopupJS($featureGroupName = "")
	{
		if(Loader::includeModule('bitrix24'))
		{
			\CBitrix24::initLicenseInfoPopupJS($featureGroupName);
		}
	}
}