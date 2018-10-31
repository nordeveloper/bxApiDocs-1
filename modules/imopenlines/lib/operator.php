<?php

namespace Bitrix\ImOpenLines;

use Bitrix\ImOpenlines\QuickAnswers\ListsDataManager;
use Bitrix\ImOpenlines\QuickAnswers\QuickAnswer;
use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

use Bitrix\ImOpenlines\Security\Permissions;
use Bitrix\ImOpenlines\Security\Helper;

Loc::loadMessages(__FILE__);

class Operator
{
	private $chatId = 0;
	private $userId = 0;
	private $error = null;
	private $moduleLoad = false;

	public function __construct($chatId, $userId = null)
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
				$this->error = new Error(__METHOD__, 'IM_LOAD_ERROR', Loc::getMessage('IMOL_OPERATOR_ERROR_IM_LOAD'));
			}
			elseif (!$pullLoad)
			{
				$this->error = new Error(__METHOD__, 'IM_LOAD_ERROR', Loc::getMessage('IMOL_OPERATOR_ERROR_PULL_LOAD'));
			}
		}

		$this->chatId = intval($chatId);

		if (is_null($userId))
		{
			$userId = $GLOBALS['USER']->GetId();
		}
		$this->userId = intval($userId);
	}

	private function checkAccess()
	{
		if (!$this->moduleLoad)
		{
			return Array(
				'RESULT' => false
			);
		}

		if ($this->chatId <= 0)
		{
			$this->error = new Error(__METHOD__, 'CHAT_ID', Loc::getMessage('IMOL_OPERATOR_ERROR_CHAT_ID'));

			return Array(
				'RESULT' => false
			);
		}
		if ($this->userId <= 0)
		{
			$this->error = new Error(__METHOD__, 'USER_ID', Loc::getMessage('IMOL_OPERATOR_ERROR_USER_ID'));

			return Array(
				'RESULT' => false
			);
		}

		$orm = \Bitrix\Im\Model\RelationTable::getList(array(
			"select" => array("ID", "ENTITY_TYPE" => "CHAT.ENTITY_TYPE"),
			"filter" => array(
				"=CHAT_ID" => $this->chatId,
				"=USER_ID" => $this->userId,
			),
		));

		if ($relation = $orm->fetch())
		{
			if ($relation["ENTITY_TYPE"] != "LINES")
			{
				$this->error = new Error(__METHOD__, 'CHAT_TYPE', Loc::getMessage('IMOL_OPERATOR_ERROR_CHAT_TYPE'));

				return Array(
					'RESULT' => false
				);
			}
		}
		else
		{
			$ormChat = \Bitrix\Im\Model\ChatTable::getById($this->chatId);
			if($chat = $ormChat->fetch())
			{
				if($chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					$parsedUserCode = Session::parseUserCode($chat['ENTITY_ID']);
					$lineId = $parsedUserCode['CONFIG_ID'];
					$fieldData = explode("|", $chat['ENTITY_DATA_1']);
					if(!\Bitrix\ImOpenLines\Config::canJoin($lineId, ($fieldData[0] == 'Y'? $fieldData[1]: null), ($fieldData[0] == 'Y'? $fieldData[2]: null)))
					{
						$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));

						return Array(
							'RESULT' => false
						);
					}
				}
				else
				{
					$this->error = new Error(__METHOD__, 'CHAT_TYPE', Loc::getMessage('IMOL_OPERATOR_ERROR_CHAT_TYPE'));

					return Array(
						'RESULT' => false
					);
				}
			}
			else
			{
				$this->error = new Error(__METHOD__, 'CHAT_ID', Loc::getMessage('IMOL_OPERATOR_ERROR_CHAT_ID'));

				return Array(
					'RESULT' => false
				);
			}
		}

		return Array(
			'RESULT' => true
		);
	}

	public function answer()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->answer($this->userId);

		return true;
	}

	public function skip()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->skip($this->userId);

		return true;
	}

	public function transfer(array $params)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'] || empty($params['TRANSFER_ID']))
		{
			return false;
		}
		if ($this->userId == $params['TRANSFER_ID'])
		{
			return false;
		}

		if (substr($params['TRANSFER_ID'], 0, 5) == 'queue')
		{
			\CUserCounter::Increment($this->userId, 'imopenlines_transfer_count_'.substr($params['TRANSFER_ID'], 5));
		}

		$chat = new Chat($this->chatId);
		$chat->transfer(Array(
			'FROM' => $this->userId,
			'TO' => $params['TRANSFER_ID']
		));

		return true;
	}

	public function setSilentMode($active = true)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->setSilentMode($active);

		return true;
	}

	public function setPinMode($active = true)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->setPauseFlag(Array(
			'ACTIVE' => $active
		));

		return true;
	}

	public function closeDialog()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->finish();

		return true;
	}

	public function markSpam()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->markSpamAndFinish($this->userId);

		return true;
	}

	public function interceptSession()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->intercept($this->userId);

		return true;
	}

	public function createLead()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$result = $chat->createLead();
		if ($result)
		{
			$this->error = new Error(__METHOD__, 'CREATE_ERROR', 'CREATE_ERROR');
		}

		return $result;
	}

	public function cancelCrmExtend($messageId)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Tracker();
		return $chat->cancel($messageId);
	}

	public function changeCrmEntity($messageId, $entityType, $entityId)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Tracker();
		return $chat->change($messageId, $entityType, $entityId);
	}

	public function joinSession()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->join($this->userId, false);

		return true;
	}

	public function openChat($userCode)
	{
		if (\Bitrix\Im\User::getInstance($this->userId)->isExtranet())
			return false;

		$chat = new Chat();
		$result = $chat->load(Array(
			'USER_CODE' => $userCode,
			'ONLY_LOAD' => 'Y',
		));
		if ($result)
		{
			$parsedUserCode = Session::parseUserCode($userCode);
			$lineId = $parsedUserCode['CONFIG_ID'];
			if ($chat->getData('AUTHOR_ID') != $this->userId)
			{
				$sessionField = $chat->getFieldData(Chat::FIELD_SESSION);
				if (!\Bitrix\ImOpenLines\Config::canJoin($lineId, $sessionField['CRM_ENTITY_TYPE'], $sessionField['CRM_ENTITY_ID']))
				{
					$result = false;
				}
			}
		}

		if ($result)
		{
			return $chat->getData();
		}
		else
		{
			$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));
			return false;
		}
	}

	public function voteAsHead($sessionId, $rating)
	{
		Session::voteAsHead($sessionId, $rating);

		return true;
	}

	public function startSession()
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->startSession($this->userId);

		return true;
	}

	public function startSessionByMessage($messageId)
	{
		$access = $this->checkAccess();
		if (!$access['RESULT'])
		{
			return false;
		}

		$chat = new Chat($this->chatId);
		$chat->startSessionByMessage($this->userId, $messageId);

		return true;
	}

	public function saveToQuickAnswers($messageId)
	{
		$message = \CIMMessenger::GetById($messageId);
		if($message)
		{
			$lineId = Session::getConfigIdByChatId($this->chatId);
			if($lineId > 0)
			{
				$listsDataManager = new ListsDataManager($lineId);
				if($listsDataManager->isHasRights())
				{
					QuickAnswer::setDataManager($listsDataManager);
					$answer = reset(QuickAnswer::getList(array('MESSAGEID' => $messageId)));
					if($answer)
					{
						$answer->update(array('TEXT' => $message['MESSAGE']));
					}
					else
					{
						$answer = reset(QuickAnswer::getList(array('TEXT' => $message['MESSAGE'])));
						if(!$answer)
						{
							$answer = QuickAnswer::add(array(
								'TEXT' => $message['MESSAGE'],
								'MESSAGEID' => $messageId,
							));
						}
					}
					if($answer && $answer->getId() > 0)
					{
						return true;
					}
				}
			}
		}

		$this->error = new Error(__METHOD__, 'CANT_SAVE_QUICK_ANSWER', Loc::getMessage('IMOL_OPERATOR_ERROR_CANT_SAVE_QUICK_ANSWER'));
		return false;
	}

	public static function getOperatorData($operatorId, $configId = null)
	{
		$operatorId = intval($operatorId);
		if ($operatorId <= 0)
		{
			return [
				'ID' => 0,
				'NAME' => '',
				'AVATAR' => '',
				'ONLINE' => false,
			];
		}

		$userData = \Bitrix\Im\User::getInstance($operatorId);

		$operator['ID'] = $operatorId;
		$operator['NAME'] = $userData->getName(false);
		if (empty($operator['NAME']))
		{
			$operator['NAME'] = Loc::getMessage('IMOL_OPERATOR_USER_NAME');
		}

		if ($configId && function_exists('customImopenlinesOperatorNames')) // Temporary hack :(
		{
			$customName = Array(
				'ID' => $operatorId,
				'NAME' => $operator['NAME']
			);
			$customName = customImopenlinesOperatorNames($configId, $customName);
			if ($customName && $customName['NAME'])
			{
				$operator['NAME'] = $customName['NAME'];
			}
		}

		$operator['AVATAR'] = $userData->getAvatar();
		$operator['ONLINE'] = $userData->isOnline();

		return $operator;
	}

	public function getSessionHistory($sessionId)
	{
		$sessionId = intval($sessionId);
		if ($sessionId <= 0)
		{
			$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));
			return false;
		}

		$orm = Model\SessionTable::getByIdPerformance($sessionId);
		$session = $orm->fetch();
		if (!$session)
		{
			$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));
			return false;
		}

		if ($session['OPERATOR_ID'] != $this->userId && !isset($session[$this->userId]))
		{
			$permission = Permissions::createWithCurrentUser();
			$allowedUserIds = Helper::getAllowedUserIds(
				Helper::getCurrentUserId(),
				$permission->getPermission(Permissions::ENTITY_HISTORY, Permissions::ACTION_VIEW)
			);
			if (is_array($allowedUserIds) && !in_array($session['OPERATOR_ID'], $allowedUserIds) &&
				!Crm::hasAccessToEntity($session['CRM_ENTITY_TYPE'], $session['CRM_ENTITY_ID'])
			)
			{
				$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));
				return false;
			}
		}

		$chatId = $session['CHAT_ID'];

		$CIMChat = new \CIMChat();
		$result = $CIMChat->GetLastMessageLimit($chatId, $session['START_ID'], $session['END_ID'], true, false);
		if ($result && isset($result['message']))
		{
			foreach ($result['message'] as $id => $ar)
				$result['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$result['usersMessage']['chat'.$chatId] = $result['usersMessage'][$chatId];
			unset($result['usersMessage'][$chatId]);
		}
		else
		{
			$this->error = new Error(__METHOD__, 'ACCESS_DENIED', Loc::getMessage('IMOL_OPERATOR_ERROR_ACCESS_DENIED'));
			return false;
		}

		$result['sessionId'] = $sessionId;
		$result['canJoin'] = \Bitrix\ImOpenLines\Config::canJoin($session['CONFIG_ID'])? 'Y':'N';
		$result['canVoteAsHead'] = \Bitrix\ImOpenLines\Config::canVoteAsHead($session['CONFIG_ID'])? 'Y':'N';
		$result['sessionVoteHead'] = intval($session['VOTE_HEAD']);

		return $result;
	}

	public function getError()
	{
		return $this->error;
	}
}