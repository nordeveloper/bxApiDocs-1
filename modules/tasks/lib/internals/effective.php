<?php

namespace Bitrix\Tasks\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Internals\Counter\EffectiveTable;
use Bitrix\Tasks\Item\Task;
use Bitrix\Tasks\Util\Type\DateTime;

Loc::loadMessages(__FILE__);

class Effective
{
	public static function getFilterId()
	{
		return 'TASKS_REPORT_EFFECTIVE_GRID';
	}

	public static function getPresetList()
	{
		return array(
			'filter_tasks_range_day' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_DAY'),
				'default' => false,
				'fields' => array(
					"DATETIME_datesel" => \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY
				)
			),
			'filter_tasks_range_month' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_MONTH'),
				'default' => true,
				'fields' => array(
					"DATETIME_datesel" => \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH
				)
			),
			'filter_tasks_range_quarter' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_QUARTER'),
				'default' => false,
				'fields' => array(
					"DATETIME_datesel" => \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER
				)
			)
		);
	}

	public static function modify($userId, $userType, Task $task, $groupId = 0, $isViolation = null)
	{
		if (!$userId ||
			!$task->responsibleId ||
			!$task->createdBy ||
			($userType == 'R' && $task->responsibleId == $task->createdBy))
		{
			return false;
		}

		$date = self::getDefaultTimeRangeFilter();

		if ($isViolation === null)
		{
			$isViolation = self::isViolation($task);
		}

		$violations = self::calcViolations(
			$userId,
			$groupId
		);  //$stat['VIOLATIONS'] + ($isViolation ? 1 : 0); //self::calcViolations($userId, $groupId); //SORRY!
		$inProgress = self::calcInProgress(
			$userId,
			$groupId
		);//$stat['OPENED'] + $stat['CLOSED']; //self::calcInProgress($userId, $groupId);

		$effective = 100;
		if($inProgress > 0)
		{
			$effective = round(
				100 - ($violations / $inProgress) * 100
			);
		}

		$dateTime = new Datetime();

		EffectiveTable::add(
			array(
				'DATETIME' => $dateTime,
				'USER_ID' => $userId,
				'USER_TYPE' => $userType,
				'GROUP_ID' => (int)$groupId,
				'EFFECTIVE' => $effective,
				'TASK_ID' => $task->getId(),

				'TASK_TITLE' => $task->title,
				'TASK_DEADLINE' => $task->deadline,

				'IS_VIOLATION'=>$isViolation  ? 'Y' : 'N'
			)
		);

		// TODO
		$stat = self::getCountersByRange($date['FROM'], $date['TO'], $userId, $groupId);

		$violations = $stat['VIOLATIONS'] + ($isViolation ? 1 : 0);
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

		\CUserCounter::Set(
			$userId,
			Counter::getPrefix().Counter\Name::EFFECTIVE,
			$effective,
			'**',
			'',
			false
		);

		return true;
	}

	public static function repair($taskId, $userId = null, $userType = 'R')
	{
		$taskId = (int)$taskId;
		$sql = "
			UPDATE b_tasks_effective SET DATETIME_REPAIR = NOW() WHERE 
				TASK_ID = {$taskId} AND IS_VIOLATION='Y' AND DATETIME_REPAIR IS NULL
		";

		if ($userId > 0)
		{
			$userType = $userType == 'A' ? 'A' : 'R';
			$sql .= ' AND USER_ID = '.$userId.' AND USER_TYPE = \''.$userType.'\'';
		}

		Application::getConnection()->queryExecute($sql);

		return true;
	}

	private static function isViolation(Task $task)
	{
		if(!$task->deadline)
		{
			return false;
		}

		$deadline = DateTime::createFrom($task->deadline);
		$now = new Datetime();

		return $deadline->checkLT($now);
	}

	private static function getDefaultTimeRangeFilter()
	{
		//		$filterOptions = new Filter\Options(
		//			Effective::getFilterId(), Effective::getPresetList()
		//		);
		//
		//		$defId = $filterOptions->getDefaultFilterId();
		//		$settings = $filterOptions->getFilterSettings($defId);
		//		$filtersRaw = Filter\Options::fetchFieldValuesFromFilterSettings($settings);
		//
		//		$dateFrom = DateTime::createFrom($filtersRaw['DATETIME_from']);
		//		$dateTo = DateTime::createFrom($filtersRaw['DATETIME_to']);
		//
		//		if (!$dateFrom || !$dateTo)
		//		{
			$currentDate = new Datetime();

			$dateFrom = DateTime::createFromTimestamp(
				strtotime($currentDate->format('01.m.Y 00:00:01'))
			);

			$dateTo = DateTime::createFromTimestamp(
				strtotime($currentDate->format('t.m.Y 23:59:59'))
			);

		//		}

		return array(
			'FROM' => $dateFrom,
			'TO' => $dateTo
		);
	}

	public static function getByRange(DateTime $timeFrom = null, Datetime $timeTo = null, $userId = null, $groupId = 0)
	{
		if(!$timeFrom || !$timeTo)
		{
			$times = self::getDefaultTimeRangeFilter();
			$timeFrom = $times['FROM'];
			$timeTo = $times['TO'];
		}

		$params = array(
			'filter' => array(
				'>=DATETIME' => $timeFrom,
				'<=DATETIME' => $timeTo
			),
			'select' => array('EFFECTIVE'),
			'runtime' => array(
				new Entity\ExpressionField('EFFECTIVE', 'AVG(EFFECTIVE)')
			)
		);

		if ($userId > 0)
		{
			$params['filter']['USER_ID'] = $userId;
			$params['group'][]='USER_ID';
		}

		if ($groupId > 0)
		{
			$params['filter']['GROUP_ID'] = $groupId;
			$params['group'][]='GROUP_ID';
		}

		$result = EffectiveTable::getRow($params);

		return $result ? $result['EFFECTIVE'] : 100;
	}

	public static function getStatByRange(DateTime $timeFrom = null, Datetime $timeTo = null, $userId = null,
										  $groupId = 0, $groupBy = 'DATE')
	{
		$availGroupsBy = array('DATE', 'HOUR');
		if (!in_array($groupBy, $availGroupsBy))
		{
			$groupBy = 'DATE';
		}

		$params = array(
			'filter' => array(
				'>=DATETIME' => $timeFrom,
				'<=DATETIME' => $timeTo,
				'USER_ID' => $userId
			),
			'select' => array('EFFECTIVE', $groupBy == 'DATE' ? 'DATE' : 'HOUR'),
			'runtime' => array(
				new Entity\ExpressionField('EFFECTIVE', 'AVG(EFFECTIVE)'),
				new Entity\ExpressionField('DATE', 'DATE(DATETIME)'),
				new Entity\ExpressionField('HOUR', 'DATE_FORMAT(DATETIME, "%%Y-%%m-%%d %%H:00:01")'),
			),
			'group'=>array(
				$groupBy
			)
		);

		if ($userId > 0)
		{
			$params['filter']['USER_ID'] = $userId;
			$params['group'][]='USER_ID';
		}

		if ($groupId > 0)
		{
			$params['filter']['GROUP_ID'] = $groupId;
			$params['group'][]='GROUP_ID';
		}

		$result = EffectiveTable::getList($params);

		return $result->fetchAll();
	}

	private static function calcInProgress($userId, $groupId = 0)
	{
		$deffered = \CTasks::STATE_DEFERRED;

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT,
				t.GROUP_ID
			FROM 
				b_tasks AS t
				JOIN b_tasks_member as tm ON 
					tm.TASK_ID = t.ID AND 
					tm.USER_ID = {$userId} AND
					tm.TYPE IN('A', 'R') 
			WHERE
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				) AND 
				t.ZOMBIE = 'N'
				
				".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : "")."
				
				AND 
				(
					(t.CLOSED_DATE IS NULL AND STATUS != {$deffered})
					OR 
					DATE(t.CLOSED_DATE) = DATE(NOW())
				)
			GROUP BY 
				t.GROUP_ID
		";

		$counters = Application::getConnection()->query($sql)->fetch();
		return $counters['COUNT'];
	}

	private static function calcViolations($userId, $groupId = 0)
	{
		$expiredTime = Counter::getExpiredTime()->format('Y-m-d H:i:s');

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT,
				t.GROUP_ID
			FROM 
				b_tasks as t
				INNER JOIN b_tasks_member as tm 
					ON tm.TASK_ID = t.ID AND tm.TYPE IN ('R', 'A')
			WHERE 
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				)  
				
				AND STATUS  < 4
				AND STATUS  != 6
				
				AND t.DEADLINE < '{$expiredTime}'
				AND t.ZOMBIE = 'N'
				
				".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : "")."
				
				AND t.CLOSED_DATE IS NULL
			GROUP BY 
				t.GROUP_ID
		";

		$res = Application::getConnection()->query($sql);
		if(!$res)
		{
			return 0;
		}

		$counters = $res->fetch();
		return $counters['COUNT'];
	}

	public static function agent($date = '')
	{
		$date = $date ? (new DateTime($date, 'Y-m-d')) : new DateTime();

		$sql = "
			SELECT DISTINCT 
			   ef1.USER_ID 
			FROM 
			   b_tasks_effective ef1
			WHERE 
			   NOT EXISTS (
			    SELECT ef2.ID FROM b_tasks_effective ef2 
			    WHERE 
			      ef2.DATETIME > '".$date->format('Y-m-d')." 00:00:00' AND 
			      ef2.DATETIME <= '".$date->format('Y-m-d')." 23:59:59' AND 
			      ef2.USER_ID = ef1.USER_ID 
				)
		";

		$users = Application::getConnection()->query($sql)->fetchAll();
		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$userId = $user['USER_ID'];
				$violations = self::calcViolations($userId);
				$inProgress = self::calcInProgress($userId);

				//				$dateRange = self::getDefaultTimeRangeFilter();
				//				$stat = self::getCountersByRange($dateRange['FROM'], $dateRange['TO'], $userId, 0);
				//
				//				$violations = $stat['VIOLATIONS']; //self::calcViolations($userId, $groupId);
				//				$inProgress = $stat['OPENED'] + $stat['CLOSED']; //self::calcInProgress($userId, $groupId);

				$effective = 100;
				if ($inProgress > 0)
				{
					$effective = round(
						100 - ($violations / $inProgress) * 100
					);
				}

				EffectiveTable::add(
					array(
						'DATETIME' => $date,
						'USER_ID' => $userId,
						'USER_TYPE' => '',
						'GROUP_ID' => 0,
						'EFFECTIVE' => $effective,
						'TASK_ID' => '',
						'IS_VIOLATION' => 'N'
					)
				);
			}
		}

		$date->addDay(1);

		return '\Bitrix\Tasks\Internals\Effective::agent("'.$date->format('Y-m-d').'");';
	}

	private static function getCountersByRange(Datetime $dateFrom, Datetime $dateTo, $userId, $groupId = 0)
	{
		$out = array();

		$userId = intval($userId);
		$groupId = intval($groupId);

		$sql = '
			SELECT
				COUNT(TASK_ID) as count
			FROM 
				b_tasks_effective as te
				JOIN b_tasks as t ON te.TASK_ID = t.ID
			WHERE
				te.USER_ID = '.intval($userId).'
				AND IS_VIOLATION = \'Y\'
				AND t.RESPONSIBLE_ID > 0
				AND (
					(DATETIME >= \''.$dateFrom->format('Y-m-d H:i:s').'\' AND DATETIME <= \''.$dateTo->format('Y-m-d H:i:s').'\')
					OR (DATETIME <= \''.$dateTo->format('Y-m-d H:i:s').'\' AND DATETIME_REPAIR IS NULL)
					OR (DATETIME <= \''.$dateTo->format('Y-m-d H:i:s').'\' AND DATETIME_REPAIR >= \''.$dateFrom->format('Y-m-d H:i:s').'\')
 				)
 				'.($groupId > 0 ? 'AND te.GROUP_ID = '.$groupId : '');

		$out['VIOLATIONS'] = (int)\Bitrix\Main\Application::getConnection()->queryScalar($sql);

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT
			FROM 
				b_tasks as t
				JOIN b_tasks_member as tm ON tm.TASK_ID = t.ID AND tm.TYPE IN ('R', 'A')
			WHERE
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				)
				
				". ($groupId>0 ? "AND t.GROUP_ID = {$groupId}" : '')."
				
				AND 
					t.CLOSED_DATE >= '".$dateFrom->format('Y-m-d H:i:s')."'
					AND t.CLOSED_DATE <= '".$dateTo->format('Y-m-d H:i:s')."'
			";
		$out['CLOSED'] = (int)\Bitrix\Main\Application::getConnection()->queryScalar($sql);

		$sql = "
            SELECT 
                COUNT(t.ID) as COUNT
            FROM 
                b_tasks as t
                JOIN b_tasks_member as tm ON tm.TASK_ID = t.ID  AND tm.TYPE IN ('R', 'A')
            WHERE
                (
                    (tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
                    OR 
                    (tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
                )
                
                ".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : '')."
                
                AND t.CREATED_DATE <= '".$dateTo->format('Y-m-d H:i:s')."'
				AND 
				(
					t.CLOSED_DATE >= '".$dateFrom->format('Y-m-d H:i:s')."'
					OR
					CLOSED_DATE is null
				)
				
                AND t.ZOMBIE = 'N'
                AND t.STATUS != 6
            ";
		$out['OPENED'] = (int)\Bitrix\Main\Application::getConnection()->queryScalar($sql);

		return $out;
	}

	public static function getMiddleCounter($userId, $groupId = 0)
	{
		$date = self::getDefaultTimeRangeFilter();

		// TODO
		$stat = self::getCountersByRange($date['FROM'], $date['TO'], $userId, $groupId);

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

//		\CUserCounter::Set(
//			$userId,
//			Counter::getPrefix().Counter\Name::EFFECTIVE,
//			$effective,
//			'**',
//			'',
//			false
//		);

		return $effective;
	}
}