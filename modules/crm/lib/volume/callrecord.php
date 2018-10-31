<?php

namespace Bitrix\Crm\Volume;

use Bitrix\Crm;
use Bitrix\Disk;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Callrecord extends Crm\Volume\Base implements Crm\Volume\IVolumeClear, Crm\Volume\IVolumeUrl
{
	/** @var array */
	protected static $filterFieldAlias = array(
	);

	/** @var Crm\Volume\Activity */
	private $activityFiles;

	/**
	 * Returns title of the indicator.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('CRM_VOLUME_CALL_RECORD_TITLE');
	}

	/**
	 * Returns availability to drop entity.
	 *
	 * @return boolean
	 */
	public function canClearEntity()
	{
		return true;
	}

	/**
	 * Can filter applied to the indicator.
	 * @return boolean
	 */
	public function canBeFiltered()
	{
		return true;
	}

	/**
	 * Tells that is is participated in the total volume.
	 * @return boolean
	 */
	public function isParticipatedTotal()
	{
		return false;
	}

	/**
	 * Get entity list path.
	 * @return string
	 */
	public function getUrl()
	{
		static $entityListPath;
		if($entityListPath === null)
		{
			$entityListPath = \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_activity_list', '/crm/activity/')
			);
		}

		return $entityListPath;
	}

	/**
	 * Get filter reset parems for entity grid.
	 * @return array
	 */
	public function getGridFilterResetParam()
	{
		$entityListReset = array(
			'FILTER_ID' => 'CRM_ACTIVITY_LIST_MY_ACTIVITIES',
			'GRID_ID' => 'CRM_ACTIVITY_LIST_MY_ACTIVITIES',
			'FILTER_FIELDS' => 'CREATED,TYPE_ID',
		);

		return $entityListReset;
	}

	/**
	 * Get filter alias for url to entity list path.
	 * @return array
	 */
	public function getFilterAlias()
	{
		return array(
			'DATE_CREATE' => 'CREATED',
		);
	}

	/**
	 * Component action list for measure process.
	 * @param array $componentCommandAlias Command alias.
	 * @return array
	 */
	public function getActionList($componentCommandAlias)
	{
		$indicatorId = static::getIndicatorId();

		$query = Crm\ActivityTable::query();

		$dateMin = new Entity\ExpressionField('DATE_MIN', "DATE_FORMAT(MIN(%s), '%%Y-%%m-%%d')", 'CREATED');
		$query->registerRuntimeField('', $dateMin)->addSelect('DATE_MIN');

		$monthCount = new Entity\ExpressionField('MONTHS', 'TIMESTAMPDIFF(MONTH, MIN(%s), MAX(%s))', array('CREATED', 'CREATED'));
		$query->registerRuntimeField('', $monthCount)->addSelect('MONTHS');

		$res = $query->exec();
		if ($row = $res->fetch())
		{
			list($dateSplitPeriod, $dateSplitPeriodUnits) = $this->getDateSplitPeriod();

			$dateMin =  new \Bitrix\Main\Type\DateTime($row['DATE_MIN'], 'Y-m-d');
			//$dateMax =  new \Bitrix\Main\Type\DateTime($row['DATE_MAX'], 'Y-m-d');
			$months =  $row['MONTHS'];

			while ($months >= 0)
			{
				$period = $dateMin->format('Y.m');
				$dateMin->add("$dateSplitPeriod $dateSplitPeriodUnits");
				$period .= '-';
				$period .= $dateMin->format('Y.m');
				$months -= $dateSplitPeriod;

				$queueList[] = array(
					'indicatorId' => $indicatorId,
					'action' => $componentCommandAlias['MEASURE_ENTITY'],
					'period' => $period,
				);
				$queueList[] = array(
					'indicatorId' => $indicatorId,
					'action' => $componentCommandAlias['MEASURE_FILE'],
					'period' => $period,
				);
			}
		}

		return $queueList;
	}

	/**
	 * Runs measure test for files.
	 * @return self
	 */
	public function measureEntity()
	{
		self::loadTablesInformation();

		$activity = new Crm\Volume\Activity();
		$activity->setFilter($this->getFilter());

		$activityQuery = $activity->prepareQuery();

		$activity->prepareFilter($activityQuery);

		// only call records
		$activityQuery->where('TYPE_ID', '=', \CCrmActivityType::Call);

		$activityCount = new Entity\ExpressionField('ACTIVITY_COUNT', 'COUNT(DISTINCT %s)', 'ID');
		$activityBindingCount = new Entity\ExpressionField('BINDINGS_COUNT', 'COUNT(%s)', 'BINDINGS.ID');
		$activityQuery
			->registerRuntimeField('', $activityCount)
			->registerRuntimeField('', $activityBindingCount)
			->addSelect('ACTIVITY_COUNT')
			->addSelect('BINDINGS_COUNT');

		$entityGroupField = array(
			'DATE_CREATE' => 'DATE_CREATED_SHORT',
			'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
		);
		foreach ($entityGroupField as $alias => $field)
		{
			$activityQuery->addSelect($field, $alias);
			$activityQuery->addGroup($field);
		}

		$querySql = $activityQuery->getQuery();

		if ($querySql != '')
		{
			$avgActivityTableRowLength = (double)self::$tablesInformation[Crm\ActivityTable::getTableName()]['AVG_SIZE'];
			$avgBindingTableRowLength = (double)self::$tablesInformation[Crm\ActivityBindingTable::getTableName()]['AVG_SIZE'];

			$querySql = "
				SELECT 
					'".static::getIndicatorId()."' as INDICATOR_TYPE,
					'".$this->getOwner()."' as OWNER_ID,
					DATE_CREATE,
					STAGE_SEMANTIC_ID, 
					(
						ACTIVITY_COUNT * {$avgActivityTableRowLength} + 
						BINDINGS_COUNT * {$avgBindingTableRowLength} ) as ACTIVITY_SIZE,
					ACTIVITY_COUNT
				FROM 
				(
					{$querySql}
				) src
			";

			$connection = \Bitrix\Main\Application::getConnection();

			$this->checkTemporally();

			$data = array(
				'INDICATOR_TYPE' => '',
				'OWNER_ID' => '',
				'DATE_CREATE' => new \Bitrix\Main\Type\Date(),
				'STAGE_SEMANTIC_ID' => '',
				'ENTITY_SIZE' => '',
				'ENTITY_COUNT' => '',
			);

			$insert = $connection->getSqlHelper()->prepareInsert(Crm\VolumeTmpTable::getTableName(), $data);

			$sqlIns = 'INSERT INTO '.$connection->getSqlHelper()->quote(Crm\VolumeTmpTable::getTableName()). '('. $insert[0]. ') ';

			$querySql = $sqlIns. $querySql;

			$connection->queryExecute($querySql);

			$this->copyTemporallyData();
		}

		return $this;
	}


	/**
	 * Runs measure test for files.
	 * @return self
	 */
	public function measureFiles()
	{
		$activityQuery = $this->prepareQuery();

		// only call records
		//$activityQuery->where('TYPE_ID', '=', \CCrmActivityType::Call);

		$entityGroupField = array(
			'DATE_CREATE' => 'DATE_CREATED_SHORT',
			'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
		);

		foreach ($entityGroupField as $alias => $field)
		{
			$activityQuery->addSelect($field, $alias);
			$activityQuery->addGroup($field);
		}

		$querySql = $activityQuery->getQuery();

		if ($querySql != '')
		{
			$querySql = "
				SELECT 
					'".static::getIndicatorId()."' as INDICATOR_TYPE,
					'".$this->getOwner()."' as OWNER_ID,
					DATE_CREATE,
					STAGE_SEMANTIC_ID, 
					SUM(FILE_SIZE) as FILE_SIZE,
					SUM(FILE_COUNT) as FILE_COUNT,
					SUM(DISK_SIZE) as DISK_SIZE,
					SUM(DISK_COUNT) as DISK_COUNT
				FROM 
				(
					{$querySql}
				) src
				GROUP BY
					DATE_CREATE,
					STAGE_SEMANTIC_ID
			";

			Crm\VolumeTable::updateFromSelect(
				$querySql,
				array(
					'FILE_SIZE' => 'destination.FILE_SIZE + source.FILE_SIZE',
					'FILE_COUNT' => 'destination.FILE_COUNT + source.FILE_COUNT',
					'DISK_SIZE' => 'destination.DISK_SIZE + source.DISK_SIZE',
					'DISK_COUNT' => 'destination.DISK_COUNT + source.DISK_COUNT',
				),
				array(
					'INDICATOR_TYPE' => 'INDICATOR_TYPE',
					'OWNER_ID' => 'OWNER_ID',
					'DATE_CREATE' => 'DATE_CREATE',
					'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
				)
			);
		}

		return $this;
	}

	/**
	 * Performs dropping entity.
	 *
	 * @return boolean
	 */
	public function clearEntity()
	{
		if (!$this->canClearEntity())
		{
			return false;
		}

		$query = $this->prepareQuery();

		$success = true;

		if ($this->prepareFilter($query))
		{
			$userPermissions = \CCrmPerms::GetUserPermissions($this->getOwner());

			$query
				//->where('TYPE_ID', '=', \CCrmActivityType::Call)// Call records
				->setSelect(array('ID', 'OWNER_TYPE_ID', 'OWNER_ID'))
				->setLimit(self::MAX_ENTITY_PER_INTERACTION)
				->setOrder(array('ID' => 'ASC'));

			if ($this->getProcessOffset() > 0)
			{
				$query->where('ID', '>', $this->getProcessOffset());
			}

			$res = $query->exec();

			while ($activity = $res->fetch())
			{
				$this->setProcessOffset($activity['ID']);

				if (\CCrmActivity::CheckItemDeletePermission($activity, $userPermissions))
				{
					Crm\Volume\Activity::DeleteActivityFiles($activity['ID']);

					if (\CCrmActivity::Delete($activity['ID'], false, false))
					{
						$this->incrementDroppedEntityCount();
					}
					else
					{
						$this->collectError(new Main\Error('Deletion failed with activity #'.$activity['ID'], self::ERROR_DELETION_FAILED));
						$this->incrementFailCount();
					}
				}
				else
				{
					$this->collectError(new Main\Error('Access denied to activity #'.$activity['ID'], self::ERROR_PERMISSION_DENIED));
					$this->incrementFailCount();
				}

				if ($this->hasTimeLimitReached())
				{
					$success = false;
					break;
				}
			}
		}

		return $success;
	}

	/**
	 * Returns count of entities.
	 *
	 * @return int
	 */
	public function countEntity()
	{
		$count = -1;

		$query = $this->prepareQuery();

		if ($this->prepareFilter($query))
		{
			$count = 0;

			$query
				//->where('TYPE_ID', '=', \CCrmActivityType::Call)// Call records
				->registerRuntimeField('', new Entity\ExpressionField('CNT', 'COUNT(%s)', 'ID'))
				->addSelect('CNT');

			$res = $query->exec();
			if ($row = $res->fetch())
			{
				$count = $row['CNT'];
			}
		}

		return $count;
	}


	/**
	 * Returns query.
	 *
	 * @return Entity\Query
	 */
	public function prepareQuery()
	{
		$this->activityFiles = new Crm\Volume\Activity();
		$this->activityFiles->setFilter($this->getFilter());

		$query = $this->activityFiles->getActivityFileMeasureQuery();

		// only call records
		$query->where('TYPE_ID', '=', \CCrmActivityType::Call);

		return $query;
	}

	/**
	 * Setups filter params into query.
	 *
	 * @param Entity\Query $query Query.
	 *
	 * @return boolean
	 */
	public function prepareFilter(Entity\Query $query)
	{
		return $this->activityFiles->prepareFilter($query);
	}
}

