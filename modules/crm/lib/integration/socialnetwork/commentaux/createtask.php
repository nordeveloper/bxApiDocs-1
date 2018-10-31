<?php
namespace Bitrix\Crm\Integration\Socialnetwork\CommentAux;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class CreateTask
{
	const SOURCE_TYPE_CRM_LEAD = 'CRM_LEAD';
	const SOURCE_TYPE_CRM_ENTITY_COMMENT = 'CRM_ENTITY_COMMENT';

	public static function getPostTypeList()
	{
		return array(
			self::SOURCE_TYPE_CRM_LEAD
		);
	}

	public static function getCommentTypeList()
	{
		return array(
			self::SOURCE_TYPE_CRM_ENTITY_COMMENT
		);
	}
}