<?php
namespace Bitrix\ImOpenLines;

use \Bitrix\Main\Loader,
	\Bitrix\Im\Model\ChatTable,
	\Bitrix\Main\DB\SqlExpression,
	\Bitrix\Main\Entity\ReferenceField;

class Im
{
	public static function addMessage($fields)
	{
		$result = false;

		if(Loader::includeModule('im'))
		{
			$fields['MESSAGE_TYPE'] = IM_MESSAGE_OPEN_LINE;

			$result = \CIMMessenger::Add($fields);
		}

		return $result;
	}

	/**
	 * @param $messages
	 * @return array
	 */
	public static function addMessagesNewsletter($messages)
	{
		$result = array();
		$userCodes = array();

		if(is_array($messages) && Loader::includeModule('im'))
		{
			foreach ($messages as $code => $message)
			{
				$result[$code] = false;
				$userCodes[] = $code;
			}

			$rawChat = ChatTable::getList(array(
				'select' => array('ID', 'ENTITY_ID', 'RECENT_MID' => 'RECENT.ITEM_MID'),
				'filter' => array(
					'=ENTITY_TYPE' => 'LINES',
					'=ENTITY_ID' => $userCodes
				),
				'runtime' => array(
					new ReferenceField(
						'RECENT',
						'\Bitrix\Im\Model\RecentTable',
						array(
							'=this.ID' => 'ref.ITEM_ID',
							'=this.AUTHOR_ID' => 'ref.USER_ID',
							'ref.ITEM_TYPE' => new SqlExpression('?i', IM_MESSAGE_OPEN_LINE)
						)
					)
				)
			));

			while($rowChat = $rawChat->fetch())
			{
				$fields = $messages[$rowChat['ENTITY_ID']];

				$fields['MESSAGE_TYPE'] = IM_MESSAGE_OPEN_LINE;
				$fields['TO_CHAT_ID'] = $rowChat['ID'];
				$fields['FROM_USER_ID'] = 0;
				$fields['SYSTEM'] = 'Y';
				$fields['SKIP_USER_CHECK'] = 'Y';
				$fields['IMPORTANT_CONNECTOR'] = 'Y';
				$fields['INCREMENT_COUNTER'] = 'N';
				$fields['PUSH'] = 'N';
				if(empty($rowChat['RECENT_MID']))
					$fields['RECENT_ADD'] = 'N';
				else
					$fields['RECENT_ADD'] = 'Y';

				$fields['NO_SESSION_OL'] = 'Y';

				$result[$rowChat["ENTITY_ID"]] = \CIMMessenger::Add($fields);
			}
		}

		return $result;
	}

	public static function addMessageLiveChat($fields)
	{
		$result = false;

		if(Loader::includeModule('im'))
		{
			$fields['MESSAGE_TYPE'] = IM_MESSAGE_CHAT;

			$result = \CIMMessenger::Add($fields);
		}

		return $result;
	}

	public static function chatHide($chatId)
	{
		return \CIMChat::hide($chatId);
	}
}