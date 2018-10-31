<?php
namespace Bitrix\ImOpenLines;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Bitrix\Im\User;

use \Bitrix\ImOpenLines\Model\QueueTable;

Loc::loadMessages(__FILE__);

class QueueManager
{
	private $error = null;
	private $id = null;
	private $config = null;

	public function __construct($id, $config = array())
	{
		$this->error = new Error(null, '', '');
		$this->id = intval($id);
		$this->config = $config;
		Loader::includeModule("im");
	}

	public function updateUsers($users)
	{
		$addQueue = Array();

		$businessUsers = Limit::getLicenseUsersLimit();
		if ($businessUsers !== false)
		{
			$users = array_intersect($users, $businessUsers);
		}
		foreach ($users as $userId)
		{
			if (!User::getInstance($userId)->isExtranet())
			{
				$addQueue[$userId] = $userId;
			}
		}

		$inQueue = Array();
		$orm = QueueTable::getList(array(
			'filter' => Array('=CONFIG_ID' => $this->id)
		));
		while ($row = $orm->fetch())
		{
			$inQueue[$row['ID']] = $row['USER_ID'];
		}

		if (implode('|', $addQueue) != implode('|', $inQueue))
		{
			foreach ($inQueue as $id => $userId)
			{
				QueueTable::delete($id);
				unset($inQueue[$id]);
			}
			foreach ($addQueue as $userId)
			{
				$orm = QueueTable::add(array(
					"CONFIG_ID" => $this->id,
					"USER_ID" => $userId,
				));
				$inQueue[$orm->getId()] = $userId;
			}
		}

		if (empty($inQueue))
		{
			if ($businessUsers === false || !isset($businessUsers[0]))
			{
				$userId = User::getInstance()->getId();
			}
			else
			{
				$userId = $businessUsers[0];
			}

			if ($userId)
			{
				QueueTable::add(array(
					"CONFIG_ID" => $this->id,
					"USER_ID" => $userId,
				));
			}
		}

		return true;
	}

	public static function checkBusinessUsers()
	{
		$businessUsers = Limit::getLicenseUsersLimit();
		if ($businessUsers === false)
		{
			return false;
		}
		$orm = QueueTable::getList(Array(
			'select' => Array('ID'),
			'filter' => Array(
				'!=USER_ID' => $businessUsers
			)
		));
		while($row = $orm->fetch())
		{
			QueueTable::delete($row['ID']);
		}

		return true;
	}

	public function getError()
	{
		return $this->error;
	}
}