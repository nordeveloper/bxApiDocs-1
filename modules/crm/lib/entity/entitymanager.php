<?php
namespace Bitrix\Crm\Entity;

use Bitrix\Main;

class EntityManager
{
	public static function resolveByTypeID($entityTypeID)
	{
		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return Lead::getInstance();
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal)
		{
			return Deal::getInstance();
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return Contact::getInstance();
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return Company::getInstance();
		}

		return null;
	}
}