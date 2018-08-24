<?php
namespace Bitrix\Crm\Security;
class EntityAuthorization
{
	public static function IsAuthorized()
	{
		return \CCrmSecurityHelper::GetCurrentUser()->IsAuthorized();
	}

	public static function checkPermission($permissionTypeID, $entityTypeID, $entityID = 0, $userPermissions = null)
	{
		if(!is_int($permissionTypeID))
		{
			$permissionTypeID = (int)$permissionTypeID;
		}

		if($permissionTypeID === EntityPermissionType::CREATE)
		{
			return self::checkCreatePermission($entityTypeID, $userPermissions);
		}
		elseif($permissionTypeID === EntityPermissionType::READ)
		{
			return self::checkReadPermission($entityTypeID, $entityID, $userPermissions);
		}
		elseif($permissionTypeID === EntityPermissionType::UPDATE)
		{
			return self::checkUpdatePermission($entityTypeID, $entityID, $userPermissions);
		}
		elseif($permissionTypeID === EntityPermissionType::DELETE)
		{
			return self::checkDeletePermission($entityTypeID, $entityID, $userPermissions);
		}

		return false;
	}

	public static function checkCreatePermission($entityTypeID, $userPermissions = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::CheckCreatePermission($userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal || $entityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return \CCrmDeal::CheckCreatePermission($userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Quote)
		{
			return \CCrmQuote::CheckCreatePermission($userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Invoice)
		{
			return \CCrmInvoice::CheckCreatePermission($userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::CheckCreatePermission($userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::CheckCreatePermission($userPermissions);
		}

		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
		$permissionEntityType = \CCrmPerms::ResolvePermissionEntityType($entityTypeName);

		return \CCrmAuthorizationHelper::CheckCreatePermission(
			$permissionEntityType,
			$userPermissions
		);
	}

	public static function checkReadPermission($entityTypeID, $entityID, $userPermissions = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!is_int($entityID))
		{
			$entityID = (int)$entityID;
		}

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::CheckReadPermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal || $entityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return \CCrmDeal::CheckReadPermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Quote)
		{
			return \CCrmQuote::CheckReadPermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Invoice)
		{
			return \CCrmInvoice::CheckReadPermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::CheckReadPermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::CheckReadPermission($entityID, $userPermissions);
		}
		
		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
		$permissionEntityType = \CCrmPerms::ResolvePermissionEntityType($entityTypeName, $entityID);

		return \CCrmAuthorizationHelper::CheckReadPermission(
			$permissionEntityType,
			$entityID,
			$userPermissions
		);
	}

	public static function checkUpdatePermission($entityTypeID, $entityID, $userPermissions = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!is_int($entityID))
		{
			$entityID = (int)$entityID;
		}

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::CheckUpdatePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal || $entityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return \CCrmDeal::CheckUpdatePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Quote)
		{
			return \CCrmQuote::CheckUpdatePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Invoice)
		{
			return \CCrmInvoice::CheckUpdatePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::CheckUpdatePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::CheckUpdatePermission($entityID, $userPermissions);
		}

		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
		$permissionEntityType = \CCrmPerms::ResolvePermissionEntityType($entityTypeName, $entityID);

		return \CCrmAuthorizationHelper::CheckUpdatePermission(
			$permissionEntityType,
			$entityID,
			$userPermissions
		);
	}

	public static function checkDeletePermission($entityTypeID, $entityID, $userPermissions = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!is_int($entityID))
		{
			$entityID = (int)$entityID;
		}

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::CheckDeletePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal || $entityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return \CCrmDeal::CheckDeletePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Quote)
		{
			return \CCrmQuote::CheckDeletePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Invoice)
		{
			return \CCrmInvoice::CheckDeletePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::CheckDeletePermission($entityID, $userPermissions);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::CheckDeletePermission($entityID, $userPermissions);
		}

		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
		$permissionEntityType = \CCrmPerms::ResolvePermissionEntityType($entityTypeName, $entityID);

		return \CCrmAuthorizationHelper::CheckDeletePermission(
			$permissionEntityType,
			$entityID,
			$userPermissions
		);
	}

	public static function getPermissionAttributes($entityTypeID, array $entityIDs)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		$entityIDs = array_unique(array_filter($entityIDs));

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return \CCrmLead::GetPermissionAttributes($entityIDs);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal || $entityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return \CCrmDeal::GetPermissionAttributes($entityIDs);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return \CCrmContact::GetPermissionAttributes($entityIDs);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return \CCrmCompany::GetPermissionAttributes($entityIDs);
		}

		$permissionEntityMap = array();
		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
		foreach($entityIDs as $entityID)
		{
			$permissionEntityType = \CCrmPerms::ResolvePermissionEntityType($entityTypeName, $entityID);
			if(!isset($permissionEntityMap[$permissionEntityType]))
			{
				$permissionEntityMap[$permissionEntityType] = array();
			}
			$permissionEntityMap[$permissionEntityType][] = $entityID;
		}

		$results = array();
		foreach($permissionEntityMap as $permissionEntityType => $permissionEntityIDs)
		{
			$results += \CCrmPerms::GetEntityAttr($permissionEntityType, $permissionEntityIDs);
		}
		return $results;
	}
}