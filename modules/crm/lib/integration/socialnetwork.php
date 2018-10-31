<?php

namespace Bitrix\Crm\Integration;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Socialnetwork
{
	const DATA_ENTITY_TYPE_CRM_LEAD = 'CRM_LEAD';
	const DATA_ENTITY_TYPE_CRM_ENTITY_COMMENT = 'CRM_ENTITY_COMMENT';

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

	public static function onLogProviderGetProvider(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'crm'
		);

		$entityType = $event->getParameter('entityType');

		switch ($entityType)
		{
			case self::DATA_ENTITY_TYPE_CRM_LEAD:
				$provider = new \Bitrix\Crm\Integration\Socialnetwork\Livefeed\CrmLead();
				break;
			case self::DATA_ENTITY_TYPE_CRM_ENTITY_COMMENT:
				$provider = new \Bitrix\Crm\Integration\Socialnetwork\Livefeed\CrmEntityComment();
				break;
			default:
				$provider = false;
		}

		if ($provider)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'provider' => $provider,
				),
				'crm'
			);
		}

		return $result;
	}


	public static function onLogProviderGetContentId(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'crm'
		);

		$eventFields = $event->getParameter('eventFields');

		$contentEntityType = $contentEntityId = false;

		if (!empty($eventFields["EVENT_ID"]))
		{
			$providersList = array(
				new \Bitrix\Crm\Integration\Socialnetwork\Livefeed\CrmLead(),
				new \Bitrix\Crm\Integration\Socialnetwork\Livefeed\CrmEntityComment(),
			);
			foreach($providersList as $provider)
			{
				if (in_array($eventFields["EVENT_ID"], $provider->getEventId()))
				{
					$contentEntityType = $provider->getContentTypeId();
					$contentEntityId = intval($eventFields["ENTITY_ID"]);
					break;
				}
			}
		}

		if (
			$contentEntityType
			&& $contentEntityId > 0
		)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'contentEntityType' => $contentEntityType,
					'contentEntityId' => $contentEntityId
				),
				'crm'
			);
		}

		return $result;
	}

	public static function onCommentAuxGetPostTypeList(Event $event)
	{
		return new EventResult(
			EventResult::SUCCESS,
			array(
				'typeList' => \Bitrix\Crm\Integration\Socialnetwork\CommentAux\CreateTask::getPostTypeList(),
			),
			'crm'
		);
	}

	public static function onCommentAuxGetCommentTypeList(Event $event)
	{
		return new EventResult(
			EventResult::SUCCESS,
			array(
				'typeList' => \Bitrix\Crm\Integration\Socialnetwork\CommentAux\CreateTask::getCommentTypeList(),
			),
			'crm'
		);
	}

	public static function onCommentAuxInitJs(Event $event)
	{
		\Bitrix\Crm\Integration\Socialnetwork\CommentAux::initJs();

		Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/crm/lib/integration/socialnetwork/commentaux/createtask.php');

		return new EventResult(
			EventResult::SUCCESS,
			array(
				'lang_additional' => array(
					'SONET_EXT_COMMENTAUX_CREATE_TASK_CRM_LEAD' => Loc::getMessage('SONET_EXT_COMMENTAUX_CREATE_TASK_CRM_LEAD'),
					'SONET_EXT_COMMENTAUX_CREATE_TASK_CRM_ENTITY_COMMENT_LEAD' => Loc::getMessage('SONET_EXT_COMMENTAUX_CREATE_TASK_CRM_ENTITY_COMMENT_LEAD'),

					'SONET_COMMENTAUX_JS_CREATETASK_POST_CRM_LEAD' => Loc::getMessage('SONET_COMMENTAUX_CREATETASK_POST_CRM_LEAD'),
					'SONET_COMMENTAUX_JS_CREATETASK_COMMENT_CRM_ENTITY_COMMENT_LEAD' => Loc::getMessage('SONET_COMMENTAUX_CREATETASK_COMMENT_CRM_ENTITY_COMMENT_LEAD')
				)
			),
			'crm'
		);
	}
}
