<?php
namespace Bitrix\Crm\Timeline;

use Bitrix\Main;
use Bitrix\Main\Type\DateTime;
use Bitrix\Crm\Timeline\Entity\TimelineTable;

class CommentEntry extends TimelineEntry
{
	public static function create(array $params)
	{
		$text = isset($params['TEXT']) ? $params['TEXT'] : '';
		if($text === '')
		{
			throw new Main\ArgumentException('Text must be greater not empty string.', 'text');
		}

		$authorID = isset($params['AUTHOR_ID']) ? (int)$params['AUTHOR_ID'] : 0;
		if($authorID <= 0)
		{
			$authorID = \CCrmSecurityHelper::GetCurrentUserID();
		}

		$settings = isset($params['SETTINGS']) ? $params['SETTINGS'] : "";

		$created = isset($params['CREATED']) && ($params['CREATED'] instanceof DateTime)
			? $params['CREATED'] : new DateTime();

		$result = TimelineTable::add(
			array(
				'TYPE_ID' => TimelineType::COMMENT,
				'TYPE_CATEGORY_ID' => 0,
				'CREATED' => $created,
				'AUTHOR_ID' => $authorID,
				'COMMENT' => $text,
				'SETTINGS' => $settings,
				'ASSOCIATED_ENTITY_TYPE_ID' => 0,
				'ASSOCIATED_ENTITY_ID' => 0
			)
		);

		if(!$result->isSuccess())
		{
			return 0;
		}

		$ID = $result->getId();
		$bindings = isset($params['BINDINGS']) && is_array($params['BINDINGS']) ? $params['BINDINGS'] : array();
		self::registerBindings($ID, $bindings);
		return $ID;
	}
	public static function rebind($entityTypeID, $oldEntityID, $newEntityID)
	{
		Entity\TimelineBindingTable::rebind($entityTypeID, $oldEntityID, $newEntityID, array(TimelineType::COMMENT));
	}
	public static function attach($srcEntityTypeID, $srcEntityID, $targEntityTypeID, $targEntityID)
	{
		Entity\TimelineBindingTable::attach($srcEntityTypeID, $srcEntityID, $targEntityTypeID, $targEntityID, array(TimelineType::COMMENT));
	}
	public static function update($primary, array $params)
	{
		$result = new Main\Result();
		
		if ($primary <= 0)
		{
			$result->addError(new Main\Error('Wrong entity ID'));
			return $result;
		}

		$updateData = array();

		if (isset($params['COMMENT']))
			$updateData['COMMENT'] = $params['COMMENT'];

		if (isset($params['SETTINGS']) && is_array($params['SETTINGS']))
			$updateData['SETTINGS'] = $params['SETTINGS'];

		if (!empty($updateData))
			$result = TimelineTable::update($primary, $updateData);

		return $result;
	}
}