<?php
namespace Bitrix\ImOpenLines;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Application,
	\Bitrix\Main\Entity\Query,
	\Bitrix\Main\ModuleManager,
	\Bitrix\Main\Type\DateTime,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Entity\ReferenceField,
	\Bitrix\Main\Entity\ExpressionField;

use \Bitrix\Im\User;

use \Bitrix\ImOpenLines\Model\QueueTable,
	\Bitrix\ImOpenLines\Model\SessionTable;

Loc::loadMessages(__FILE__);

class Queue
{
	private $error = null;
	private $id = null;
	private $session = null;
	private $config = null;
	private $chat = null;

	/**
	 * Queue constructor.
	 * @param $session
	 * @param $config
	 * @param $chat
	 */
	public function __construct($session, $config, $chat)
	{
		$this->error = new Error(null, '', '');
		$this->session = $session;
		$this->config = $config;
		$this->chat = $chat;
	}

	/**
	 * @param bool $manual
	 * @param int $currentOperator
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getNextUser($manual = false, $currentOperator = 0)
	{
		$result = Array(
			'RESULT' => false,
			'USER_ID' => 0,
			'USER_LIST' => Array()
		);

		$firstUserId = 0;
		$firstUpdateId = 0;
		$updateId = 0;

		if (!Loader::includeModule('im'))
		{
			return $result;
		}

		$filter = Array('=CONFIG_ID' => $this->config['ID']);

		if ($this->config['QUEUE_TYPE'] == Config::QUEUE_TYPE_STRICTLY)
		{
			$order = Array('ID' => 'asc');
		}
		else
		{
			$order = Array(
				'LAST_ACTIVITY_DATE' => 'asc',
				'LAST_ACTIVITY_DATE_EXACT' => 'asc'
			);
		}
		$res = self::getList(Array(
			'select' => Array('ID', 'USER_ID', 'IS_ONLINE_CUSTOM'),
			'filter' => $filter,
			'order' => $order,
		));

		$session = null;
		while($queueUser = $res->fetch())
		{
			if (!User::getInstance($queueUser['USER_ID'])->isActive())
			{
				continue;
			}

			if (User::getInstance($queueUser['USER_ID'])->isAbsent())
			{
				continue;
			}

			if ($this->config['CHECK_ONLINE'] == 'Y' && $queueUser['IS_ONLINE_CUSTOM'] != 'Y')
			{
				continue;
			}

			if ($this->config['TIMEMAN'] == "Y" && !self::getActiveStatusByTimeman($queueUser['USER_ID']))
			{
				continue;
			}

			if($this->config["MAX_CHAT"] > 0 && (empty($currentOperator) || $currentOperator != $queueUser['USER_ID']))
			{
				$filterSession = array(
					'=OPERATOR_ID' => $queueUser['USER_ID'],
					//'!CHECK.SESSION_ID' => null,
					'CONFIG_ID' => $this->config['ID'],
				);

				if($this->config["TYPE_MAX_CHAT"] == Config::TYPE_MAX_CHAT_CLOSED)
				{
					$filterSession['<STATUS'] = 50;
				}
				elseif($this->config["TYPE_MAX_CHAT"] == Config::TYPE_MAX_CHAT_ANSWERED_NEW)
				{
					$filterSession['<STATUS'] = 25;
				}
				else
				{
					$filterSession['<STATUS'] = 40;
				}

				$cntSessions = SessionTable::getList(array(
					'select' => array('CNT'),
					'filter' => $filterSession,
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)')
					)
				))->fetch();

				if($cntSessions["CNT"] >= $this->config["MAX_CHAT"])
				{
					continue;
				}
			}

			if(empty($firstUserId))
			{
				$firstUserId = $queueUser['USER_ID'];
				$firstUpdateId = $queueUser['ID'];
			}

			if($this->session['QUEUE_HISTORY'][$queueUser['USER_ID']] == true)
			{
				continue;
			}

			$result['USER_ID'] = $queueUser['USER_ID'];
			$updateId = $queueUser['ID'];
			$result['RESULT'] = true;

			break;
		}

		if(empty($result['USER_ID']) && !empty($firstUserId))
		{
			$result['USER_ID'] = $firstUserId;
			$updateId = $firstUpdateId;
			$result['RESULT'] = true;
		}

		if (!$this->session['JOIN_BOT'] && $updateId > 0)
		{
			QueueTable::update($updateId, Array('LAST_ACTIVITY_DATE' => new DateTime(), 'LAST_ACTIVITY_DATE_EXACT' => microtime(true) * 10000));
		}

		Log::write(Array('Filter' => $filter, 'Result' => $result), 'GET NEXT USER');

		return $result;
	}

	/**
	 * Check the operator responsible for CRM on the possibility of transfer of chat.
	 *
	 * @param $idUser
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function isActiveCrmUser($idUser)
	{
		$result = true;

		if (!Loader::includeModule('im'))
		{
			$result = false;
		}

		if ($result != false && !User::getInstance($idUser)->isActive())
		{
			$result = false;
		}

		if ($result != false && User::getInstance($idUser)->isAbsent())
		{
			$result = false;
		}

		if ($result != false && $this->config['TIMEMAN'] == "Y" && !self::getActiveStatusByTimeman($idUser))
		{
			$result = false;
		}

		Log::write(Array('idUser' => $idUser, 'Result' => $result), 'IS ACTIVE CRM USER');

		return $result;
	}

	public function getQueue()
	{
		if (!Loader::includeModule('im'))
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
			'USER_ID' => 0,
			'USER_LIST' => Array()
		);
		$session = null;
		while($queueUser = $res->fetch())
		{
			if (!User::getInstance($queueUser['USER_ID'])->isActive())
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

	/**
	 * Returns the time in a second after which the operator is considered offline.
	 *
	 * @return int
	 */
	public static function getTimeLastActivityOperator()
	{
		return ModuleManager::isModuleInstalled('bitrix24')? 1440: 180;
	}

	public static function getList($params)
	{
		$lastActivityDate = self::getTimeLastActivityOperator();
		$timeHelper = Application::getConnection()->getSqlHelper()->addSecondsToDateTime('(-'.$lastActivityDate.')');

		$query = new Query(QueueTable::getEntity());
		if(Loader::includeModule('im'))
		{
			$query->registerRuntimeField('', new ReferenceField(
				'IM_STATUS',
				'\Bitrix\Im\Model\StatusTable',
				array("=ref.USER_ID" => "this.USER_ID",),
				array("join_type"=>"left")
			));

			$query->registerRuntimeField('', new ExpressionField('IS_ONLINE_CUSTOM', 'CASE WHEN %s > '.$timeHelper.' &&  %s IS NULL THEN \'Y\' ELSE \'N\' END', Array('USER.LAST_ACTIVITY_DATE', 'IM_STATUS.IDLE')));
		}
		else
		{
			$query->registerRuntimeField('', new ExpressionField('IS_ONLINE_CUSTOM', 'CASE WHEN %s > '.$timeHelper.' THEN \'Y\' ELSE \'N\' END', Array('USER.LAST_ACTIVITY_DATE')));
		}

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

	/**
	 * This operator online?
	 *
	 * @param $id The user ID of the operator
	 * @return bool
	 */
	public static function isOperatorOnline($id)
	{
		return \CUser::IsOnLine($id, self::getTimeLastActivityOperator());
	}

	public function getError()
	{
		return $this->error;
	}
}