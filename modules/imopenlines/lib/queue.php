<?php
namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Queue
{
	private $error = null;
	private $id = null;
	private $session = null;
	private $config = null;
	private $chat = null;

	public function __construct($session, $config, $chat)
	{
		$this->error = new Error(null, '', '');
		$this->session = $session;
		$this->config = $config;
		$this->chat = $chat;
	}

	public function getNextUser($manual = false)
	{
		$result = Array(
			'RESULT' => false,
			'FIRST_IN_QUEUE' => 0,
			'FIRST_IN_QUEUE_ID' => 0,
			'USER_ID' => 0,
			'USER_LIST' => Array()
		);

		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return $result;
		}

		$filter = Array('=CONFIG_ID' => $this->config['ID']);
		//TODO удалить.
		if (!empty($this->session['QUEUE_HISTORY']) && $this->config['NO_ANSWER_RULE'] != Config::RULE_QUEUE)
		{
			if ($this->config['QUEUE_TYPE'] != Config::QUEUE_TYPE_EVENLY || !$manual)
			{
				$filter['!=USER_ID'] = array_keys($this->session['QUEUE_HISTORY']);
			}
		}
		//TODO END удалить.
		if ($this->config['QUEUE_TYPE'] == Config::QUEUE_TYPE_STRICTLY)
		{
			$order = Array('ID' => 'asc');
		}
		else
		{
			$order = Array('LAST_ACTIVITY_DATE' => 'asc');
		}
		$res = self::getList(Array(
			'select' => Array('ID', 'USER_ID', 'IS_ONLINE_CUSTOM'),
			'filter' => $filter,
			'order' => $order,
		));

		$session = null;
		$reserve = Array();
		while($queueUser = $res->fetch())
		{
			if (!\Bitrix\Im\User::getInstance($queueUser['USER_ID'])->isActive())
			{
				continue;
			}

			if (\Bitrix\Im\User::getInstance($queueUser['USER_ID'])->isAbsent())
			{
				//TODO удалить.
				$reserve['FIRST_IN_QUEUE'] = $queueUser['USER_ID'];
				$reserve['FIRST_IN_QUEUE_ID'] = $queueUser['ID'];
				//TODO END удалить.
				continue;
			}

			if ($result['FIRST_IN_QUEUE'] <= 0)
			{
				$result['FIRST_IN_QUEUE'] = $queueUser['USER_ID'];
				$result['FIRST_IN_QUEUE_ID'] = $queueUser['ID'];
			}

			if (
				$queueUser['IS_ONLINE_CUSTOM'] != 'Y'
			)
			{
				continue;
			}

			if ($this->config['TIMEMAN'] == "Y" && !self::getActiveStatusByTimeman($queueUser['USER_ID']))
			{
				if (!$session)
				{
					$session = new Session();
					$session->loadByArray($this->session, $this->config, $this->chat);
				}

				$this->session['QUEUE_HISTORY'][$queueUser['USER_ID']] = true;

				if ($this->session['ID'])
				{
					$session->update(Array(
						'QUEUE_HISTORY' => $this->session['QUEUE_HISTORY'],
						'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
						'SKIP_DATE_CLOSE' => 'Y'
					));
				}

				continue;
			}

			if (!$this->session['JOIN_BOT'])
			{
				Model\QueueTable::update($queueUser['ID'], Array('LAST_ACTIVITY_DATE' => new \Bitrix\Main\Type\DateTime()));
			}

			$result['USER_ID'] = $queueUser['USER_ID'];
			$result['RESULT'] = true;

			break;
		}

		if (!$result['RESULT'] && !$result['FIRST_IN_QUEUE'])
		{
			$result['FIRST_IN_QUEUE'] = $reserve['FIRST_IN_QUEUE'];
			$result['FIRST_IN_QUEUE_ID'] = $reserve['FIRST_IN_QUEUE_ID'];
		}

		if (!$result['RESULT'] && $result['FIRST_IN_QUEUE'] && !$this->session['JOIN_BOT'])
		{
			Model\QueueTable::update($result['FIRST_IN_QUEUE_ID'], Array('LAST_ACTIVITY_DATE' => new \Bitrix\Main\Type\DateTime()));
		}


		Log::write(Array('Filter' => $filter, 'Result' => $result), 'GET NEXT USER');

		return $result;
	}

	public function getQueue()
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return null;
		}

		$filter = Array('=CONFIG_ID' => $this->config['ID']);
		$res = self::getList(Array(
			'select' => Array('ID', 'USER_ID'),
			'filter' => $filter
		));
		$result = Array(
			'RESULT' => false,
			'FIRST_IN_QUEUE' => 0,
			'FIRST_IN_QUEUE_ID' => 0,
			'USER_ID' => 0,
			'USER_LIST' => Array()
		);
		$session = null;
		while($queueUser = $res->fetch())
		{
			if (!\Bitrix\Im\User::getInstance($queueUser['USER_ID'])->isActive())
			{
				continue;
			}

			$result['RESULT'] = true;
			$result['USER_LIST'][] = $queueUser['USER_ID'];
		}

		Log::write(Array('Filter' => $filter, 'Result' => $result), 'GET ALL QUEUE');

		return $result;
	}

	public static function getActiveStatusByTimeman($userId)
	{
		if ($userId <= 0)
			return false;

		if (\CModule::IncludeModule('timeman'))
		{
			$tmUser = new \CTimeManUser($userId);
			$tmSettings = $tmUser->GetSettings(Array('UF_TIMEMAN'));
			if (!$tmSettings['UF_TIMEMAN'])
			{
				$result = true;
			}
			else
			{
				$tmUser->GetCurrentInfo(true); // need for reload cache

				if ($tmUser->State() == 'OPENED')
				{
					$result = true;
				}
				else
				{
					$result = false;
				}
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	public static function getList($params)
	{
		$lastActivityDate = \IsModuleInstalled('bitrix24')? 1440: 180;
		$timeHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper()->addSecondsToDateTime('(-'.$lastActivityDate.')');

		$query = new \Bitrix\Main\Entity\Query(\Bitrix\ImOpenLines\Model\QueueTable::getEntity());
		$query->registerRuntimeField('', new \Bitrix\Main\Entity\ExpressionField('IS_ONLINE_CUSTOM', 'CASE WHEN %s > '.$timeHelper.' THEN \'Y\' ELSE \'N\' END', Array('USER.LAST_ACTIVITY_DATE')));

		if (isset($params['select']))
		{
			$query->setSelect($params['select']);
		}
		else
		{
			$query->addSelect('ID')->addSelect('IS_ONLINE_CUSTOM');
		}

		if (isset($params['filter']))
		{
			$query->setFilter($params['filter']);
		}

		if (isset($params['order']))
		{
			$query->setOrder($params['order']);
		}

		return $query->exec();
	}

	public function getError()
	{
		return $this->error;
	}
}