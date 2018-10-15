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

class Favorite extends Base
{
	/**
	 * Add task to favorite
	 *
	 * @param int $taskId
	 * @param array $params
	 *
	 * @return bool
	 */
	public function addAction($taskId, array $params = array())
	{
		return false;
	}

	/**
	 * Remove existing task
	 *
	 * @param int $taskId
	 * @param array $params
	 *
	 * @return bool
	 */
	public function deleteAction($taskId, array $params = array())
	{
		return false;
	}

	/**
	 * Get list all task
	 *
	 * @param array $params ORM get list params
	 *
	 *
	 * @return array
	 */
	public function listAction(array $params = array())
	{
		return [];
	}
}