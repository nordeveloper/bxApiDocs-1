<?php
namespace Bitrix\Crm\Controller;
use Bitrix\Main;
use Bitrix\Main\Web\Uri;

use Bitrix\Crm;
use Bitrix\Crm\Search\SearchEnvironment;

class Entity extends Main\Engine\Controller
{
	//region Search
	//BX.ajax.runAction("crm.api.entity.search", { data: { search: "John Smith", options: { scope: 'denomination', types: [ BX.CrmEntityType.names.contact ] } } });
	public static function searchAction($search, $options)
	{
		if(!is_array($options))
		{
			$options = array();
		}

		//region Resolve Entity type IDs to Entity Type Names if required.
		$types = isset($options['types']) && is_array($options['types']) ? $options['types'] : array();
		for($i = 0, $length = count($types); $i < $length; $i++)
		{
			if(is_numeric($types[$i]))
			{
				$types[$i] = \CCrmOwnerType::ResolveName($types[$i]);
			}
		}
		//endregion

		$typeMap = array_fill_keys($types, true);
		$enableAllTypes = empty($typeMap);

		//region Resolve Search scope
		$scope = isset($options['scope'])
			? EntitySearchScope::resolveID($options['scope']) : EntitySearchScope::UNDEFINED;
		if($scope === EntitySearchScope::UNDEFINED)
		{
			$scope = EntitySearchScope::DENOMINATION;
		}
		//endregion

		$items = array();
		if($enableAllTypes || isset($typeMap[\CCrmOwnerType::LeadName]))
		{
			if($scope === EntitySearchScope::INDEX)
			{
				$filter = array('FIND' => $search);
				SearchEnvironment::convertEntityFilterValues(\CCrmOwnerType::Lead, $filter);
			}
			else //if($scope === EntitySearchScope::DENOMINATION)
			{
				$filter = array('LOGIC' => 'OR', '%FULL_NAME' => $search, '%TITLE' => $search);
			}

			$dbResult = \CCrmLead::GetListEx(
				array(),
				$filter,
				false,
				array('nTopCount' => 20),
				array('ID')
			);

			if(is_object($dbResult))
			{
				while($fields = $dbResult->Fetch())
				{
					$items[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Lead, 'ENTITY_ID' => $fields['ID']);
				}
			}
		}
		if($enableAllTypes || isset($typeMap[\CCrmOwnerType::ContactName]))
		{
			if($scope === EntitySearchScope::INDEX)
			{
				$filter = array('FIND' => $search);
				SearchEnvironment::convertEntityFilterValues(\CCrmOwnerType::Contact, $filter);
			}
			else //if($scope === EntitySearchScope::DENOMINATION)
			{
				$parts = preg_split ('/[\s]+/', $search, 2, PREG_SPLIT_NO_EMPTY);
				if(count($parts) < 2)
				{
					$filter = array('%FULL_NAME' => $search);
				}
				else
				{
					$filter = array('LOGIC' => 'AND');
					for($i = 0; $i < 2; $i++)
					{
						$filter["__INNER_FILTER_NAME_{$i}"] = array('%FULL_NAME' => $parts[$i]);
					}
				}
			}

			$dbResult = \CCrmContact::GetListEx(
				array(),
				$filter,
				false,
				array('nTopCount' => 20),
				array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
			);

			if(is_object($dbResult))
			{
				while($fields = $dbResult->Fetch())
				{
					$items[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Contact, 'ENTITY_ID' => $fields['ID']);
				}
			}
		}
		if($enableAllTypes || isset($typeMap[\CCrmOwnerType::CompanyName]))
		{
			if($scope === EntitySearchScope::INDEX)
			{
				$filter = array('FIND' => $search);
				SearchEnvironment::convertEntityFilterValues(\CCrmOwnerType::Company, $filter);
			}
			else //if($scope === EntitySearchScope::DENOMINATION)
			{
				$filter = array('%TITLE' => $search);
				$filter['=IS_MY_COMPANY'] = isset($options['isMyCompany'])
				&& strtoupper($options['isMyCompany']) === 'Y' ? 'Y' : 'N';
			}

			$dbResult = \CCrmCompany::GetListEx(
				array(),
				$filter,
				false,
				array('nTopCount' => 20),
				array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO')
			);

			if(is_object($dbResult))
			{
				while($fields = $dbResult->Fetch())
				{
					$items[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Company, 'ENTITY_ID' => $fields['ID']);
				}
			}
		}
		if($enableAllTypes || isset($typeMap[\CCrmOwnerType::DealName]))
		{
			if($scope === EntitySearchScope::INDEX)
			{
				$filter = array('FIND' => $search);
				SearchEnvironment::convertEntityFilterValues(\CCrmOwnerType::Deal, $filter);
			}
			else //if($scope === EntitySearchScope::DENOMINATION)
			{
				$filter = array('%TITLE' => $search);
			}

			$dbResult = \CCrmDeal::GetListEx(
				array(),
				$filter,
				false,
				array('nTopCount' => 20),
				array('ID', 'TITLE', 'COMPANY_TITLE', 'CONTACT_HONORIFIC', 'CONTACT_NAME', 'CONTACT_SECOND_NAME', 'CONTACT_LAST_NAME')
			);

			if(is_object($dbResult))
			{
				while($fields = $dbResult->Fetch())
				{
					$items[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Deal, 'ENTITY_ID' => $fields['ID']);
				}
			}
		}

		return self::prepareSearchResults($items);
	}
	public static function prepareSearchResults(array $items)
	{
		$map = array();
		$results = array();

		foreach($items as $item)
		{
			$entityTypeID = isset($item['ENTITY_TYPE_ID']) ? (int)$item['ENTITY_TYPE_ID'] : 0;
			$entityID = isset($item['ENTITY_ID']) ? (int)$item['ENTITY_ID'] : 0;

			if(!\CCrmOwnerType::IsDefined($entityTypeID) || $entityID <= 0)
			{
				continue;
			}

			$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
			if(!isset($map[$entityTypeName]))
			{
				$map[$entityTypeName] = array();
			}
			$map[$entityTypeName][] = $entityID;
		}

		foreach($map as $entityTypeName => $entityIDs)
		{
			if($entityTypeName === \CCrmOwnerType::LeadName)
			{
				$dbResult = \CCrmLead::GetListEx(
					array(),
					array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
				);
				if(is_object($dbResult))
				{

					while($fields = $dbResult->Fetch())
					{
						$entityID = (int)$fields['ID'];
						$item = array(
							'module' => 'crm',
							'entityType' => $entityTypeName,
							'entityId'  => $entityID,
							'title' => isset($fields['TITLE']) ? $fields['TITLE'] : '',
							'subtitle' => \CCrmLead::PrepareFormattedName($fields),
							'showUrl' => new Uri(
								\CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Lead, $entityID, false)
							)
						);

						$results["{$entityTypeName}:{$fields['ID']}"] = $item;
					}
				}
			}
			elseif($entityTypeName === \CCrmOwnerType::ContactName)
			{
				$dbResult = \CCrmContact::GetListEx(
					array(),
					array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
				);
				if(is_object($dbResult))
				{
					while($fields = $dbResult->Fetch())
					{
						$entityID = (int)$fields['ID'];
						$item = array(
							'module' => 'crm',
							'entityType' => $entityTypeName,
							'entityId'  => $entityID,
							'title' => \CCrmContact::PrepareFormattedName($fields),
							'subtitle' => isset($fields['COMPANY_TITLE']) ? $fields['COMPANY_TITLE'] : '',
							'showUrl' => new Uri(
								\CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Contact, $entityID, false)
							)
						);

						if(isset($fields['PHOTO']) && $fields['PHOTO'] > 0)
						{
							$fileInfo = \CFile::ResizeImageGet(
								$fields['PHOTO'],
								array('width' => 100, 'height' => 100),
								BX_RESIZE_IMAGE_EXACT
							);
							if(is_array($fileInfo))
							{
								$item['imageUrl'] = $fileInfo['src'];
							}
						}

						$results["{$entityTypeName}:{$fields['ID']}"] = $item;
					}
				}
			}
			elseif($entityTypeName === \CCrmOwnerType::CompanyName)
			{
				$dbResult = \CCrmCompany::GetListEx(
					array(),
					array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO')
				);
				if(is_object($dbResult))
				{
					$typeList = \CCrmStatus::GetStatusList('COMPANY_TYPE');
					$industryList = \CCrmStatus::GetStatusList('INDUSTRY');

					while($fields = $dbResult->Fetch())
					{
						$descriptions = array();
						if(isset($typeList[$fields['COMPANY_TYPE']]))
						{
							$descriptions[] = $typeList[$fields['COMPANY_TYPE']];
						}
						if(isset($industryList[$fields['INDUSTRY']]))
						{
							$descriptions[] = $industryList[$fields['INDUSTRY']];
						}

						$entityID = (int)$fields['ID'];
						$item = array(
							'module' => 'crm',
							'entityType' => $entityTypeName,
							'entityId' => $entityID,
							'title' => $fields['TITLE'],
							'subtitle' => implode(', ', $descriptions),
							'showUrl' => new Uri(
								\CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Company, $entityID, false)
							)
						);

						if(isset($fields['LOGO']) && $fields['LOGO'] > 0)
						{
							$fileInfo = \CFile::ResizeImageGet(
								$fields['LOGO'],
								array('width' => 100, 'height' => 100),
								BX_RESIZE_IMAGE_EXACT
							);
							if(is_array($fileInfo))
							{
								$item['imageUrl'] = $fileInfo['src'];
							}
						}

						$results["{$entityTypeName}:{$fields['ID']}"] = $item;
					}
				}
			}
			elseif($entityTypeName === \CCrmOwnerType::DealName)
			{
				$dbResult = \CCrmDeal::GetListEx(
					array(),
					array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'TITLE', 'COMPANY_TITLE', 'CONTACT_HONORIFIC', 'CONTACT_NAME', 'CONTACT_SECOND_NAME', 'CONTACT_LAST_NAME')
				);
				if(is_object($dbResult))
				{
					while($fields = $dbResult->Fetch())
					{
						$descriptions = array();
						if(isset($fields['COMPANY_TITLE']) && $fields['COMPANY_TITLE'] != '')
						{
							$descriptions[] = $fields['COMPANY_TITLE'];
						}

						$descriptions[] =\CCrmContact::PrepareFormattedName(
							array(
								'LOGIN' => '',
								'HONORIFIC' => isset($fields['CONTACT_HONORIFIC']) ? $fields['CONTACT_HONORIFIC'] : '',
								'NAME' => isset($fields['CONTACT_NAME']) ? $fields['CONTACT_NAME'] : '',
								'SECOND_NAME' => isset($fields['CONTACT_SECOND_NAME']) ? $fields['CONTACT_SECOND_NAME'] : '',
								'LAST_NAME' => isset($fields['CONTACT_LAST_NAME']) ? $fields['CONTACT_LAST_NAME'] : ''
							)
						);

						$entityID = (int)$fields['ID'];
						$item = array(
							'module' => 'crm',
							'entityType' => $entityTypeName,
							'entityId'  => $entityID,
							'title' => isset($fields['TITLE']) ? $fields['TITLE'] : '',
							'subtitle' => implode(', ', $descriptions),
							'showUrl' => new Uri(
								\CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Deal, $entityID, false)
							)
						);

						$results["{$entityTypeName}:{$fields['ID']}"] = $item;
					}
				}
			}
		}

		foreach($map as $entityTypeName => $entityIDs)
		{
			if($entityTypeName === \CCrmOwnerType::DealName)
			{
				continue;
			}

			$dbResult = \CCrmFieldMulti::GetListEx(
				array(),
				array(
					'=ENTITY_ID' => $entityTypeName,
					'@ELEMENT_ID' => $entityIDs,
					'@TYPE_ID' => array('PHONE' , 'EMAIL')
				)
			);

			while($fields = $dbResult->Fetch())
			{
				$key = "{$fields['ENTITY_ID']}:{$fields['ELEMENT_ID']}";
				if(!isset($results[$key]))
				{
					continue;
				}

				$typeKey = strtolower($fields['TYPE_ID']);
				if(!isset($results[$key][$typeKey]))
				{
					$results[$key][$typeKey] = array();
				}

				$results[$key][$typeKey][] = array(
					'type' => $fields['VALUE_TYPE'],
					'value' => $fields['VALUE']
				);
			}
		}

		$results = array_values($results);
		Main\Type\Collection::sortByColumn($results, array('title' => SORT_ASC));
		return $results;
	}
	//endregion

	//region LRU
	/**
	 * Add items to LRU items.
	 * @param string $category Category name (it's used for saving user option).
	 * @param string $code Code (it's used for saving user option).
	 * @param array $items Source items.
	 */
	public static function addLastRecentlyUsedItems($category, $code, array $items)
	{
		$values = array();
		foreach($items as $item)
		{
			$entityTypeID = isset($item['ENTITY_TYPE_ID']) ? (int)$item['ENTITY_TYPE_ID'] : 0;
			$entityID = isset($item['ENTITY_ID']) ? (int)$item['ENTITY_ID'] : 0;

			if(\CCrmOwnerType::IsDefined($entityTypeID) && $entityID > 0)
			{
				$values[] = "{$entityTypeID}:{$entityID}";
			}
		}

		$values = array_unique(
			array_merge(
				self::getRecentlyUsedItems($category, $code, array('RAW_FORMAT' => true)),
				array_values($values)
			)
		);

		$qty = count($values);
		if($qty > 20)
		{
			$values = array_slice($values, $qty - 20);
		}

		\CUserOptions::SetOption($category, $code, $values);
	}

	/**
	 * Get LRU items.
	 * @param string $category Category name (it's used for saving user option).
	 * @param string $code Code (it's used for saving user option).
	 * @param array|null $options Options.
	 * @return array|bool
	 */
	public static function getRecentlyUsedItems($category, $code, array $options = null)
	{
		if(!is_array($options))
		{
			$options = array();
		}

		$values = \CUserOptions::GetOption($category, $code, array());
		if(!is_array($values))
		{
			$values = array();
		}

		if(isset($options['RAW_FORMAT']) && $options['RAW_FORMAT'] === true)
		{
			return $values;
		}

		$items = array();
		foreach($values as $value)
		{
			if(!is_string($value))
			{
				continue;
			}

			$parts = explode(':', $value);
			if(count($parts) > 1)
			{
				$items[] = array('ENTITY_TYPE_ID' => (int)$parts[0], 'ENTITY_ID' => (int)$parts[1]);
			}
		}

		$qty = count($items);
		if($qty < 20 && isset($options['EXPAND_ENTITY_TYPE_ID']))
		{
			self::expandItems($items, $options['EXPAND_ENTITY_TYPE_ID'], 20 - $qty);
		}

		return $items;
	}
	/**
	 * Expand source items by recently created items of specified entity type.
	 * @param array $items Source items.
	 * @param int $entityTypeID Entity Type ID.
	 * @param int $limit Limit of new items.
	 */
	protected static function expandItems(array &$items, $entityTypeID, $limit = 20)
	{
		$map = array();
		foreach($items as $item)
		{
			$entityTypeID = isset($item['ENTITY_TYPE_ID']) ? (int)$item['ENTITY_TYPE_ID'] : 0;
			$entityID = isset($item['ENTITY_ID']) ? (int)$item['ENTITY_ID'] : 0;

			if(!\CCrmOwnerType::IsDefined($entityTypeID) || $entityID <= 0)
			{
				continue;
			}

			$map["{$entityTypeID}:{$entityID}"] = $item;
		}

		$userPermissions = \CCrmPerms::GetCurrentUserPermissions();
		$entityIDs = null;
		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			$entityIDs = \CCrmLead::GetTopIDs($limit, 'DESC', $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			$entityIDs = \CCrmContact::GetTopIDs($limit, 'DESC', $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			$entityIDs = \CCrmCompany::GetTopIDs($limit, 'DESC', $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal)
		{
			$entityIDs = \CCrmDeal::GetTopIDs($limit, 'DESC', $userPermissions);
		}

		if(!is_array($entityIDs))
		{
			return;
		}

		foreach($entityIDs as $entityID)
		{
			$key = "{$entityTypeID}:{$entityID}";
			if(isset($map[$key]))
			{
				continue;
			}

			$map[$key] = array('ENTITY_TYPE_ID' => $entityTypeID, 'ENTITY_ID' => (int)$entityID);
		}

		$items = array_values($map);
	}
	//endregion

	//region Delete
	public static function deleteAction(array $params)
	{
		$gridID = isset($params['GRID_ID']) ? $params['GRID_ID'] : '';
		$entityTypeID = isset($params['ENTITY_TYPE_ID']) ? (int)$params['ENTITY_TYPE_ID'] : \CCrmOwnerType::Undefined;
		$entityIDs = isset($params['ENTITY_IDS']) && is_array($params['ENTITY_IDS']) ? $params['ENTITY_IDS'] : array();

		if(!empty($entityIDs))
		{
			return self::deleteByIDs($gridID, $entityTypeID, $entityIDs);
		}

		$filterFields = isset($params['FILTER']) && is_array($params['FILTER']) ? $params['FILTER'] : array();
		if(empty($filterFields))
		{
			$filterOptions = new Main\UI\Filter\Options($gridID);
			$filterFields = $filterOptions->getFilter();
			$entityFilter = Crm\Filter\Factory::createEntityFilter(
				Crm\Filter\Factory::createEntitySettings($entityTypeID, $gridID)
			);

			$entityFilter->prepareListFilterParams($filterFields);
			Crm\Search\SearchEnvironment::convertEntityFilterValues($entityTypeID, $filterFields);

			\CCrmEntityHelper::PrepareMultiFieldFilter($filterFields, array(), '=%', false);
		}
		$extras = isset($params['EXTRAS']) && is_array($params['EXTRAS']) ? $params['EXTRAS'] : array();
		return self::deleteByFilter($gridID, $entityTypeID, $filterFields, $extras);
	}
	public static function deleteByIDs($gridID, $entityTypeID, array $entityIDs)
	{
		sort($entityIDs, SORT_NUMERIC);
		$hash = md5(
			\CCrmOwnerType::ResolveName($entityTypeID).':'.strtoupper($gridID).':'.implode(',', $entityIDs)
		);

		if(!isset($_SESSION['CRM_ENTITY_DELETION_DATA']))
		{
			$_SESSION['CRM_ENTITY_DELETION_DATA'] = array();
		}

		$progressData = isset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID])
			? $_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] : null;

		if(is_array($progressData) && !(isset($progressData['HASH']) && $progressData['HASH'] === $hash))
		{
			unset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID], $progressData);
			$progressData = null;
		}

		if(!is_array($progressData))
		{
			$progressData = array(
				'HASH' => $hash,
				'ENTITY_TYPE_ID' => $entityTypeID,
				'ENTITY_IDS' => $entityIDs,
				'CURRENT_ENTITY_INDEX' => 0,
				'PROCESSED_COUNT' => 0
			);
			$_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] = $progressData;
		}

		$currentEntityIndex = isset($progressData['CURRENT_ENTITY_INDEX'])
			? (int)$progressData['CURRENT_ENTITY_INDEX'] : 0;
		$processedCount = isset($progressData['PROCESSED_COUNT'])
			? (int)$progressData['PROCESSED_COUNT'] : 0;

		$userPermissions = Crm\Security\EntityAuthorization::getUserPermissions(
			Crm\Security\EntityAuthorization::getCurrentUserID()
		);

		$entity = Crm\Entity\EntityManager::resolveByTypeID($entityTypeID);

		$totalCount = count($entityIDs);
		if($currentEntityIndex < 0)
		{
			$currentEntityIndex = 0;
		}
		for($i = 0; $i < 10; $i++)
		{
			if($currentEntityIndex >= $totalCount)
			{
				break;
			}

			$currentEntityID = $entityIDs[$currentEntityIndex];
			if($currentEntityID > 0 && $entity->checkDeletePermission($currentEntityID, $userPermissions))
			{
				$entity->delete($currentEntityID);
			}

			$processedCount++;
			$currentEntityIndex++;
		}

		$isInProgress = $processedCount < $totalCount;
		if($isInProgress)
		{
			$progressData['PROCESSED_COUNT'] = $processedCount;
			$progressData['CURRENT_ENTITY_INDEX'] = $currentEntityIndex;

			$_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] = $progressData;
		}
		else
		{
			unset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID]);
		}

		return array(
			'STATUS' => $isInProgress ? 'PROGRESS' : 'COMPLETED',
			'PROCESSED_ITEMS' => $processedCount,
			'TOTAL_ITEMS' => $totalCount
		);
	}
	public static function deleteByFilter($gridID, $entityTypeID, array $filterFields, array $extras)
	{
		$entity = Crm\Entity\EntityManager::resolveByTypeID($entityTypeID);
		if($entityTypeID === \CCrmOwnerType::Deal)
		{
			if(isset($extras['CATEGORY_ID']) && $extras['CATEGORY_ID'] >= 0)
			{
				$filterFields['CATEGORY_ID'] = (int)$extras['CATEGORY_ID'];
			}

			$filterFields['IS_RECURRING'] = isset($extras['IS_RECURRING']) && $extras['IS_RECURRING'] === 'Y' ? 'Y' : 'N';
		}

		ksort($filterFields, SORT_STRING);
		$hash = md5(
			\CCrmOwnerType::ResolveName($entityTypeID)
			.':'.strtoupper($gridID)
			.':'.implode(',', array_map(function($k, $v){ return "{$k}:{$v}"; }, array_keys($filterFields), $filterFields))
		);

		if(!isset($_SESSION['CRM_ENTITY_DELETION_DATA']))
		{
			$_SESSION['CRM_ENTITY_DELETION_DATA'] = array();
		}

		$progressData = isset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID])
			? $_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] : null;

		if(is_array($progressData) && !(isset($progressData['HASH']) && $progressData['HASH'] === $hash))
		{
			unset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID], $progressData);
			$progressData = null;
		}

		if(!is_array($progressData))
		{
			$totalCount = $entity->getCount(array('filter' => $filterFields));
			$progressData = array(
				'HASH' => $hash,
				'ENTITY_TYPE_ID' => $entityTypeID,
				'FILTER' => $filterFields,
				'CURRENT_ENTITY_ID' => 0,
				'PROCESSED_COUNT' => 0,
				'TOTAL_COUNT' => $totalCount
			);
			$_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] = $progressData;
		}

		$currentEntityID = isset($progressData['CURRENT_ENTITY_ID'])
			? (int)$progressData['CURRENT_ENTITY_ID'] : 0;
		$processedCount = isset($progressData['PROCESSED_COUNT'])
			? (int)$progressData['PROCESSED_COUNT'] : 0;
		$totalCount = isset($progressData['TOTAL_COUNT'])
			? (int)$progressData['TOTAL_COUNT'] : 0;

		$userPermissions = Crm\Security\EntityAuthorization::getUserPermissions(
			Crm\Security\EntityAuthorization::getCurrentUserID()
		);

		$entityFilter = $filterFields;
		if($currentEntityID > 0)
		{
			$entityFilter['>ID'] = $currentEntityID;
		}

		$entityIDs = $entity->getTopIDs(
			array(
				'order' => array('ID' => 'ASC'),
				'filter' => $entityFilter,
				'limit' => 10
			)
		);

		$errors = array();
		if(!empty($entityIDs))
		{
			foreach($entityIDs as $entityID)
			{
				$currentEntityID = $entityID;
				if($entity->checkDeletePermission($currentEntityID, $userPermissions))
				{
					$error = $entity->delete($currentEntityID);
					if(is_array($error))
					{
						\CCrmOwnerType::TryGetEntityInfo(
							$entityTypeID,
							$currentEntityID,
							$entityInfo,
							false
						);
						$error['INFO'] = $entityInfo;
						$errors[] = $error;
					}
				}
				$processedCount++;
			}
		}
		elseif($processedCount !== $totalCount)
		{
			$processedCount = $totalCount;
		}

		$isInProgress = $processedCount < $totalCount;
		if($isInProgress)
		{
			$progressData['PROCESSED_COUNT'] = $processedCount;
			$progressData['CURRENT_ENTITY_ID'] = $currentEntityID;

			$_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID] = $progressData;
		}
		else
		{
			unset($_SESSION['CRM_ENTITY_BATCH_DELETION_DATA'][$gridID]);
		}

		$resultData = array(
			'STATUS' => $isInProgress ? 'PROGRESS' : 'COMPLETED',
			'PROCESSED_ITEMS' => $processedCount,
			'TOTAL_ITEMS' => $totalCount
		);

		if(!empty($errors))
		{
			$resultData['ERRORS'] = $errors;
		}
		return $resultData;
	}
	//endregion
}