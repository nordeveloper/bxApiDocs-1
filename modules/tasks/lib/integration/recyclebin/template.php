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
use Bitrix\Tasks\Internals\Task\TemplateTable;
use Bitrix\Recyclebin\Internals\Contracts\Recyclebinable;
use Bitrix\Recyclebin\Internals\Entity;
use Bitrix\Main\Localization\Loc;

if (Loader::includeModule('recyclebin'))
{
	class Template implements Recyclebinable
	{
		/**
		 * @param       $templateId
		 * @param array $template
		 *
		 * @return \Bitrix\Main\Result
		 */
		public static function OnBeforeDelete($templateId, array $template = [])
		{
			$recyclebin = new Entity($templateId, Manager::TASKS_TEMPLATE_RECYCLEBIN_ENTITY, Manager::MODULE_ID);
			$recyclebin->setTitle($template['TITLE']);

			$additionalData = self::collectAdditionalData($templateId);
			if ($additionalData)
			{
				foreach ($additionalData as $action => $data)
				{
					$recyclebin->add($action, $data);
				}
			}

			$result = $recyclebin->save();

			return $result;
		}

		private static function collectAdditionalData($templateId)
		{
			$data = [];

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
					'UPDATE '.TemplateTable::getTableName().' SET ZOMBIE=\'N\' WHERE ID='.$entity->getEntityId()
				);
			} catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			try
			{
				$template = $connection->query(
					'SELECT ID, REPLICATE, REPLICATE_PARAMS FROM '.
					TemplateTable::getTableName().
					' WHERE ID='.
					$entity->getEntityId()
				)->fetch();

				if ($template["REPLICATE"] == "Y")
				{
					$name = 'CTasks::RepeatTaskByTemplateId('.$entity->getEntityId().');';

					// First, remove all agents for this template
					//				self::removeAgents($id);

					// Set up new agent

					$nextTime = \CTasks::getNextTime(
						unserialize($template['REPLICATE_PARAMS']),
						$entity->getEntityId()
					); // localtime
					if ($nextTime)
					{
						/** @noinspection PhpDynamicAsStaticMethodCallInspection */
						\CAgent::AddAgent(
							$name,
							'tasks',
							'N',        // is periodic?
							86400,        // interval (24 hours)
							$nextTime,    // datecheck
							'Y',        // is active?
							$nextTime    // next_exec
						);
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

		private static function restoreAdditionalData($taskId, $action, array $data = [])
		{
			$result = new Result();

			try
			{
				/*	foreach ($data as $value)
					{
						switch ($action)
						{
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

							case 'TAGS':
								$tag = new \CTaskTags;
								$tag->Add(
									[
										'TASK_ID' => $taskId,
										'USER_ID' => $value['USER_ID'],
										'NAME'    => $value['NAME']
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

							case 'PARAMS':
								ParameterTable::add(
									[
										'TASK_ID' => $taskId,
										'CODE'    => $value['CODE'],
										'VALUE'   => $value['VALUE']
									]
								);
								break;
						}
					}*/
			}
			catch (\Exception $e)
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}

			return $result;
		}


		public static function getNotifyMessages()
		{
			return [
				'NOTIFY' => [
					'RESTORE' => Loc::getMessage('TASKS_TEMPLATE_RECYCLEBIN_RESTORE_MESSAGE'),
					'REMOVE' => Loc::getMessage('TASKS_TEMPLATE_RECYCLEBIN_REMOVE_MESSAGE'),
				],
				'CONFIRM' => [
					'RESTORE' => Loc::getMessage('TASKS_TEMPLATE_RECYCLEBIN_RESTORE_CONFIRM'),
					'REMOVE' => Loc::getMessage('TASKS_TEMPLATE_RECYCLEBIN_REMOVE_CONFIRM')
				]
			];
		}

	}
}