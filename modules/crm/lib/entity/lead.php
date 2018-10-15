<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;

use Bitrix\Crm\LeadTable;
use Bitrix\Crm\Security\EntityAuthorization;

class Lead extends EntityBase
{
	/** @var Lead|null  */
	protected static $instance = null;

	/**
	 * @return Lead
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new Lead();
		}
		return self::$instance;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Lead;
	}

	//region Db
	protected function getDbEntity()
	{
		return LeadTable::getEntity();
	}
	//endregion
	//region Permissions
	protected function buildPermissionSql(array $params)
	{
		return \CCrmLead::BuildPermSql(
			isset($params['alias']) ? $params['alias'] : 'L',
			isset($params['permissionType']) ? $params['permissionType'] : 'READ',
			isset($params['options']) && is_array($params['options']) ? $params['options'] : array()
		);
	}
	public function checkReadPermission($ID = 0, $userPermissions = null)
	{
		return \CCrmLead::CheckReadPermission($ID, $userPermissions);
	}
	//endregion
}