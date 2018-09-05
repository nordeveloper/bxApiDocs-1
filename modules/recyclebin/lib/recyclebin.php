<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 18.05.18
 * Time: 15:38
 */

namespace Bitrix\Recyclebin;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Recyclebin\Internals\Entity;
use Bitrix\Recyclebin\Internals\Models\RecyclebinDataTable;
use Bitrix\Recyclebin\Internals\Models\RecyclebinTable;
use Bitrix\Recyclebin\Internals\User;

class Recyclebin
{
	public static function restore($recyclebinId, array $params = [])
	{
		$entity = self::getEntityData($recyclebinId);
		if (!$entity)
		{
			return false;
		}

		if($entity->getOwnerId() != User::getId() && !User::isSuper())
		{
			throw new AccessDeniedException('Access Denied');
		}

		$handler = self::getHandler($entity);

		$result = call_user_func([$handler, 'moveFromRecyclebin'], $entity);

		if ($result)
		{
			self::removeRecyclebinInternal($recyclebinId);
		}

		return $result;
	}

	private static function getEntityData($recyclebinId)
	{
		try
		{
			$recyclebin = RecyclebinTable::getById($recyclebinId)->fetch();

			$data = [];
			if ($recyclebin)
			{
				$recyclebinData = RecyclebinDataTable::getList(['filter' => ['RECYCLEBIN_ID' => $recyclebinId]])->fetchAll();
				if ($recyclebinData)
				{
					foreach ($recyclebinData as $action => $value)
					{
						$data[$action] = $value;
					}
				}
			}

			$entity = new Entity($recyclebin['ENTITY_ID'], $recyclebin['ENTITY_TYPE'], $recyclebin['MODULE_ID']);
			$entity->setId($recyclebinId);
			$entity->setData($data);
			$entity->setOwnerId($recyclebin['USER_ID']);

			return $entity;
		}
		catch (\Exception $e)
		{
		}

		return false;
	}

	private static function getHandler(Entity $entity)
	{
		$modules = self::getAvailableModules();
		$module = $modules[$entity->getModuleId()];
		$entityData = $module['LIST'][$entity->getEntityType()];

		return $entityData['HANDLER'];
	}

	public static function getAvailableModules()
	{
		static $list = null;

		if (!$list)
		{
			$event = new Event("recyclebin", "OnModuleSurvey");
			$event->send();
			if ($event->getResults())
			{
				foreach ($event->getResults() as $eventResult)
				{
					if ($eventResult->getType() == EventResult::SUCCESS)
					{
						$params = $eventResult->getParameters();
						if (empty($params) || !isset($params['LIST']) || empty($params['LIST']))
						{
							continue;
						}

						$moduleId = $eventResult->getModuleId();

						$list[$moduleId] = $params;
					}
				}
			}
		}

		return $list;
	}

	private static function removeRecyclebinInternal($recyclebinId)
	{
		try
		{
			if (RecyclebinTable::delete($recyclebinId))
			{
				RecyclebinDataTable::deleteByRecyclebinId($recyclebinId);
			}

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public static function remove($recyclebinId, array $params = [])
	{
		$entity = self::getEntityData($recyclebinId);
		if (!$entity)
		{
			return false;
		}
		if($entity->getOwnerId() != User::getId() && !User::isSuper())
		{
			throw new AccessDeniedException('Access Denied');
		}

		$handler = self::getHandler($entity);

		$result = call_user_func([$handler, 'removeFromRecyclebin'], $entity);

		if ($result)
		{
			self::removeRecyclebinInternal($recyclebinId);
		}

		return $result;
	}

	public static function preview($recyclebinId, array $params = [])
	{
		return false;
	}
}