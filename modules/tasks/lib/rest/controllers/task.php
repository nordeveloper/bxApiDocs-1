<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 10.08.18
 * Time: 12:44
 */

namespace Bitrix\Tasks\Rest\Controllers;

use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Web\Json;

class Task extends Base
{
	/**
	 * Return all DB and UF_ fields of task
	 *
	 * @return array
//	 */
	public function fieldsAction()
	{
		return  [];
	}

	/**
	 * Create new task
	 *
	 * @param array $fields fields of task
	 * @param array $params
	 *
	 * @return int Result id added task
	 */
	public function addAction(array $fields, array $params = array())
	{
		$task = \CTaskItem::add($fields, $this->getCurrentUser()->getId(), $params);

		return $task->getId();
	}

	protected function buildErrorFromException(\Exception $exception)
	{
		if(!($exception instanceof \TasksException))
		{
			return parent::buildErrorFromException($exception);
		}

		$message = unserialize($exception->getMessage());
		return new Error($message[0]['text'], $exception->getCode(), [$message[0]]);
	}

	private function getTask($id, $userId = null)
	{
		if(!$userId)
		{
			$userId = $this->getCurrentUser()->getId();
		}

		return \CTaskItem::getInstance($id, $userId);
	}

	/**
	 * Update existing task
	 *
	 * @param int $id
	 * @param array $fields
	 * @param array $params
	 *
	 * @return bool
	 */
	public function updateAction($id, array $fields, array $params = array())
	{
		$task = $this->getTask($id);

		$task->update($fields, $params);

		return true;
	}

	/**
	 * Remove existing task
	 *
	 * @param int $id
	 * @param array $params
	 *
	 * @return bool
	 */
	public function deleteAction($id, array $params = array())
	{
		$task = $this->getTask($id);

		$task->delete($params);

		return true;
	}

	/**
	 * Get list all task
	 *
	 * @param array $params ORM get list params
	 *
	 *
	 * @return Response\DataType\Page
	 */
	public function listAction(array $params = array(), PageNavigation $pageNavigation)
	{
		$userId = $this->getCurrentUser()->getId();

		$select = $filter = $order = $group = [];

		if(array_key_exists('filter', $params))
		{
			$filter = $params['filter'];
		}
		if(array_key_exists('select', $params))
		{
			$select = $params['select'];
		}
		if(array_key_exists('order', $params))
		{
			$order = $params['order'];
		}
		if(array_key_exists('group', $params))
		{
			$group = $params['group'];
		}

		$res = \CTaskItem::fetchListArray($userId, $order, $filter, $params, $select, $group);
		/** @var \CDBResult $nav */
		$nav = $res[1];
		$nav->NavStart(10);

		$list = (array)$res[0];
		$count = $res[1]->NavRecordCount;

		return new Response\DataType\Page($list, $count);
	}

	/**
	 * @param int $id
	 * @param array $params
	 * [
	 * 	select => ['*', 'UF_*', 'SE_TAGS'] // default: *
	 *
	 * ]
	 *
	 * @return array;
	 */
	public function getAction($id, array $params = array())
	{
		$task = $this->getTask($id);

		return $task->getData(false, $params);
	}


	/***************************************** ACTIONS ****************************************************************/


	/**
	 * @param int $id
	 * @param int $userId
	 * @param array $params
	 *
	 * @return array;
	 */
	public function delegateAction($id, $userId, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function startAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function pauseAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function completeAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function deferAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function renewAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function approveAction($id, array $params = array())
	{
		return  [];
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return array;
	 */
	public function disapproveAction($id, array $params = array())
	{
		return  [];
	}
}