<?php
namespace Bitrix\Tasks\Rest\Controllers\Task\Counters;

use Bitrix\Tasks\Rest\Controllers\Base;

use Bitrix\Tasks\Internals\Effective as InternalsEffective;

class Effective extends Base
{
	/**
	 * Get effective data
	 *
	 * @param int $userId
	 * @param int $groupId
	 * @param array $params
	 *
	 * @return array
	 */
	public function getAction($userId = 0, $groupId=0, array $params = array())
	{
		$date = InternalsEffective::getDefaultTimeRangeFilter();

		if(!$userId)
		{
			$userId = $this->getCurrentUser()->getId();
		}

		$stat = InternalsEffective::getCountersByRange($date['FROM'], $date['TO'], $userId, $groupId);

		$violations = $stat['VIOLATIONS'];
		$inProgress = $stat['OPENED'] + $stat['CLOSED'];

		$effective = 100;
		if ($inProgress > 0)
		{
			$effective = round(
				100 - ($violations / $inProgress) * 100
			);
		}

		if ($effective < 0)
		{
			$effective = 0;
		}

		return [
			'effective'=>$effective,
			'violations'=>$violations,
			'in_progress'=>$inProgress,
			'date_start'=>$date['FROM'],
			'date_end'=>$date['TO']
		];
	}

	/**
	 * Get effective data by days
	 *
	 * @param int $userId
	 * @param int $groupId
	 * @param array $params
	 *
	 * @return array
	 */
	public function statByDayAction($userId = 0 , $groupId = 0, array $params = array())
	{
		$date = InternalsEffective::getDefaultTimeRangeFilter();

		if(!$userId)
		{
			$userId = $this->getCurrentUser()->getId();
		}

		return InternalsEffective::getStatByRange($date['FROM'], $date['TO'], $userId, $groupId);
	}
}