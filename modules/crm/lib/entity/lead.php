<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;
use Bitrix\Crm;

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
		return Crm\LeadTable::getEntity();
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

	public static function getSubsidiaryEntities($ID)
	{
		$dbResult = Crm\LeadTable::getList(
			array(
				'filter' => array('=ID' => $ID),
				'select' => array('ID', 'COMPANY_ID', 'CONTACT_ID', 'STATUS_SEMANTIC_ID')
			)
		);

		$fields = $dbResult->fetch();
		if(!(is_array($fields) && $fields['STATUS_SEMANTIC_ID'] === Crm\PhaseSemantics::SUCCESS))
		{
			return array();
		}

		$results = array();
		if(isset($fields['COMPANY_ID']) && $fields['COMPANY_ID'] > 0)
		{
			$results[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Company, 'ENTITY_ID' => (int)$fields['COMPANY_ID']);
		}

		if(isset($fields['CONTACT_ID']) && $fields['CONTACT_ID'] > 0)
		{
			$results[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Contact, 'ENTITY_ID' => (int)$fields['CONTACT_ID']);
		}

		$dbResult = Crm\DealTable::getList(
			array(
				'filter' => array('=LEAD_ID' => $ID),
				'select' => array('ID')
			)
		);
		while($fields = $dbResult->fetch())
		{
			$results[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Deal, 'ENTITY_ID' => (int)$fields['ID']);
		}

		return $results;
	}
}