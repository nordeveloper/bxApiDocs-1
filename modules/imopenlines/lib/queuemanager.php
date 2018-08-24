<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

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
		\Bitrix\Main\Loader::includeModule("im");
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
			if (!\Bitrix\Im\User::getInstance($userId)->isExtranet())
			{
				$addQueue[$userId] = $userId;
			}
		}

		$inQueue = Array();
		$orm = Model\QueueTable::getList(array(
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
				Model\QueueTable::delete($id);
				unset($inQueue[$id]);
			}
			foreach ($addQueue as $userId)
			{
				$orm = Model\QueueTable::add(array(
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
				$userId = \Bitrix\Im\User::getInstance()->getId();
			}
			else
			{
				$userId = $businessUsers[0];
			}
			
			if ($userId)
			{
				Model\QueueTable::add(array(
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
		$orm = Model\QueueTable::getList(Array(
			'select' => Array('ID'),
			'filter' => Array(
				'!=USER_ID' => $businessUsers
			)
		));
		while($row = $orm->fetch())
		{
			Model\QueueTable::delete($row['ID']);
		}

		return true;
	}

	public function getError()
	{
		return $this->error;
	}
}