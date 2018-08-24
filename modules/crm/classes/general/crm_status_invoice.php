<?
/**
 * This class allow to manage the sale order's statuses from crm module
 */

IncludeModuleLangFile(__FILE__);

class CCrmStatusInvoice extends CCrmStatus
{
	private static $FIELD_INFOS = null;

	protected static $languageID = '';

	private static $statusList = null;

	// Get Fields Metadata
	public static function GetFieldsInfo()
	{
		if(!self::$FIELD_INFOS)
		{
			self::$FIELD_INFOS = array(
				'ID' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::ReadOnly)
				),
				'ENTITY_ID' => array(
					'TYPE' => 'string',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::ReadOnly)
				),
				'STATUS_ID' => array(
					'TYPE' => 'string',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::ReadOnly)
				),
				'SORT' => array(
					'TYPE' => 'integer',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::Required)
				),
				'NAME' => array(
					'TYPE' => 'string',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::Required)
				),
				'NAME_INIT' => array(
					'TYPE' => 'string',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::ReadOnly)
				),
				'SYSTEM' => array(
					'TYPE' => 'char',
					'ATTRIBUTES' => array(CCrmFieldInfoAttr::ReadOnly)
				)
			);
		}
		return self::$FIELD_INFOS;
	}

	private static function CheckFilter($filterInfo, $row)
	{
		$result = false;

		if (isset($row[$filterInfo['FIELD']]))
		{
			$fieldValue = $fieldValueOrig = $row[$filterInfo['FIELD']];

			switch ($filterInfo['FIELD_TYPE'])
			{
				case 'integer':
					$fieldValue = intval($fieldValue);
					switch ($filterInfo['OPERATION'])
					{
						case '=':
						case 'IN':
						case 'LIKE':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (!is_array($filterValue))
									$filterValue = array($filterValue);
								foreach ($filterValue as $v)
								{
									if ($fieldValue === intval($v))
									{
										$result = true;
										break;
									}
								}
								if ($filterInfo['NEGATIVE'] === 'Y')
									$result = !$result;
							}
							break;

						case '>':
						case '<':
						case '>=':
						case '<=':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (is_array($filterValue))
								{
									if (!empty($filterValue))
										$filterValue = $filterValue[0];
									else
										$filterValue = 0;
								}
								$filterValue = intval($filterValue);
								switch ($filterInfo['OPERATION'])
								{
									case '>':
										if ($fieldValue > $filterValue)
											$result = true;
										break;
									case '<':
										if ($fieldValue < $filterValue)
											$result = true;
										break;
									case '>=':
										if ($fieldValue >= $filterValue)
											$result = true;
										break;
									case '<=':
										if ($fieldValue <= $filterValue)
											$result = true;
										break;
								}
							}
							if ($filterInfo['NEGATIVE'] === 'Y')
								$result = !$result;
							break;
					}
					break;

				case 'string':
				case 'char':
					$fieldValue = strval($fieldValue);
					switch ($filterInfo['OPERATION'])
					{
						case '=':
						case 'IN':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (!is_array($filterValue))
									$filterValue = array($filterValue);
								foreach ($filterValue as $v)
								{
									if ($fieldValue === strval($v))
									{
										$result = true;
										break;
									}
								}
								if ($filterInfo['NEGATIVE'] === 'Y')
									$result = !$result;
							}
							break;

						case 'LIKE':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (!is_array($filterValue))
									$filterValue = array($filterValue);
								foreach ($filterValue as $v)
								{
									if (strpos(ToUpper($fieldValue), ToUpper(strval($v))) !== false)
									{
										$result = true;
										break;
									}
								}
								if ($filterInfo['NEGATIVE'] === 'Y')
									$result = !$result;
							}
							break;

						case '>':
						case '<':
						case '>=':
						case '<=':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (is_array($filterValue))
								{
									if (!empty($filterValue))
										$filterValue = $filterValue[0];
									else
										$filterValue = '';
								}
								$filterValue = strval($filterValue);
								switch ($filterInfo['OPERATION'])
								{
									case '>':
										if (strcmp($fieldValue, $filterValue) > 0)
											$result = true;
										break;
									case '<':
										if (strcmp($fieldValue, $filterValue) < 0)
											$result = true;
										break;
									case '>=':
										if (strcmp($fieldValue, $filterValue) >= 0)
											$result = true;
										break;
									case '<=':
										if (strcmp($fieldValue, $filterValue) <= 0)
											$result = true;
										break;
								}
								if ($filterInfo['NEGATIVE'] === 'Y')
									$result = !$result;
							}
							break;
						/*case 'QUERY':
							if ($fieldValueOrig === '' && $filterInfo['OR_NULL'] === 'Y')
							{
								$result = true;
							}
							else
							{
								$filterValue = $filterInfo['FILTER_VALUE'];
								if (is_array($filterValue))
								{
									if (!empty($filterValue))
										$filterValue = $filterValue[0];
									else
										$filterValue = '';
								}
								$filterValue = strval($filterValue);
								// TODO: further parse query and check conditions ...
							}
							break;*/
					}
					break;
			}
		}

		return $result;
	}

	public static function GetList($arSort=array(), $arFilter=Array(), $arSelect=Array())
	{
		if (!CModule::IncludeModule('sale'))
		{
			return array();
		}

		$arStatus = array();

		self::ensureLanguageDefined();

		$fieldsInfo = self::GetFieldsInfo();
		$filterOperations = array();
		foreach ($arFilter as $k => $v)
		{
			$operationInfo =  CSqlUtil::GetFilterOperation($k);
			$operationInfo['FILTER_VALUE'] = $v;
			$fieldName = $operationInfo['FIELD'];

			$info = isset($fieldsInfo[$fieldName]) ? $fieldsInfo[$fieldName] : null;
			if ($info)
			{
				$operationInfo['FIELD_TYPE'] = $info['TYPE'];
				$filterOperations[] = $operationInfo;
			}
		}

		$res = CSaleStatus::GetList(
			array(),
			array('LID' => self::$languageID), false, false, array('ID', 'SORT', 'NAME')
		);

		while ($row = $res->Fetch())
		{
			if ($row['ID'] === 'F') continue;

			$arStatus[$row['ID']] = array(
				'ID' => strval(ord($row['ID'])),
				'ENTITY_ID' => 'INVOICE_STATUS',
				'STATUS_ID' => $row['ID'],
				'NAME' => $row['NAME'],
				'NAME_INIT' => '',
				'SORT' => $row['SORT'],
				'SYSTEM' => 'N'
			);

			if (in_array($row['ID'], array('N', 'P', 'F', 'D')))
			{
				if ($row['ID'] === 'F')
					$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_F');
				elseif ($row['ID'] === 'D')
					$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_D');
				elseif ($row['ID'] === 'N')
					$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_N');
				elseif ($row['ID'] === 'P')
					$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_P');

				$arStatus[$row['ID']]['SYSTEM'] = 'Y';
			}
		}

		// filter
		$arResult = array();
		foreach ($arStatus as $row)
		{
			$bRowSelected = true;
			foreach ($filterOperations as $filterInfo)
			{
				if (!self::CheckFilter($filterInfo, $row))
				{
					$bRowSelected = false;
					break;
				}
			}
			if ($bRowSelected)
				$arResult[] = $row;
		}

		// sort
		if (count($arSort) > 0 && count($arResult) > 0)
		{
			$arSortKeys = array_keys($arSort);
			$arSortBy = $arSortDir = $arSortType = array();
			$origFieldsNames = array_keys($fieldsInfo);
			$numSorts = 0;
			foreach ($arSortKeys as $sortKey)
			{
				if (in_array($sortKey, $origFieldsNames, true))
				{
					$arSortBy[] = ToUpper($sortKey);
					$arSortDir[] = (ToUpper($arSort[$sortKey]) === 'DESC') ? SORT_DESC : SORT_ASC;
					$sortType = SORT_REGULAR;
					switch ($fieldsInfo[$sortKey]['TYPE'])
					{
						case 'integer':
							$sortType = SORT_NUMERIC;
							break;

						case 'string':
						case 'char':
							$sortType = SORT_STRING;
							break;
					}
					$arSortType[] = $sortType;
					$numSorts++;
				}
			}
			if ($numSorts > 0)
			{
				$fieldsNames = array();
				foreach ($origFieldsNames as $fieldName)
				{
					if (!in_array($fieldName, $arSortBy, true))
						$fieldsNames[] = $fieldName;
				}
				$fieldsNames = array_merge($arSortBy, $fieldsNames);
				$fieldsIndex = $columns = array();
				$index = 0;
				foreach ($fieldsNames as $fieldName)
				{
					$columns[$index] = array();
					$fieldsIndex[$fieldName] = $index++;
				}
				foreach ($arResult as $row)
				{
					foreach ($row as $fieldName => $fieldValue)
					{
						if (isset($fieldsIndex[$fieldName]))
							$columns[$fieldsIndex[$fieldName]][] = $fieldValue;
					}
				}
				$args = array();
				$index = 0;
				foreach ($columns as &$column)
				{
					$args[] = &$column;
					if ($index < $numSorts)
					{
						$args[] = &$arSortDir[$index];
						$args[] = &$arSortType[$index];
					}
					$index++;
				}
				unset($column);
				call_user_func_array('array_multisort', $args);
				$numRows = count($arResult);
				$arResult = array();
				for ($index = 0; $index < $numRows; $index++)
				{
					$row = array();
					foreach ($origFieldsNames as $fieldName)
						$row[$fieldName] = $columns[$fieldsIndex[$fieldName]][$index];
					$arResult[] = $row;
				}
			}
		}

		// select
		if (count($arResult) > 0 && is_array($arSelect) && count($arSelect) > 0)
		{
			$selectedFields = array_intersect($arSelect, array_keys($fieldsInfo));
			if (count($selectedFields) > 0)
			{
				$arStatus = $arResult;
				$arResult = array();
				foreach ($arStatus as $row)
				{
					$newRow = array();
					foreach ($selectedFields as $fieldName)
						$newRow[$fieldName] = $row[$fieldName];
					$arResult[] = $newRow;
				}
			}

		}

		return $arResult;
	}

	protected static function ensureLanguageDefined()
	{
		if (empty(self::$languageID))
		{
			$arFilter = array('=LID' => SITE_ID, '=ACTIVE' => 'Y');
			if (defined("ADMIN_SECTION"))
				$arFilter = array('=DEF' => 'Y', '=ACTIVE' => 'Y');

			self::$languageID = LANGUAGE_ID;
			$arLang = \Bitrix\Main\SiteTable::getRow(
				array('filter' => $arFilter, 'select' => array('LANGUAGE_ID'), 'limit' => 1)
			);
			if (is_array($arLang) && !empty($arLang['LANGUAGE_ID']))
				self::$languageID = $arLang['LANGUAGE_ID'];

			if (empty(self::$languageID))
				self::$languageID = 'en';
		}
	}

	/**
	 * Adds new sale order status
	 * @param array $arFields Array with status properties.
	 * @param bool $bCheckStatusId Dummy
	 * @return int|bool Returns the new status id or false if addition failed.
	 */
	public function Add($arFields, $bCheckStatusId = true)
	{
		if (!CModule::IncludeModule('sale'))
		{
			return false;
		}

		if(!isset($arFields['NAME']))
			return false;

		$arStatus = array(
					'ID' => self::getNewId(),
					'LANG' => self::getStatusLang($arFields['NAME'])
			);

		if(isset($arFields['SORT']))
			$arStatus['SORT'] = $arFields['SORT'];

		$result = CSaleStatus::Add($arStatus);
		if(is_string($result))
		{
			$result = ord($result);
		}

		self::$statusList = null;

		return $result;
	}

	/**
	 * Updates sale order status
	 * @param int $statusId Updated status ID
	 * @param array $arFields Array with status properties.
	 * @param array $arOptions Array with option.
	 * @return int|bool Returns the updated status id or false if updating failed.
	 */
	public function Update($statusId, $arFields, $arOptions = array())
	{
		if (!CModule::IncludeModule('sale'))
		{
			return false;
		}

		$statusId = intval($statusId);
		if ($statusId === ($statusId & 0xFF) && $statusId >= 65 && $statusId <= 90)
			$statusId = chr($statusId);
		else
			return false;

		if (isset($arFields['NAME']))
		{
			$arStatusFields = array(
						'LANG' => self::getStatusLang($arFields['NAME'], $statusId)
				);
		}

		if(isset($arFields['SORT']))
			$arStatusFields['SORT'] = $arFields['SORT'];

		self::$statusList = null;

		return CSaleStatus::Update($statusId, $arStatusFields);
	}

	/**
	 * Deletes sale order status
	 * @param int $statusId Status ID
	 * @return bool Deletion success.s
	 */
	public function Delete($statusId)
	{
		if (!CModule::IncludeModule('sale'))
		{
			return false;
		}

		$statusId = intval($statusId);
		if ($statusId === ($statusId & 0xFF) && $statusId >= 65 && $statusId <= 90)
			$statusId = chr($statusId);
		else
			return false;

		self::$statusList = null;

		return CSaleStatus::Delete($statusId);
	}

	/**
	 * Returns statuses
	 * @param string $entityId Wich statuses we interested for?
	 * @param string $internalOnly Dummy
	 * @return array Statuses list
	 */
	public static function GetStatus($entityId, $internalOnly = false)
	{
		return self::getStatusList();
	}

	public static function getStatusIds($statusType)
	{
		$result = array();

		if (!in_array($statusType, array('success', 'failed', 'neutral'), true))
			return $result;

		$statuses = self::getStatusList();
		if ($statusType === 'success')
		{
			$result[] = 'P';
		}
		else if ($statusType === 'failed')
		{
			$check = false;
			foreach($statuses as $statusId => $statusInfo)
			{
				if ($check)
					$result[] = $statusId;
				if ($statusId === 'P')
					$check = true;
			}
			unset($check);
		}
		else if ($statusType === 'neutral')
		{
			foreach($statuses as $statusId => $statusInfo)
			{
				if ($statusId === 'P')
					break;
				$result[] = $statusId;
			}
		}

		return $result;
	}

	public static function isStatusFailed($statusId)
	{
		$arStatuses = self::getStatusList();
		if ($arStatuses[$statusId]['SORT'] >= $arStatuses['D']['SORT'])
			return true;
		return false;
	}

	public static function isStatusNeutral($statusId)
	{
		$arStatuses = self::getStatusList();
		if ($arStatuses[$statusId]['SORT'] < $arStatuses['P']['SORT'])
			return true;
		return false;
	}

	public static function isStatusSuccess($statusId)
	{
		return ($statusId === 'P') ? true : false;
	}

	/**
	 * Returns array with status name on all site languages
	 * @param string $name Status name
	 * @param int $statusId (optional) Status ID needed if we updating status.
	 * @return array Array of status names.
	 */
	private function getStatusLang($name, $statusId = false)
	{
		if (!CModule::IncludeModule('sale'))
		{
			return false;
		}

		$arStatusLang = array();
		$by = "sort";
		$order = "asc";
		$dbLang = CLangAdmin::GetList($by, $order, array("ACTIVE" => "Y"));

		self::ensureLanguageDefined();

		while ($arLang = $dbLang->Fetch())
		{
			$statusName = '';

			if($statusId && $arLang["LID"] != self::$languageID)
			{
				$arLangStatus = CSaleStatus::GetLangByID($statusId, $arLang["LID"]);

				if($arLangStatus && isset($arLangStatus['NAME']))
					$statusName = $arLangStatus['NAME'];
			}

			if(strlen($statusName) <=0)
				$statusName = $name;

			$arStatusLang[] = array(
									'LID' => $arLang["LID"],
									'NAME' => $statusName
								);
		}

		return $arStatusLang;
	}

	/**
	 * Returns new unique ID for sale status.
	 * @return string(1)
	 */
	private function getNewId()
	{
		do
		{
			$newId = chr(rand(65, 90)); //A-Z
		}
		while(self::isIdExist($newId));

		return $newId;
	}

	/**
	 * Checks if status with ID alredy exist
	 */
	private function isIdExist($statusId)
	{
		$statusList = self::getStatusList();

		return isset($statusList[$statusId]);
	}

	/**
	 * Returns object of CCrmStatusInvoice type.
	 * This method must be called by event: OnBeforeCrmStatusCreate from crm.config.status/component.php
	 * RegisterModuleDependences('crm', 'OnBeforeCrmStatusCreate', 'crm', 'CCrmStatusInvoice', 'createCrmStatus');
	 * @param string $entityId Wich entity created object
	 * @return CCrmStatusInvoice Status object
	 */
	public function createCrmStatus($entityId)
	{
		if($entityId != "INVOICE_STATUS")
			return false;

		return new CCrmStatusInvoice($entityId);
	}

	/**
	 * Returns status list
	 * This method must be called by event OnCrmStatusGetList from crm.config.status/component.php
	 * RegisterModuleDependences('crm', 'OnCrmStatusGetList', 'crm', 'CCrmStatusInvoice', 'getStatusList');
	 * @return array Status list
	 */
	public static function getStatusList($entityId = 'INVOICE_STATUS', $internalOnly = false)
	{
		if (!CModule::IncludeModule('sale'))
		{
			return array();
		}

		if($entityId != 'INVOICE_STATUS')
			return array();

		if (self::$statusList === null)
		{
			$arStatus = array();

			self::ensureLanguageDefined();

			$res = CSaleStatus::GetList(
				array('SORT' => 'ASC'),
				array('LID' => self::$languageID), false, false, array('ID', 'SORT', 'NAME')
			);

			while ($row = $res->Fetch())
			{
				if ($row['ID'] === 'F') continue;

				$arStatus[$row['ID']] = array(
					'ID' => ord($row['ID']),
					'ENTITY_ID' => 'INVOICE_STATUS',
					'STATUS_ID' => $row['ID'],
					'NAME' => $row['NAME'],
					'NAME_INIT' => '',
					'SORT' => $row['SORT'],
					'SYSTEM' => 'N'
				);

				if (in_array($row['ID'], array('N', 'P', 'F', 'D')))
				{
					if ($row['ID'] === 'F')
						$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_F');
					elseif ($row['ID'] === 'D')
						$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_D');
					elseif ($row['ID'] === 'N')
						$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_N');
					elseif ($row['ID'] === 'P')
						$arStatus[$row['ID']]['NAME_INIT'] = GetMessage('CRM_INVOICE_STATUSN_P');

					$arStatus[$row['ID']]['SYSTEM'] = 'Y';
				}
			}

			self::$statusList = $arStatus;
		}

		return self::$statusList;
	}
	public function GetStatusById($statusId)
	{
		$statusId = intval($statusId);
		if ($statusId === ($statusId & 0xFF) && $statusId >= 65 && $statusId <= 90)
			$statusId = chr($statusId);
		else
			return false;

		$arStatus = self::getByID($statusId);
		if(is_array($arStatus))
		{
			return array(
				'ID' => ord($arStatus['ID']),
				'ENTITY_ID' => 'INVOICE_STATUS',
				'STATUS_ID' => $arStatus['ID'],
				'NAME' => $arStatus['NAME'],
				'NAME_INIT' => '',
				'SORT' => $arStatus['SORT'],
				'SYSTEM' => 'N'
			);
		}

		return false;
	}
	public static function getByID($statusID)
	{
		self::ensureLanguageDefined();
		return CSaleStatus::GetByID($statusID, self::$languageID);
	}


	public static function prepareStatusEntityInfos(array &$entityTypes)
	{
		$entityTypes['INVOICE_STATUS'] = array(
			'ID' =>'INVOICE_STATUS',
			'NAME' => GetMessage('CRM_STATUS_TYPE_INVOICE_STATUS'),
			'SEMANTIC_INFO' => array(
				'START_FIELD' => 'N',
				'FINAL_SUCCESS_FIELD' => 'P',
				'FINAL_UNSUCCESS_FIELD' => 'D',
				'FINAL_SORT' => 0
			)
		);
	}

	/**
	 * Adds Entity type to the list
	 * Method must be called by event OnGetEntityTypes from CCrmStatus::GetEntityTypes()
	 * RegisterModuleDependences('crm', 'OnGetEntityTypes', 'crm', 'CCrmStatusInvoice', 'onGetEntityTypes');
	 * @param array $arEntityType List of the entities types
	 * @return array List of the entities types
	 */
	public function onGetEntityTypes($arEntityType)
	{
		return $arEntityType;
	}
}
?>