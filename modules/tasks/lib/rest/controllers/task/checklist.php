<?php
namespace Bitrix\Tasks\Rest\Controllers\Task;

use Bitrix\Main\Engine\Action;
use Bitrix\Tasks\Item\Task;
use Bitrix\Tasks\Rest\Controllers\Base;

use \Bitrix\Main\Error;
use Bitrix\Tasks\Internals\Task\CheckListTable;

class Checklist extends Base
{
	const ACCESS_CREATE = 100;
	const ACCESS_READ = 200;
	const ACCESS_UPDATE = 300;
	const ACCESS_DELETE = 400;

	const ACCESS_SORT = 500;

	/**
	 * Return all fields of checklist item
	 *
	 * @return array
	 */
	public function fieldsAction()
	{
		return CheckListTable::getMap();
	}

	/**
	 *  Add checklist item to task
	 *
	 * @param Task $task
	 * @param array $fields
	 * @param array $params
	 *
	 * @return int|null
	 * @throws \Exception
	 */
	public function addAction(Task $task, array $fields, array $params = array())
	{
		$fields['TASK_ID'] = $task->getId();
		$fields['CREATED_BY'] = $this->getCurrentUser()->getId();
		$fields['SORT_INDEX'] = array_key_exists('SORT_INDEX', $fields) ? $fields['SORT_INDEX'] : 0;

		$result = CheckListTable::add($fields);

		if(!$result->isSuccess())
		{
			$errors = $result->getErrors();
			foreach($errors as $error)
			{
				$this->errorCollection[] = new Error($error->getMessage(), $error->getCode());
			}
			return null;
		}

		return $result->getId();
	}

	/**
	 * Update task checklist item
	 *
	 * @param Task $task
	 * @param $itemId
	 * @param array $fields
	 * @param array $params
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function updateAction(Task $task, $itemId, array $fields, array $params = array())
	{
		$result = CheckListTable::update($itemId, $fields);

		if(!$result->isSuccess())
		{
			$errors = $result->getErrors();
			foreach($errors as $error)
			{
				$this->errorCollection[] = new Error($error->getMessage(), $error->getCode());
			}
			return null;
		}

		return $result->isSuccess();
	}

	/**
	 * Remove existing checklist item
	 *
	 * @param $itemId
	 * @param array $params
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function deleteAction($itemId, array $params = array())
	{
		$result = CheckListTable::delete($itemId);

		if(!$result->isSuccess())
		{
			$errors = $result->getErrors();
			foreach($errors as $error)
			{
				$this->errorCollection[] = new Error($error->getMessage(), $error->getCode());
			}
			return null;
		}

		return $result->isSuccess();
	}

	/**
	 * Get list all task checklist item
	 *
	 * @param Task $task
	 * @param array $params
	 *
	 * @return \Bitrix\Main\ORM\Query\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction(Task $task, array $params = array())
	{
		$params['filter']['TASK_ID'] = $task->getId();

		return CheckListTable::getList($params);
	}

	/***************************************** ACTIONS ****************************************************************/

	/**
	 * @param Task $task
	 * @param $itemId
	 * @param array $params
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function completeAction(Task $task, $itemId, array $params = array())
	{
		return $this->updateAction($task, $itemId, ['IS_COMPLETE'=>'Y'], $params);
	}

	/**
	 * @param Task $task
	 * @param $itemId
	 * @param array $params
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function renewAction(Task $task, $itemId, array $params = array())
	{
		return $this->updateAction($task, $itemId, ['IS_COMPLETE'=>'N'], $params);
	}

	/**
	 * @param \CTaskItem $task
	 * @param int $itemId
	 * @param int $afterItemId
	 *
	 * @param array $params
	 *
	 * @return bool
	 * @throws \TasksException
	 */
	public function moveAfterAction(\CTaskItem $task, $itemId, $afterItemId, array $params = array())
	{
		if($itemId == $afterItemId)
		{
			$this->errorCollection[] = new Error('ItemId and afterItemId is equal');
			return null;
		}

		$item = new \CTaskCheckListItem($task, $itemId);
		$item->moveAfterItem($afterItemId);

		return true;
	}

	protected function checkAccess($action, array $params)
	{
		$taskId = null;

		if(!array_key_exists('taskId', $params))
		{
			$taskId = \CTaskCheckListItem::getTaskIdByItemId($params['itemId']);
			if(!$taskId)
			{
				$this->errorCollection[] = new Error('Task not found', 'NO_TASK');
				return null;
			}
		}

		$task = \CTaskItem::getInstance($taskId, $this->getCurrentUser()->getId());

		if (!$task->isActionAllowed(\CTaskItem::ACTION_READ))
		{
			$this->errorCollection[]= new Error('Access denied to task');
			return null;
		}

		switch($action)
		{
			case self::ACCESS_CREATE:
			case self::ACCESS_READ:
			case self::ACCESS_UPDATE:
			case self::ACCESS_DELETE:
				if (!$task->isActionAllowed(\CTaskItem::ACTION_CHECKLIST_ADD_ITEMS))
				{
					$this->errorCollection[]= new Error('Access denied to update checklist item');
					return null;
				}
			break;
			case self::ACCESS_SORT:
				if (!$task->isActionAllowed(\CTaskItem::ACTION_CHECKLIST_REORDER_ITEMS))
				{
					$this->errorCollection[]= new Error('Access denied to move checklist item');
					return null;
				}
			break;
		}

		return true;
	}
}