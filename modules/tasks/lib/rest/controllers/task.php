<?php

namespace Bitrix\Tasks\Rest\Controllers;

use Bitrix\Main\Engine\ActionFilter as BaseActionFilter;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Tasks\Exception;
use Bitrix\Tasks\Internals\Task\LogTable;
use Bitrix\Tasks\Manager;
use Bitrix\Tasks\Util;
use Bitrix\Tasks\Util\Type\DateTime;
use TasksException;

//zoo

final class Task extends Base
{
	public function configureActions()
	{
		return [
			'fields' => [
				'prefilters' => [
					new BaseActionFilter\HttpMethod(['GET']),
				]
			],

			'add' => [
				'prefilters' => [
					new BaseActionFilter\Authentication(),
					new BaseActionFilter\HttpMethod(['GET', 'POST']),
					//					new ActionFilter\Task()
				]
			]
		];
	}

	/**
	 * Return all DB and UF_ fields of task
	 *
	 * @return array
	 */
	public function fieldsAction()
	{
		return \CTasks::getFieldsInfo();
	}

	/**
	 * Create new task
	 *
	 * @param array $fields See in tasks.api.task.fields
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 * @throws \CTaskAssertException
	 * @throws \Exception
	 */
	public function addAction(array $fields, array $params = array())
	{
		$task = \CTaskItem::add($fields, $this->getCurrentUser()->getId(), $params);

		return $this->getAction($task);
	}

	/**
	 * Get task item data
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array|null
	 */
	public function getAction(\CTaskItem $task, array $select = array(), array $params = array())
	{
		$select = !empty($select) && !in_array('*', $select) ? $select : array_keys(\CTasks::getFieldsInfo());
		$select = array_intersect($select, array_keys(\CTasks::getFieldsInfo()));

		if (in_array('STATUS', $select)) // [1]
		{
			$select[] = 'REAL_STATUS';
		}

		$dateFields = array_filter(
			\CTasks::getFieldsInfo(),
			function ($item) {// [2]
				if ($item['type'] == 'datetime')
				{
					return $item;
				}

				return null;
			}
		);

		$params['select'] = $select;
		$row = $task->getData(false, $params);

		if (array_key_exists('STATUS', $row))
		{
			$row['STATUS'] = $row['REAL_STATUS'];
			unset($row['REAL_STATUS']);
		}

		if (array_key_exists('CREATED_BY', $row))
		{
			try
			{
				$row['CREATOR'] = self::getUserInfo($row['CREATED_BY']);
			}
			catch (\Exception $e)
			{
				$row['CREATOR']['ID'] = $row['CREATED_BY'];
			}
		}

		if (array_key_exists('RESPONSIBLE_ID', $row))
		{
			try
			{
				$row['RESPONSIBLE'] = self::getUserInfo($row['RESPONSIBLE_ID']);
			}
			catch (\Exception $e)
			{
				$row['RESPONSIBLE']['ID'] = $row['RESPONSIBLE_ID'];
			}
		}

		foreach ($dateFields as $fieldName => $fieldData)
		{
			if (array_key_exists($fieldName, $row))
			{
				if ($row[$fieldName])
				{
					$row[$fieldName] = date('c', strtotime($row[$fieldName]));
				}
			}
		}

		$str = $row['VIEWED_DATE'] ? $row['VIEWED_DATE'] : $row['CREATED_DATE'];


		$filterLog[] = [
			'>CREATED_DATE' => $str,
			'TASK_ID' => $row['ID']
		];

		$_result = LogTable::getList([
			'select' => ['TASK_ID', 'FIELD', 'FROM_VALUE', 'TO_VALUE'],
			'filter' => [
				'!USER_ID' => $this->getCurrentUser()->getId(),
				'FIELD' => ['COMMENT'],
				$filterLog
			]
		]);

		while ($_row = $_result->fetch())
		{
			$row['NEW_COMMENTS_COUNT']++;
		}

		$action = $this->accessAction($task);
		$row['action'] = $action['access'][$this->getCurrentUser()->getId()];

		return ['task'=>self::snake2camelCaseRecursive($row)];
	}

	/**
	 * @param $userId
	 *
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getUserInfo($userId)
	{
		static $users = array();

		if (!$userId)
		{
			return null;
		}

		if (!$users[$userId])
		{
			// prepare link to profile
			$replaceList = array('user_id' => $userId);
			$link = \CComponentEngine::makePathFromTemplate('/company/personal/user/#user_id#/', $replaceList);

			$userFields = \Bitrix\Main\UserTable::getRowById($userId);
			if (!$userFields)
			{
				return null;
			}

			// format name
			$userName = \CUser::FormatName(
				'#NOBR##LAST_NAME# #NAME##/NOBR#',
				array(
					'LOGIN'       => $userFields['LOGIN'],
					'NAME'        => $userFields['NAME'],
					'LAST_NAME'   => $userFields['LAST_NAME'],
					'SECOND_NAME' => $userFields['SECOND_NAME']
				),
				true,
				false
			);


			$users[$userId] = array(
				'ID'   => $userId,
				'NAME' => $userName,
				'LINK' => $link,
				'ICON' => \Bitrix\Tasks\Ui\Avatar::getPerson($userFields['PERSONAL_PHOTO'])
			);
		}

		return $users[$userId];
	}

	private static function snake2camelCaseRecursive($data)
	{
		$list = [];
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$list[self::snake2camel($key)] = self::snake2camelCaseRecursive($value);
			}
			else
			{
				$list[self::snake2camel($key)] = $value;
			}
		}

		return $list;
	}

	/**
	 * Update existing task
	 *
	 * @param \CTaskItem $task
	 * @param array $fields See in tasks.api.task.fields
	 * @param array $params
	 *
	 * @return array
	 */
	public function updateAction(\CTaskItem $task, array $fields, array $params = array())
	{
		global $DB;

		$dateFields = array_filter(
			\CTasks::getFieldsInfo(),
			function ($item) {
				if ($item['type'] == 'datetime')
				{
					return $item;
				}

				return null;
			}
		);

		foreach ($dateFields as $fieldName => $fieldData)
		{
			if (array_key_exists($fieldName, $fields))
			{
				if ($fields[$fieldName])
				{
					$fields[$fieldName] = date(
						$DB->DateFormatToPhp(\CSite::GetDateFormat('FULL')),
						strtotime($fields[$fieldName])
					);
				}
			}
		}

		$task->update($fields, $params);
		\Bitrix\Pull\MobileCounter::send($this->getCurrentUser()->getId());

		return $this->getAction($task);
	}

	/**
	 * Remove existing task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function deleteAction(\CTaskItem $task, array $params = array())
	{
		$task->delete($params);

		return ['task'=>true];
	}

	/**
	 * Get list all task
	 *
	 * @param array $params
	 * @param PageNavigation $pageNavigation
	 *
	 * @return Response\DataType\Page
	 */
	public function listAction(array $filter = array(), array $select = array(), array $group = array(),
							   array $order = array(), array $params = array(), PageNavigation $pageNavigation)
	{
		$select = !empty($select) && !in_array('*', $select) ? $select : array_keys(\CTasks::getFieldsInfo());
		$select = array_intersect($select, array_keys(\CTasks::getFieldsInfo()));
		if (in_array('STATUS', $select))
		{
			$select[] = 'REAL_STATUS';
		}

		$filter = $this->getFilter($filter);

		$params['USE_MINIMAL_SELECT_LEGACY'] = 'N'; // VERY VERY BAD HACK! DONT REPEAT IT !

		if (!isset($params['RETURN_ACCESS']))
		{
			$params['RETURN_ACCESS'] = 'N'; // VERY VERY BAD HACK! DONT REPEAT IT !
		}

		$dateFields = array_filter(
			\CTasks::getFieldsInfo(),
			function ($item) {// [2]
				if ($item['type'] == 'datetime')
				{
					return $item;
				}

				return null;
			}
		);

		foreach ($filter as $fieldName => $fieldData)
		{
			preg_match('#(\w+)#', $fieldName, $m);

			if (array_key_exists($m[1], $dateFields))
			{
				if ($filter[$fieldName])
				{
					$filter[$fieldName] = DateTime::createFromTimestamp(strtotime($filter[$fieldName]));
				}
			}
		}

		$getListParams = [
			'limit'  => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
			'page'   => $pageNavigation->getCurrentPage(),

			'select'       => $select,
			'legacyFilter' => !empty($filter) ? $filter : [],
			'order'        => !empty($order) ? $order : [],
			'group'        => !empty($group) ? $group : [],
		];

		$params['PUBLIC_MODE'] = 'Y'; // VERY VERY BAD HACK! DONT REPEAT IT !
		$result = Manager\Task::getList($this->getCurrentUser()->getId(), $getListParams, $params);

		$list = array_values($result['DATA']);

		$dateFields = array_filter(
			\CTasks::getFieldsInfo(),
			function ($item) {
				if ($item['type'] == 'datetime')
				{
					return $item;
				}

				return null;
			}
		);

		foreach ($list as &$row)
		{
			if (array_key_exists('STATUS', $row))
			{
				$row['SUB_STATUS'] = $row['STATUS'];
				$row['STATUS'] = $row['REAL_STATUS'];
				unset($row['REAL_STATUS']);
			}

			if (array_key_exists('CREATED_BY', $row))
			{
				try
				{
					$row['CREATOR'] = self::getUserInfo($row['CREATED_BY']);
				}
				catch (\Exception $e)
				{
					$row['CREATOR']['ID'] = $row['CREATED_BY'];
				}
			}

			if (array_key_exists('RESPONSIBLE_ID', $row))
			{
				try
				{
					$row['RESPONSIBLE'] = self::getUserInfo($row['RESPONSIBLE_ID']);
				}
				catch (\Exception $e)
				{
					$row['RESPONSIBLE']['ID'] = $row['RESPONSIBLE_ID'];
				}
			}

			foreach ($dateFields as $fieldName => $fieldData)
			{
				if (array_key_exists($fieldName, $row))
				{
					if ($row[$fieldName])
					{
						$row[$fieldName] = date('c', strtotime($row[$fieldName]));
					}
				}
			}

			$row = self::snake2camelCaseRecursive($row);

			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				$users = array_unique(array_merge([$row['CREATED_BY']], [$row['RESPONSIBLE_ID']]));
				foreach ($users as $userId)
				{
					\CPullWatch::Add($userId, 'TASK_' . $row['ID']);
				}
			}
		}
		unset($row);

		return new Response\DataType\Page(
			'tasks',
			$list,
			function () use ($getListParams, $params, $result) {
				$obj = $result['AUX']['OBJ_RES'];
				return $obj->nSelectedCount;
		});

	}

	private function getFilter($filter)
	{
		if (!empty($filter))
		{
			if (array_key_exists('SEARCH_INDEX', $filter))
			{
				$filter['SEARCH_INDEX'] = '%'.$filter['SEARCH_INDEX'].'%';
			}

			$roleId = '';
			if(array_key_exists('ROLE', $filter))
			{
				$roleId = $filter['ROLE'];
			}

			if (array_key_exists('WO_DEADLINE', $filter) && $filter['WO_DEADLINE'] == 'Y')
			{

				switch ($roleId)
				{
					case 'R':
						$filter['!CREATED_BY'] = $this->getCurrentUser()->getId();
						break;
					case 'O':
						$filter['!RESPONSIBLE_ID'] = $this->getCurrentUser()->getId();
						break;
					default:
						$f = array();

						if (array_key_exists('GROUP_ID', $filter))
						{
							$filter['!REFERENCE:RESPONSIBLE_ID'] = 'CREATED_BY';
						}
						else
						{
							$f['::LOGIC'] = 'OR';
							$f['::SUBFILTER-R'] = [
								'!CREATED_BY' => $this->getCurrentUser()->getId(),
								'RESPONSIBLE_ID' => $this->getCurrentUser()->getId()
							];
							$f['::SUBFILTER-O'] = [
								'CREATED_BY' => $this->getCurrentUser()->getId(),
								'!RESPONSIBLE_ID' => $this->getCurrentUser()->getId()
							];

							$filter['::SUBFILTER-OR'] = $f;
						}
						break;
				}

				$filter['DEADLINE'] = '';
			}

			if (array_key_exists('NOT_VIEWED', $filter) && $filter['NOT_VIEWED'] == 'Y')
			{
				$filter['VIEWED'] = 0;
				$filter['VIEWED_BY'] = $this->getCurrentUser()->getId();

				$f = [];
				$filter['!CREATED_BY'] = $this->getCurrentUser()->getId();
				switch($roleId)
				{
					default:
						break;
					case '': // view all
						$f['::LOGIC'] = 'OR';
						$f['::SUBFILTER-R'] = [
							'RESPONSIBLE_ID' => $this->getCurrentUser()->getId()
						];
						$f['::SUBFILTER-A'] = [
							'=ACCOMPLICE' => $this->getCurrentUser()->getId()
						];
						$filter['::SUBFILTER-OR-NW'] = $f;
						break;
					case 'A':
						$filter['::SUBFILTER-R'] = [
							'=ACCOMPLICE' => $this->getCurrentUser()->getId()
						];
						break;
					case 'U':
						$filter['::SUBFILTER-R'] = [
							'=AUDITOR' => $this->getCurrentUser()->getId()
						];
						break;
				}

			}

			if (array_key_exists('STATUS', $filter))
			{
				$filter['REAL_STATUS'] = $filter['STATUS']; // hack for darkness times
				unset($filter['STATUS']);
			}
		}

		if(array_key_exists('ROLE', $filter))
		{
			switch ($filter['ROLE'])
			{
				default:
					if (array_key_exists('GROUP_ID', $filter))
					{
						$filter['MEMBER'] = $this->getCurrentUser()->getId();
					}

					$f = [];
					$f['::LOGIC'] = 'OR';
					$f['::SUBFILTER-1'] = [
						'REAL_STATUS' => $filter['REAL_STATUS']
					];
					unset($filter['REAL_STATUS']);

					$f['::SUBFILTER-2'] = [
						'=CREATED_BY' => $this->getCurrentUser()->getId(),
						'REAL_STATUS' => \CTasks::STATE_SUPPOSEDLY_COMPLETED
					];
					$filter['::SUBFILTER-OR-ORIGIN'] = $f;
					break;

				case 'R':
					$filter['=RESPONSIBLE_ID'] = $this->getCurrentUser()->getId();
					break;
				case 'A':
					$filter['=ACCOMPLICE'] = $this->getCurrentUser()->getId();
					break;
				case 'U':
					$filter['=AUDITOR'] = $this->getCurrentUser()->getId();
					break;
				case 'O':
					if (!array_key_exists('GROUP_ID', $filter))
					{
						$filter['!REFERENCE:RESPONSIBLE_ID'] = 'CREATED_BY';
						$filter['=CREATED_BY'] = $this->getCurrentUser()->getId();
					}
					break;

			}

			unset($filter['ROLE']);
		}

		return $filter;
	}

	public function accessAction(\CTaskItem $task, array $users = array(), array $params = array())
	{
		if (empty($users))
		{
			$users[] = $this->getCurrentUser()->getId();
		}

		$returnAsString = !array_key_exists('AS_STRING', $params) ||
						  array_key_exists('AS_STRING', $params) &&
						  $params['AS_STRING'] != 'N';

		$list = [];
		foreach ($users as $userId)
		{
			$list[$userId] = static::translateAllowedActionNames(
				\CTaskItem::getAllowedActionsArray($userId, $task->getData(false), $returnAsString)
			);
		}

		return ['access'=>$list];
	}

	private static function translateAllowedActionNames($can)
	{
		$newCan = array();
		if (is_array($can))
		{
			foreach ($can as $act => $flag)
			{
				$newCan[ str_replace('ACTION_', '', $act) ] = $flag;
			}

			static::replaceKey($newCan, 'CHANGE_DIRECTOR', 'EDIT.ORIGINATOR');
			static::replaceKey($newCan, 'CHECKLIST_REORDER_ITEMS', 'CHECKLIST.REORDER');
			static::replaceKey($newCan, 'ELAPSED_TIME_ADD', 'ELAPSEDTIME.ADD');
			static::replaceKey($newCan, 'START_TIME_TRACKING', 'DAYPLAN.TIMER.TOGGLE');

			// todo: when mobile stops using this fields, remove the third argument here
			static::replaceKey($newCan, 'CHANGE_DEADLINE', 'EDIT.PLAN', false); // used in mobile already
			static::replaceKey($newCan, 'CHECKLIST_ADD_ITEMS', 'CHECKLIST.ADD', false); // used in mobile already
			static::replaceKey($newCan, 'ADD_FAVORITE', 'FAVORITE.ADD', false); // used in mobile already
			static::replaceKey($newCan, 'DELETE_FAVORITE', 'FAVORITE.DELETE', false); // used in mobile already
		}

		return $newCan;
	}
	private static function replaceKey(array &$data, $from, $to, $dropFrom = true)
	{
		if (array_key_exists($from, $data))
		{
			$data[ $to ] = $data[ $from ];
			if ($dropFrom)
			{
				unset($data[ $from ]);
			}
		}
	}

	/**
	 * Delegate task to another user
	 *
	 * @param \CTaskItem $task
	 * @param $userId
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function delegateAction(\CTaskItem $task, $userId, array $params = array())
	{
		$task->delegate($userId, $params);

		return $this->getAction($task);
	}

	/**
	 * Start execute task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function startAction(\CTaskItem $task, array $params = array())
	{
		$row = $task->getData(true);
		if($row['ALLOW_TIME_TRACKING'] === 'Y')
		{
			if(!$this->startTimer($task, true))
			{
				return null;
			}
		}

		$task->startExecution($params);

		return $this->getAction($task);
	}

	/**
	 * Start an execution timer for a specified task
	 *
	 * @param \CTaskItem $task
	 * @param bool $stopPrevious
	 * @return bool|null
	 * @throws TasksException
	 */
	private function startTimer(\CTaskItem $task, $stopPrevious = false)
	{
		$timer = \CTaskTimerManager::getInstance($this->getCurrentUser()->getId());
		$lastTimer = $timer->getLastTimer();
		if(!$stopPrevious && $lastTimer['TASK_ID'] && $lastTimer['TIMER_STARTED_AT'] > 0 && intval($lastTimer['TASK_ID']) && $lastTimer['TASK_ID'] != $task->getId())
		{
			$additional = array();

			// use direct query here, avoiding cached CTaskItem::getData(), because $lastTimer['TASK_ID'] unlikely will be in cache
			list($tasks, $res) = \CTaskItem::fetchList($this->getCurrentUser()->getId(), array(), array('ID' => intval($lastTimer['TASK_ID'])), array(), array('ID', 'TITLE'));
			if(is_array($tasks))
			{
				$_task = array_shift($tasks);
				if($_task)
				{
					$data = $_task->getData(false);
					if(intval($data['ID']))
					{
						$additional['TASK'] = array(
							'ID' => $data['ID'],
							'TITLE' => $data['TITLE']
						);
					}
				}
			}

			$this->addError(new Error(GetMessage('TASKS_OTHER_TASK_ON_TIMER', ['ID'=>$data['ID'], 'TITLE'=>$data['TITLE']])));
			return null;
		}
		else
		{
			if($timer->start($task->getId()) === false)
			{
				$this->addError(new Error(GetMessage('TASKS_FAILED_START_TASK_TIMER')));
			}
		}

		return true;
	}

	/**
	 * Stop an execution timer for a specified task
	 *
	 * @param \CTaskItem $task
	 * @return bool|null
	 */
	private function stopTimer(\CTaskItem $task)
	{
		$timer = \CTaskTimerManager::getInstance($this->getCurrentUser()->getId());
		if($timer->stop($task->getId()) === false)
		{
			$this->addError(new Error(GetMessage('TASKS_FAILED_STOP_TASK_TIMER')));
			return null;
		}

		return true;
	}

	/**
	 * Stop execute task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function pauseAction(\CTaskItem $task, array $params = array())
	{
		$row = $task->getData(true);
		if($row['ALLOW_TIME_TRACKING'] === 'Y')
		{
			if(!$this->stopTimer($task))
			{
				return null;
			}
		}

		$task->pauseExecution($params);

		return $this->getAction($task);
	}

	/**
	 * Complete task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function completeAction(\CTaskItem $task, array $params = array())
	{
		$task->complete($params);

		return $this->getAction($task);
	}

	/**
	 * Defer task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function deferAction(\CTaskItem $task, array $params = array())
	{
		$task->defer($params);

		return $this->getAction($task);
	}

	/**
	 * Renew task after complete
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function renewAction(\CTaskItem $task, array $params = array())
	{
		$task->renew($params);

		return $this->getAction($task);
	}

	/**
	 * Approve task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function approveAction(\CTaskItem $task, array $params = array())
	{
		$task->approve($params);

		return $this->getAction($task);
	}

	/**
	 * Disapprove task
	 *
	 * @param \CTaskItem $task
	 * @param array $params
	 *
	 * @return array
	 * @throws TasksException
	 */
	public function disapproveAction(\CTaskItem $task, array $params = array())
	{
		$task->disapprove($params);

		return $this->getAction($task);
	}

	/**
	 * Become an auditor of a specified task
	 *
	 * @param \CTaskItem $task
	 * @return array
	 * @throws TasksException
	 */
	public function startWatchAction(\CTaskItem $task)
	{
		$task->startWatch();

		return $this->getAction($task);
	}

	/**
	 * Stop being an auditor of a specified task
	 *
	 * @param \CTaskItem $task
	 * @return array
	 * @throws TasksException
	 */
	public function stopWatchAction(\CTaskItem $task)
	{
		$task->stopWatch();

		return $this->getAction($task);
	}

	protected function buildErrorFromException(\Exception $exception)
	{
		if (!($exception instanceof Exception))
		{
			return parent::buildErrorFromException($exception);
		}

		if (Util::is_serialized($exception->getMessage()))
		{
			$message = unserialize($exception->getMessage());

			return new Error($message[0]['text'], $exception->getCode(), [$message[0]]);
		}

		return new Error($exception->getMessage(), $exception->getCode());
	}
}

/**
 * [1] for compatible with future php api
 * [2] for compatible with rest standarts
 */