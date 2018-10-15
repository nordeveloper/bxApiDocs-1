<?php
namespace Bitrix\Crm\Controller;

use Bitrix\Main;
use Bitrix\Crm;

class UserField extends Main\Engine\Controller
{
	/** @var \CCrmPerms|null  */
	private static $userPermissions = null;
	/** @var \CCrmFields[]|null */
	private static $userFieldEntities = null;
	/** @var array|null */
	private static $languageIDs = null;

	public static function resolveUserFieldEntityID($entityTypeID)
	{
		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::GetUserFieldEntityID();
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal)
		{
			return \CCrmDeal::GetUserFieldEntityID();
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::GetUserFieldEntityID();
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::GetUserFieldEntityID();
		}
		elseif($entityTypeID === \CCrmOwnerType::Quote)
		{
			return \CCrmQuote::GetUserFieldEntityID();
		}
		elseif($entityTypeID === \CCrmOwnerType::Invoice)
		{
			return \CCrmInvoice::GetUserFieldEntityID();
		}

		return '';
	}

	public static function resolveEntityTypeID($userFieldEntityID)
	{
		if($userFieldEntityID === \CCrmLead::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Lead;
		}
		elseif($userFieldEntityID === \CCrmDeal::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Deal;
		}
		elseif($userFieldEntityID === \CCrmContact::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Contact;
		}
		elseif($userFieldEntityID === \CCrmCompany::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Company;
		}
		elseif($userFieldEntityID === \CCrmQuote::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Quote;
		}
		elseif($userFieldEntityID === \CCrmInvoice::GetUserFieldEntityID())
		{
			return \CCrmOwnerType::Invoice;
		}

		return \CCrmOwnerType::Undefined;
	}
	protected static function getCurrentUserPermissions()
	{
		if(self::$userPermissions === null)
		{
			self::$userPermissions = \CCrmPerms::GetCurrentUserPermissions();
		}
		return self::$userPermissions;
	}
	protected static function getLanguageIDs()
	{
		if(self::$languageIDs === null)
		{
			$dbResult = \CLanguage::GetList($by = '', $order = '');
			while($arLang = $dbResult->Fetch())
			{
				self::$languageIDs[] = $arLang['LID'];
			}
		}
		return self::$languageIDs;
	}
	protected static function getUserFieldEntity($entityTypeID)
	{
		global $USER_FIELD_MANAGER;

		$userFieldEntityID = self::resolveUserFieldEntityID($entityTypeID);
		if($userFieldEntityID === '')
		{
			return null;
		}

		if(self::$userFieldEntities === null)
		{
			self::$userFieldEntities = array();
		}

		if(isset(self::$userFieldEntities[$userFieldEntityID]))
		{
			return self::$userFieldEntities[$userFieldEntityID];
		}

		return (self::$userFieldEntities[$userFieldEntityID] = new \CCrmFields($USER_FIELD_MANAGER, $userFieldEntityID));
	}

	//BX.ajax.runAction("crm.api.userField.setSharedLabel", { data: { userFieldEntityType: "CRM_LEAD", fieldName: "UF_CRM_1519828243", label: "My String #1" } });
	public function setSharedLabelAction($userFieldEntityType, $fieldName, $label)
	{
		if(!\CCrmAuthorizationHelper::CheckConfigurationUpdatePermission(self::getCurrentUserPermissions()))
		{
			return false;
		}

		$entityTypeID = self::resolveEntityTypeID($userFieldEntityType);
		if($entityTypeID === \CCrmOwnerType::Undefined)
		{
			return false;
		}

		$langLabels = array_fill_keys(self::getLanguageIDs(), $label);

		$parentField = Crm\Conversion\ConversionManager::getParentalField($entityTypeID, $fieldName);
		$daughterlyFields = Crm\Conversion\ConversionManager::getConcernedFields(
			$parentField['ENTITY_TYPE_ID'],
			$parentField['FIELD_NAME']
		);

		$userFieldEntity = self::getUserFieldEntity($parentField['ENTITY_TYPE_ID']);
		if($userFieldEntity)
		{
			$field = $userFieldEntity->GetByName($parentField['FIELD_NAME']);
			if($field)
			{
				$userFieldEntity->UpdateField(
					$field['ID'],
					array(
						'EDIT_FORM_LABEL' => $langLabels,
						'LIST_COLUMN_LABEL' => $langLabels,
						'LIST_FILTER_LABEL' => $langLabels
					)
				);
			}
		}

		foreach($daughterlyFields as $daughterlyField)
		{
			$userFieldEntity = self::getUserFieldEntity($daughterlyField['ENTITY_TYPE_ID']);
			if($userFieldEntity)
			{
				$field = $userFieldEntity->GetByName($daughterlyField['FIELD_NAME']);
				if($field)
				{
					$userFieldEntity->UpdateField(
						$field['ID'],
						array(
							'EDIT_FORM_LABEL' => $langLabels,
							'LIST_COLUMN_LABEL' => $langLabels,
							'LIST_FILTER_LABEL' => $langLabels
						)
					);
				}
			}
		}

		return true;
	}
	//BX.ajax.runAction("crm.api.userField.synchronizeEnumeration", { data: { userFieldEntityType: "CRM_LEAD", fieldName: "UF_CRM_1529916647" });
	public function synchronizeEnumerationAction($userFieldEntityType, $fieldName)
	{
		return $this->synchronizeEnumeration($userFieldEntityType, $fieldName);
	}
	public function synchronizeEnumeration($userFieldEntityType, $fieldName)
	{
		if(!\CCrmAuthorizationHelper::CheckConfigurationUpdatePermission(self::getCurrentUserPermissions()))
		{
			return false;
		}

		$entityTypeID = self::resolveEntityTypeID($userFieldEntityType);
		if($entityTypeID === \CCrmOwnerType::Undefined)
		{
			return false;
		}

		$srcMap = self::prepareEnumerationMap($entityTypeID, $fieldName);
		if(!is_array($srcMap))
		{
			return false;
		}

		$srcKeys = array_keys($srcMap);

		$userFieldEntity = self::getUserFieldEntity($entityTypeID);
		if(!$userFieldEntity)
		{
			return false;
		}

		$userField = $userFieldEntity->GetByName($fieldName);
		if(!(is_array($userField) && isset($userField['USER_TYPE_ID']) && $userField['USER_TYPE_ID'] === 'enumeration'))
		{
			return false;
		}

		$originField = Crm\Conversion\ConversionManager::getParentalField($entityTypeID, $fieldName);
		$dstFields = array_merge(
			array($originField),
			Crm\Conversion\ConversionManager::getConcernedFields($originField['ENTITY_TYPE_ID'], $originField['FIELD_NAME'])
		);

		foreach($dstFields as $dstField)
		{
			if($entityTypeID === $dstField['ENTITY_TYPE_ID'] && $fieldName === $dstField['FIELD_NAME'])
			{
				continue;
			}

			$dstUserFieldEntity = self::getUserFieldEntity($dstField['ENTITY_TYPE_ID']);
			if(!$dstUserFieldEntity)
			{
				continue;
			}

			$dstUserField = $dstUserFieldEntity->GetByName($dstField['FIELD_NAME']);
			if(!(is_array($dstUserField) && isset($dstUserField['USER_TYPE_ID']) && $dstUserField['USER_TYPE_ID'] === 'enumeration'))
			{
				continue;
			}

			$dstMap = self::prepareEnumerationMap($dstField['ENTITY_TYPE_ID'], $dstField['FIELD_NAME']);
			if(!is_array($dstMap))
			{
				continue;
			}

			$dstKeys = array_keys($dstMap);
			$isChanged = false;

			//region Update & Deletion
			$keysToUpdate = array_fill_keys(array_intersect($srcKeys, $dstKeys), true);
			$keysToDelete = array_fill_keys(array_diff($dstKeys, $srcKeys), true);

			for($i = 0, $length = count($dstKeys); $i < $length; $i++)
			{
				$key = $dstKeys[$i];

				if(!isset($keysToDelete[$key]) && !isset($keysToUpdate[$key]))
				{
					continue;
				}

				if(isset($keysToUpdate[$key]))
				{
					if($dstMap[$key]['SORT'] != $srcMap[$key]['SORT'])
					{
						$dstMap[$key]['SORT'] = $srcMap[$key]['SORT'];
						if(!$isChanged)
						{
							$isChanged = true;
						}
					}
				}
				else// if(isset($keysToDelete[$key]))
				{
					$dstMap[$key]['DEL'] = 'Y';
					if(!$isChanged)
					{
						$isChanged = true;
					}
				}
			}
			//endregion

			//region Creation
			$keysToCreate = array_values(array_diff($srcKeys, $dstKeys));

			for($i = 0, $length = count($keysToCreate); $i < $length; $i++)
			{
				$key = $keysToCreate[$i];

				$dstMap[$key] = $srcMap[$key];
				$dstMap[$key]['ID'] = "n{$i}";
				unset($dstMap[$key]['USER_FIELD_ID'], $dstMap[$key]['XML_ID']);
				if(!$isChanged)
				{
					$isChanged = true;
				}
			}
			//endregion

			if(!$isChanged)
			{
				continue;
			}

			$list = array();
			foreach($dstMap as $item)
			{
				$list[$item['ID']] = $item;
			}

			$dstUserFieldEntity->UpdateField(
				$dstUserField['ID'],
				array('USER_TYPE_ID' => 'enumeration', 'LIST' => $list)
			);
		}

		return true;
	}
	protected static function prepareEnumerationMap($entityTypeID, $fieldName)
	{
		$userFieldEntity = self::getUserFieldEntity($entityTypeID);
		if(!$userFieldEntity)
		{
			return null;
		}

		$field = $userFieldEntity->GetByName($fieldName);
		if(!$field)
		{
			return null;
		}

		$userFieldEnumEntity = new \CUserFieldEnum();
		$dbResult = $userFieldEnumEntity->GetList(array(), array('USER_FIELD_ID' => $field['ID']));
		$map = array();
		while($enumFields = $dbResult->Fetch())
		{
			$value = isset($enumFields['VALUE']) ? $enumFields['VALUE'] : '';
			if($value === '')
			{
				continue;
			}
			$map[md5($value)] = $enumFields;
		}
		;
		return $map;
	}
}