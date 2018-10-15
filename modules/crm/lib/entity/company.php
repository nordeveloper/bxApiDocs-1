<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;

use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\Security\EntityAuthorization;

class Company extends EntityBase
{
	/** @var Company|null  */
	protected static $instance = null;

	/**
	 * @return Company
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new Company();
		}
		return self::$instance;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Company;
	}

	//region Db
	protected function getDbEntity()
	{
		return CompanyTable::getEntity();
	}
	//endregion

	//region Permissions
	protected function buildPermissionSql(array $params)
	{
		return \CCrmCompany::BuildPermSql(
			isset($params['alias']) ? $params['alias'] : 'L',
			isset($params['permissionType']) ? $params['permissionType'] : 'READ',
			isset($params['options']) && is_array($params['options']) ? $params['options'] : array()
		);
	}
	public function checkReadPermission($ID = 0, $userPermissions = null)
	{
		return \CCrmCompany::CheckReadPermission($ID, $userPermissions);
	}
	//endregion
}