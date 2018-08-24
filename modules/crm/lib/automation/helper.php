<?php
namespace Bitrix\Crm\Automation;

use Bitrix\Main\Loader;

if (!Loader::includeModule('bizproc'))
{
	return;
}

class Helper extends \Bitrix\Bizproc\Automation\Helper
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
}