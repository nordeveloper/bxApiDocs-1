<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Crm\Tracking;

use Bitrix\Main\Config\Option;

/**
 * Class Manager
 *
 * @package Bitrix\Crm\Tracking
 */
class Manager
{
	/**
	 * Return true if tracking is accessible.
	 *
	 * @return bool
	 */
	public static function isAccessible()
	{
		return Option::get('crm', '~tracking_enabled', 'N') === 'Y';
	}

	/**
	 * Return true if tracking configured.
	 *
	 * @return bool
	 */
	public static function isConfigured()
	{
		$optionName = '~tracking_configured';
		if (Option::get('crm', $optionName, 'N') === 'Y')
		{
			return true;
		}

		if (empty(Provider::getReadySources()))
		{
			return false;
		}

		Option::set('crm', $optionName, 'Y');
		return true;
	}
}