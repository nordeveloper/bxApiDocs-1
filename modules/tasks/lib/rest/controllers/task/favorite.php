<?php
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
	public function addAction(\CTaskItem $task, array $params = array())
	{
		$task->addToFavorite($params);

		return true;
	}

	/**
	 * Remove existing task
	 *
	 * @param int $taskId
	 * @param array $params
	 *
	 * @return bool
	 */
	public function deleteAction(\CTaskItem $task, array $params = array())
	{
		$task->deleteFromFavorite($params);

		return true;
	}
}