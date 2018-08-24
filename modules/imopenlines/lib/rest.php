<?php
namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

if(!\Bitrix\Main\Loader::includeModule('rest'))
	return;

Loc::loadMessages(__FILE__);

class Rest extends \IRestService
{
	public static function onRestServiceBuildDescription()
	{
		return array(
			'imopenlines' => array(
				'imopenlines.operator.answer' => array(__CLASS__, 'operatorAnswer'),
				'imopenlines.operator.skip' => array(__CLASS__, 'operatorSkip'),
				'imopenlines.operator.spam' => array(__CLASS__, 'operatorSpam'),
				'imopenlines.operator.transfer' => array(__CLASS__, 'operatorTransfer'),
				'imopenlines.operator.finish' => array(__CLASS__, 'operatorFinish'),

				'imopenlines.session.intercept' => array(__CLASS__, 'sessionIntercept'),

				'imopenlines.bot.session.operator' => array(__CLASS__, 'botSessionOperator'),
				'imopenlines.bot.session.send.message' =>  array('callback' => array(__CLASS__, 'botSessionSendAutoMessage'), 'options' => array('private' => true)), // legacy
				'imopenlines.bot.session.message.send' => array(__CLASS__, 'botSessionSendAutoMessage'),
				'imopenlines.bot.session.transfer' => array(__CLASS__, 'botSessionTransfer'),
				'imopenlines.bot.session.finish' => array(__CLASS__, 'botSessionFinish'),

				'imopenlines.network.join' => array(__CLASS__, 'networkJoin'),
				'imopenlines.network.message.add' => array(__CLASS__, 'networkMessageAdd'),
				'imopenlines.config.path.get' => array(__CLASS__, 'configGetPath'),
			),
		);
	}

	public static function operatorAnswer($arParams, $n, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->answer();
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException($control->getError()->msg, $control->getError()->code, \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function operatorSkip($arParams, $n, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->skip();
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException($control->getError()->msg, $control->getError()->code, \CRestServer::STATUS_WRONG_REQUEST);
		}


		return true;
	}

	public static function operatorSpam($arParams, $n, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->markSpam();
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException($control->getError()->msg, $control->getError()->code, \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function operatorFinish($arParams, $n, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->closeDialog();
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException($control->getError()->msg, $control->getError()->code, \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function operatorTransfer($arParams, $n, \CRestServer $server)
	{
		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$transferId = null;
		if (isset($arParams['TRANSFER_ID']))
		{
			if (substr($arParams['TRANSFER_ID'], 0, 5) == 'queue')
			{
				$arParams['QUEUE_ID'] = substr($arParams['TRANSFER_ID'], 5);
			}
			else
			{
				$arParams['USER_ID'] = $arParams['TRANSFER_ID'];
			}
		}

		if (isset($arParams['USER_ID']))
		{
			$arParams['USER_ID'] = intval($arParams['USER_ID']);

			if ($arParams['USER_ID'] <= 0)
			{
				throw new \Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
			}

			$transferId = $arParams['USER_ID'];
		}
		else if (isset($arParams['QUEUE_ID']))
		{
			$arParams['QUEUE_ID'] = intval($arParams['QUEUE_ID']);

			if ($arParams['QUEUE_ID'] <= 0)
			{
				throw new \Bitrix\Rest\RestException("QUEUE ID can't be empty", "QUEUE_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
			}

			$transferId = 'queue'.$arParams['QUEUE_ID'];
		}
		else
		{
			throw new \Bitrix\Rest\RestException("Queue ID or User ID can't be empty", "TRANSFER_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->transfer(Array(
			'TRANSFER_ID' => $transferId,
		));
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException("You can not redirect to this operator", "OPERATOR_WRONG", \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function sessionIntercept($arParams, $n, \CRestServer $server)
	{
		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$control = new \Bitrix\ImOpenLines\Operator($arParams['CHAT_ID']);
		$result = $control->interceptSession();

		if (!$result)
		{
			throw new \Bitrix\Rest\RestException("You can not redirect to this operator", "OPERATOR_WRONG", \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}


	public static function botSessionOperator($arParams, $n, \CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new \Bitrix\Imopenlines\Chat($arParams['CHAT_ID']);
		$result = $chat->endBotSession();
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException("Operator is not a bot", "WRONG_CHAT", \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function botSessionSendAutoMessage($arParams, $n, \CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new \Bitrix\Imopenlines\Chat($arParams['CHAT_ID']);
		$chat->sendAutoMessage($arParams['NAME']);

		return true;
	}

	public static function botSessionTransfer($arParams, $n, \CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		$arParams['LEAVE'] = isset($arParams['LEAVE']) && $arParams['LEAVE'] == 'Y'? 'Y': 'N';

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$transferId = null;
		if (isset($arParams['TRANSFER_ID']))
		{
			if (substr($arParams['TRANSFER_ID'], 0, 5) == 'queue')
			{
				$arParams['QUEUE_ID'] = substr($arParams['TRANSFER_ID'], 5);
			}
			else
			{
				$arParams['USER_ID'] = $arParams['TRANSFER_ID'];
			}
		}

		if (isset($arParams['USER_ID']))
		{
			$arParams['USER_ID'] = intval($arParams['USER_ID']);

			if ($arParams['USER_ID'] <= 0)
			{
				throw new \Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
			}

			$transferId = $arParams['USER_ID'];
		}
		else if (isset($arParams['QUEUE_ID']))
		{
			$arParams['QUEUE_ID'] = intval($arParams['QUEUE_ID']);

			if ($arParams['QUEUE_ID'] <= 0)
			{
				throw new \Bitrix\Rest\RestException("QUEUE ID can't be empty", "QUEUE_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
			}

			$transferId = 'queue'.$arParams['QUEUE_ID'];
		}
		else
		{
			throw new \Bitrix\Rest\RestException("Queue ID or User ID can't be empty", "TRANSFER_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		$botFound = false;
		$botId = 0;
		foreach ($bots as $bot)
		{
			if ($bot['APP_ID'] == $server->getAppId())
			{
				$botFound = true;
				$botId = $bot['BOT_ID'];
				break;
			}
		}
		if (!$botFound)
		{
			throw new \Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new \Bitrix\Imopenlines\Chat($arParams['CHAT_ID']);
		$result = $chat->transfer(Array(
			'FROM' => $botId,
			'TO' => $transferId,
			'MODE' => Chat::TRANSFER_MODE_BOT,
			'LEAVE' => $arParams['LEAVE']
		));
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException("You can not redirect to this operator", "OPERATOR_WRONG", \CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function botSessionFinish($arParams, $n, \CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		$botFound = false;
		$botId = 0;
		foreach ($bots as $bot)
		{
			if ($bot['APP_ID'] == $server->getAppId())
			{
				$botFound = true;
				$botId = $bot['BOT_ID'];
				break;
			}
		}
		if (!$botFound)
		{
			throw new \Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new \Bitrix\Imopenlines\Chat($arParams['CHAT_ID']);
		$chat->answer($botId);
		$chat->finish();

		return true;
	}

	public static function configGetPath($arParams, $n, \CRestServer $server)
	{
		return array(
			'SERVER_ADDRESS' => \Bitrix\ImOpenLines\Common::getServerAddress(),
			'PUBLIC_PATH' => \Bitrix\ImOpenLines\Common::getPublicFolder()
		);
	}

	public static function networkJoin($arParams, $n, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['CODE']) || strlen($arParams['CODE']) != 32)
		{
			throw new \Bitrix\Rest\RestException("You entered an invalid code", "CODE", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			throw new \Bitrix\Rest\RestException("Module IMBOT is not installed", "IMBOT_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (\Bitrix\ImBot\Bot\Network::isFdcCode($arParams['CODE']))
		{
			throw new \Bitrix\Rest\RestException("Line not found", "NOT_FOUND", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$network = new \Bitrix\ImOpenLines\Network();
		$result = $network->join($arParams['CODE']);
		if (!$result)
		{
			throw new \Bitrix\Rest\RestException($network->getError()->msg, $network->getError()->code, \CRestServer::STATUS_WRONG_REQUEST);
		}

		return $result;
	}

	public static function networkMessageAdd($arParams, $n, \CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['CODE']) || strlen($arParams['CODE']) != 32)
		{
			throw new \Bitrix\Rest\RestException("You entered an invalid code", "CODE", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			throw new \Bitrix\Rest\RestException("Module IMBOT is not installed", "IMBOT_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (\Bitrix\ImBot\Bot\Network::isFdcCode($arParams['CODE']))
		{
			throw new \Bitrix\Rest\RestException("Line not found", "NOT_FOUND", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$networkBot = null;

		$bots = \Bitrix\Im\Bot::getListCache();
		foreach ($bots as $bot)
		{
			if ($bot['APP_ID'] == $arParams['CODE'])
			{
				$networkBot = $bot;
				break;
			}
		}
		if (!$networkBot)
		{
			throw new \Bitrix\Rest\RestException("Line not found", "NOT_FOUND", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$arMessageFields = Array();

		$arMessageFields['DIALOG_ID'] = intval($arParams['USER_ID']);
		if (empty($arMessageFields['DIALOG_ID']))
		{
			throw new \Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$isBitrix24 = \Bitrix\Main\Loader::includeModule('bitrix24');
		if (!$isBitrix24 || !\CBitrix24::IsNfrLicense())
		{
			$dateLimit = new \Bitrix\Main\Type\DateTime();
			$dateLimit->add('-1 WEEK');

			$check = \Bitrix\Imopenlines\Model\RestNetworkLimitTable::getList(Array(
				'filter' => Array(
					'=BOT_ID' => $networkBot['BOT_ID'],
					'=USER_ID' => $arMessageFields['DIALOG_ID'],
					'>DATE_CREATE' => $dateLimit
				)
			))->fetch();
			if ($check)
			{
				throw new \Bitrix\Rest\RestException("You cant send more than one message per week to each user.", "USER_MESSAGE_LIMIT", \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arMessageFields['MESSAGE'] = trim($arParams['MESSAGE']);
		if (strlen($arMessageFields['MESSAGE']) <= 0)
		{
			throw new \Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['ATTACH']) && !empty($arParams['ATTACH']))
		{
			$attach = \CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
			if ($attach)
			{
				if ($attach->IsAllowSize())
				{
					$arMessageFields['ATTACH'] = $attach;
				}
				else
				{
					throw new \Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", \CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			else if ($arParams['ATTACH'])
			{
				throw new \Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['KEYBOARD']) && !empty($arParams['KEYBOARD']))
		{
			$keyboard = Array();
			if (!isset($arParams['KEYBOARD']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
			}
			else
			{
				$keyboard = $arParams['KEYBOARD'];
			}
			$keyboard['BOT_ID'] = $arParams['BOT_ID'];

			$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			if ($keyboard)
			{
				$arMessageFields['KEYBOARD'] = $keyboard;
			}
			else
			{
				throw new \Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}
		$arMessageFields['PARAMS']['IMOL_QUOTE_MSG'] = 'Y';

		$id = \Bitrix\Im\Bot::addMessage(array('BOT_ID' => $networkBot['BOT_ID']), $arMessageFields);
		if (!$id)
		{
			throw new \Bitrix\Rest\RestException("Message isn't added", "WRONG_REQUEST", \CRestServer::STATUS_WRONG_REQUEST);
		}

		\Bitrix\Imopenlines\Model\RestNetworkLimitTable::add(Array('BOT_ID' => $networkBot['BOT_ID'], 'USER_ID' => $arMessageFields['DIALOG_ID']));

		return true;
	}
}
