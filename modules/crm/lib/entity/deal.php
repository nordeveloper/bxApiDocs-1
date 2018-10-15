<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;

use Bitrix\Crm\DealTable;
use Bitrix\Crm\Security\EntityAuthorization;

class Deal extends EntityBase
{
	/** @var Deal|null  */
	protected static $instance = null;

	/**
	 * @return Deal
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new Deal();
		}
		return self::$instance;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Deal;
	}

	//region Db
	protected function getDbEntity()
	{
		return DealTable::getEntity();
	}
	//endregion

	//region Permissions
	protected function buildPermissionSql(array $params)
	{
		return \CCrmDeal::BuildPermSql(
			isset($params['alias']) ? $params['alias'] : 'L',
			isset($params['permissionType']) ? $params['permissionType'] : 'READ',
			isset($params['options']) && is_array($params['options']) ? $params['options'] : array()
		);
	}
	public function checkReadPermission($ID = 0, $userPermissions = null)
	{
		return \CCrmDeal::CheckReadPermission($ID, $userPermissions);
	}
	//endregion


}