<?php

namespace Bitrix\Crm\Volume;

use Bitrix\Crm;
use Bitrix\Disk;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Quote extends Crm\Volume\Base implements  Crm\Volume\IVolumeClear, Crm\Volume\IVolumeClearActivity, Crm\Volume\IVolumeClearEvent, Crm\Volume\IVolumeUrl
{
	/** @var array */
	protected static $entityList = array(
		Crm\QuoteTable::class,
		Crm\QuoteElementTable::class,
		Crm\Binding\QuoteContactTable::class,
	);

	/** @var array */
	protected static $filterFieldAlias = array(
		'DEAL_STAGE_SEMANTIC_ID' => 'DEAL.STAGE_SEMANTIC_ID',
		'LEAD_STAGE_SEMANTIC_ID' => 'LEAD.STATUS_SEMANTIC_ID',
		'QUOTE_STATUS_ID' => 'STATUS_ID',
		'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
		'QUOTE_STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
		'DEAL_DATE_CREATE' => 'DEAL.DATE_CREATE',
		'LEAD_DATE_CREATE' => 'LEAD.DATE_CREATE',
		'QUOTE_DATE_CREATE' => 'DATE_CREATE',
		'DATE_CREATE' => 'DATE_CREATE',
		'DEAL_DATE_CLOSE' => 'DEAL.CLOSEDATE',
		'LEAD_DATE_CLOSE' => 'LEAD.DATE_CLOSED',
		'QUOTE_DATE_CLOSE' => 'CLOSEDATE',
	);

	/**
	 * Returns title of the indicator.
	 * @return string
	 */
	public function getTitle()
	{
		//$descriptions = \CCrmOwnerType::GetAllCategoryCaptions();
		//return $descriptions[\CCrmOwnerType::Quote];
		return Loc::getMessage('CRM_VOLUME_QUOTE_TITLE');
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
				\Bitrix\Main\Config\Option::get('crm', 'path_to_quote_list', '/crm/quote/list/')
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
			'FILTER_ID' => 'CRM_QUOTE_LIST_V12',
			'GRID_ID' => 'CRM_QUOTE_LIST_V12',
			'FILTER_FIELDS' => 'STATUS_ID,DATE_CREATE',
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
			'DATE_CREATE' => 'DATE_CREATE',
			'STAGE_SEMANTIC_ID' => function(&$param, $filterInp){
				$statuses = Crm\Volume\Quote::getStatusSemantics($filterInp);
				foreach ($statuses as $status)
				{
					if (is_array($param))
					{
						if (!isset($param['STATUS_ID']))
						{
							$param['STATUS_ID'] = array();
						}
						$param['STATUS_ID'][] = htmlspecialcharsbx($status);
					}
					else
					{
						$param .= "&STATUS_ID[]=". htmlspecialcharsbx($status);
					}
				}
			},
		);
	}

	/**
	 * Returns availability to drop entity.
	 *
	 * @return boolean
	 */
	public function canClearEntity()
	{
		$userPermissions = \CCrmPerms::GetUserPermissions($this->getOwner());
		if ($userPermissions->HavePerm('QUOTE', BX_CRM_PERM_NONE, 'READ'))
		{
			$this->collectError(new Main\Error('', self::ERROR_PERMISSION_DENIED));

			return false;
		}

		if ($userPermissions->HavePerm('QUOTE', BX_CRM_PERM_NONE, 'DELETE'))
		{
			$this->collectError(new Main\Error('', self::ERROR_PERMISSION_DENIED));

			return false;
		}

		return true;
	}

	/**
	 * Returns availability to drop entity activities.
	 *
	 * @return boolean
	 */
	public function canClearActivity()
	{
		$activityVolume = new Crm\Volume\Activity();
		return $activityVolume->canClearEntity();
	}

	/**
	 * Returns availability to drop entity event.
	 *
	 * @return boolean
	 */
	public function canClearEvent()
	{
		$eventVolume = new Crm\Volume\Event();
		return $eventVolume->canClearEntity();
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
	 * Returns relations between quote status and stage semantic.
	 * @param array $stageIds Only for this stage will return statuses.
	 * @return array
	 */
	public static function getStatusSemantics($stageIds = array())
	{
		static $processStatusIDs;

		if (empty($processStatusIDs))
		{
			$processStatusIDs = array(
				Crm\PhaseSemantics::PROCESS => array(),
				Crm\PhaseSemantics::FAILURE => array(),
				Crm\PhaseSemantics::SUCCESS => array(),
			);
			foreach (array_keys(\CCrmQuote::GetStatuses()) as $statusID)
			{
				if (\CCrmQuote::GetSemanticID($statusID) === Crm\PhaseSemantics::PROCESS)
				{
					$processStatusIDs[Crm\PhaseSemantics::PROCESS][] = $statusID;
				}
				if (\CCrmQuote::GetSemanticID($statusID) === Crm\PhaseSemantics::FAILURE)
				{
					$processStatusIDs[Crm\PhaseSemantics::FAILURE][] = $statusID;
				}
				if (\CCrmQuote::GetSemanticID($statusID) === Crm\PhaseSemantics::SUCCESS)
				{
					$processStatusIDs[Crm\PhaseSemantics::SUCCESS][] = $statusID;
				}
			}
		}
		if (count($stageIds) > 0)
		{
			$statuses = array();
			foreach ($stageIds as $stageId)
			{
				if (isset($processStatusIDs[$stageId]))
				{
					$statuses = array_merge($statuses, $processStatusIDs[$stageId]);
				}
			}

			return $statuses;
		}

		return $processStatusIDs;
	}


	/**
	 * Component action list for measure process.
	 * @param array $componentCommandAlias Command alias.
	 * @return array
	 */
	public function getActionList($componentCommandAlias)
	{
		$indicatorId = static::getIndicatorId();


		$query = Crm\QuoteTable::query();

		$dateMin = new Entity\ExpressionField('DATE_MIN', "DATE_FORMAT(MIN(%s), '%%Y-%%m-%%d')", 'DATE_CREATE');
		$query->registerRuntimeField('', $dateMin)->addSelect('DATE_MIN');

		$monthCount = new Entity\ExpressionField('MONTHS', 'TIMESTAMPDIFF(MONTH, MIN(%s), MAX(%s))', array('DATE_CREATE', 'DATE_CREATE'));
		$query->registerRuntimeField('', $monthCount)->addSelect('MONTHS');

		$res = $query->exec();
		if ($row = $res->fetch())
		{
			$dateMin =  new \Bitrix\Main\Type\DateTime($row['DATE_MIN'], 'Y-m-d');
			$months =  $row['MONTHS'];

			while ($months >= 0)
			{
				list($dateSplitPeriod, $dateSplitPeriodUnits) = $this->getDateSplitPeriod();

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


		$query = Crm\ActivityTable::query();

		$dateMin = new Entity\ExpressionField('DATE_MIN', "DATE_FORMAT(MIN(%s), '%%Y-%%m-%%d')", 'CREATED');
		$query->registerRuntimeField('', $dateMin)->addSelect('DATE_MIN');

		$monthCount = new Entity\ExpressionField('MONTHS', 'TIMESTAMPDIFF(MONTH, MIN(%s), MAX(%s))', array('CREATED', 'CREATED'));
		$query->registerRuntimeField('', $monthCount)->addSelect('MONTHS');

		$res = $query->exec();
		if ($row = $res->fetch())
		{
			$dateMin =  new \Bitrix\Main\Type\DateTime($row['DATE_MIN'], 'Y-m-d');
			$months =  $row['MONTHS'];

			while ($months >= 0)
			{
				list($dateSplitPeriod, $dateSplitPeriodUnits) = $this->getDateSplitPeriod();

				$period = $dateMin->format('Y.m');
				$dateMin->add("$dateSplitPeriod $dateSplitPeriodUnits");
				$period .= '-';
				$period .= $dateMin->format('Y.m');
				$months -= $dateSplitPeriod;

				$queueList[] = array(
					'indicatorId' => $indicatorId,
					'action' => $componentCommandAlias['MEASURE_ACTIVITY'],
					'period' => $period,
				);
				$queueList[] = array(
					'indicatorId' => $indicatorId,
					'action' => $componentCommandAlias['MEASURE_EVENT'],
					'period' => $period,
				);
			}
		}

		return $queueList;
	}


	/**
	 * Returns query.
	 * @return Entity\Query
	 */
	public function prepareQuery()
	{
		$query = Crm\QuoteTable::query();

		$dealRelation = new Entity\ReferenceField(
			'DEAL',
			Crm\DealTable::class,
			Entity\Query\Join::on('this.DEAL_ID', 'ref.ID'),//->where('this.OWNER_TYPE_ID', \CCrmOwnerType::Deal),
			array('join_type' => 'LEFT')
		);
		$query->registerRuntimeField('', $dealRelation);

		$leadRelation = new Entity\ReferenceField(
			'LEAD',
			Crm\LeadTable::class,
			Entity\Query\Join::on('this.LEAD_ID', 'ref.ID'),//->where('this.OWNER_TYPE_ID', \CCrmOwnerType::Lead),
			array('join_type' => 'LEFT')
		);
		$query->registerRuntimeField('', $leadRelation);

		// Register runtime field STAGE_SEMANTIC_ID
		self::registerStageField($query);

		return $query;
	}

	/**
	 * Registers runtime field STAGE_SEMANTIC_ID.
	 * @param Entity\Query $query Query to append.
	 * @param string $sourceAlias Source table alias.
	 * @param string $fieldAlias Field alias.
	 * @return void
	 */
	public static function registerStageField(Entity\Query $query, $sourceAlias = '', $fieldAlias = 'STAGE_SEMANTIC_ID')
	{
		$caseSql = '';
		$stageStatusMirror = self::getStatusSemantics();
		foreach ($stageStatusMirror as $stageId => $statusList)
		{
			foreach ($statusList as $statusId)
			{
				$caseSql .= " WHEN '{$statusId}' THEN '{$stageId}' ";
			}
		}
		$stageField = new Entity\ExpressionField(
			$fieldAlias,
			"CASE %s {$caseSql} ELSE NULL END",
			($sourceAlias != '' ? "{$sourceAlias}.STATUS_ID" : 'STATUS_ID')
		);

		$query->registerRuntimeField('', $stageField);
	}

	/**
	 * Setups filter params into query.
	 * @param Entity\Query $query Query.
	 * @return boolean
	 */
	public function prepareFilter(Entity\Query $query)
	{
		$isAllValueApplied = true;
		$filter = $this->getFilter();

		foreach ($filter as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}
			$key0 = trim($key, '<>!=');
			if ($key0 == 'STAGE_SEMANTIC_ID' || $key0 == 'QUOTE_STAGE_SEMANTIC_ID')
			{
				$statuses = self::getStatusSemantics($value);
				$query->where('STATUS_ID', 'in', $statuses);
			}
			elseif (isset(static::$filterFieldAlias[$key0]))
			{
				$key1 = str_replace($key0, static::$filterFieldAlias[$key0], $key);
				if (is_array($value))
				{
					$query->where($key1, 'in', $value);
				}
				else
				{
					$query->addFilter($key1, $value);
				}
			}
			else
			{
				$isAllValueApplied = $this->addFilterEntityField($query, $query->getEntity(), $key, $value);
			}
		}

		return $isAllValueApplied;
	}

	/**
	 * Returns query to measure files attached to quotes.
	 * @return Entity\Query
	 */
	public function getFileMeasureQuery()
	{
		$query = $this->prepareQuery();

		// file type
		$file = new Entity\ReferenceField(
			'FILE',
			Main\FileTable::class,
			Entity\Query\Join::on('this.ELEMENTS.ELEMENT_ID', 'ref.ID')
							 ->where('this.ELEMENTS.STORAGE_TYPE_ID', Crm\Integration\StorageType::File),
			array('join_type' => 'LEFT')
		);
		$query->registerRuntimeField('', $file);


		// disk type
		if (parent::isModuleAvailable('disk'))
		{
			$diskFile = new Entity\ReferenceField(
				'DISK_FILE',
				Disk\Internals\FileTable::class,
				Entity\Query\Join::on('this.ELEMENTS.ELEMENT_ID', 'ref.ID')
								 ->where('this.ELEMENTS.STORAGE_TYPE_ID', '=', Crm\Integration\StorageType::Disk)
								 ->where('ref.TYPE', '=', \Bitrix\Disk\Internals\ObjectTable::TYPE_FILE),
				array('join_type' => 'LEFT')
			);
			$query->registerRuntimeField('', $diskFile);

			$diskSize = new Entity\ExpressionField('DISK_SIZE', 'SUM(IFNULL(%s, 0))', 'DISK_FILE.SIZE');
			$diskCount = new Entity\ExpressionField('DISK_COUNT', 'COUNT(%s)', 'DISK_FILE.ID');
			$query
				->registerRuntimeField('', $diskSize)
				->registerRuntimeField('', $diskCount);

			$fileSize = new Entity\ExpressionField('FILE_SIZE', 'SUM(IFNULL(%s, 0)) + SUM(IFNULL(%s, 0))', array('FILE.FILE_SIZE', 'DISK_FILE.SIZE'));
			$fileCount = new Entity\ExpressionField('FILE_COUNT', 'COUNT(%s) + COUNT(%s)', array('FILE.ID', 'DISK_FILE.ID'));
			$query
				->registerRuntimeField('', $fileSize)
				->registerRuntimeField('', $fileCount);

			$query
				->addSelect('FILE_SIZE')
				->addSelect('FILE_COUNT')
				->addSelect('DISK_SIZE')
				->addSelect('DISK_COUNT');
		}
		else
		{
			$fileSize = new Entity\ExpressionField('FILE_SIZE', 'SUM(IFNULL(%s, 0))', 'FILE.FILE_SIZE');
			$fileCount = new Entity\ExpressionField('FILE_COUNT', 'COUNT(%s)', 'FILE.ID');
			$query
				->registerRuntimeField('', $fileSize)
				->addSelect('FILE_SIZE')
				->registerRuntimeField('', $fileCount)
				->addSelect('FILE_COUNT');
		}

		return $query;
	}



	/**
	 * Runs measure test for tables.
	 * @return self
	 */
	public function measureEntity()
	{
		self::loadTablesInformation();

		$query = $this->prepareQuery();

		if ($this->prepareFilter($query))
		{
			$avgQuoteTableRowLength = (double)self::$tablesInformation[Crm\QuoteTable::getTableName()]['AVG_SIZE'];

			$connection = \Bitrix\Main\Application::getConnection();

			$this->checkTemporally();

			$fields = array(
				'INDICATOR_TYPE' => '',
				'OWNER_ID' => '',
				'DATE_CREATE' => new \Bitrix\Main\Type\Date(),
				'STAGE_SEMANTIC_ID' => '',
				'ENTITY_COUNT' => '',
				'ENTITY_SIZE' => '',
			);

			$insert = $connection->getSqlHelper()->prepareInsert(Crm\VolumeTmpTable::getTableName(), $fields);

			$sqlIns = 'INSERT INTO '.$connection->getSqlHelper()->quote(Crm\VolumeTmpTable::getTableName()). '('. $insert[0]. ') ';

			$query
				->registerRuntimeField('', new Entity\ExpressionField('INDICATOR_TYPE', '\''.static::getIndicatorId().'\''))
				->addSelect('INDICATOR_TYPE')

				->registerRuntimeField('', new Entity\ExpressionField('OWNER_ID', '\''.$this->getOwner().'\''))
				->addSelect('OWNER_ID')

				//date
				->addSelect('DATE_CREATE_SHORT')
				->addGroup('DATE_CREATE_SHORT')

				// STAGE_SEMANTIC_ID
				->addSelect('STAGE_SEMANTIC_ID')
				->addGroup('STAGE_SEMANTIC_ID')

				->registerRuntimeField('', new Entity\ExpressionField('ENTITY_COUNT', 'COUNT(%s)', 'ID'))
				->addSelect('ENTITY_COUNT')

				->registerRuntimeField('', new Entity\ExpressionField('ENTITY_SIZE', 'COUNT(%s) * '.$avgQuoteTableRowLength, 'ID'))
				->addSelect('ENTITY_SIZE');

			$connection->queryExecute($sqlIns. $query->getQuery());

			$entityList = self::getEntityList();
			foreach ($entityList as $entityClass)
			{
				if ($entityClass == Crm\QuoteTable::class)
				{
					continue;
				}
				/**
				 * @var \Bitrix\Main\Entity\DataManager $entityClass
				 */
				$entityEntity = $entityClass::getEntity();

				if (!$entityEntity->hasField('QUOTE_ID'))
				{
					continue;
				}
				$fieldName = 'QUOTE_ID';

				$query = $this->prepareQuery();

				if ($this->prepareFilter($query))
				{
					$reference = new Entity\ReferenceField(
						'RefEntity',
						$entityClass,
						array('this.ID' => 'ref.'.$fieldName),
						array('join_type' => 'INNER')
					);
					$query->registerRuntimeField('', $reference);

					$primary = $entityEntity->getPrimary();
					if (is_array($primary) && !empty($primary))
					{
						array_walk($primary, function (&$item)
						{
							$item = 'RefEntity.'.$item;
						});
					}
					elseif (!empty($primary))
					{
						$primary = array('RefEntity.'.$primary);
					}

					$query
						//primary
						//->setSelect($primary)
						->registerRuntimeField('', new Entity\ExpressionField('COUNT_REF', 'COUNT(*)'))
						->addSelect('COUNT_REF')
						->setGroup($primary)

						//date
						->addSelect('DATE_CREATE_SHORT')
						->addGroup('DATE_CREATE_SHORT')

						// STAGE_SEMANTIC_ID
						->addSelect('STAGE_SEMANTIC_ID')
						->addGroup('STAGE_SEMANTIC_ID');

					$avgTableRowLength = (double)self::$tablesInformation[$entityClass::getTableName()]['AVG_SIZE'];

					$query1 = new Entity\Query($query);
					$query1
						->registerRuntimeField('', new Entity\ExpressionField('INDICATOR_TYPE', '\''.static::getIndicatorId().'\''))
						->addSelect('INDICATOR_TYPE')

						->registerRuntimeField('', new Entity\ExpressionField('OWNER_ID', '\''.$this->getOwner().'\''))
						->addSelect('OWNER_ID')

						//date
						->addSelect('DATE_CREATE_SHORT')
						->addGroup('DATE_CREATE_SHORT')

						// STAGE_SEMANTIC_ID
						->addSelect('STAGE_SEMANTIC_ID')
						->addGroup('STAGE_SEMANTIC_ID')

						->registerRuntimeField('', new Entity\ExpressionField('REF_SIZE', 'SUM(COUNT_REF) * '. $avgTableRowLength))
						->addSelect('REF_SIZE');

					Crm\VolumeTmpTable::updateFromSelect(
						$query1,
						array('ENTITY_SIZE' => 'destination.ENTITY_SIZE + source.REF_SIZE'),
						array(
							'INDICATOR_TYPE' => 'INDICATOR_TYPE',
							'OWNER_ID' => 'OWNER_ID',
							'DATE_CREATE' => 'DATE_CREATE_SHORT',
							'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
						)
					);
				}
			}

			$this->copyTemporallyData();
		}

		return $this;
	}


	/**
	 * Runs measure test for tables.
	 * @return self
	 */
	public function measureFiles()
	{
		self::loadTablesInformation();

		$query = $this->prepareQuery();

		if ($this->prepareFilter($query))
		{
			$source = array();

			$groupByFields = array(
				'DATE_CREATE_SHORT' => 'DATE_CREATE_SHORT',
				'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
			);

			$entityUserFieldList = $this->getUserTypeFieldList(Crm\QuoteTable::class);
			/** @var array $userField */
			foreach ($entityUserFieldList as $userField)
			{
				$sql = $this->prepareUserFieldQuery(Crm\QuoteTable::class, $userField, $groupByFields);

				if ($sql !== '')
				{
					$source[] = $sql;
				}
			}

			$diskConnector = static::getDiskConnector(Crm\QuoteTable::class);
			if ($diskConnector !== null)
			{
				$sql = $this->prepareDiskAttachedQuery(Crm\QuoteTable::class, $diskConnector, $groupByFields);
				if ($sql !== '')
				{
					$source[] = $sql;
				}
			}

			$liveFeedConnector = static::getLiveFeedConnector(Crm\QuoteTable::class);
			if ($liveFeedConnector !== null)
			{
				$sql = $this->prepareLiveFeedQuery(Crm\QuoteTable::class, $liveFeedConnector, $groupByFields);
				if ($sql !== '')
				{
					$source[] = $sql;
				}
			}

			$queryFile = $this->getFileMeasureQuery();
			if ($this->prepareFilter($queryFile))
			{
				$queryFile
					//date
					->addSelect('DATE_CREATE_SHORT')
					->addGroup('DATE_CREATE_SHORT')

					// STAGE_SEMANTIC_ID
					->addSelect('STAGE_SEMANTIC_ID')
					->addGroup('STAGE_SEMANTIC_ID');

				$source[] = $queryFile->getQuery();
			}

			if (count($source) > 0)
			{
				$querySql = "
					SELECT 
						'".static::getIndicatorId()."' as INDICATOR_TYPE,
						'".$this->getOwner()."' as OWNER_ID,
						DATE_CREATE_SHORT as DATE_CREATE,
						STAGE_SEMANTIC_ID, 
						SUM(FILE_SIZE) as FILE_SIZE,
						SUM(FILE_COUNT) as FILE_COUNT,
						SUM(DISK_SIZE) as DISK_SIZE,
						SUM(DISK_COUNT) as DISK_COUNT
					FROM 
					(
						(".implode(' ) UNION ( ', $source).")
					) src
					GROUP BY 
						STAGE_SEMANTIC_ID, 
						DATE_CREATE
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
						'INDICATOR_TYPE',
						'OWNER_ID',
						'DATE_CREATE',
						'STAGE_SEMANTIC_ID',
					)
				);
			}
		}

		return $this;
	}

	/**
	 * Runs measure test for activities.
	 * @param array $additionActivityFilter Filter for activity list.
	 * @return self
	 */
	public function measureActivity($additionActivityFilter = array())
	{
		self::loadTablesInformation();

		$querySql = $this->prepareActivityQuery(array(
			'DATE_CREATE' => 'QUOTE.DATE_CREATE_SHORT',
			'QUOTE_STAGE_SEMANTIC_ID' => 'QUOTE_STAGE_SEMANTIC_ID',
		));

		if ($querySql != '')
		{
			$avgActivityTableRowLength = (double)self::$tablesInformation[Crm\ActivityTable::getTableName()]['AVG_SIZE'];
			$avgBindingTableRowLength = (double)self::$tablesInformation[Crm\ActivityBindingTable::getTableName()]['AVG_SIZE'];

			$querySql = "
				SELECT 
					'".static::getIndicatorId()."' as INDICATOR_TYPE,
					'".$this->getOwner()."' as OWNER_ID,
					DATE_CREATE,
					QUOTE_STAGE_SEMANTIC_ID, 
					(	FILE_SIZE +
						ACTIVITY_COUNT * {$avgActivityTableRowLength} + 
						BINDINGS_COUNT * {$avgBindingTableRowLength} ) as ACTIVITY_SIZE,
					ACTIVITY_COUNT
				FROM 
				(
					{$querySql}
				) src
			";

			Crm\VolumeTable::updateFromSelect(
				$querySql,
				array(
					'ACTIVITY_SIZE' => 'destination.ACTIVITY_SIZE + source.ACTIVITY_SIZE',
					'ACTIVITY_COUNT' => 'destination.ACTIVITY_COUNT + source.ACTIVITY_COUNT',
				),
				array(
					'INDICATOR_TYPE' => 'INDICATOR_TYPE',
					'OWNER_ID' => 'OWNER_ID',
					'DATE_CREATE' => 'DATE_CREATE',
					'STAGE_SEMANTIC_ID' => 'QUOTE_STAGE_SEMANTIC_ID',
				)
			);
		}

		return $this;
	}

	/**
	 * Runs measure test for events.
	 * @param array $additionEventFilter Filter for event list.
	 * @return self
	 */
	public function measureEvent($additionEventFilter = array())
	{
		self::loadTablesInformation();

		$querySql = $this->prepareEventQuery(array(
			'DATE_CREATE' => 'QUOTE.DATE_CREATE_SHORT',
			'QUOTE_STAGE_SEMANTIC_ID' => 'QUOTE_STAGE_SEMANTIC_ID',
		));

		if ($querySql != '')
		{
			$avgEventTableRowLength = (double)self::$tablesInformation[Crm\EventTable::getTableName()]['AVG_SIZE'];

			$querySql = "
				SELECT 
					'".static::getIndicatorId()."' as INDICATOR_TYPE,
					'".$this->getOwner()."' as OWNER_ID,
					DATE_CREATE,
					QUOTE_STAGE_SEMANTIC_ID, 
					(	FILE_SIZE +
						EVENT_COUNT * {$avgEventTableRowLength} ) as EVENT_SIZE,
					EVENT_COUNT
				FROM 
				(
					{$querySql}
				) src
			";

			Crm\VolumeTable::updateFromSelect(
				$querySql,
				array(
					'EVENT_SIZE' => 'destination.EVENT_SIZE + source.EVENT_SIZE',
					'EVENT_COUNT' => 'destination.EVENT_COUNT + source.EVENT_COUNT',
				),
				array(
					'INDICATOR_TYPE' => 'INDICATOR_TYPE',
					'OWNER_ID' => 'OWNER_ID',
					'DATE_CREATE' => 'DATE_CREATE',
					'STAGE_SEMANTIC_ID' => 'QUOTE_STAGE_SEMANTIC_ID',
				)
			);
		}

		return $this;
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
			$query
				->registerRuntimeField('', new Entity\ExpressionField('CNT', 'COUNT(%s)', 'ID'))
				->addSelect('CNT')
			;

			$count = 0;
			$res = $query->exec();
			if ($row = $res->fetch())
			{
				$count = $row['CNT'];
			}
		}

		return $count;
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

		if ($this->prepareFilter($query))
		{
			$userPermissions = \CCrmPerms::GetUserPermissions($this->getOwner());

			$connection = \Bitrix\Main\Application::getConnection();

			$query
				->addSelect('ID', 'QUOTE_ID')
				->setLimit(self::MAX_ENTITY_PER_INTERACTION)
				->setOrder(array('ID' => 'ASC'))
			;

			if ($this->getProcessOffset() > 0)
			{
				$query->where('ID', '>', $this->getProcessOffset());
			}

			$res = $query->exec();

			$success = true;

			$entity = new \CCrmQuote(false);
			while ($quote = $res->fetch())
			{
				$this->setProcessOffset($quote['QUOTE_ID']);

				$entityAttr = $userPermissions->GetEntityAttr('QUOTE', array($quote['QUOTE_ID']));
				$attr = $entityAttr[$quote['QUOTE_ID']];

				if($userPermissions->CheckEnityAccess('QUOTE', 'DELETE', $attr))
				{
					$connection->startTransaction();

					if ($entity->Delete($quote['QUOTE_ID'], array('CURRENT_USER' => $this->getOwner())))
					{
						$connection->commitTransaction();
						$this->incrementDroppedEntityCount();
					}
					else
					{
						$connection->rollbackTransaction();

						$err = '';
						global $APPLICATION;
						if ($APPLICATION instanceof \CMain)
						{
							$err = $APPLICATION->GetException();
						}
						if ($err == '')
						{
							$err = 'Deletion failed with quote #'.$quote['QUOTE_ID'];
						}
						$this->collectError(new Main\Error($err, self::ERROR_DELETION_FAILED));

						$this->incrementFailCount();
					}
				}
				else
				{
					$this->collectError(new Main\Error('Access denied to drop quote #'.$quote['QUOTE_ID'], self::ERROR_PERMISSION_DENIED));
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
	 * Returns count of activities.
	 * @param array $additionActivityFilter Filter for activity list.
	 * @return int
	 */
	public function countActivity($additionActivityFilter = array())
	{
		$additionActivityFilter['=BINDINGS.OWNER_TYPE_ID'] = \CCrmOwnerType::Quote;
		return parent::countActivity($additionActivityFilter);
	}

	/**
	 * Performs dropping associated entity activities.
	 *
	 * @return boolean
	 */
	public function clearActivity()
	{
		if (!$this->canClearActivity())
		{
			return false;
		}

		$userPermissions = \CCrmPerms::GetUserPermissions($this->getOwner());

		$activityVolume = new Crm\Volume\Activity();
		$activityVolume->setFilter($this->getFilter());

		$query = $activityVolume->prepareQuery();

		$success = true;

		if ($activityVolume->prepareFilter($query))
		{
			$query
				->setSelect(array(
					'ID' => 'ID',
					'QUOTE_ID' => 'BINDINGS.OWNER_ID',
				))
				->where('BINDINGS.OWNER_TYPE_ID', '=', \CCrmOwnerType::Quote)
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

				$activity['OWNER_TYPE_ID'] = \CCrmOwnerType::Quote;
				$activity['OWNER_ID'] = $activity['QUOTE_ID'];

				if (\CCrmActivity::CheckItemDeletePermission($activity, $userPermissions))
				{
					\CCrmActivity::DeleteByOwner(\CCrmOwnerType::Quote, $activity['QUOTE_ID']);

					//todo: fail count here

					$this->incrementDroppedActivityCount();
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
	 * Returns count of events.
	 * @param array $additionEventFilter Filter for events list.
	 * @return int
	 */
	public function countEvent($additionEventFilter = array())
	{
		$additionEventFilter['=ENTITY_TYPE'] = \CCrmOwnerType::QuoteName;
		return parent::countEvent($additionEventFilter);
	}

	/**
	 * Performs dropping associated entity events.
	 *
	 * @return boolean
	 */
	public function clearEvent()
	{
		if (!$this->canClearEvent())
		{
			return false;
		}

		$eventVolume = new Crm\Volume\Event();
		$eventVolume->setFilter($this->getFilter());

		$query = $eventVolume->prepareQuery();

		$success = true;

		if ($eventVolume->prepareFilter($query))
		{
			$query
				->addSelect('ID', 'RELATION_ID')
				->where('ENTITY_TYPE', '=', \CCrmOwnerType::QuoteName)
				->setLimit(self::MAX_ENTITY_PER_INTERACTION)
				->setOrder(array('RELATION_ID' => 'ASC'));

			if ($this->getProcessOffset() > 0)
			{
				$query->where('RELATION_ID', '>', $this->getProcessOffset());
			}

			$res = $query->exec();

			$entity = new \CCrmEvent();
			while ($event = $res->fetch())
			{
				$this->setProcessOffset($event['RELATION_ID']);

				if ($entity->Delete($event['RELATION_ID'], array('CURRENT_USER' => $this->getOwner())) !== false)
				{
					$this->incrementDroppedEventCount();
				}
				else
				{
					$this->collectError(new Main\Error('Deletion failed with event #'.$event['RELATION_ID'], self::ERROR_DELETION_FAILED));
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
}

