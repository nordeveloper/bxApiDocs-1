<?php
namespace Bitrix\Tasks\Rest\Controllers\Task;

use Bitrix\Tasks\Rest\Controllers\Base;
use Bitrix\Tasks\Util\Result;
use Bitrix\Tasks\Internals;

class Counters extends Base
{
	/**
	 * @param int $userId
	 * @param int $groupId
	 * @param string $type
	 *
	 * @return array
	 */
	public function getAction($userId=0, $groupId = 0, $type = 'view_all')
	{
		if(!$userId)
		{
			$userId = $this->getCurrentUser()->getId();
		}

		$counterInstance = Internals\Counter::getInstance($userId, $groupId);

		return $counterInstance->getCounters($type);
	}
}