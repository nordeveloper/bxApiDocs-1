<?php

namespace Bitrix\Sale\Cashbox\Restrictions;

use Bitrix\Sale\Services\Base;

class Manager extends Base\RestrictionManager
{
	protected static $classNames = null;

	/**
	 * @return string
	 */
	public static function getEventName()
	{
		return 'onSaleCashboxRestrictionsClassNamesBuildList';
	}

	/**
	 * @return array
	 */
	public static function getBuildInRestrictions()
	{
		$restrictions = array(
			'\Bitrix\Sale\Cashbox\Restrictions\PaySystem' => 'lib/cashbox/restrictions/paysystem.php',
		);

		if (!IsModuleInstalled('bitrix24'))
		{
			$restrictions['\Bitrix\Sale\Cashbox\Restrictions\Company'] = 'lib/cashbox/restrictions/company.php';
		}

		return $restrictions;
	}

	/**
	 * @return int
	 */
	protected static function getServiceType()
	{
		return parent::SERVICE_TYPE_CASHBOX;
	}
}