<?php
namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat
{
	const FIELD_SESSION = 'LINES_SESSION';
	const FIELD_CONFIG = 'LINES_CONFIG';
	const FIELD_SILENT_MODE = 'LINES_SILENT_MODE';
	const FIELD_LIVECHAT = 'LIVECHAT_SESSION';

	const RATING_TYPE_CLIENT = 'CLIENT';
	const RATING_TYPE_HEAD = 'HEAD';
	const RATING_VALUE_LIKE = '5';
	const RATING_VALUE_DISLIKE = '1';

	public static $fieldAssoc = Array(
		'LINES_SESSION' => 'ENTITY_DATA_1',
		'LINES_CONFIG' => 'ENTITY_DATA_2',
		'LINES_SILENT_MODE' => 'ENTITY_DATA_3',
		'LIVECHAT_SESSION' => 'ENTITY_DATA_1',
	);

	const TRANSFER_MODE_AUTO = 'AUTO';
	const TRANSFER_MODE_MANUAL = 'MANUAL';
	const TRANSFER_MODE_BOT = 'BOT';

	const TEXT_WELCOME = 'WELCOME';

	const CHAT_TYPE_OPERATOR = 'LINES';
	const CHAT_TYPE_CLIENT = 'LIVECHAT';

	private $error = null;
	private $moduleLoad = false;
	private $isCreated = false;
	private $isDataLoaded = false;
	private $joinByUserId = 0;

	public function __construct($chatId = 0, $params = Array())
	{
		$imLoad = \Bitrix\Main\Loader::includeModule('im');
		$pullLoad = \Bitrix\Main\Loader::includeModule('pull');
		if ($imLoad && $pullLoad)
		{
			$this->error = new Error(null, '', '');
			$this->moduleLoad = true;
		}
		else
		{
			if (!$imLoad)
			{
				$this->error = new Error(__METHOD__, 'IM_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_IM_LOAD'));
			}
			elseif (!$pullLoad)
			{
				$this->error = new Error(__METHOD__, 'PULL_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_PULL_LOAD'));
			}
		}
		$chatId = intval($chatId);
		if ($chatId > 0)
		{
			$chat = \Bitrix\Im\Model\ChatTable::getById($chatId)->fetch();
			if ($chat && in_array($chat['ENTITY_TYPE'], Array(self::CHAT_TYPE_OPERATOR, self::CHAT_TYPE_CLIENT)))
			{
				if (isset($params['CONNECTOR']['chat']['description']) && $chat['DESCRIPTION'] != $params['CONNECTOR']['chat']['description'])
				{
					$chatManager = new \CIMChat(0);
					$chatManager->SetDescription($chat['ID'], $params['CONNECTOR']['chat']['description']);
					$chat['DESCRIPTION'] = $params['CONNECTOR']['chat']['description'];
				}

				$this->chat = $chat;
				$this->isDataLoaded = true;
			}
		}
	}

	private function isModuleLoad()
	{
		return $this->moduleLoad;
	}

	public function load($params)
	{
		if (!$this->isModuleLoad())
		{
			return false;
		}
		$orm = \Bitrix\Im\Model\ChatTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE' => 'LINES',
				'=ENTITY_ID' => $params['USER_CODE']
			),
			'limit' => 1
		));
		if($chat = $orm->fetch())
		{
			if (isset($params['CONNECTOR']['chat']['description']) && $chat['DESCRIPTION'] != $params['CONNECTOR']['chat']['description'])
			{
				$chatManager = new \CIMChat(0);
				$chatManager->SetDescription($chat['ID'], $params['CONNECTOR']['chat']['description']);
				$chat['DESCRIPTION'] = $params['CONNECTOR']['chat']['description'];
			}
			$this->chat = $chat;

			$this->isDataLoaded = true;
			return true;
		}
		else if ($params['ONLY_LOAD'] == 'Y')
		{
			return false;
		}

		$parsedUserCode = Session::parseUserCode($params['USER_CODE']);
		$connectorId = $parsedUserCode['CONNECTOR_ID'];

		$avatarId = 0;
		$userName = '';
		$chatColorCode = '';
		$addChat['USERS'] = false;
		if ($params['USER_ID'])
		{
			$orm = \Bitrix\Main\UserTable::getById($params['USER_ID']);
			if ($user = $orm->fetch())
			{
				if ($user['PERSONAL_PHOTO'] > 0)
				{
					$avatarId = $user['PERSONAL_PHOTO'];
				}
				$addChat['USERS'] = Array($params['USER_ID']);

				if ($connectorId != 'livechat' || !empty($user['NAME']))
				{
					$userName = \Bitrix\Im\User::getInstance($params['USER_ID'])->getFullName(false);
				}
				$chatColorCode = \Bitrix\Im\Color::getCodeByNumber($params['USER_ID']);
				if (\Bitrix\Im\User::getInstance($params['USER_ID'])->getGender() == 'M')
				{
					$replaceColor = \Bitrix\Im\Color::getReplaceColors();
					if (isset($replaceColor[$chatColorCode]))
					{
						$chatColorCode = $replaceColor[$chatColorCode];
					}
				}
			}
		}

		$description = '';
		if (isset($params['CONNECTOR']['chat']['description']))
		{
			$description = trim($params['CONNECTOR']['chat']['description']);
		}

		$titleParams = $this->getTitle($params['LINE_NAME'], $userName, $chatColorCode);

		$addChat['TYPE'] = IM_MESSAGE_OPEN_LINE;
		$addChat['AVATAR_ID'] = $avatarId;
		$addChat['TITLE'] = $titleParams['TITLE'];
		$addChat['COLOR'] = $titleParams['COLOR'];
		$addChat['DESCRIPTION'] = $description;
		$addChat['ENTITY_TYPE'] = 'LINES';
		$addChat['ENTITY_ID'] = $params['USER_CODE'];
		$addChat['SKIP_ADD_MESSAGE'] = 'Y';

		$chat = new \CIMChat(0);
		$id = $chat->Add($addChat);
		if (!$id)
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\ChatTable::getById($id);
		$this->chat = $orm->fetch();
		$this->isCreated = true;
		$this->isDataLoaded = true;

		return true;
	}

	public function answer($userId, $skipSession = false, $skipMessage = false)
	{
		if ($this->chat['AUTHOR_ID'] == $userId)
		{
			return true;
		}
		$answerTag = 'answer_'.$this->chat['ID'];
		if (Common::getCacheTag($answerTag, 60))
		{
			return true;
		}

		Common::setCacheTag($answerTag, 60);

		$chat = new \CIMChat(0);
		$relations = \CIMChat::GetRelationById($this->chat['ID']);

		if (!isset($relations[$userId]))
		{
			$chat->AddUser($this->chat['ID'], $userId, false, true);
		}

		$session = null;
		if ($skipSession)
		{
			Common::removeCacheTag($answerTag);
		}
		else
		{
			$session = new Session();
			$result = $session->load(Array(
				'USER_CODE' => $this->chat['ENTITY_ID'],
				'MODE' => Session::MODE_OUTPUT,
				'OPERATOR_ID' => $userId
			));
			if (!$result)
			{
				Common::removeCacheTag($answerTag);
				return false;
			}
			else if($session->isNowCreated())
			{
				Common::removeCacheTag($answerTag);
			}
		}

		if ($skipSession)
		{
			list(, $lineId) = explode('|', $this->chat['ENTITY_ID']);
			$configManager = new Config();
			$config = $configManager->get($lineId);
		}
		else if ($session && !$session->isNowCreated())
		{
			$session->setOperatorId($userId, false);

			if ($session->getData('CRM_ACTIVITY_ID') > 0)
			{
				$endTime = new Main\Type\DateTime();
				$endTime->add(intval($session->getConfig('AUTO_CLOSE_TIME')).' SECONDS');
				$endTime->add('1 DAY');

				$crmManager = new Crm();
				$crmManager->updateActivity(Array(
					'ID' => $session->getData('CRM_ACTIVITY_ID'),
					'UPDATE' => Array(
						'ANSWERED' => 'Y',
						'END_TIME' => $endTime
					)
				));
			}

			$sessionUpdate = Array(
				'OPERATOR_ID' => $userId,
				'WAIT_ACTION' => 'N',
				'WAIT_ANSWER' => 'N',
				'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
				'CHECK_DATE_CLOSE' => null,
				'SEND_NO_ANSWER_TEXT' => 'Y'
			);
			if (!\Bitrix\Im\User::getInstance($userId)->isBot() && $session->getData('DATE_OPERATOR_ANSWER') <= 0)
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$sessionUpdate['DATE_OPERATOR_ANSWER'] = $currentDate;
				$sessionUpdate['TIME_ANSWER'] = $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
			}

			$session->update($sessionUpdate);
			$config = $session->getConfig();
		}
		else if ($session)
		{
			$config = $session->getConfig();
		}

		$relations = \CIMChat::GetRelationById($this->chat['ID']);
		foreach ($relations as $relation)
		{
			if ($userId == $relation['USER_ID'])
				continue;

			if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
				continue;

			if ($config['WELCOME_BOT_LEFT'] == Config::BOT_LEFT_CLOSE && \Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
				continue;

			$chat->DeleteUser($this->chat['ID'], $relation['USER_ID'], false, true);
		}

		$this->update(Array(
			'AUTHOR_ID' => $userId
		));

		\Bitrix\Pull\Event::add($userId, Array(
			'module_id' => 'imopenlines',
			'command' => 'linesAnswer',
			'params' => Array(
				'chatId' => $this->chat['ID']
			)
		));

		if (!$skipMessage)
		{
			$userAnswer = \Bitrix\Im\User::getInstance($userId);

			Im::addMessage(Array(
				"FROM_USER_ID" => $userId,
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_ANSWER_'.$userAnswer->getGender(), Array('#USER#' => '[USER='.$userAnswer->getId().']'.$userAnswer->getFullName(false).'[/USER]')),
				"SYSTEM" => 'Y',
			));
		}

		Common::removeCacheTag($answerTag);

		if ($session)
		{
			$eventData = array(
				'RUNTIME_SESSION' => $session,
				'USER_ID' => $userId,
			);
			$event = new \Bitrix\Main\Event("imopenlines", "OnChatAnswer", $eventData);
			$event->send();
		}

		return true;
	}

	public function intercept($userId)
	{
		if ($this->chat['AUTHOR_ID'] <= 0 || $this->chat['AUTHOR_ID'] == $userId)
		{
			return false;
		}

		$previousOwnerId = $this->chat['AUTHOR_ID'];

		$this->answer($userId, false, true);

		$previousOwner = \Bitrix\Im\User::getInstance($previousOwnerId);
		$newOwner = \Bitrix\Im\User::getInstance($userId);

		\CIMChat::AddMessage(Array(
			"FROM_USER_ID" => $userId,
			"TO_CHAT_ID" => $this->chat['ID'],
			"MESSAGE" => Loc::getMessage('IMOL_CHAT_INTERCEPT_'.$newOwner->getGender(), Array(
				'#USER_1#' => '[USER='.$newOwner->getId().']'.$newOwner->getFullName(false).'[/USER]',
				'#USER_2#' => '[USER='.$previousOwner->getId().']'.$previousOwner->getFullName(false).'[/USER]'
			)),
			"SYSTEM" => 'Y',
		));

		return true;
	}

	public function skip($userId = 0)
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID'],
			'SKIP_CREATE' => 'Y'
		));
		if (!$result)
		{
			if ($userId > 0)
			{
				$chat = new \CIMChat();
				$chat->DeleteUser($this->chat['ID'], $userId, false);
			}

			return false;
		}

		if ($session)
		{
			$eventData = array(
				'RUNTIME_SESSION' => $session,
				'USER_ID' => $userId,
			);
			$event = new \Bitrix\Main\Event("imopenlines", "OnChatSkip", $eventData);
			$event->send();
		}


		if ($userId)
		{
			$userSkip = \Bitrix\Im\User::getInstance($userId);

			Im::addMessage(Array(
				"FROM_USER_ID" => $userId,
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_SKIP_'.$userSkip->getGender(), Array('#USER#' => '[USER='.$userSkip->getId().']'.$userSkip->getFullName(false).'[/USER]')),
				"SYSTEM" => 'Y',
			));
		}

		if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL && $userId)
		{
			$count = 0;
			$relations = \CIMChat::GetRelationById($this->chat['ID']);
			foreach ($relations as $relation)
			{
				if ($userId == $relation['USER_ID'])
					continue;

				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
					continue;

				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
					continue;

				$count++;
			}
			if ($count > 0)
			{
				$chat = new \CIMChat(0);
				$chat->DeleteUser($this->chat['ID'], $userId, false, true);
			}
			else
			{
				$session->transferToNextInQueue();
			}
		}
		else
		{
			$session->transferToNextInQueue();
		}

		return true;
	}

	public function endBotSession()
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if ($result && \Bitrix\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
		{
			if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL)
			{
				$chat = new \CIMChat(0);
				if ($session->getConfig('WELCOME_BOT_LEFT') != Config::BOT_LEFT_CLOSE)
				{
					$chat->DeleteUser($this->chat['ID'], $session->getData('OPERATOR_ID'), false, true);
				}
				else
				{
					$chat->SetOwner($this->chat['ID'], 0);
				}

				$queue = $session->getQueue();
				$this->join($queue['USER_LIST'], true);

				Im::addMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => Loc::getMessage('IMOL_CHAT_TO_QUEUE'),
					"SYSTEM" => 'Y',
				));
			}
			else
			{
				$session->transferToNextInQueue();
			}

			return true;
		}

		return false;
	}

	public function waitAnswer($userId)
	{
		$this->update(Array(
			'AUTHOR_ID' => $userId,
			self::getFieldName(self::FIELD_SILENT_MODE) => 'N'
		));
	}

	public function transfer($params)
	{
		$mode = in_array($params['MODE'], Array(self::TRANSFER_MODE_AUTO, self::TRANSFER_MODE_BOT))? $params['MODE']: self::TRANSFER_MODE_MANUAL;
		$selfExit = isset($params['LEAVE']) && $params['LEAVE'] == 'N'? false: true;
		$skipCheck = isset($params['SKIP_CHECK']) && $params['SKIP_CHECK'] == 'Y';

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$event = new \Bitrix\Main\Event("imopenlines", "OnOperatorTransfer", [
			'CHAT' => $this->chat,
			'SESSION' => $session,
			'TRANSFER' => [
				'MODE' => $mode,
				'FROM' => $params['FROM'],
				'TO' => $params['TO'],
			]
		]);
		$event->send();

		foreach($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() != \Bitrix\Main\EventResult::SUCCESS)
				continue;

			$newValues = $eventResult->getParameters();
			if (!empty($newValues['TRANSFER_ID']))
			{
				$params['TO'] = $newValues['TRANSFER_ID'];
			}
		}

		if (substr($params['TO'], 0, 5) == 'queue')
		{
			if($params['TO']  == 'queue')
			{
				$queueId = 0;
			}
			else
			{
				$queueId = intval(substr($params['TO'], 5));

				$config = \Bitrix\ImOpenlines\Model\ConfigTable::getById($queueId)->fetch();
				if (!$config)
				{
					return false;
				}
			}

			$session->setOperatorId(0, true);
			$this->update(Array('AUTHOR_ID' => 0));

			$userFrom = \Bitrix\Im\User::getInstance($params['FROM']);

			$lineFromId = $session->getConfig('ID');
			$lineFrom = $session->getConfig('LINE_NAME');

			if($queueId == 0)
			{
				$queueId = $lineFromId;
			}

			Log::write(Array($params['FROM'], $queueId), 'TRANSFER TO LINE');

			\Bitrix\ImOpenLines\Model\OperatorTransferTable::Add(Array(
				'CONFIG_ID' => $session->getData('CONFIG_ID'),
				'SESSION_ID' => $session->getData('ID'),
				'USER_ID' => $params['FROM'],
				'TRANSFER_MODE' => $mode,
				'TRANSFER_TYPE' => 'QUEUE',
				'TRANSFER_LINE_ID' => $queueId
			));

			if ($userFrom->isBot() && !$session->getData('DATE_OPERATOR'))
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$session->update(Array(
					'CONFIG_ID' => $queueId,
					'DATE_OPERATOR' => $currentDate,
					'QUEUE_HISTORY' => Array(),
					'TIME_BOT' => $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp(),
					'CHECK_DATE_CLOSE' => null,
				));
			}
			else
			{
				$session->update(Array(
					'CONFIG_ID' => $queueId,
					'QUEUE_HISTORY' => Array(),
					'CHECK_DATE_CLOSE' => null,
				));
			}
			$lineTo = $session->getConfig('LINE_NAME');

			if($params['TO']  == 'queue')
			{
				$message = Loc::getMessage('IMOL_CHAT_RETURNED_TO_QUEUE');
			}
			else if ($lineFromId == $queueId)
			{
				$message = Loc::getMessage('IMOL_CHAT_SKIP_'.$userFrom->getGender(), Array(
					'#USER#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName(false).'[/USER]',
				));
			}
			else
			{
				$message = Loc::getMessage('IMOL_CHAT_TRANSFER_LINE_'.$userFrom->getGender(), Array(
					'#USER_FROM#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName(false).'[/USER]',
					'#LINE_FROM#' => '[b]'.$lineFrom.'[/b]',
					'#LINE_TO#' => '[b]'.$lineTo.'[/b]',
				));
			}

			$queue = $session->getQueue();

			$chat = new \CIMChat(0);
			$relations = \CIMChat::GetRelationById($this->chat['ID']);
			foreach ($relations as $relation)
			{
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
					continue;

				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
					continue;

				if (!in_array($relation['USER_ID'], $queue['USER_LIST']))
				{
					$chat->DeleteUser($this->chat['ID'], $relation['USER_ID'], false, true);
				}
			}

			if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL)
			{
				$chat = new \CIMChat(0);
				$chat->SetOwner($this->chat['ID'], 0);

				$this->join($queue['USER_LIST'], true);

				Im::addMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => $message,
					"SYSTEM" => 'Y',
				));
			}
			else
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => $message,
					"SYSTEM" => 'Y',
				));

				$session->transferToNextInQueue(false);
			}
		}
		else
		{
			$transferUserId = intval($params['TO']);

			if (!$skipCheck)
			{
				if (
					\Bitrix\Im\User::getInstance($transferUserId)->isBot() ||
					\Bitrix\Im\User::getInstance($transferUserId)->isExtranet() ||
					\Bitrix\Im\User::getInstance($transferUserId)->isConnector()
				)
				{
					return false;
				}
			}

			$chat = new \CIMChat(0);
			$relations = \CIMChat::GetRelationById($this->chat['ID']);
			foreach ($relations as $relation)
			{
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
					continue;

				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
					continue;

				if ($relation['USER_ID'] != $params['FROM'] || $selfExit)
				{
					$chat->DeleteUser($this->chat['ID'], $relation['USER_ID'], false, true);
				}
			}

			if ($session->getConfig('ACTIVE') == 'Y')
			{
				$this->update(Array('AUTHOR_ID' => 0));
			}
			else
			{
				$this->update(Array('AUTHOR_ID' => $transferUserId));
			}
			if($transferUserId > 0)
			{
				$chat->AddUser($this->chat['ID'], $transferUserId, false, true);
			}

			$userFrom = \Bitrix\Im\User::getInstance($params['FROM']);
			if($transferUserId > 0)
			{
				$userTo = \Bitrix\Im\User::getInstance($transferUserId);
			}

			Log::write(Array($params['FROM'], $transferUserId), 'TRANSFER TO USER');

			if ($transferUserId > 0 && $params['FROM'] > 0 && ($mode == self::TRANSFER_MODE_MANUAL || $mode == self::TRANSFER_MODE_BOT))
			{
				$message = Loc::getMessage('IMOL_CHAT_TRANSFER_'.$userFrom->getGender(), Array(
					'#USER_FROM#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName(false).'[/USER]',
					'#USER_TO#' => '[USER='.$userTo->getId().']'.$userTo->getFullName(false).'[/USER]')
				);
			}
			else if(empty($transferUserId))
			{
				$message = Loc::getMessage('IMOL_CHAT_NO_OPERATOR_AVAILABLE_IN_QUEUE');
			}
			else
			{
				$message = Loc::getMessage('IMOL_CHAT_NEXT_IN_QUEUE', Array('#USER_TO#' => '[USER='.$userTo->getId().']'.$userTo->getFullName(false).'[/USER]'));
			}

			\Bitrix\ImOpenLines\Model\OperatorTransferTable::Add(Array(
				'CONFIG_ID' => $session->getData('CONFIG_ID'),
				'SESSION_ID' => $session->getData('ID'),
				'USER_ID' => intval($params['FROM']),
				'TRANSFER_MODE' => $mode,
				'TRANSFER_TYPE' => 'USER',
				'TRANSFER_USER_ID' => $transferUserId
			));

			Im::addMessage(Array(
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => $message,
				"SYSTEM" => 'Y',
			));

			if ($userFrom->isBot() && !$session->getData('DATE_OPERATOR'))
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$session->update(Array(
					'DATE_OPERATOR' => $currentDate,
					'TIME_BOT' => $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp(),
				));
			}

			if ($mode == self::TRANSFER_MODE_MANUAL)
			{
				$this->answer($transferUserId, false, true);
			}
			else
			{
				$session->setOperatorId($transferUserId, true);
				$session->update(Array(
					'CHECK_DATE_CLOSE' => null,
				));
			}
		}

		return true;
	}

	public function join($userId, $skipMessage = true, $skipRecent = false)
	{
		if (!$this->isDataLoaded)
			return false;

		if (empty($userId))
			return false;

		$chat = new \CIMChat($this->joinByUserId);
		return $chat->AddUser($this->chat['ID'], $userId, false, $skipMessage, $skipRecent);
	}

	public function leave($userId)
	{
		if (!$this->isDataLoaded)
			return false;

		$chat = new \CIMChat(0);
		return $chat->DeleteUser($this->chat['ID'], $userId, false, true);
	}

	public function close()
	{
		if (!$this->isDataLoaded)
			return false;

		$relationList = \Bitrix\Im\Model\RelationTable::getList(array(
			"select" => array("ID", "USER_ID", "EXTERNAL_AUTH_ID" => "USER.EXTERNAL_AUTH_ID"),
			"filter" => array(
				"=CHAT_ID" => $this->chat['ID']
			),
		));
		while ($relation = $relationList->fetch())
		{
			if ($relation['EXTERNAL_AUTH_ID'] == "imconnector")
				continue;

			$this->leave($relation['USER_ID']);
		}

		$this->updateFieldData(Chat::FIELD_SESSION, Array(
			'ID' => '0',
			'PAUSE' => 'N',
			'WAIT_ACTION' => 'N'
		));

		$this->update(Array(
			'AUTHOR_ID' => 0,
			self::getFieldName(self::FIELD_SILENT_MODE) => 'N'
		));

		return true;
	}

	public function setUserIdForJoin($userId)
	{
		$this->joinByUserId = intval($userId);
		return true;
	}

	public function setCrmFlag($params)
	{
		$active = $params['ACTIVE'] == 'Y'? 'Y': 'N';
		$entityType = $params['ENTITY_TYPE'];
		$entityId = intval($params['ENTITY_ID']);
		$dealId = intval($params['DEAL_ID']);

		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if (
			$sessionField['CRM'] == $active &&
			$sessionField['CRM_ENTITY_TYPE'] == $entityType &&
			$sessionField['CRM_ENTITY_ID'] == $entityId &&
			$sessionField['CRM_DEAL_ID'] == $dealId
		)
		{
			return true;
		}

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'CRM' => $active,
			'CRM_ENTITY_TYPE' => $entityType,
			'CRM_ENTITY_ID' => $entityId,
			'CRM_DEAL_ID' => $dealId
		));

		return true;
	}

	public function updateSessionStatus($status)
	{
		$users = Array();
		$relations = \Bitrix\Im\Chat::getRelation($this->chat['ID'], Array(
			'SELECT' => Array('USER_ID'),
			'USER_DATA' => 'Y',
		));
		foreach ($relations as $relation)
		{
			if ($relation['USER_DATA']["EXTERNAL_AUTH_ID"] == 'imconnector')
			{
				continue;
			}
			$users[] = $relation['USER_ID'];
		}

		\Bitrix\Pull\Event::add($users, Array(
			'module_id' => 'imopenlines',
			'command' => 'updateSessionStatus',
			'params' => Array(
				'chatId' => $this->chat['ID'],
				'status' => (int)$status
			),
		));

		return true;
	}

	public function finish()
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$session->finish();

		return true;
	}

	public function markSpamAndFinish($userId)
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$user = \Bitrix\Im\User::getInstance($userId);
		$message = Loc::getMessage('IMOL_CHAT_MARK_SPAM_'.$user->getGender(), Array(
			'#USER#' => '[USER='.$user->getId().']'.$user->getFullName(false).'[/USER]',
		));

		Im::addMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
		));

		if ($session->getData('SOURCE') == 'livechat')
		{
			$parsedUserCode = Session::parseUserCode($session->getData('USER_CODE'));
			$chatId = $parsedUserCode['EXTERNAL_CHAT_ID'];
			$liveChat = new Chat($chatId);
			$liveChat->updateFieldData(Chat::FIELD_LIVECHAT, Array(
				'SESSION_ID' => 0,
				'SHOW_FORM' => 'N'
			));
		}

		$session->markSpam();
		$session->finish();

		return true;
	}

	public function dismissedOperatorFinish()
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$session->dismissedOperatorFinish();

		return true;
	}

	public function startSession($userId)
	{
		$session = new Session();
		$session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID'],
			'MODE' => Session::MODE_OUTPUT,
			'OPERATOR_ID' => $userId,
		));
		if ($session->isNowCreated())
		{
			$dateClose = new \Bitrix\Main\Type\DateTime();
			$dateClose->add('1 MONTH');

			$sessionUpdate = Array(
				'CHECK_DATE_CLOSE' => $dateClose
			);
			$session->update($sessionUpdate);
		}
		else
		{
			$this->join($userId, false);
		}

		return true;
	}

	public function startSessionByMessage($userId, $messageId)
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$message = \CIMMessenger::GetById($messageId);
		if ($message['CHAT_ID'] != $this->chat['ID'])
		{
			return false;
		}

		$user = \Bitrix\Im\User::getInstance($userId);
		Im::addMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"SYSTEM" => 'Y',
			"MESSAGE" => Loc::getMessage('IMOL_CHAT_CLOSE_FOR_OPEN_'.$user->getGender(), Array(
				'#USER#' => '[USER='.$user->getId().']'.$user->getFullName(false).'[/USER]',
			)),
		));

		$configId = $session->getData('CONFIG_ID');
		$sessionId = $session->getData('SESSION_ID');

		$session->finish(false, true, false);

		$session = new Session();
		$session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID'],
			'MODE' => Session::MODE_OUTPUT,
			'OPERATOR_ID' => $userId,
			'CONFIG_ID' => $configId,
			'PARENT_ID' => $sessionId
		));

		Im::addMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"FROM_USER_ID" => $message['AUTHOR_ID'],
			"MESSAGE" => $message['MESSAGE'],
			"PARAMS" => $message['PARAMS'],
			"SKIP_CONNECTOR" => 'Y',
		));

		$this->answer($userId, false, true);

		$dateClose = new \Bitrix\Main\Type\DateTime();
		$dateClose->add('1 MONTH');

		$sessionUpdate = Array(
			'CHECK_DATE_CLOSE' => $dateClose
		);
		$session->update($sessionUpdate);

		return true;
	}

	public function setSilentMode($active = true)
	{
		$active = $active? 'Y': '';
		if ($this->chat[self::getFieldName(self::FIELD_SILENT_MODE)] == $active)
			return true;

		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], Array(
			self::getFieldName(self::FIELD_SILENT_MODE) => $active
		));

		Im::addMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"MESSAGE" => Loc::getMessage($active? 'IMOL_CHAT_STEALTH_ON': 'IMOL_CHAT_STEALTH_OFF'),
			"SYSTEM" => 'Y',
		));

		return true;
	}

	public function isSilentModeEnabled()
	{
		return $this->chat[self::getFieldName(self::FIELD_SILENT_MODE)] == 'Y';
	}

	public function setPauseFlag($params)
	{
		$pause = $params['ACTIVE'] == 'Y'? 'Y': 'N';
		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if ($sessionField['PAUSE'] == $pause)
		{
			return true;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$session->pause($pause == 'Y');

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'PAUSE' => $pause,
		));

		if ($pause == 'Y')
		{
			$datePause = new \Bitrix\Main\Type\DateTime();
			$datePause->add('1 WEEK');

			$formattedDate = \FormatDate('d F', $datePause->getTimestamp());
			Im::addMessage(Array(
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_PAUSE_ON', array('#DATE#' => '[b]'.$formattedDate.'[/b]')),
				"SYSTEM" => 'Y',
			));
		}
		else
		{
			Im::addMessage(Array(
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_PAUSE_OFF'),
				"SYSTEM" => 'Y',
			));
		}

		return true;
	}

	public function createLead()
	{
		if (!\Bitrix\Main\Loader::includeModule('crm'))
		{
			return false;
		}

		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if ($sessionField['CRM'] == 'Y')
		{
			return true;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$params = $session->getData();
		$crmData = $session->createLead(array(
			'CONFIG_ID' => $params['CONFIG_ID'],
			'SESSION_ID' => $params['ID'],
			'MODE' => $params['MODE'],
			'USER_CODE' => $params['USER_CODE'],
			'USER_ID' => $params['USER_ID'],
			'CRM_TITLE' => $this->getData('TITLE'),
			'OPERATOR_ID' => $params['OPERATOR_ID'],
			'CHAT_ID' => $params['CHAT_ID'],
			'SKIP_CREATE' => 'N',
		));
		if (!$crmData)
			return false;

		$session->update(Array(
			'CRM' => 'Y',
			'CRM_CREATE' => $crmData['LEAD_CREATE'],
			'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
			'CRM_ACTIVITY_ID' => $crmData['ACTIVITY_ID'],
			'CRM_DEAL_ID' => $crmData['DEAL_ID'],
		));

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'CRM' => 'Y',
			'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
			'CRM_DEAL_ID' => $crmData['DEAL_ID'],
		));

		return true;
	}

	public function getFieldData($field)
	{
		if (!$this->isDataLoaded())
		{
			return false;
		}

		if (!in_array($field, Array(self::FIELD_CONFIG, self::FIELD_SESSION, self::FIELD_LIVECHAT)))
		{
			return false;
		}

		$data = Array();

		if ($field == self::FIELD_SESSION)
		{
			$data = Array(
				'ID' => time(),
				'CRM' => 'N',
				'CRM_ENTITY_TYPE' => 'NONE',
				'CRM_ENTITY_ID' => '0',
				'CRM_DEAL_ID' => '0',
				'PAUSE' => 'N',
				'WAIT_ACTION' => 'N',
				'DATE_CREATE' => '0'
			);

			$fieldData = explode("|", $this->chat[self::getFieldName($field)]);
			if (isset($fieldData[0]) && $fieldData[0] == 'Y')
			{
				$data['CRM'] = $fieldData[0];
			}
			if (isset($fieldData[1]))
			{
				$data['CRM_ENTITY_TYPE'] = $fieldData[1];
			}
			if (isset($fieldData[2]))
			{
				$data['CRM_ENTITY_ID'] = $fieldData[2];
			}
			if (isset($fieldData[3]) && $fieldData[3] == 'Y')
			{
				$data['PAUSE'] = $fieldData[3];
			}
			if (isset($fieldData[4]) && $fieldData[4] == 'Y')
			{
				$data['WAIT_ACTION'] = $fieldData[4];
			}
			if (isset($fieldData[5]))
			{
				$data['ID'] = intval($fieldData[5]);
			}
			if (isset($fieldData[6]))
			{
				$data['DATE_CREATE'] = intval($fieldData[6]);
			}
			if (isset($fieldData[7]))
			{
				$data['CRM_DEAL_ID'] = intval($fieldData[7]);
			}
		}
		else if ($field == self::FIELD_LIVECHAT)
		{
			$data = Array(
				'READED' => 'N',
				'READED_ID' => '0',
				'READED_TIME' => false,
				'SESSION_ID' => '0',
				'SHOW_FORM' => 'Y',
			);
			$fieldData = explode("|", $this->chat[self::getFieldName($field)]);
			if (isset($fieldData[0]) && $fieldData[0] == 'Y')
			{
				$data['READED'] = $fieldData[0];
			}
			if (isset($fieldData[1]))
			{
				$data['READED_ID'] = intval($fieldData[1]);
			}
			if (isset($fieldData[2]))
			{
				$data['READED_TIME'] = $fieldData[2];
			}
			if (isset($fieldData[3]))
			{
				$data['SESSION_ID'] = intval($fieldData[3]);
			}
			if (isset($fieldData[4]))
			{
				$data['SHOW_FORM'] = $fieldData[4] == 'N'? 'N': 'Y';
			}
		}

		return $data;
	}

	public function getTitle($lineName, $userName = '', $userColor = '')
	{
		if (!$userName)
		{
			$result = self::getGuestName($userColor);
			$userName = $result['USER_NAME'];
			$userColor = $result['USER_COLOR'];
		}

		if (!$userColor)
		{
			$userColor = \Bitrix\Im\Color::getRandomCode();
		}

		return Array(
			'TITLE' => Loc::getMessage('IMOL_CHAT_CHAT_NAME', Array("#USER_NAME#" => $userName, "#LINE_NAME#" => $lineName)),
			'COLOR' => $userColor
		);
	}

	public function updateFieldData($field, $fieldData)
	{
		if (!in_array($field, Array(self::FIELD_CONFIG, self::FIELD_SESSION, self::FIELD_LIVECHAT)))
		{
			return false;
		}

		$data = Array();
		if ($field == self::FIELD_SESSION)
		{
			$data = self::getFieldData($field);
			if (isset($fieldData['CRM']))
			{
				$data['CRM'] = $fieldData['CRM'];
			}
			if (isset($fieldData['CRM_ENTITY_TYPE']))
			{
				$data['CRM_ENTITY_TYPE'] = $fieldData['CRM_ENTITY_TYPE'];
			}
			if (isset($fieldData['CRM_ENTITY_ID']))
			{
				$data['CRM_ENTITY_ID'] = $fieldData['CRM_ENTITY_ID'];
			}
			if (isset($fieldData['PAUSE']))
			{
				$data['PAUSE'] = $fieldData['PAUSE'];
			}
			if (isset($fieldData['WAIT_ACTION']))
			{
				$data['WAIT_ACTION'] = $fieldData['WAIT_ACTION'];
			}
			if (isset($fieldData['ID']))
			{
				$data['ID'] = $fieldData['ID'];
			}
			if (isset($fieldData['DATE_CREATE']))
			{
				$data['DATE_CREATE'] = $fieldData['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime? $fieldData['DATE_CREATE']->getTimestamp(): intval($fieldData['DATE_CREATE']);
			}
			if (isset($fieldData['CRM_DEAL_ID']))
			{
				$data['CRM_DEAL_ID'] = $fieldData['CRM_DEAL_ID'];
			}
			$this->chat[self::getFieldName($field)] = $data['CRM'].'|'.$data['CRM_ENTITY_TYPE'].'|'.$data['CRM_ENTITY_ID'].'|'.$data['PAUSE'].'|'.$data['WAIT_ACTION'].'|'.$data['ID'].'|'.$data['DATE_CREATE'].'|'.$data['CRM_DEAL_ID'];
		}
		else if ($field == self::FIELD_LIVECHAT)
		{
			$data = self::getFieldData($field);
			if (isset($fieldData['READED']))
			{
				$data['READED'] = $fieldData['READED'];
			}
			if (isset($fieldData['READED_ID']))
			{
				$data['READED_ID'] = intval($fieldData['READED_ID']);
			}
			if (isset($fieldData['READED_TIME']))
			{
				$data['READED_TIME'] = $fieldData['READED_TIME'] instanceof \Bitrix\Main\Type\DateTime? date('c', $fieldData['READED_TIME']->getTimestamp()): false;
			}
			if (isset($fieldData['SESSION_ID']))
			{
				$data['SESSION_ID'] = intval($fieldData['SESSION_ID']);
			}
			if (isset($fieldData['SHOW_FORM']))
			{
				$data['SHOW_FORM'] = $fieldData['SHOW_FORM'] == 'N'? 'N': 'Y';
			}
			$this->chat[self::getFieldName($field)] = $data['READED'].'|'.$data['READED_ID'].'|'.$data['READED_TIME'].'|'.$data['SESSION_ID'].'|'.$data['SHOW_FORM'];
		}

		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], Array(
			self::getFieldName($field) => $this->chat[self::getFieldName($field)]
		));

		$users = Array();
		$relationList = \Bitrix\Im\Model\RelationTable::getList(array(
			"select" => array("ID", "USER_ID", "EXTERNAL_AUTH_ID" => "USER.EXTERNAL_AUTH_ID"),
			"filter" => array(
				"=CHAT_ID" => $this->chat['ID']
			),
		));
		while ($relation = $relationList->fetch())
		{
			if (
				\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot() ||
				\Bitrix\Im\User::getInstance($relation['USER_ID'])->isNetwork() ||
				$field != self::FIELD_LIVECHAT && \Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector()
			)
			{
				continue;
			}
			\CIMContactList::CleanChatCache($relation['USER_ID']);
			$users[] = $relation['USER_ID'];
		}

		if (!empty($users))
		{
			\Bitrix\Pull\Event::add($users, Array(
				'module_id' => 'im',
				'command' => 'chatUpdateParam',
				'params' => Array(
					'chatId' => $this->chat['ID'],
					'name' => strtolower(self::getFieldName($field)),
					'value' => $this->chat[self::getFieldName($field)]
				),
				'extra' => method_exists('\Bitrix\Im\Common', 'getPullExtra') ?
					\Bitrix\Im\Common::getPullExtra() :
					Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
			));
		}

		return $data;
	}

	public function update($fields)
	{
		foreach($fields as $field => $value)
		{
			if ($this->chat[$field] === $value)
			{
				unset($fields[$field]);
			}
			else
			{
				$this->chat[$field] = $value;
			}
		}

		if (empty($fields))
		{
			return true;
		}

		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], $fields);

		$relations = \CIMChat::GetRelationById($this->chat['ID']);
		foreach ($relations as $rel)
		{
			\CIMContactList::CleanChatCache($rel['USER_ID']);

			if (isset($fields['AUTHOR_ID']))
			{
				if ($rel['USER_ID'] == $this->chat['AUTHOR_ID'])
				{
					\Bitrix\Im\Model\RelationTable::update($rel['ID'], Array('MANAGER' => 'N'));
				}
				if ($rel['USER_ID'] == $fields['AUTHOR_ID'])
				{
					\Bitrix\Im\Model\RelationTable::update($rel['ID'], Array('MANAGER' => 'Y'));
				}
			}
		}

		if (array_key_exists('AUTHOR_ID', $fields))
		{
			$parsedUserCode = Session::parseUserCode($this->chat['ENTITY_ID']);
			if ($parsedUserCode['CONNECTOR_ID'] == 'livechat')
			{
				\Bitrix\Pull\Event::add($parsedUserCode['CONNECTOR_USER_ID'], Array(
					'module_id' => 'imopenlines',
					'command' => 'sessionOperatorChange',
					'params' => Array(
						'chatId' => (int)$parsedUserCode['EXTERNAL_CHAT_ID'],
						'operator' => Rest::objectEncode(
							Operator::getOperatorData($fields['AUTHOR_ID'], $parsedUserCode['CONFIG_ID'])
						)
					)
				));
			}
		}

		return true;
	}

	public function getData($field = '')
	{
		if (!$this->isDataLoaded)
			return false;

		if ($field)
		{
			return isset($this->chat[$field])? $this->chat[$field]: null;
		}
		else
		{
			return $this->chat;
		}
	}

	public static function getGuestName($chatColorCode = '')
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
			return false;

		if (\Bitrix\Im\Color::isEnabled())
		{
			if (!$chatColorCode)
			{
				\CGlobalCounter::Increment('im_chat_color_id', \CGlobalCounter::ALL_SITES, false);
				$chatColorId = \CGlobalCounter::GetValue('im_chat_color_id', \CGlobalCounter::ALL_SITES);
				$chatColorCode = \Bitrix\Im\Color::getCodeByNumber($chatColorId);
			}
			\CGlobalCounter::Increment('im_chat_color_'.$chatColorCode, \CGlobalCounter::ALL_SITES, false);

			$chatColorCodeCount = \CGlobalCounter::GetValue('im_chat_color_'.$chatColorCode, \CGlobalCounter::ALL_SITES);
			if ($chatColorCodeCount == 99)
			{
				\CGlobalCounter::Set('im_chat_color_'.$chatColorCode, 1, \CGlobalCounter::ALL_SITES, '', false);
			}
			$userName = Loc::getMessage('IMOL_CHAT_CHAT_NAME_COLOR_GUEST', Array("#COLOR#" => \Bitrix\Im\Color::getName($chatColorCode), "#NUMBER#" => $chatColorCodeCount+1));
		}
		else
		{
			$guestId = \CGlobalCounter::GetValue('imol_guest_id', \CGlobalCounter::ALL_SITES);
			\CGlobalCounter::Increment('imol_guest_id', \CGlobalCounter::ALL_SITES, false);
			if ($guestId == 99)
			{
				\CGlobalCounter::Set('imol_guest_id', 1, \CGlobalCounter::ALL_SITES, '', false);
			}
			$userName = Loc::getMessage('IMOL_CHAT_CHAT_NAME_GUEST', Array("#NUMBER#" => $guestId+1));
		}

		return Array(
			'USER_NAME' => $userName,
			'USER_COLOR' => $chatColorCode,
		);
	}

	public static function getFieldName($field)
	{
		return self::$fieldAssoc[$field];
	}

	public function sendJoinMessage($userList)
	{
		if (empty($userList))
			return false;

		if (count($userList) == 1)
		{
			$toUserId = $userList[0];
			$userName = \Bitrix\Im\User::getInstance($toUserId)->getFullName(false);
			$message = Loc::getMessage('IMOL_CHAT_ASSIGN_OPERATOR', Array('#USER#' => '[USER='.$toUserId.']'.$userName.'[/USER]'));
		}
		else
		{
			$message = Loc::getMessage('IMOL_CHAT_ASSIGN_OPERATOR_LIST');
		}

		$messageId = Im::addMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"FROM_USER_ID" => 0,
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
			"IMPORTANT_CONNECTOR" => 'N'
		));

		return $messageId;
	}

	public function sendAutoMessage($type = null)
	{
		if (!$type)
			return true;

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$messageId = false;
		if ($type == self::TEXT_WELCOME)
		{
			if ($session->getConfig('WELCOME_MESSAGE') == 'Y' && $session->getConfig('SOURCE') != 'network')
			{
				$messageId = Im::addMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => $session->getConfig('WELCOME_MESSAGE_TEXT'),
					"SYSTEM" => 'Y',
					"IMPORTANT_CONNECTOR" => 'Y',
					"PARAMS" => Array(
						"CLASS" => "bx-messenger-content-item-ol-output"
					)
				));
			}
		}

		return $messageId;
	}

	public function isNowCreated()
	{
		return $this->isCreated;
	}

	public function isDataLoaded()
	{
		return $this->isDataLoaded;
	}

	public function getError()
	{
		return $this->error;
	}

	public static function sendRatingNotify($type, $sessionId, $rating, $toUserId, $fromUserId = null)
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		if (!in_array($type, Array(self::RATING_TYPE_CLIENT, self::RATING_TYPE_HEAD)))
		{
			return false;
		}

		$rating = intval($rating);
		if ($rating > 5 || $rating < 1)
		{
			return false;
		}

		$toUserId = intval($toUserId);
		if ($toUserId <= 0)
		{
			return false;
		}

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
		{
			$fromUserId = $GLOBALS['USER']->GetId();
		}

		if ($toUserId == $fromUserId)
		{
			return false;
		}

		$userName = '';
		if ($type == self::RATING_TYPE_CLIENT)
		{
			$notifyMessageName = $rating == self::RATING_VALUE_DISLIKE? 'IMOL_CHAT_NOTIFY_RATING_CLIENT_DISLIKE': 'IMOL_CHAT_NOTIFY_RATING_CLIENT_LIKE';
			$ratingImage = $rating == self::RATING_VALUE_DISLIKE? '[dislike]': '[like]';
			$ratingText = Loc::getMessage('IMOL_CHAT_NOTIFY_RATING_VALUE_'.($rating == self::RATING_VALUE_DISLIKE? 'DISLIKE': 'LIKE'));
		}
		else
		{
			$userName = \Bitrix\Im\User::getInstance($fromUserId)->getFullName(false);
			$userGender = \Bitrix\Im\User::getInstance($fromUserId)->getGender();
			$notifyMessageName = 'IMOL_CHAT_NOTIFY_RATING_HEAD_'.$userGender.'_LIKE';
			$ratingImage = $rating = "[RATING=".$rating."]";
			$ratingText = Loc::getMessage('IMOL_CHAT_NOTIFY_RATING_VALUE_'.$rating);
		}

		$userViewChat = \CIMContactList::InRecent($toUserId, IM_MESSAGE_OPEN_LINE, $sessionId);
		\CIMNotify::Add(array(
			"TO_USER_ID" => $toUserId,
			"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
			"NOTIFY_MODULE" => "imopenlines",
			"NOTIFY_EVENT" => $type == self::RATING_TYPE_CLIENT? 'rating_client': 'rating_head',
			"NOTIFY_TAG" => "IMOL|RATING|".$type.'|'.$sessionId,
			"NOTIFY_MESSAGE" => '[b]'.Loc::getMessage('IMOL_CHAT_NOTIFY_RATING_TITLE').'[/b][br]'.Loc::getMessage($notifyMessageName, Array(
				'#NUMBER#' => '[CHAT=imol|'.$sessionId.']'.$sessionId.'[/CHAT]',
				'#USER#' => '[USER='.$fromUserId.']'.$userName.'[/USER]',
				'#RATING#' => $ratingImage,
			)),
			"NOTIFY_MESSAGE_OUT" => '[b]'.Loc::getMessage('IMOL_CHAT_NOTIFY_RATING_TITLE').'[/b][br]'.Loc::getMessage($notifyMessageName, Array(
				'#NUMBER#' => '[URL=/online/?IM_HISTORY=imol|'.$sessionId.']'.$sessionId.'[/URL]',
				'#USER#' => $userName,
				'#RATING#' => $ratingText,
			)),
			"RECENT_ADD" => $userViewChat? 'Y': 'N'
		));

		return true;
	}

	public static function onGetNotifySchema()
	{
		return array(
			"imopenlines" => array(
				"rating_client" => Array(
					"NAME" => Loc::getMessage('IMOL_CHAT_NOTIFY_SCHEMA_RATING_CLIENT'),
					"LIFETIME" => 86400*7
				),
				"rating_head" => Array(
					"NAME" => Loc::getMessage('IMOL_CHAT_NOTIFY_SCHEMA_RATING_HEAD'),
					"LIFETIME" => 86400*7
				),
			),
		);
	}

	public static function onAppLang($icon, $lang = null)
	{
		$title = Loc::getMessage('IMOL_CHAT_APP_ICON_QUICK_TITLE', null, $lang);
		$description = Loc::getMessage('IMOL_CHAT_APP_ICON_QUICK_DESCRIPTION', null, $lang);

		$result = false;
		if (strlen($title) > 0)
		{
			$result = Array(
				'TITLE' => $title,
				'DESCRIPTION' => $description,
				'COPYRIGHT' => ''
			);
		}

		return $result;
	}
}