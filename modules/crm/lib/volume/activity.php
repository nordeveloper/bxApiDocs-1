<?php

namespace Bitrix\Crm\Volume;

use Bitrix\Crm;
use Bitrix\Disk;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Activity extends Crm\Volume\Base implements Crm\Volume\IVolumeClear, Crm\Volume\IVolumeUrl
{
	/** @var array */
	protected static $entityList = array(
		Crm\ActivityTable::class,
		Crm\ActivityBindingTable::class,
		Crm\ActivityElementTable::class,
		Crm\Activity\MailMetaTable::class,
		Crm\Activity\Entity\CustomTypeTable::class,
		Crm\UserActivityTable::class,
		Crm\Statistics\Entity\ActivityStatisticsTable::class,
		Crm\Statistics\Entity\ActivityChannelStatisticsTable::class,
	);


	/** @var array */
	protected static $filterFieldAlias = array(
		'DEAL_STAGE_SEMANTIC_ID' => 'DEAL.STAGE_SEMANTIC_ID',
		'LEAD_STAGE_SEMANTIC_ID' => 'LEAD.STATUS_SEMANTIC_ID',
		'QUOTE_STATUS_ID' => 'QUOTE.STATUS_ID',
		'INVOICE_STATUS_ID' => 'INVOICE.STATUS_ID',
		'DEAL_DATE_CREATE' => 'DEAL.DATE_CREATE',
		'LEAD_DATE_CREATE' => 'LEAD.DATE_CREATE',
		'QUOTE_DATE_CREATE' => 'QUOTE.DATE_CREATE',
		'INVOICE_DATE_CREATE' => 'INVOICE.DATE_INSERT',
		'DEAL_DATE_CLOSE' => 'DEAL.CLOSEDATE',
		'LEAD_DATE_CLOSE' => 'LEAD.DATE_CLOSED',
		'QUOTE_DATE_CLOSE' => 'QUOTE.CLOSEDATE',
		'DATE_CREATE' => 'CREATED',
	);

	/**
	 * Returns title of the indicator.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('CRM_VOLUME_ACTIVITY_TITLE');
	}


	/**
	 * Returns Socialnetwork log entity list attached to disk object.
	 * @param string $entityClass Class name of entity.
	 * @return string|null
	 */
	public static function getLiveFeedConnector($entityClass)
	{
		$attachedEntityList = array();
		if (parent::isModuleAvailable('socialnetwork') && parent::isModuleAvailable('disk'))
		{
			$attachedEntityList[Crm\ActivityTable::class] = \CCrmLiveFeedEntity::Activity;
		}

		return $attachedEntityList[$entityClass] ? : null;
	}

	/**
	 * Returns table list corresponding to indicator.
	 * @return string[]
	 */
	public function getTableList()
	{
		$tableNames = parent::getTableList();

		$tableNames[] = \CCrmActivity::COMMUNICATION_TABLE_NAME;

		return $tableNames;
	}

	/**
	 * Returns availability to drop entity.
	 *
	 * @return boolean
	 */
	public function canClearEntity()
	{
		// @see: Using of \CCrmActivity::CheckItemDeletePermission()
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
			'FILTER_FIELDS' => 'CREATED',
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

		//$dateMax = new Entity\ExpressionField('DATE_MAX', "DATE_FORMAT(MAX(%s), '%%Y-%%m-%%d')", 'CREATED');
		//$query->registerRuntimeField('', $dateMax)->addSelect('DATE_MAX');

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
	 * Returns query.
	 * @param string $indicator Volume indicator class name.
	 * @return Entity\Query
	 */
	public function prepareQuery($indicator = '')
	{
		$query = Crm\ActivityTable::query();

		$query->whereColumn('OWNER_TYPE_ID', '=', 'BINDINGS.OWNER_TYPE_ID');

		if (
			$indicator == Crm\Volume\Company::class
		)
		{
			$companyRelation = new Entity\ReferenceField(
				'COMPANY',
				Crm\CompanyTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Company),
				array('join_type' => ($indicator == Crm\Volume\Company::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $companyRelation);

			/** @global \CDatabase $DB */
			global $DB;
			$dayField = new Entity\ExpressionField(
				'COMPANY_DATE_CREATE_SHORT',
				$DB->datetimeToDateFunction('%s'),
				'COMPANY.DATE_CREATE'
			);
			$query->registerRuntimeField('', $dayField);
		}
		elseif (
			$indicator == Crm\Volume\Contact::class
		)
		{
			$contactRelation = new Entity\ReferenceField(
				'CONTACT',
				Crm\ContactTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Contact),
				array('join_type' => ($indicator == Crm\Volume\Contact::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $contactRelation);

			/** @global \CDatabase $DB */
			global $DB;
			$dayField = new Entity\ExpressionField(
				'CONTACT_DATE_CREATE_SHORT',
				$DB->datetimeToDateFunction('%s'),
				'CONTACT.DATE_CREATE'
			);
			$query->registerRuntimeField('', $dayField);
		}
		else
		{
			$dealRelation = new Entity\ReferenceField(
				'DEAL',
				Crm\DealTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Deal),
				array('join_type' => ($indicator == Crm\Volume\Deal::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $dealRelation);

			$leadRelation = new Entity\ReferenceField(
				'LEAD',
				Crm\LeadTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Lead),
				array('join_type' => ($indicator == Crm\Volume\Lead::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $leadRelation);

			$quoteRelation = new Entity\ReferenceField(
				'QUOTE',
				Crm\QuoteTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Quote),
				array('join_type' => ($indicator == Crm\Volume\Quote::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $quoteRelation);

			// STAGE_SEMANTIC_ID
			Crm\Volume\Quote::registerStageField($query, 'QUOTE', 'QUOTE_STAGE_SEMANTIC_ID');


			$invoiceRelation = new Entity\ReferenceField(
				'INVOICE',
				Crm\InvoiceTable::class,
				Entity\Query\Join::on('this.BINDINGS.OWNER_ID', 'ref.ID')->where('this.BINDINGS.OWNER_TYPE_ID', \CCrmOwnerType::Invoice),
				array('join_type' => ($indicator == Crm\Volume\Invoice::class ? 'INNER' : 'LEFT'))
			);
			$query->registerRuntimeField('', $invoiceRelation);

			/** @global \CDatabase $DB */
			global $DB;
			$dayField = new Entity\ExpressionField(
				'INVOICE_DATE_CREATE_SHORT',
				$DB->datetimeToDateFunction('%s'),
				'INVOICE.DATE_INSERT'
			);
			$query->registerRuntimeField('', $dayField);

			// STAGE_SEMANTIC_ID
			Crm\Volume\Invoice::registerStageField($query, 'INVOICE', 'INVOICE_STAGE_SEMANTIC_ID');

			$stageField = new Entity\ExpressionField(
				'STAGE_SEMANTIC_ID',
				'case '.
				' when %s is not null then %s '.
				' when %s is not null then %s '.
				' when (%s) is not null then (%s) '.
				' when (%s) is not null then (%s) '.
				'end',
				array(
					'DEAL.STAGE_SEMANTIC_ID', 'DEAL.STAGE_SEMANTIC_ID',
					'LEAD.STATUS_SEMANTIC_ID', 'LEAD.STATUS_SEMANTIC_ID',
					'QUOTE_STAGE_SEMANTIC_ID', 'QUOTE_STAGE_SEMANTIC_ID',
					'INVOICE_STAGE_SEMANTIC_ID', 'INVOICE_STAGE_SEMANTIC_ID'
				)
			);
			$query->registerRuntimeField('', $stageField);
		}

		return $query;
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
			if ($key0 === 'STAGE_SEMANTIC_ID')
			{
				$query->where(Entity\Query::filter()
					->logic('or')
					->where(array(
						array('DEAL.STAGE_SEMANTIC_ID', 'in', $value),
						array('LEAD.STATUS_SEMANTIC_ID', 'in', $value),
						array('QUOTE.STATUS_ID', 'in', Crm\Volume\Quote::getStatusSemantics($value)),
						array('INVOICE.STATUS_ID', 'in', Crm\Volume\Invoice::getStatusSemantics($value)),
					))
				);
			}
			elseif ($key0 === 'QUOTE_STAGE_SEMANTIC_ID')
			{
				$statuses = Crm\Volume\Quote::getStatusSemantics($value);
				$query->where('QUOTE.STATUS_ID', 'in', $statuses);
			}
			elseif ($key0 === 'INVOICE_STAGE_SEMANTIC_ID')
			{
				$statuses = Crm\Volume\Invoice::getStatusSemantics($value);
				$query->where('INVOICE.STATUS_ID', 'in', $statuses);
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
	 * Returns query to measure files attached to activity.
	 * @param string $indicator Volume indicator class name.
	 * @return Entity\Query
	 */
	public function getActivityFileMeasureQuery($indicator = '')
	{
		$query = $this->prepareQuery($indicator);

		$this->prepareFilter($query);

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
			$diskCount = new Entity\ExpressionField('DISK_COUNT', 'COUNT(DISTINCT %s)', 'DISK_FILE.ID');
			$query
				->registerRuntimeField('', $diskSize)
				->registerRuntimeField('', $diskCount);

			$fileSize = new Entity\ExpressionField('FILE_SIZE', 'SUM(IFNULL(%s, 0)) + SUM(IFNULL(%s, 0))', array('FILE.FILE_SIZE', 'DISK_FILE.SIZE'));
			$fileCount = new Entity\ExpressionField('FILE_COUNT', 'COUNT(DISTINCT %s) + COUNT(DISTINCT %s)', array('FILE.ID', 'DISK_FILE.ID'));
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
			$fileCount = new Entity\ExpressionField('FILE_COUNT', 'COUNT(DISTINCT %s)', 'FILE.ID');
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
			$avgActivityTableRowLength = (double)self::$tablesInformation[Crm\ActivityTable::getTableName()]['AVG_SIZE'];

			$connection = \Bitrix\Main\Application::getConnection();

			$this->checkTemporally();

			$data = array(
				'INDICATOR_TYPE' => '',
				'OWNER_ID' => '',
				'DATE_CREATE' => new \Bitrix\Main\Type\Date(),
				'STAGE_SEMANTIC_ID' => '',
				'ENTITY_COUNT' => '',
				'ENTITY_SIZE' => '',
			);

			$insert = $connection->getSqlHelper()->prepareInsert(Crm\VolumeTmpTable::getTableName(), $data);

			$sqlIns = 'INSERT INTO '.$connection->getSqlHelper()->quote(Crm\VolumeTmpTable::getTableName()). '('. $insert[0]. ') ';

			$query
				->registerRuntimeField('', new Entity\ExpressionField('INDICATOR_TYPE', '\''.static::getIndicatorId().'\''))
				->addSelect('INDICATOR_TYPE')

				->registerRuntimeField('', new Entity\ExpressionField('OWNER_ID', '\''.$this->getOwner().'\''))
				->addSelect('OWNER_ID')

				//date
				->addSelect('DATE_CREATED_SHORT', 'DATE_CREATE')
				->addGroup('DATE_CREATED_SHORT')

				// STAGE_SEMANTIC_ID
				->addSelect('STAGE_SEMANTIC_ID')
				->addGroup('STAGE_SEMANTIC_ID')

				->registerRuntimeField('', new Entity\ExpressionField('ENTITY_COUNT', 'COUNT(DISTINCT %s)', 'ID'))
				->addSelect('ENTITY_COUNT')

				->registerRuntimeField('', new Entity\ExpressionField('ENTITY_SIZE', 'COUNT(DISTINCT %s) * '.$avgActivityTableRowLength, 'ID'))
				->addSelect('ENTITY_SIZE');

			$querySql = $sqlIns. $query->getQuery();

			$connection->queryExecute($querySql);

			$entityList = self::getEntityList();
			foreach ($entityList as $entityClass)
			{
				if ($entityClass == Crm\ActivityTable::class)
				{
					continue;
				}
				/**
				 * @var \Bitrix\Main\Entity\DataManager $entityClass
				 */
				$entityEntity = $entityClass::getEntity();

				$fieldName = 'ACTIVITY_ID';
				if ($entityEntity->hasField($fieldName))
				{
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
							->registerRuntimeField('', new Entity\ExpressionField('COUNT_REF', 'COUNT(*)'))
							->addSelect('COUNT_REF')
							->setGroup($primary)

							//date
							->addSelect('DATE_CREATED_SHORT', 'DATE_CREATE')
							->addGroup('DATE_CREATED_SHORT')

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
							->addSelect('DATE_CREATE')
							->addGroup('DATE_CREATE')

							// STAGE_SEMANTIC_ID
							->addSelect('STAGE_SEMANTIC_ID')
							->addGroup('STAGE_SEMANTIC_ID')

							->registerRuntimeField('', new Entity\ExpressionField('REF_SIZE', 'SUM(COUNT_REF) * '. $avgTableRowLength))
							->addSelect('REF_SIZE');

						Crm\VolumeTmpTable::updateFromSelect(
							$query1,
							array('ENTITY_SIZE' => 'destination.ENTITY_SIZE + source.REF_SIZE'),
							array(
								'INDICATOR_TYPE',
								'OWNER_ID',
								'DATE_CREATE',
								'STAGE_SEMANTIC_ID',
							)
						);
					}
				}
			}

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
		$querySql = $this->prepareActivityQuery(array(
			'DATE_CREATE' => 'DATE_CREATED_SHORT',
			'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
		));

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
				HAVING 
					SUM(FILE_COUNT) > 0
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

		return $this;
	}


	/**
	 * Returns count of entities.
	 * @return int
	 */
	public function countEntity()
	{
		$count = -1;

		$query = $this->prepareQuery();

		if ($this->prepareFilter($query))
		{
			$count = 0;

			$countField = new Entity\ExpressionField('CNT', 'COUNT(DISTINCT %s)', 'ID');
			$query
				->registerRuntimeField('', $countField)
				->addSelect('CNT');


			$res = $query->exec();
			if ($row = $res->fetch())
			{
				$count = $row['CNT'];
				$this->activityCount = $row['CNT'];
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

		$success = true;

		if ($this->prepareFilter($query))
		{
			$userPermissions = \CCrmPerms::GetUserPermissions($this->getOwner());

			$query
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
	 * Force to drop files attached to activity.
	 *
	 * @param int $activityId Activity Id.
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function DeleteActivityFiles($activityId)
	{
		$activityId = (int)$activityId;
		if ($activityId <= 0)
		{
			return false;
		}

		$res = Crm\ActivityElementTable::getList(array(
			'filter' => array('=ACTIVITY_ID' => $activityId),
			'select' => array('STORAGE_TYPE_ID', 'ELEMENT_ID'),
		));
		while ($row = $res->fetch())
		{
			if ($row['STORAGE_TYPE_ID'] == Crm\Integration\StorageType::File)
			{
				\CFile::Delete($row['ELEMENT_ID']);
			}
			elseif ($row['STORAGE_TYPE_ID'] == Crm\Integration\StorageType::Disk)
			{
				\Bitrix\Main\Loader::includeModule('disk');

				$file = \Bitrix\Disk\File::getById($row['ELEMENT_ID']);
				if ($file instanceof \Bitrix\Disk\File)
				{
					$file->delete(\Bitrix\Disk\SystemUser::SYSTEM_USER_ID);
				}
			}
		}

		return true;
	}
}
