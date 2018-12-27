<?php
namespace Bitrix\Crm\Synchronization;

use Bitrix\Main;
use Bitrix\Crm\UserField\UserFieldManager;
use Bitrix\Crm\Conversion\ConversionManager;

class UserFieldEnumerationSynchronizer extends UserFieldCustomSynchronizer
{
	/** @var array */
	private static $synchronizedFields = array();

	protected static function isFieldLocked($fieldID)
	{
		return isset(self::$synchronizedFields[$fieldID]);
	}
	protected static function lockField($fieldID)
	{
		self::$synchronizedFields[$fieldID] = true;
	}
	protected static function unlockField($fieldID)
	{
		unset(self::$synchronizedFields[$fieldID]);
	}

	protected static function prepareValueMap($entityTypeID, $fieldName)
	{
		$userFieldEntity = UserFieldManager::getUserFieldEntity($entityTypeID);
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
	public static function synchronize($userFieldEntityType, $fieldName, array $params = null)
	{
		$entityTypeID = UserFieldManager::resolveEntityTypeID($userFieldEntityType);
		if($entityTypeID === \CCrmOwnerType::Undefined)
		{
			return false;
		}

		$userFieldEntity = UserFieldManager::getUserFieldEntity($entityTypeID);
		if(!$userFieldEntity)
		{
			return false;
		}

		$userField = $userFieldEntity->GetByName($fieldName);
		if(!(is_array($userField) && isset($userField['USER_TYPE_ID']) && $userField['USER_TYPE_ID'] === 'enumeration'))
		{
			return false;
		}

		//Skip locked field (for prevent loop)
		if(self::isFieldLocked($userField['ID']))
		{
			return false;
		}

		$srcMap = self::prepareValueMap($entityTypeID, $fieldName);
		if(!is_array($srcMap))
		{
			return false;
		}

		$srcKeys = array_keys($srcMap);

		$originField = ConversionManager::getParentalField($entityTypeID, $fieldName);
		$dstFields = array_merge(
			array($originField),
			ConversionManager::getConcernedFields($originField['ENTITY_TYPE_ID'], $originField['FIELD_NAME'])
		);

		foreach($dstFields as $dstField)
		{
			if($entityTypeID === $dstField['ENTITY_TYPE_ID'] && $fieldName === $dstField['FIELD_NAME'])
			{
				continue;
			}

			$dstUserFieldEntity = UserFieldManager::getUserFieldEntity($dstField['ENTITY_TYPE_ID']);
			if(!$dstUserFieldEntity)
			{
				continue;
			}

			$dstUserField = $dstUserFieldEntity->GetByName($dstField['FIELD_NAME']);
			if(!(is_array($dstUserField) && isset($dstUserField['USER_TYPE_ID']) && $dstUserField['USER_TYPE_ID'] === 'enumeration'))
			{
				continue;
			}

			$dstMap = self::prepareValueMap($dstField['ENTITY_TYPE_ID'], $dstField['FIELD_NAME']);
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

			$ID = $dstUserField['ID'];
			self::lockField($ID);
			$dstUserFieldEntity->UpdateField($ID, array('USER_TYPE_ID' => 'enumeration', 'LIST' => $list));
			self::unlockField($ID);
		}

		return true;
	}
	public static function onSetEnumerationValues($event)
	{
		if(!($event instanceof Main\Event))
		{
			return;
		}

		$params = $event->getParameters();
		$ID = is_array($params) && isset($params[0]) ? (int)$params[0] : 0;

		//Skip locked field (for prevent loop)
		if($ID <= 0 || self::isFieldLocked($ID))
		{
			return;
		}

		$fields = \CUserTypeEntity::GetByID($ID);
		if(is_array($fields) && isset($fields['ENTITY_ID']) && isset($fields['FIELD_NAME']))
		{
			self::synchronize($fields['ENTITY_ID'], $fields['FIELD_NAME']);
		}
	}
}
