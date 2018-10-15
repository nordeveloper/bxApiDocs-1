<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;

use Bitrix\Crm\ContactTable;
use Bitrix\Crm\Security\EntityAuthorization;

class Contact extends EntityBase
{
	/** @var Contact|null  */
	protected static $instance = null;

	/**
	 * @return Contact
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new Contact();
		}
		return self::$instance;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Contact;
	}

	//region Db
	protected function getDbEntity()
	{
		return ContactTable::getEntity();
	}
	//endregion

	//region Permissions
	protected function buildPermissionSql(array $params)
	{
		return \CCrmContact::BuildPermSql(
			isset($params['alias']) ? $params['alias'] : 'L',
			isset($params['permissionType']) ? $params['permissionType'] : 'READ',
			isset($params['options']) && is_array($params['options']) ? $params['options'] : array()
		);
	}
	public function checkReadPermission($ID = 0, $userPermissions = null)
	{
		return \CCrmContact::CheckReadPermission($ID, $userPermissions);
	}
	//endregion
}