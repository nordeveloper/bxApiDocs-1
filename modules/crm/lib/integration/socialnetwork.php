<?php

namespace Bitrix\Crm\Integration;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Socialnetwork
{
	public static function onUserProfileRedirectGetUrl(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'crm'
		);

		$userFields = $event->getParameter('userFields');

		if (
			!is_array($userFields)
			|| empty($userFields["UF_USER_CRM_ENTITY"])
		)
		{
			return $result;
		}

		$entityCode = trim($userFields["UF_USER_CRM_ENTITY"]);
		$entityData = explode('_', $entityCode);

		if (
			!empty($entityData[0])
			&& !empty($entityData[1])
			&& intval($entityData[1]) > 0
		)
		{
			$url = \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::resolveID(\CUserTypeCrm::getLongEntityType($entityData[0])), $entityData[1]);
			if (!empty($url))
			{
				$result = new EventResult(
					EventResult::SUCCESS,
					array(
						'url' => $url,
					),
					'crm'
				);
			}
		}

		return $result;
	}
}
