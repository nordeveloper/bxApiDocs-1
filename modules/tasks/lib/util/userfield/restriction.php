<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2015 Bitrix
 */

namespace Bitrix\Tasks\Util\UserField;

use Bitrix\Main\Config\Option;
use Bitrix\Main\UserFieldTable;
use Bitrix\Tasks\Integration\Bitrix24;
use Bitrix\Tasks\Util\UserField;

final class Restriction
{
	public static function canUse($entityCode, $userId = 0)
	{
		if(static::hadUserFieldsBefore($entityCode)) // you can read\write field values, but editing scheme is not guaranteed
		{
			return true;
		}

		// otherwise, bitrix24 will tell us
		return Bitrix24\Task::checkFeatureEnabled('task_user_field');
	}

	public static function canManage($entityCode, $userId = 0)
	{
		// for any entity, ask bitrix24
		return Bitrix24\Task::checkFeatureEnabled('task_user_field');
	}

	public static function canCreateMandatory($entityCode, $userId = 0)
	{
		return static::canManage($entityCode, $userId) && (Bitrix24::isLicensePaid() || Bitrix24::isLicenseShareware());
	}

	private static function hadUserFieldsBefore($entityCode)
	{
		$optName = 'have_uf_'.ToLower($entityCode);

		$flag = Option::get('tasks', $optName);

		if($flag === '') // not checked before, check then
		{
			$filter = array(
				'=ENTITY_ID' => $entityCode,
			);

			$className = UserField::getControllerClassByEntityCode($entityCode);
			if($className)
			{
				$filter['!@FIELD_NAME'] = array_keys($className::getSysScheme());
			}

			$item = UserFieldTable::getList(array(
				'filter' => $filter,
				'limit' => 1,
				'select' => array(
					'ID'
				)
			))->fetch();

			Option::set('tasks', $optName, intval($item['ID']) ? '1' : '0');

			return intval($item['ID']) > 0;
		}
		else
		{
			return $flag == '1';
		}
	}
}