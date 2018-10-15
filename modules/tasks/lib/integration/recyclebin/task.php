<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 17.05.18
 * Time: 10:51
 */

namespace Bitrix\Tasks\Integration\Recyclebin;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Tasks\Integration\Forum;
use Bitrix\Tasks\Internals\TaskTable;
use Bitrix\Recyclebin\Internals\Contracts\Recyclebinable;
use Bitrix\Recyclebin\Internals\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Kanban\TaskStageTable;
use Bitrix\Tasks\Kanban\StagesTable;
use Bitrix\Tasks\Util\Type\DateTime;
use Bitrix\Tasks\Util\User;

if (Loader::includeModule('recyclebin'))
{
	class Task implements Recyclebinable
	{
		/**
		 * @param $taskId
		 * @param array $task
		 *
		 * @return Result
		 * @throws \Bitrix\Main\ArgumentException
		 * @throws \Bitrix\Main\ObjectPropertyException
		 * @throws \Bitrix\Main\SystemException
		 */
		public static function OnBeforeTaskDelete($taskId, array $task = [])
		{
			$recyclebin = new Entity($taskId, Manager::TASKS_RECYCLEBIN_ENTITY, Manager::MODULE_ID);
			$recyclebin->setTitle($task['TITLE']);

			$additionalData = self::collectTaskAdditionalData($taskId);
			if ($additionalData)
			{
				foreach ($additionalData as $action => $data)
				{
					$recyclebin->add($action, $data);
				}
			}

			$result = $recyclebin->save();
			$resultData = $result->getData();

			return $resultData['ID'];
		}

		/**
		 * @param $taskId
		 *
		 * @return array
		 * @throws \Bitrix\Main\ArgumentException
		 * @throws \Bitrix\Main\ObjectPropertyException
		 * @throws \Bitrix\Main\SystemException
		 */
		private static function collectTaskAdditionalData($taskId)
		{
			$data = [];

			try
			{
				$res = TaskStageTable::getList(['filter' => ['TASK_ID' => $taskId], 'select' => ['STAGE_ID']]);
				if ($res)
				{
					$data['STAGES'] = $res->fetchAll();
				}
			}
			catch (\Exception $e)
			{
				dd($e);
			}
			$res = \CTaskMembers::GetList([], ['TASK_ID' => $taskId, 'TYPE'=>['O','R','A','U']]);
			if ($res)
			{
				while ($row = $res->Fetch())
				{
					$data['MEMBERS'][] = [
						'USER_ID' => $row['USER_ID'],
						'TYPE'    => $row['TYPE']
					];
				}

			}

			$res = \CTaskDependence::GetList([], ['TASK_ID' => $taskId]);
			if ($res)
			{
				while ($row = $res->Fetch())
				{
					$data['DEPENDENCE_TASK'][] = [
						'DEPENDS_ON_ID' => $row['DEPENDS_ON_ID']
					];
				}
			}

			$res = \CTaskDependence::GetList([], ['DEPENDS_ON_ID' => $taskId]);
			if ($res)
			{
				while ($row = $res->Fetch())
				{
					$data['DEPENDENCE_ON'][] = [
						'TASK_ID' => $row['TASK_ID']
					];
				}
			}

			if(\CModule::IncludeModule('crm'))
			{
				$needActivityFields = [
					'OWNER_ID',
					'OWNER_TYPE_ID',
					'TYPE_ID',
					'PROVIDER_ID',
					'PROVIDER_TYPE_ID',
					'PROVIDER_GROUP_ID',
					'CALENDAR_EVENT_ID',
					'PARENT_ID',
					'THREAD_ID',
					'ASSOCIATED_ENTITY_ID',
					'SUBJECT',
					'CREATED',
					'LAST_UPDATED',
					'START_TIME',
					'END_TIME',
					'DEADLINE',
					'COMPLETED',
					'STATUS',
					'RESPONSIBLE_ID',
					'PRIORITY',
					'NOTIFY_TYPE',
					'NOTIFY_VALUE',
					'DESCRIPTION',
					'DESCRIPTION_TYPE',
					'ORIGINATOR_ID'
				];
				$res = \CCrmActivity::GetList(
					[],
					['TYPE_ID' => \CCrmActivityType::Task, 'ASSOCIATED_ENTITY_ID' => $taskId]
				);
				while ($a = $res->Fetch())
				{
					$activity = [];
					foreach ($needActivityFields as $fieldCode)
					{
						$activity[$fieldCode] = $a[$fieldCode];
					}

					$data['ACTIVITIES'][] = $activity;
				}
			}

			return $data;
		}

		/**
		 * @param Entity $entity
		 *
		 * @return Result
		 */
		public static function moveFromRecyclebin(Entity $entity)
		{
			$result = new Result();

			$connection = Application::getConnection();

			try
			{
				$connection->queryExecute(
					'UPDATE '.TaskTable::getTableName().' SET ZOMBIE=\'N\' WHERE ID='.$entity->getEntityId()
				);

				$arLogFields = array(
					"TASK_ID" => $entity->getEntityId(),
					"USER_ID" => User::getId(),
					"CREATED_DATE" => new DateTime(),
					"FIELD" => 'RENEW'
				);

				$log = new \CTaskLog();
				$log->Add($arLogFields);
			}
			catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			$dataEntity = $entity->getData();

			try
			{
				if ($dataEntity)
				{
					foreach ($dataEntity as $value)
					{
						$data = unserialize($value['DATA']);
						$action = $value['ACTION'];

						self::restoreTaskAdditionalData($entity->getEntityId(), $action, $data);
					}
				}

				$task = \CTaskItem::getInstance($entity->getEntityId(), 1);
				$task->update([], ['FORCE_RECOUNT_COUNTER'=>'Y']);
			}
			catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			return $result;
		}

		/**
		 * @param $taskId
		 * @param $action
		 * @param array $data
		 *
		 * @return Result
		 */
		private static function restoreTaskAdditionalData($taskId, $action, array $data = [])
		{
			$result = new Result();

			try
			{
				foreach ($data as $value)
				{
					switch ($action)
					{
						case 'STAGES':
							StagesTable::pinInTheStage($taskId, $value['STAGE_ID']);
							break;
						case 'MEMBERS':
							$member = new \CTaskMembers;
							$member->Add(
								[
									'TASK_ID' => $taskId,
									'USER_ID' => $value['USER_ID'],
									'TYPE'    => $value['TYPE']
								]
							);
							break;

						case 'DEPENDENCE_TASK':
							$tag = new \CTaskDependence;
							$tag->Add(
								[
									'TASK_ID'       => $taskId,
									'USER_ID'       => $value['USER_ID'],
									'DEPENDS_ON_ID' => $value['DEPENDS_ON_ID']
								]
							);
							break;
						case 'ACTIVITIES':
							if(\CModule::includeModule('crm'))
							{
								\CCrmActivity::Add($value);
							}
							break;
					}
				}
			}
			catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			return $result;

		}

		/**
		 * @param Entity $entity
		 *
		 * @return Result
		 */
		public static function removeFromRecyclebin(Entity $entity)
		{
			$result = new Result;

			$taskId = $entity->getEntityId();

			try
			{
				$connection = Application::getConnection();

				$res = $connection->query('SELECT FORUM_TOPIC_ID FROM b_tasks WHERE ID = ' . $taskId);
				$task = $res->fetch();

				Forum\Task\Topic::delete($task["FORUM_TOPIC_ID"]);
			}
			catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			return $result;
		}

		/**
		 * @param Entity $entity
		 *
		 * @return bool|void
		 * @throws NotImplementedException
		 */
		public static function previewFromRecyclebin(Entity $entity)
		{
			throw new NotImplementedException("Coming soon...");
		}

		public static function getNotifyMessages()
		{
			return [
				'NOTIFY'=> [
					'RESTORE' => Loc::getMessage('TASKS_RECYCLEBIN_RESTORE_MESSAGE'),
					'REMOVE' => Loc::getMessage('TASKS_RECYCLEBIN_REMOVE_MESSAGE'),
				],
				'CONFIRM' => [
					'RESTORE' => Loc::getMessage('TASKS_RECYCLEBIN_RESTORE_CONFIRM'),
					'REMOVE' => Loc::getMessage('TASKS_RECYCLEBIN_REMOVE_CONFIRM')
				]
			];
		}

	}
}