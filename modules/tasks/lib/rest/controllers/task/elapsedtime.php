<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 10.08.18
 * Time: 12:44
 */

namespace Bitrix\Tasks\Rest\Controllers\Task;

use Bitrix\Tasks\Rest\Controllers\Base;

use \Bitrix\Main\Error;

class Elapsedtime extends Base
{
	/**
	 * Return all fields of elapsed time
	 *
	 * @return array
	 */
	public function fieldsAction()
	{
		return  [];
	}


	/**
	 * Add elapsed time to task
	 *
	 * @param int $taskId
	 * @param array $fields
	 *
	 * @param array $params
	 *
	 * @return int
	 */
	public function addAction($taskId, array $fields, array $params = array())
	{
		return 1;
	}

	/**
	 * Update task elapsed time
	 *
	 * @param int $taskId
	 * @param int $itemId
	 * @param array $fields
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function updateAction($taskId, $itemId, array $fields, array $params = array())
	{
		return false;
	}

	/**
	 * Remove existing elapsed time
	 *
	 * @param int $taskId
	 * @param int $itemId
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function deleteAction($taskId, $itemId, array $params = array())
	{
		return false;
	}

	/**
	 * Get list all task elapsed time
	 *
	 * @param int $taskId
	 * @param array $params ORM get list params
	 *
	 *
	 * @return array
	 */
	public function listAction($taskId, array $params = array())
	{
		return [];
	}
}