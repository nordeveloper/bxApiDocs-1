<?php

namespace Bitrix\Recyclebin\Internals;

class User
{
	/**
	 * Returns current user ID
	 * @return integer
	 */
	public static function getId()
	{
		global $USER;

		if (is_object($USER) && method_exists($USER, 'getId'))
		{
			$userId = $USER->getId();
			if ($userId > 0)
			{
				return $userId;
			}
		}

		return 0;
	}

	/**
	 * Check if a user with a given id is admin
	 *
	 * @param 0 $userId
	 *
	 * @return bool
	 */
	public static function isAdmin($userId = 0)
	{
		global $USER;

		static $users = array();

		if ($userId === 0 || $userId === false)
		{
			$userId = null;
		}

		$isAdmin = false;
		$loggedInUserId = null;

		if ($userId === null)
		{
			if (is_object($USER) && method_exists($USER, 'GetID'))
			{
				$loggedInUserId = (int)$USER->GetID();
				$userId = $loggedInUserId;
			}
			else
			{
				$loggedInUserId = false;
			}
		}

		if ($userId > 0)
		{
			if (!isset($users[$userId]))
			{
				if ($loggedInUserId === null)
				{
					if (is_object($USER) && method_exists($USER, 'GetID'))
					{
						$loggedInUserId = (int)$USER->GetID();
					}
				}

				if ((int)$userId === $loggedInUserId)
				{
					$users[$userId] = (bool)$USER->isAdmin();
				}
				else
				{
					/** @noinspection PhpDynamicAsStaticMethodCallInspection */
					$ar = \CUser::GetUserGroup($userId);
					if (in_array(1, $ar, true) || in_array('1', $ar, true))
						$users[$userId] = true;    // user is admin
					else
						$users[$userId] = false;    // user isn't admin
				}
			}

			$isAdmin = $users[$userId];
		}

		return ($isAdmin);
	}

	public static function isSuper($userId = 0)
	{
		return static::isAdmin($userId) || \Bitrix\Recyclebin\Integration\Bitrix24\User::isAdmin($userId);
	}
}