<?php

/**
* Bitrix Framework
* @package bitrix
* @subpackage crm
* @copyright 2001-2019 Bitrix
*/

namespace Bitrix\Crm\Integration\Im;

use Bitrix\Main\Localization\Loc;

class Chat
{
	const CHAT_ENTITY_TYPE = "CRM";

	public static function getChatId($params = [])
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		$entityType = $params['ENTITY_TYPE'];
		$entityId = intval($params['ENTITY_ID']);

		if (empty($entityType) || empty($entityId))
		{
			return false;
		}

		if (false)
		{
			// check user permission to $entityType & $entityId
			// if user hasn't access return false
			return false;
		}

		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => ['ID'],
			'filter' => [
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'=ENTITY_ID' => $entityType.'|'.$entityId,
			],
		))->fetch();
		if ($chatData)
		{
			return $chatData['ID'];
		}

		return false;
	}

	public static function joinChat($params = [])
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		$entityType = $params['ENTITY_TYPE'];
		$entityId = intval($params['ENTITY_ID']);
		$userId = intval($params['USER_ID']);

		if (empty($entityType) || empty($entityId) || empty($userId))
		{
			return false;
		}

		if (false)
		{
			// check user permission to $entityType & $entityId
			// if user hasn't access return false
			return false;
		}


		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => [
				'ID',
				'RELATION_USER_ID' => 'RELATION.USER_ID',
			],
			'filter' => [
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'=ENTITY_ID' => $entityType.'|'.$entityId,
			],
			'runtime' => Array(
				new \Bitrix\Main\Entity\ReferenceField(
					'RELATION',
					'\Bitrix\Im\Model\RelationTable',
					array(
						"=ref.CHAT_ID" => "this.ID",
						"=ref.USER_ID" => new \Bitrix\Main\DB\SqlExpression('?', $userId)
					),
					array("join_type"=>"LEFT")
				)
			)
		))->fetch();
		if ($chatData)
		{
			if (!$chatData['RELATION_USER_ID'])
			{
				$chat = new \CIMChat(0);
				$chat->AddUser($chatData['ID'], [$userId], false);
			}

			return $chatData['ID'];
		}
		else
		{
			return self::createChat([
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'USER_ID' => $userId,
				'SKIP_CHECK' => true,
			]);
		}

	}

	public static function createChat($params = [])
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		$entityType = $params['ENTITY_TYPE'];
		$entityId = $params['ENTITY_ID'];
		$userId = intval($params['USER_ID']);

		if (empty($entityType) || empty($entityId) || empty($userId))
		{
			return false;
		}

		if (false && $params['SKIP_CHECK'] !== true)
		{
			// check user permission to $entityType & $entityId
			// if user hasn't access return false
			return false;
		}

		$crmEntityTitle = '';
		$crmEntityAvatarId = 0;

		$entityData = self::getEntityData($entityType, $entityId, true);
		if ($entityType == \CCrmOwnerType::CompanyName)
		{
			if (isset($entityData['TITLE']))
			{
				$crmEntityTitle = $entityData['TITLE'];
			}
			if (isset($entityData['LOGO']))
			{
				$crmEntityAvatarId = intval($entityData['LOGO']);
			}
		}
		else if (
			$entityType == \CCrmOwnerType::LeadName || $entityType == \CCrmOwnerType::DealName
		)
		{
			if (isset($entityData['TITLE']))
			{
				$crmEntityTitle = $entityData['TITLE'];
			}
		}
		else if ($entityType == \CCrmOwnerType::ContactName)
		{
			if (isset($entityData['FULL_NAME']))
			{
				$crmEntityTitle = $entityData['FULL_NAME'];
			}
			if (isset($entityData['PHOTO']))
			{
				$crmEntityAvatarId = intval($entityData['PHOTO']);
			}
		}

		if (!$crmEntityTitle)
		{
			$crmEntityTitle = '#'.$entityId;;
		}

		$crmResponsibleId = intval($entityData['ASSIGNED_BY_ID']);;

		$joinUserList = [$userId];
		// select users from crm entity and merge with $joinUserList
		// $joinUserList = array_merge($joinUserList, $crmUserList);

		$chatFields = array(
			'TITLE' => self::buildChatName([
				'ENTITY_TYPE' => $entityType,
				'ENTITY_TITLE' => $crmEntityTitle,
			]),
			'TYPE' => IM_MESSAGE_CHAT,
			'ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
			'ENTITY_ID' => $entityType.'|'.$entityId,
			'SKIP_ADD_MESSAGE' => 'Y',
			'AUTHOR_ID' => $crmResponsibleId,
			'USERS' => $joinUserList
		);
		if ($crmEntityAvatarId)
		{
			$chatFields['AVATAR_ID'] = $crmEntityAvatarId;
		}

		$chat = new \CIMChat(0);
		$chatId = $chat->add($chatFields);

		// first message in chat, if you delete this message, need set SKIP_ADD_MESSAGE = N in creating chat props
		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $chatId,
			"USER_ID" => $userId,
			"MESSAGE" => '[b]'.Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_TITLE_'.$entityType).'[/b]',
			"SYSTEM" => 'Y',
			"ATTACH" => self::getEntityCard($entityType, $entityId, $entityData)
		]);

		$users = [];
		foreach ($joinUserList as $uid)
		{
			$users[$uid] = \Bitrix\Im\User::getInstance($uid)->getArray(['JSON' => 'Y']);
		}

		// Better to use watch code (CPullWatch) for timeline
		\Bitrix\Pull\Event::add(array_keys($users), Array(
			'module_id' => 'crm',
			'command' => 'chatCreate',
			'params' => Array(
				'entityType' => $entityType,
				'entityId' => $entityId,
				'chatId' => $chatId,
				'users' => $users,
			),
			'extra' => \Bitrix\Im\Common::getPullExtra()
		));

		return $chatId;
	}

	public static function deleteChat($params = [])
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		$entityType = $params['ENTITY_TYPE'];
		$entityId = $params['ENTITY_ID'];

		if (empty($entityType) || empty($entityId))
		{
			return false;
		}

		return \CIMChat::DeleteEntityChat(self::CHAT_ENTITY_TYPE, $entityType.'|'.$entityId);
	}

	public static function buildChatName($params = [])
	{
		$entityType = $params['ENTITY_TYPE'];
		$entityTitle = $params['ENTITY_TITLE'];

		if (empty($entityType) || empty($entityTitle))
		{
			return false;
		}

		$currentSite = \CSite::getById(SITE_ID);
		$siteLanguageId = (
			($siteFields = $currentSite->fetch())
				? $siteFields['LANGUAGE_ID']
				: LANGUAGE_ID
		);

		$localizePhrase = 'CRM_INTEGRATION_IM_CHAT_TITLE_'.$entityType;

		return Loc::getMessage($localizePhrase, array(
			"#TITLE#" => $entityTitle
		), $siteLanguageId);
	}

	public static function getEntityCard($entityType, $entityId, $entityData = null)
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return null;
		}

		if (!in_array($entityType, [
			\CCrmOwnerType::LeadName,
			\CCrmOwnerType::ContactName,
			\CCrmOwnerType::CompanyName,
			\CCrmOwnerType::DealName
		]))
		{
			return null;
		}

		if (!$entityData)
		{
			$entityData = self::getEntityData($entityType, $entityId, true);
		}

		if (!$entityData)
		{
			return null;
		}

		$attach = new \CIMMessageParamAttach();

		$entityGrid = Array();
		if ($entityType == \CCrmOwnerType::LeadName)
		{
			if (isset($entityData['TITLE']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['TITLE'],
					'LINK' => \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::ResolveID($entityType), $entityId, false)
				));
			}

			if (!empty($entityData['FULL_NAME']) && strpos($entityData['TITLE'], $entityData['FULL_NAME']) === false)
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_FULL_NAME'), 'VALUE' => $entityData['FULL_NAME']);
			}
			if (!empty($entityData['COMPANY_TITLE']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_COMPANY_TITLE'), 'VALUE' => $entityData['COMPANY_TITLE']);
			}
			if (!empty($entityData['POST']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_POST'), 'VALUE' => $entityData['POST']);
			}

		}
		else if ($entityType == \CCrmOwnerType::ContactName)
		{
			if (isset($entityData['FULL_NAME']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['FULL_NAME'],
					'LINK' => \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::ResolveID($entityType), $entityId, false)
				));
			}

			if (!empty($entityData['POST']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_POST'), 'VALUE' => $entityData['POST']);
			}
		}
		else if ($entityType == \CCrmOwnerType::CompanyName || $entityType == \CCrmOwnerType::DealName)
		{
			if (isset($entityData['TITLE']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['TITLE'],
					'LINK' => \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::ResolveID($entityType), $entityId, false)
				));
			}
		}

		if ($entityData['HAS_PHONE'] == 'Y' && isset($entityData['FM']['PHONE']))
		{
			$fields = Array();
			foreach ($entityData['FM']['PHONE'] as $phones)
			{
				foreach ($phones as $phone)
				{
					$fields[] = $phone;
				}
			}
			$entityGrid[] = Array('DISPLAY' => 'LINE', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_PHONE'), 'VALUE' => implode('[br]', $fields), 'HEIGHT' => '20');
		}
		if ($entityData['HAS_EMAIL'] == 'Y' && $entityData['FM']['EMAIL'])
		{
			$fields = Array();
			foreach ($entityData['FM']['EMAIL'] as $emails)
			{
				foreach ($emails as $email)
				{
					$fields[] = $email;
				}
			}
			$entityGrid[] = Array('DISPLAY' => 'LINE', 'NAME' => Loc::getMessage('CRM_INTEGRATION_IM_CHAT_CARD_EMAIL'), 'VALUE' => implode('[br]', $fields), 'HEIGHT' => '20');
		}
		$attach->AddGrid($entityGrid);

		return $attach;
	}

	public static function getEntityData($entityType, $entityId, $withMultiFields = false)
	{
		if ($entityType == \CCrmOwnerType::LeadName)
		{
			$entity = new \CCrmLead(false);
		}
		else if ($entityType == \CCrmOwnerType::CompanyName)
		{
			$entity = new \CCrmCompany(false);
		}
		else if ($entityType == \CCrmOwnerType::ContactName)
		{
			$entity = new \CCrmContact(false);
		}
		else if ($entityType == \CCrmOwnerType::DealName)
		{
			$entity = new \CCrmDeal(false);
		}
		else
		{
			return false;
		}
		$data = $entity->GetByID($entityId, false);

		if ($withMultiFields)
		{
			$multiFields = new \CCrmFieldMulti();
			$res = $multiFields->GetList(Array(), Array(
				'ENTITY_ID' => $entityType,
				'ELEMENT_ID' => $entityId
			));
			while ($row = $res->Fetch())
			{
				$data['FM'][$row['TYPE_ID']][$row['VALUE_TYPE']][] = $row['VALUE'];
			}
		}

		return $data;
	}
}
?>