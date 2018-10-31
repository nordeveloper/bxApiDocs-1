<?php
namespace Bitrix\Crm\Automation;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helper
{
	protected static $isBizprocEnabled;

	public static function isBizprocEnabled()
	{
		if (static::$isBizprocEnabled === null)
		{
			static::$isBizprocEnabled = Loader::includeModule('bizproc');
		}

		return static::$isBizprocEnabled;
	}

	public static function getNavigationBarItems($entityTypeId)
	{
		if (Factory::isAutomationAvailable($entityTypeId))
		{
			return [
				[
					'id' => 'automation',
					'name' => Loc::getMessage('CRM_AUTOMATION_HELPER_ROBOT_TITLE'),
					'active' => false,
					'url' => '/crm/configs/automation/'.\CCrmOwnerType::ResolveName($entityTypeId).'/0/'
				]
			];
		}
		return [];
	}

	public static function __callStatic($name, $arguments)
	{
		if (static::isBizprocEnabled())
		{
			return call_user_func_array([\Bitrix\Bizproc\Automation\Helper::class, $name], $arguments);
		}

		return null;
	}
}