<?php
namespace Bitrix\Crm\Requisite;

use Bitrix\Main;
use Bitrix\Crm;

class EntityLink
{
	const ERR_INVALID_ENTITY_TYPE                           = 201;
	const ERR_INVALID_ENTITY_ID                             = 202;
	const ERR_ENTITY_NOT_FOUND                              = 203;
	const ERR_INVALID_REQUSIITE_ID                          = 204;
	const ERR_INVALID_BANK_DETAIL_ID                        = 205;
	const ERR_INVALID_MC_REQUSIITE_ID                       = 206;
	const ERR_INVALID_MC_BANK_DETAIL_ID                     = 207;
	const ERR_REQUISITE_NOT_FOUND                           = 208;
	const ERR_REQUISITE_TIED_TO_ENTITY_WITHOUT_CLIENT       = 209;
	const ERR_REQUISITE_NOT_ASSIGNED                        = 210;
	const ERR_BANK_DETAIL_NOT_FOUND                         = 211;
	const ERR_BANK_DETAIL_NOT_ASSIGNED_WO_REQUISITE         = 212;
	const ERR_BANK_DETAIL_NOT_ASSIGNED                      = 213;
	const ERR_MC_REQUISITE_TIED_TO_ENTITY_WITHOUT_MYCOMPANY = 214;
	const ERR_MC_REQUISITE_NOT_FOUND                        = 215;
	const ERR_MC_REQUISITE_NOT_ASSIGNED                     = 216;
	const ERR_MC_BANK_DETAIL_NOT_ASSIGNED_WO_MC_REQUISITE   = 217;
	const ERR_MC_BANK_DETAIL_NOT_FOUND                      = 218;
	const ERR_MC_BANK_DETAIL_NOT_ASSIGNED                   = 219;

	private static $FIELD_INFOS = null;

	/**
	 * @param $entityTypeId
	 * @param $entityId
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws \Exception
	 */
	public static function getByEntity($entityTypeId, $entityId)
	{
		$dbResult = LinkTable::getList(
			array(
				'filter' => array('=ENTITY_TYPE_ID' => $entityTypeId, '=ENTITY_ID' => $entityId),
				'select' => array('REQUISITE_ID', 'BANK_DETAIL_ID', 'MC_REQUISITE_ID', 'MC_BANK_DETAIL_ID'),
				'limit' => 1
			)
		);
		$fields = $dbResult->fetch();
		return is_array($fields) ? $fields : null;
	}

	/**
	 * @return array
	 */
	public static function getFieldsInfo()
	{
		if(!self::$FIELD_INFOS)
		{
			self::$FIELD_INFOS = array(
				'ENTITY_TYPE_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required, \CCrmFieldInfoAttr::Immutable)
				),
				'ENTITY_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required, \CCrmFieldInfoAttr::Immutable)
				),
				'REQUISITE_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required)
				),
				'BANK_DETAIL_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required)
				),
				'MC_REQUISITE_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required)
				),
				'MC_BANK_DETAIL_ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(\CCrmFieldInfoAttr::Required)
				)
			);
		}

		return self::$FIELD_INFOS;
	}

	public function getEntityClientSellerInfo($entityTypeId, $entityId)
	{
		$entityNotFound = false;
		$result = array(
			'CLIENT_ENTITY_TYPE_ID' => \CCrmOwnerType::Undefined,
			'CLIENT_ENTITY_ID' => 0,
			'MYCOMPANY_ID' => 0
		);
		switch ($entityTypeId)
		{
			case \CCrmOwnerType::Deal:
				$res = \CCrmDeal::GetListEx(
					array(),
					array('ID' => $entityId, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'COMPANY_ID', 'CONTACT_ID', 'MYCOMPANY_ID')
				);
				$row = $res->Fetch();
				if (is_array($row))
				{
					if (isset($row['COMPANY_ID']) && $row['COMPANY_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Company;
						$result['CLIENT_ENTITY_ID'] = (int)$row['COMPANY_ID'];
					}
					else if (isset($row['CONTACT_ID']) && $row['CONTACT_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Contact;
						$result['CLIENT_ENTITY_ID'] = (int)$row['CONTACT_ID'];
					}

					if (isset($row['MYCOMPANY_ID']) && $row['MYCOMPANY_ID'] > 0)
						$result['MYCOMPANY_ID'] = (int)$row['MYCOMPANY_ID'];
				}
				else
				{
					$entityNotFound = true;
				}
				break;

			case \CCrmOwnerType::Quote:
				$res = \CCrmQuote::GetList(
					array(),
					array('=ID' => $entityId, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'COMPANY_ID', 'CONTACT_ID', 'MYCOMPANY_ID')
				);
				$row = $res->Fetch();
				if (is_array($row))
				{
					if (isset($row['COMPANY_ID']) && $row['COMPANY_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Company;
						$result['CLIENT_ENTITY_ID'] = (int)$row['COMPANY_ID'];
					}
					else if (isset($row['CONTACT_ID']) && $row['CONTACT_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Contact;
						$result['CLIENT_ENTITY_ID'] = (int)$row['CONTACT_ID'];
					}

					if (isset($row['MYCOMPANY_ID']) && $row['MYCOMPANY_ID'] > 0)
						$result['MYCOMPANY_ID'] = (int)$row['MYCOMPANY_ID'];
				}
				else
				{
					$entityNotFound = true;
				}
				break;

			case \CCrmOwnerType::Invoice:
				$res = \CCrmInvoice::GetList(
					array(),
					array('ID' => $entityId, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'UF_COMPANY_ID', 'UF_CONTACT_ID', 'UF_MYCOMPANY_ID')
				);
				$row = $res->Fetch();
				if (is_array($row))
				{
					if (isset($row['UF_COMPANY_ID']) && $row['UF_COMPANY_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Company;
						$result['CLIENT_ENTITY_ID'] = (int)$row['UF_COMPANY_ID'];
					}
					else if (isset($row['UF_CONTACT_ID']) && $row['UF_CONTACT_ID'] > 0)
					{
						$result['CLIENT_ENTITY_TYPE_ID'] = \CCrmOwnerType::Contact;
						$result['CLIENT_ENTITY_ID'] = (int)$row['UF_CONTACT_ID'];
					}

					if (isset($row['UF_MYCOMPANY_ID']) && $row['UF_MYCOMPANY_ID'] > 0)
						$result['MYCOMPANY_ID'] = (int)$row['UF_MYCOMPANY_ID'];
				}
				else
				{
					$entityNotFound = true;
				}
				break;
		}

		if ($entityNotFound)
			throw new Main\SystemException('Entity is not found', self::ERR_ENTITY_NOT_FOUND);

		return $result;
	}

	public static function checkConsistence($entityTypeId, $entityId,
											$requisiteId, $bankDetailId,
											$mcRequisiteId, $mcBankDetailId)
	{
		if(!is_int($entityTypeId) || $entityTypeId <= 0
			|| !($entityTypeId === \CCrmOwnerType::Deal
				|| $entityTypeId === \CCrmOwnerType::Quote
				|| $entityTypeId === \CCrmOwnerType::Invoice))
		{
			throw new Main\SystemException(
				'Entity type is not defined or invalid.',
				self::ERR_INVALID_ENTITY_TYPE
			);
		}

		if(!is_int($entityId) || $entityId <= 0)
		{
			throw new Main\SystemException(
				'Entity identifier is not defined or invalid.',
				self::ERR_INVALID_ENTITY_ID
			);
		}

		if(!is_int($requisiteId) || $requisiteId < 0)
		{
			throw new Main\SystemException(
				'Requisite identifier is not defined or invalid.',
				self::ERR_INVALID_REQUSIITE_ID
			);
		}

		if(!is_int($bankDetailId) || $bankDetailId < 0)
		{
			throw new Main\SystemException(
				'BankDetail identifier is not defined or invalid.',
				self::ERR_INVALID_BANK_DETAIL_ID
			);
		}

		if(!is_int($mcRequisiteId) || $mcRequisiteId < 0)
		{
			throw new Main\SystemException(
				'Requisite identifier of your company is not defined or invalid.',
				self::ERR_INVALID_MC_REQUSIITE_ID
			);
		}

		if(!is_int($mcBankDetailId) || $mcBankDetailId < 0)
		{
			throw new Main\SystemException(
				'BankDetail identifier of your company is not defined or invalid.',
				self::ERR_INVALID_MC_BANK_DETAIL_ID
			);
		}

		$clientSellerInfo = self::getEntityClientSellerInfo($entityTypeId, $entityId);

		$requisite = new Crm\EntityRequisite();
		$bankDetail = new Crm\EntityBankDetail();

		if ($requisiteId > 0)
		{
			$entityTypeName = ucfirst(strtolower(\CCrmOwnerType::ResolveName($entityTypeId)));
			if ($clientSellerInfo['CLIENT_ENTITY_TYPE_ID'] === \CCrmOwnerType::Undefined
				|| $clientSellerInfo['CLIENT_ENTITY_ID'] <= 0)
			{
				throw new Main\SystemException(
					"Requisite with ID '$requisiteId' can not be tied to the $entityTypeName ".
						"in which the client is not selected.",
					self::ERR_REQUISITE_TIED_TO_ENTITY_WITHOUT_CLIENT
				);
			}

			$res = $requisite->getList(
				array(
					'filter' => array('=ID' => $requisiteId),
					'select' => array('ID', 'ENTITY_TYPE_ID', 'ENTITY_ID')
				)
			);
			$row = $res->fetch();
			unset($res);
			if (!is_array($row))
			{
				throw new Main\SystemException(
					"The Requisite with ID '$requisiteId' is not found.",
					self::ERR_REQUISITE_NOT_FOUND
				);
			}
			$rqEntityTypeId = isset($row['ENTITY_TYPE_ID']) ? (int)$row['ENTITY_TYPE_ID'] : 0;
			$rqEntityId = isset($row['ENTITY_ID']) ? (int)$row['ENTITY_ID'] : 0;
			$clientEntityTypeId = (int)$clientSellerInfo['CLIENT_ENTITY_TYPE_ID'];
			$clientEntityId = (int)$clientSellerInfo['CLIENT_ENTITY_ID'];
			if ($clientEntityTypeId !== $rqEntityTypeId || $clientEntityId !== $rqEntityId)
			{
				$clientEntityTypeName = ucfirst(strtolower(\CCrmOwnerType::ResolveName($clientEntityTypeId)));
				throw new Main\SystemException(
					"The Requisite with ID '$requisiteId' is not assigned to $clientEntityTypeName ".
						"with ID '$clientEntityId'.",
					self::ERR_REQUISITE_NOT_ASSIGNED
				);
			}
		}

		if ($bankDetailId > 0)
		{
			if ($requisiteId <= 0)
			{
				throw new Main\SystemException(
					"The BankDetail can not be assigned without Requisite.",
					self::ERR_BANK_DETAIL_NOT_ASSIGNED_WO_REQUISITE
				);
			}

			$res = $bankDetail->getList(
				array(
					'filter' => array('=ID' => $bankDetailId),
					'select' => array('ID', 'ENTITY_TYPE_ID', 'ENTITY_ID')
				)
			);
			$row = $res->fetch();
			unset($res);
			if (!is_array($row))
			{
				throw new Main\SystemException(
					"The BankDetail with ID '$bankDetailId' is not found.",
					self::ERR_BANK_DETAIL_NOT_FOUND
				);
			}
			$bdEntityTypeId = isset($row['ENTITY_TYPE_ID']) ? (int)$row['ENTITY_TYPE_ID'] : 0;
			$bdEntityId = isset($row['ENTITY_ID']) ? (int)$row['ENTITY_ID'] : 0;
			if ($bdEntityTypeId !== \CCrmOwnerType::Requisite || $bdEntityId !== $requisiteId)
			{
				throw new Main\SystemException(
					"The BankDetail with ID '$bankDetailId' is not assigned to Requisite with ID '$requisiteId'.",
					self::ERR_BANK_DETAIL_NOT_ASSIGNED
				);
			}
		}

		if ($mcRequisiteId > 0)
		{
			$entityTypeName = ucfirst(strtolower(\CCrmOwnerType::ResolveName($entityTypeId)));
			if ($clientSellerInfo['MYCOMPANY_ID'] <= 0)
			{
				throw new Main\SystemException(
					"Requisite of your company with ID '$requisiteId' can not be tied to the $entityTypeName ".
					"in which your company is not selected.",
					self::ERR_MC_REQUISITE_TIED_TO_ENTITY_WITHOUT_MYCOMPANY
				);
			}

			$myCompanyId = (int)$clientSellerInfo['MYCOMPANY_ID'];
			$res = $requisite->getList(
				array(
					'filter' => array('=ID' => $mcRequisiteId),
					'select' => array('ID', 'ENTITY_TYPE_ID', 'ENTITY_ID')
				)
			);
			$row = $res->fetch();
			unset($res);
			if (!is_array($row))
			{
				throw new Main\SystemException(
					"The Requisite of your company with ID '$mcRequisiteId' is not found.",
					self::ERR_MC_REQUISITE_NOT_FOUND
				);
			}
			$rqEntityTypeId = isset($row['ENTITY_TYPE_ID']) ? (int)$row['ENTITY_TYPE_ID'] : 0;
			$rqEntityId = isset($row['ENTITY_ID']) ? (int)$row['ENTITY_ID'] : 0;
			if ($rqEntityTypeId !== \CCrmOwnerType::Company || $rqEntityId !== $myCompanyId)
			{
				throw new Main\SystemException(
					"The Requisite with ID '$mcRequisiteId' is not assigned to your company with ID '$myCompanyId'.",
					self::ERR_MC_REQUISITE_NOT_ASSIGNED
				);
			}
		}

		if ($mcBankDetailId > 0)
		{
			if ($mcRequisiteId <= 0)
			{
				throw new Main\SystemException(
					"The BankDetail of your company can not be assigned without Requisite of your company.",
					self::ERR_MC_BANK_DETAIL_NOT_ASSIGNED_WO_MC_REQUISITE
				);
			}

			$res = $bankDetail->getList(
				array(
					'filter' => array('=ID' => $mcBankDetailId),
					'select' => array('ID', 'ENTITY_TYPE_ID', 'ENTITY_ID')
				)
			);
			$row = $res->fetch();
			unset($res);
			if (!is_array($row))
			{
				throw new Main\SystemException(
					"The BankDetail of your company with ID '$mcBankDetailId' is not found.",
					self::ERR_MC_BANK_DETAIL_NOT_FOUND
				);
			}
			$bdEntityTypeId = isset($row['ENTITY_TYPE_ID']) ? (int)$row['ENTITY_TYPE_ID'] : 0;
			$bdEntityId = isset($row['ENTITY_ID']) ? (int)$row['ENTITY_ID'] : 0;
			if ($bdEntityTypeId !== \CCrmOwnerType::Requisite || $bdEntityId !== $mcRequisiteId)
			{
				throw new Main\SystemException(
					"The BankDetail of your company with ID '$mcBankDetailId' is not assigned to ".
						"Requisite of your company with ID '$mcRequisiteId'.",
					self::ERR_MC_BANK_DETAIL_NOT_ASSIGNED
				);
			}
		}
	}

	/**
	 * @param $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList($parameters)
	{
		return LinkTable::getList($parameters);
	}

	/**
	 * @param $entityTypeId
	 * @param $entityId
	 * @param $requisiteId
	 * @param int $bankDetailId
	 * @param int $mcRequisiteId
	 * @param int $mcBankDetailId
	 * @throws Main\ArgumentException
	 * @throws Main\NotSupportedException
	 */
	public static function register(
		$entityTypeId, $entityId,
		$requisiteId = 0, $bankDetailId = 0,
		$mcRequisiteId = 0, $mcBankDetailId = 0
	)
	{
		$errMsgGreaterThanZero = 'Must be greater than zero';

		$entityTypeId = (int)$entityTypeId;
		if($entityTypeId <= 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'entityTypeId');

		$entityId = (int)$entityId;
		if($entityId <= 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'entityId');

		$requisiteId = (int)$requisiteId;
		if($requisiteId < 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'requisiteId');

		$bankDetailId = (int)$bankDetailId;
		if($bankDetailId < 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'bankDetailId');

		$mcRequisiteId = (int)$mcRequisiteId;
		if($mcRequisiteId < 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'mcRequisiteId');

		$mcBankDetailId = (int)$mcBankDetailId;
		if($mcBankDetailId < 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'mcBankDetailId');

		LinkTable::upsert(
			array(
				'ENTITY_TYPE_ID' => $entityTypeId,
				'ENTITY_ID' => $entityId,
				'REQUISITE_ID' => $requisiteId,
				'BANK_DETAIL_ID' => $bankDetailId,
				'MC_REQUISITE_ID' => $mcRequisiteId,
				'MC_BANK_DETAIL_ID' => $mcBankDetailId
			)
		);
	}

	/**
	 * @param $entityTypeId
	 * @param $entityId
	 * @throws Main\ArgumentException
	 * @throws \Exception
	 */
	public static function unregister($entityTypeId, $entityId)
	{
		$errMsgGreaterThanZero = 'Must be greater than zero';

		$entityTypeId = (int)$entityTypeId;
		if($entityTypeId <= 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'entityTypeId');

		$entityId = (int)$entityId;
		if($entityId <= 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'entityId');

		LinkTable::delete(
			array(
				'ENTITY_TYPE_ID' => $entityTypeId,
				'ENTITY_ID' => $entityId
			)
		);
	}

	/**
	 * @param $requisiteId
	 * @throws Main\ArgumentException
	 * @throws Main\NotSupportedException
	 */
	public static function unregisterByRequisite($requisiteId)
	{
		$errMsgGreaterThanZero = 'Must be greater than zero';

		$requisiteId = (int)$requisiteId;
		if ($requisiteId <= 0)
			throw new Main\ArgumentException($errMsgGreaterThanZero, 'requisiteId');

		$connection = Main\Application::getConnection();

		if($connection instanceof Main\DB\MysqlCommonConnection
			|| $connection instanceof Main\DB\MssqlConnection
			|| $connection instanceof Main\DB\OracleConnection)
		{
			$tableName = LinkTable::getTableName();
			if ($connection instanceof Main\DB\MssqlConnection
				|| $connection instanceof Main\DB\OracleConnection)
			{
				$tableName = strtoupper($tableName);
			}
			$connection->queryExecute(
				"DELETE FROM {$tableName} WHERE (REQUISITE_ID = {$requisiteId} AND MC_REQUISITE_ID = 0) OR ".
				"(MC_REQUISITE_ID = {$requisiteId} AND REQUISITE_ID = 0) OR ".
				"(MC_REQUISITE_ID = {$requisiteId} AND REQUISITE_ID = {$requisiteId})"
			);
			$connection->queryExecute(
				"UPDATE {$tableName} ".
				"SET REQUISITE_ID = 0, BANK_DETAIL_ID = 0 ".
				"WHERE REQUISITE_ID = {$requisiteId} AND MC_REQUISITE_ID > 0"
			);
			$connection->queryExecute(
				"UPDATE {$tableName} ".
				"SET MC_REQUISITE_ID = 0, MC_BANK_DETAIL_ID = 0 ".
				"WHERE MC_REQUISITE_ID = {$requisiteId} AND REQUISITE_ID > 0"
			);
		}
		else
		{
			$dbType = $connection->getType();
			throw new Main\NotSupportedException("The '{$dbType}' is not supported in current context");
		}
	}

	/**
	 * @return array Array of identifiers by default for seller, requisites and bank details
	 */
	public static function getDefaultMyCompanyRequisiteLink()
	{
		$myCompanyId = 0;
		$mcRequisiteId = 0;
		$mcBankDetailId = 0;

		$myCompanyId = self::getDefaultMyCompanyId();

		if ($myCompanyId > 0)
		{
			$requisite = new Crm\EntityRequisite();
			$res = $requisite->getList(
				array(
					'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
					'filter' => array(
						'=ENTITY_TYPE_ID' => \CCrmOwnerType::Company,
						'=ENTITY_ID' => $myCompanyId
					),
					'select' => array('ID'),
					'limit' => 1
				)
			);
			if ($row = $res->fetch())
				$mcRequisiteId = (int)$row['ID'];

			if ($mcRequisiteId > 0)
			{
				$bankDetail = new Crm\EntityBankDetail();
				$res = $bankDetail->getList(
					array(
						'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
						'filter' => array(
							'=ENTITY_TYPE_ID' => \CCrmOwnerType::Requisite,
							'=ENTITY_ID' => $mcRequisiteId
						),
						'select' => array('ID'),
						'limit' => 1
					)
				);
				if ($row = $res->fetch())
				{
					$mcBankDetailId = (int)$row['ID'];
				}
			}
		}

		return array(
			'MYCOMPANY_ID' => $myCompanyId,
			'MC_REQUISITE_ID' => $mcRequisiteId,
			'MC_BANK_DETAIL_ID' => $mcBankDetailId
		);
	}

	/**
	 * @return int ID of default seller company.
	 */
	public static function getDefaultMyCompanyId()
	{
		$myCompanyId = (int)Main\Config\Option::get('crm', 'def_mycompany_id', 0);

		if ($myCompanyId > 0)
		{
			$res = \CCrmCompany::GetListEx(
				array(),
				array('ID' => $myCompanyId, 'IS_MY_COMPANY' => 'Y', 'CHECK_PERMISSIONS' => 'N'),
				false,
				array('nTopCount' => 1),
				array('ID')
			);
			if (!is_object($res) || !($row = $res->Fetch()) || !is_array($row))
				$myCompanyId = 0;
		}

		if ($myCompanyId <= 0)
		{
			$myCompanyId = 0;

			$res = \CCrmCompany::GetListEx(
				array('ID' => 'ASC'),
				array('IS_MY_COMPANY' => 'Y', 'CHECK_PERMISSIONS' => 'N'),
				false,
				array('nTopCount' => 1),
				array('ID')
			);
			if (($row = $res->Fetch()) && is_array($row) && isset($row['ID']))
			{
				$myCompanyId = (int)$row['ID'];
			}

			self::setDefaultMyCompanyId($myCompanyId);
		}

		return $myCompanyId;
	}

	public static function setDefaultMyCompanyId($defMyCompanyId)
	{
		Main\Config\Option::set('crm', 'def_mycompany_id', (int)$defMyCompanyId);
	}

	/**
	 * @param int $entityTypeID
	 * @param int $entityID
	 * @return bool
	 */
	public static function checkReadPermissionOwnerEntity($entityTypeID = 0, $entityID = 0)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(intval($entityTypeID) <= 0 && intval($entityID) <= 0)
		{
			return (
				\CCrmAuthorizationHelper::CheckReadPermission(\CCrmOwnerType::Deal, 0)
				&& \CCrmAuthorizationHelper::CheckReadPermission(\CCrmOwnerType::Quote, 0)
				&& \CCrmAuthorizationHelper::CheckReadPermission(\CCrmOwnerType::Invoice, 0)
			);
		}

		if ($entityTypeID === \CCrmOwnerType::Deal
			|| $entityTypeID === \CCrmOwnerType::Quote
			|| $entityTypeID === \CCrmOwnerType::Invoice)
		{
			$entityType = \CCrmOwnerType::ResolveName($entityTypeID);
			return \CCrmAuthorizationHelper::CheckReadPermission($entityType, $entityID);
		}

		return false;
	}

	/**
	 * @param int $entityTypeID
	 * @param int $entityID
	 * @return bool
	 */
	public static function checkUpdatePermissionOwnerEntity($entityTypeID = 0, $entityID = 0)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(intval($entityTypeID) <= 0 && intval($entityID) <= 0)
		{
			return (
				\CCrmAuthorizationHelper::CheckUpdatePermission(\CCrmOwnerType::Deal, 0)
				&& \CCrmAuthorizationHelper::CheckUpdatePermission(\CCrmOwnerType::Quote, 0)
				&& \CCrmAuthorizationHelper::CheckUpdatePermission(\CCrmOwnerType::Invoice, 0)
			);
		}

		if ($entityTypeID === \CCrmOwnerType::Deal
			|| $entityTypeID === \CCrmOwnerType::Quote
			|| $entityTypeID === \CCrmOwnerType::Invoice)
		{
			$entityType = \CCrmOwnerType::ResolveName($entityTypeID);
			return \CCrmAuthorizationHelper::CheckUpdatePermission($entityType, $entityID);
		}

		return false;
	}

	public static function moveDependencies(
		$targEntityTypeId = 0, $targEntityId = 0,
		$seedEntityTypeId = 0, $seedEntityId = 0,
		$targRequisiteId = 0, $seedRequisiteId = 0,
		$targBankDetailId = 0, $seedBankDetailId = 0
	)
	{
		$targRequisiteId = (int)$targRequisiteId;
		$targBankDetailId = (int)$targBankDetailId;
		$seedRequisiteId = (int)$seedRequisiteId;
		$seedBankDetailId = (int)$seedBankDetailId;

		if (!(\CCrmOwnerType::IsDefined($targEntityTypeId) && \CCrmOwnerType::IsDefined($seedEntityTypeId)
			&& $targEntityId > 0 && $seedEntityId > 0 && $targRequisiteId > 0 && $seedRequisiteId > 0))
		{
			return false;
		}

		if ($targBankDetailId > 0 && $seedBankDetailId > 0)
		{
			LinkTable::updateDependencies(
				array('REQUISITE_ID' => $targRequisiteId, 'BANK_DETAIL_ID' => $targBankDetailId),
				array('REQUISITE_ID' => $seedRequisiteId, 'BANK_DETAIL_ID' => $seedBankDetailId)
			);
			LinkTable::updateDependencies(
				array('MC_REQUISITE_ID' => $targRequisiteId, 'MC_BANK_DETAIL_ID' => $targBankDetailId),
				array('MC_REQUISITE_ID' => $seedRequisiteId, 'MC_BANK_DETAIL_ID' => $seedBankDetailId)
			);
		}
		else
		{
			LinkTable::updateDependencies(
				array('REQUISITE_ID' => $targRequisiteId),
				array('REQUISITE_ID' => $seedRequisiteId)
			);
			LinkTable::updateDependencies(
				array('MC_REQUISITE_ID' => $targRequisiteId),
				array('MC_REQUISITE_ID' => $seedRequisiteId)
			);
		}

		$event = new Main\Event(
			'crm',
			'OnAfterRequisiteLinkMoveDependencies',
			array(
				'targEntityTypeId' => $targEntityTypeId,
				'targEntityId' => $targEntityId,
				'seedEntityTypeId' => $seedEntityTypeId,
				'seedEntityId' => $seedEntityId,
				'targRequisiteId' => $targRequisiteId,
				'targBankDetailId' => $targBankDetailId,
				'seedRequisiteId' => $seedRequisiteId,
				'seedBankDetailId' => $seedBankDetailId
			)
		);
		$event->send();

		return true;
	}
}