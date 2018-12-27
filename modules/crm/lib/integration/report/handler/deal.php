<?php

namespace Bitrix\Crm\Integration\Report\Handler;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\Security\EntityAuthorization;
use Bitrix\Crm\UtmTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Report\VisualConstructor\IReportMultipleData;
use Bitrix\Report\VisualConstructor\IReportMultipleGroupedData;
use Bitrix\Report\VisualConstructor\IReportSingleData;
use Bitrix\Crm\StatusTable;

class Deal extends Base implements  IReportSingleData, IReportMultipleData, IReportMultipleGroupedData
{

	const WHAT_WILL_CALCULATE_DEAL_COUNT = 'DEAL_COUNT';
	const WHAT_WILL_CALCULATE_DEAL_SUM = 'DEAL_SUM';
	const WHAT_WILL_CALCULATE_DEAL_WON_COUNT = 'DEAL_WON_COUNT';
	const WHAT_WILL_CALCULATE_DEAL_WON_SUM = 'DEAL_WON_SUM';
	const WHAT_WILL_CALCULATE_DEAL_LOSES_SUM = 'DEAL_LOSES_SUM';
	const WHAT_WILL_CALCULATE_DEAL_COUNT_AND_SUM = 'DEAL_COUNT_AND_SUM';


	const WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM = 'FIRST_DEAL_WON_SUM';
	const WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM = 'RETURN_DEAL_WON_SUM';


	const WHAT_WILL_CALCULATE_DEAL_CONVERSION = 'DEAL_CONVERSION';

	const GROUPING_BY_STAGE = 'STAGE';
	const GROUPING_BY_DATE = 'DATE';
	const GROUPING_BY_SOURCE = 'SOURCE';
	const GROUPING_BY_RESPONSIBLE = 'RESPONSIBLE';


	const FILTER_FIELDS_PREFIX = 'FROM_DEAL_';


	const STAGE_DEFAULT_COLORS = [
		'DEFAULT_COLOR' => '#ACE9FB',
		'DEFAULT_FINAL_SUCCESS__COLOR' => '#DBF199',
		'DEFAULT_FINAL_UN_SUCCESS_COLOR' => '#FFBEBD',
		'DEFAULT_LINE_COLOR' => '#ACE9FB',
	];

	public function __construct()
	{
		parent::__construct();
		$this->setTitle('Deal');
		$this->setCategoryKey('crm');
	}

	protected function collectFormElements()
	{
		parent::collectFormElements();
	}


	public function mutateFilterParameter($filterParameters)
	{
		$filterParameters =  parent::mutateFilterParameter($filterParameters);

		$fieldsToOrmMap =  $this->getDealFieldsToOrmMap();

		foreach ($filterParameters as $key => $value)
		{
			if (isset($fieldsToOrmMap[$key]) && $fieldsToOrmMap[$key] !== $key)
			{
				$filterParameters[$fieldsToOrmMap[$key]] = $value;
				unset($filterParameters[$key]);
			}
			elseif (!isset($fieldsToOrmMap[$key]))
			{
				unset($filterParameters[$key]);
			}

		}

		return $filterParameters;
	}

	/**
	 * Called every time when calculate some report result before passing some concrete handler, such us getMultipleData or getSingleData.
	 * Here you can get result of configuration fields of report, if report in widget you can get configurations of widget.
	 *
	 * @return mixed
	 */
	public function prepare()
	{
		$filterParameters = $this->getFilterParameters();


		/** @var DropDown $grouping */
		$groupingField = $this->getFormElement('groupingBy');
		$groupingValue = $groupingField ? $groupingField->getValue() : null;

		$calculateField = $this->getFormElement('calculate');
		$calculateValue = $calculateField ? $calculateField->getValue() : null;


		$query = new Query(DealTable::getEntity());

		switch ($groupingValue)
		{
			case self::GROUPING_BY_DATE:
				$query->registerRuntimeField(new ExpressionField('DATE_CREATE_DAY', "DATE_FORMAT(%s, '%%Y-%%m-%%d 00:00')", 'DATE_CREATE'));
				$query->addSelect('DATE_CREATE_DAY');
				$query->addGroup('DATE_CREATE_DAY');
				break;
			case self::GROUPING_BY_STAGE:
				$query->addSelect('STAGE_ID');
				$query->addGroup('STAGE_ID');


				if (!isset($filterParameters['CATEGORY_ID']))
				{
					$query->where('CATEGORY_ID', 0);
				}

				$statusNameListByStatusId = [];
				foreach ($this->getStageList() as $status)
				{
					$statusNameListByStatusId[$status['STATUS_ID']]['NAME'] = $status['NAME'];
					$statusNameListByStatusId[$status['STATUS_ID']]['ENTITY_ID'] = $status['ENTITY_ID'];
				}

				break;
			case self::GROUPING_BY_SOURCE:
				$query->addSelect('SOURCE_ID');
				$query->addGroup('SOURCE_ID');

				foreach ($this->getSourceNameList() as $source)
				{
					$sourceNameListByStatusId[$source['STATUS_ID']] = $source['NAME'];
				}
				break;
			case self::GROUPING_BY_RESPONSIBLE:
				$query->addSelect('ASSIGNED_BY_ID');
				$query->addGroup('ASSIGNED_BY_ID');
				break;
		}


		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_DEAL_COUNT_AND_SUM:
				$query->addSelect(new ExpressionField('VALUE', 'COUNT(*)'));
				$query->addSelect(new ExpressionField('SUM', 'SUM(OPPORTUNITY_ACCOUNT)'));
				break;
			case self::WHAT_WILL_CALCULATE_DEAL_CONVERSION:
			case self::WHAT_WILL_CALCULATE_DEAL_WON_COUNT:
			case self::WHAT_WILL_CALCULATE_DEAL_COUNT:
				$query->addSelect(new ExpressionField('VALUE', 'COUNT(*)'));
				break;
			case self::WHAT_WILL_CALCULATE_DEAL_SUM:
			case self::WHAT_WILL_CALCULATE_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM:
				$query->addSelect(new ExpressionField('VALUE', 'SUM(OPPORTUNITY_ACCOUNT)'));
				break;
		}

		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM:
				$query->where('IS_RETURN_CUSTOMER', 'N');
				break;
			case self::WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM:
				$query->where('IS_RETURN_CUSTOMER', 'Y');
				break;
		}

		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_DEAL_WON_COUNT:
			case self::WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM:
				$query->where('STAGE_SEMANTIC_ID', 'S');
				break;
			case self::WHAT_WILL_CALCULATE_DEAL_LOSES_SUM:
				$query->where('STAGE_SEMANTIC_ID', 'F');
				break;
			case self::WHAT_WILL_CALCULATE_DEAL_CONVERSION:
				$query->addGroup('STAGE_SEMANTIC_ID');
				$query->addSelect('STAGE_SEMANTIC_ID');
				break;
		}


		foreach ($filterParameters as $key => $value)
		{

			if ($key === 'TIME_PERIOD')
			{
				if ($value['from'] !== "" && $value['to'] !== "")
				{
					$query->where('DATE_CREATE', '<=', $value['to'])
						  ->where(
						  	Query::filter()
								 ->logic('or')
								 ->whereNull('DATE_CLOSED')
								 ->where('DATE_CLOSED', '>=', $value['from'])
						  );

					continue;
				}
			}

			switch 	($value['type'])
			{
				case 'date':
				case 'diapason':
					if ($value['from'] !== "")
					{
						$query->where($key, '>=', $value['from']);
					}

					if ($value['to'] !== "")
					{
						$query->where($key, '<=', $value['to']);
					}
					break;
				case 'list':
				case 'text':
				case 'checkbox':
					$query->addFilter($key, $value['value']);
					break;

			}
		}
		$this->addPermissionsCheck($query);
		$results = $query->exec()->fetchAll();

		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_DEAL_CONVERSION:
				$allDealCount = [];
				$successDealCount = [];
				foreach ($results as $result)
				{
					switch ($groupingValue)
					{
						case self::GROUPING_BY_RESPONSIBLE:
							$groupingFieldName = 'ASSIGNED_BY_ID';
							$groupingFieldValue = $result[$groupingFieldName];
							break;
						default:
							$groupingFieldValue = 'withoutGrouping';
					}


					$allDealCount[$groupingFieldValue] += $result['VALUE'];
					if ($result['STAGE_SEMANTIC_ID'] == 'S')
					{
						$successDealCount[$groupingFieldValue] += $result['VALUE'];
					}
				}
				$results = [];

				foreach ($allDealCount as $groupingKey => $count)
				{
					if (!empty($successDealCount[$groupingKey]))
					{
						$results[] = [
							$groupingFieldName => $groupingKey,
							'VALUE' => ($successDealCount[$groupingKey] / $count) * 100
						];
					}
					else
					{
						$results[] = [
							$groupingFieldName => $groupingKey,
							'VALUE' => 0
						];
					}

				}
				break;
		}


		$dealCalculatedValue = [];

		foreach ($results as $result)
		{
			switch ($groupingValue)
			{
				case self::GROUPING_BY_DATE:
					$dealCalculatedValue[$result['DATE_CREATE_DAY']]['value'] = $result['VALUE'];
					$dealCalculatedValue[$result['DATE_CREATE_DAY']]['title'] = $result['DATE_CREATE_DAY'];
					break;
				case self::GROUPING_BY_STAGE:
					$dealCalculatedValue[$result['STAGE_ID']]['value'] = $result['VALUE'];
					if ($result['SUM'])
					{
						$dealCalculatedValue[$result['STAGE_ID']]['additionalValues'] = [
							'sum' => [
								'VALUE' => $result['SUM']
							]
						];
					}

					$dealCalculatedValue[$result['STAGE_ID']]['title'] = !empty($statusNameListByStatusId[$result['STAGE_ID']]['NAME']) ? $statusNameListByStatusId[$result['STAGE_ID']]['NAME'] : '';
					$dealCalculatedValue[$result['STAGE_ID']]['color'] = $this->getStageColor($result['STAGE_ID']);
					break;
				case self::GROUPING_BY_SOURCE:
					$dealCalculatedValue[$result['SOURCE_ID']]['value'] = $result['VALUE'];
					$dealCalculatedValue[$result['SOURCE_ID']]['title'] = !empty($sourceNameListByStatusId[$result['SOURCE_ID']]) ? $sourceNameListByStatusId[$result['SOURCE_ID']] : '';
					break;
				case self::GROUPING_BY_RESPONSIBLE:
					//TODO optimise here
					$userInfo = $this->getUserInfo($result['ASSIGNED_BY_ID']);
					$dealCalculatedValue[$result['ASSIGNED_BY_ID']]['value'] = $result['VALUE'];
					$dealCalculatedValue[$result['ASSIGNED_BY_ID']]['title'] = $userInfo['name'];
					$dealCalculatedValue[$result['ASSIGNED_BY_ID']]['logo'] = $userInfo['icon'];
					$dealCalculatedValue[$result['ASSIGNED_BY_ID']]['targetUrl'] = $userInfo['link'];
					break;
				default:
					$dealCalculatedValue['withoutGrouping'] = $result['VALUE'];
					break;

			}

		}

		if ($groupingValue === self::GROUPING_BY_STAGE && isset($statusNameListByStatusId))
		{
			$sortedLeadCountListByStatus = [];
			foreach ($statusNameListByStatusId as $statusId => $status)
			{
				if (in_array($statusId, $this->getDealUnSuccessStageNameList()))
				{
					continue;
				}

				if (!empty($dealCalculatedValue[$statusId]))
				{
					$sortedLeadCountListByStatus[$statusId] = $dealCalculatedValue[$statusId];
				}
				else
				{
					$sortedLeadCountListByStatus[$statusId] = [
						'value' => 0,
						'title' => $status['NAME'],
						'color' => $this->getStageColor($statusId)
					];
				}
			}
			$dealCalculatedValue = $sortedLeadCountListByStatus;
		}


		return $dealCalculatedValue;
	}


	private function addPermissionsCheck(Query $query, $userId = 0)
	{
		static $permissionEntity;
		if($userId <= 0)
		{
			$userId = EntityAuthorization::getCurrentUserID();
		}
		$userPermissions = EntityAuthorization::getUserPermissions($userId);

		$permissionSql = $this->buildPermissionSql(
			array(
				'alias' => 'L',
				'permissionType' => 'READ',
				'options' => array(
					'PERMS' => $userPermissions,
					'RAW_QUERY' => true
				)
			)
		);

		if ($permissionSql)
		{
			if (!$permissionEntity)
			{
				$permissionEntity = \Bitrix\Main\Entity\Base::compileEntity(
					'deal_user_perms',
					array('ENTITY_ID' => array('data_type' => 'integer')),
					array('table_name' => "({$permissionSql})")
				);
			}


			$query->registerRuntimeField('',
				new ReferenceField('PERMS',
					$permissionEntity,
					array('=this.ID' => 'ref.ENTITY_ID'),
					array('join_type' => 'INNER')
				)
			);


		}

	}

	private function buildPermissionSql(array $params)
	{
		return \CCrmDeal::BuildPermSql(
			isset($params['alias']) ? $params['alias'] : 'D',
			isset($params['permissionType']) ? $params['permissionType'] : 'READ',
			isset($params['options']) && is_array($params['options']) ? $params['options'] : array()
		);
	}

	private function getDealFieldsToOrmMap()
	{
		$fields  = array(
			'ID' => 'ID',
			'TITLE' => 'TITLE',
			'ASSIGNED_BY_ID' => 'ASSIGNED_BY_ID',
			'OPPORTUNITY' => 'OPPORTUNITY',
			'CURRENCY_ID' => 'CURRENCY_ID',
			'PROBABILITY' => 'PROBABILITY',
			'IS_NEW' => 'IS_NEW',
			'IS_RETURN_CUSTOMER' => 'IS_RETURN_CUSTOMER',
			'IS_REPEATED_APPROACH' => 'IS_REPEATED_APPROACH',
			'SOURCE_ID' => 'SOURCE_ID',
			'STAGE_SEMANTIC_ID' => 'STAGE_SEMANTIC_ID',
			'STAGE_ID' => 'STAGE_ID',
			'CATEGORY_ID' => 'CATEGORY_ID',
			'BEGINDATE' => 'BEGINDATE',
			'CLOSED' => 'CLOSED',
			//'*ACTIVITY_COUNTER' => 'CLOSED',
			'EVENT_DATE' => 'EVENT_DATE',
			'EVENT_ID' => 'EVENT_ID',
			'CONTACT_ID' => 'CONTACT_ID',
			'CONTACT_FULL_NAME' => 'CONTACT.FULL_NAME',
			'COMPANY_ID' => 'COMPANY_ID',
			'COMPANY_TITLE' => 'COMPANY.TITLE',
			'COMMENTS' => 'COMMENTS',
			'TYPE_ID' => 'TYPE_ID',
			'DATE_CREATE' => 'DATE_CREATE',
			'DATE_MODIFY' => 'DATE_MODIFY',
			'CREATED_BY_ID' => 'CREATED_BY_ID',
			'MODIFY_BY_ID' => 'MODIFY_BY_ID',
			'PRODUCT_ROW_PRODUCT_ID' => 'PRODUCT_ROW.PRODUCT_ID',
			'ORIGINATOR_ID' => 'ORIGINATOR_ID',
			'WEBFORM_ID' => 'WEBFORM_ID',
			'CRM_DEAL_RECURRING_ACTIVE' => 'CRM_DEAL_RECURRING.ACTIVE',
			'CRM_DEAL_RECURRING_NEXT_EXECUTION' => 'CRM_DEAL_RECURRING.NEXT_EXECUTION',
			'CRM_DEAL_RECURRING_LIMIT_DATE' => 'CRM_DEAL_RECURRING.LIMIT_DATE',
			'CRM_DEAL_RECURRING_COUNTER_REPEAT' => 'CRM_DEAL_RECURRING.COUNTER_REPEAT',
		);


		$codeList = UtmTable::getCodeList();
		foreach ($codeList as $code)
		{
			$fields[$code] = $code . '.VALUE';
		}

		return $fields;











	}

	private function getStageColor($statusId)
	{
		$stageList = $this->getStageList();

		$colorsList = $this->getStageColorList($stageList[$statusId]['ENTITY_ID']);
		if (!isset($colorsList[$statusId]))
		{
			return self::STAGE_DEFAULT_COLORS['DEFAULT_COLOR'];
		}



		return $colorsList[$statusId];
	}

	public function getTargetUrl($baseUri, $params = [])
	{
		$calculateField = $this->getFormElement('calculate');
		$calculateValue = $calculateField ? $calculateField->getValue() : null;
		$filterParameters = $this->getFilterParameters();

		if (!empty($filterParameters['TIME_PERIOD']))
		{
			/** @var DateTime $from */
			$from = $filterParameters['TIME_PERIOD']['from'];
			/** @var DateTime $to */
			$to = $filterParameters['TIME_PERIOD']['to'];


			$params['ACTIVE_TIME_PERIOD_datesel'] =  $filterParameters['TIME_PERIOD']['datesel'];
			$params['ACTIVE_TIME_PERIOD_month'] =  $filterParameters['TIME_PERIOD']['month'];
			$params['ACTIVE_TIME_PERIOD_year'] =  $filterParameters['TIME_PERIOD']['year'];
			$params['ACTIVE_TIME_PERIOD_quarter'] =  $filterParameters['TIME_PERIOD']['quarter'];
			$params['ACTIVE_TIME_PERIOD_days'] =  $filterParameters['TIME_PERIOD']['days'];
			$params['ACTIVE_TIME_PERIOD_from'] =  $from->format('d.m.Y H:i:s');
			$params['ACTIVE_TIME_PERIOD_to'] =  $to->format('d.m.Y H:i:s');


		}

		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM:
				$params['IS_RETURN_CUSTOMER'] = 'N';
				break;
			case self::WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM:
				$params['IS_RETURN_CUSTOMER'] = 'Y';
				break;
		}

		switch ($calculateValue)
		{
			case self::WHAT_WILL_CALCULATE_RETURN_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_FIRST_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_DEAL_WON_SUM:
			case self::WHAT_WILL_CALCULATE_DEAL_WON_COUNT:
				$params['STATUS_SEMANTIC_ID'] = 'S';
				break;
			case self::WHAT_WILL_CALCULATE_DEAL_COUNT:
			case self::WHAT_WILL_CALCULATE_DEAL_SUM:
				$params['STATUS_SEMANTIC_ID'] = ['P', 'S'];
				break;
		}
		return parent::getTargetUrl($baseUri, $params); // TODO: Change the autogenerated stub
	}

	private function getStageColorList($entityId)
	{
		static $result = [];
		if (!empty($result[$entityId]))
		{
			return $result;
		}

		$leadStatusColors = (array)unserialize(\COption::GetOptionString('crm', 'CONFIG_STATUS_' . $entityId));

		if ($leadStatusColors)
		{
			foreach ($leadStatusColors as $statusKey => $value)
			{
				$result[$statusKey] = $value['COLOR'];
			}
		}

		return $result;
	}

	private function getDealUnSuccessStageNameList()
	{
		static $unSuccessStageList;
		if (!empty($unSuccessStageList))
		{
			return $unSuccessStageList;
		}

		$filterParameters = $this->getFilterParameters();
		$namespaces = [];
		if (!isset($filterParameters['CATEGORY_ID']))
		{
			$namespaces[] = DealCategory::prepareStageNamespaceID(0);
		}
		else
		{
			foreach ($filterParameters['CATEGORY_ID']['value'] as $category)
			{
				$namespaces[] = DealCategory::prepareStageNamespaceID($category);
			}
		}

		$unSuccessStageList = [];
		foreach ($namespaces as $namespace)
		{
			$stageSemanticInfo = \CCrmStatus::GetDealStageSemanticInfo($namespace);
			$unSuccessStageList[] = $stageSemanticInfo['FINAL_UNSUCCESS_FIELD'];
		}

		return $unSuccessStageList;
	}

	private function getStageList()
	{
		static $stageList = [];
		if (!empty($stageList))
		{
			return $stageList;
		}


		$filterParameters = $this->getFilterParameters();
		$categories = [];
		if (!isset($filterParameters['CATEGORY_ID']))
		{
			$categories[] = 0;
		}
		else
		{
			foreach ($filterParameters['CATEGORY_ID']['value'] as $category)
			{
				$categories[] = $category;
			}

		}

		foreach ($categories as $category)
		{
			$stageListByCategory = DealCategory::getStageList($category);
			$entityId = $category == 0 ? 'DEAL_STAGE' : 'DEAL_STAGE_' . $category;
			foreach ($stageListByCategory as $stageId => $name)
			{
				$stageList[$stageId] = [
					'NAME' => $name,
					'STATUS_ID' => $stageId,
					'ENTITY_ID' => $entityId
				];
			}
		}
		return $stageList;
	}


	private function getSourceNameList()
	{
		$sourceListQuery = new Query(StatusTable::getEntity());
		$sourceListQuery->where('ENTITY_ID', 'SOURCE');
		$sourceListQuery->addSelect('STATUS_ID');
		$sourceListQuery->addSelect('NAME');
		return $sourceListQuery->exec()->fetchAll();
	}

	public function getUserInfo($userId)
	{
		static $users = array();

		if(!$userId)
		{
			return null;
		}

		if(!$users[$userId])
		{
			// prepare link to profile
			$replaceList = array('user_id' => $userId);
			$template = '/company/personal/user/#user_id#/';
			$link = \CComponentEngine::makePathFromTemplate($template, $replaceList);

			$userFields = \Bitrix\Main\UserTable::getRowById($userId);
			if(!$userFields)
			{
				return null;
			}

			// format name
			$userName = \CUser::FormatName(
				\CSite::GetNameFormat(),
				array(
					'LOGIN' => $userFields['LOGIN'],
					'NAME' => $userFields['NAME'],
					'LAST_NAME' => $userFields['LAST_NAME'],
					'SECOND_NAME' => $userFields['SECOND_NAME']
				),
				true, false
			);

			// prepare icon
			$fileTmp = \CFile::ResizeImageGet(
				$userFields['PERSONAL_PHOTO'],
				array('width' => 42, 'height' => 42),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
			$userIcon = $fileTmp['src'];

			$users[$userId] = array(
				'id' => $userId,
				'name' => $userName,
				'link' => $link,
				'icon' => $userIcon
			);
		}

		return $users[$userId];
	}


	/**
	 * array with format
	 * array(
	 *     'title' => 'Some Title',
	 *     'value' => 0,
	 *     'targetUrl' => 'http://url.domain?params=param'
	 * )
	 * @return array
	 */
	public function getSingleData()
	{
		$calculatedData = $this->getCalculatedData();
		$result = [
			'value' => $calculatedData['withoutGrouping'],
		];


		return $result;
	}

	/**
	 * @return array
	 */
	public function getSingleDemoData()
	{
		return [
			'value' => 5
		];
	}

	/**
	 * array with format
	 * array(
	 *     'items' => array(
	 *            array(
	 *                'label' => 'Some Title',
	 *                'value' => 5,
	 *                'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *     )
	 * )
	 * @return array
	 */
	public function getMultipleData()
	{

		$calculatedData = $this->getCalculatedData();
		$items = [];
		if (!empty($calculatedData))
		{
			foreach ($calculatedData as $data)
			{
				$item = [
					'label' => $data['title'],
					'value' => $data['value'],
					'color' => $data['color'],
				];
				if (isset($data['additionalValues']['sum']))
				{
					$item['additionalValues']['sum'] = [
						'value' => $data['additionalValues']['sum']['VALUE']
					];

					$config['additionalValues']['sum']['titleShort'] = Loc::getMessage('CRM_REPORT_DEAL_HANDLER_DEAL_SUM_SHORT_TITLE');
				}
				$items[] = $item;
			}
		}

		$config['titleShort'] = Loc::getMessage('CRM_REPORT_DEAL_HANDLER_DEAL_COUNT_SHORT_TITLE');
		$config['titleMedium'] = 'meduim';

		return [
			'items' => $items,
			'config' => $config,
		];
	}



	/**
	 * @return array
	 */
	public function getMultipleDemoData()
	{
		return [
			'items' => [
				[
					'label' => 'First group',
					'value' => 1
				],
				[
					'label' => 'Second group',
					'value' => 5
				],
				[
					'label' => 'Third group',
					'value' => 1
				],
				[
					'label' => 'Fourth group',
					'value' => 8
				]
			]
		];
	}

	/**
	 * Array format for return this method:<br>
	 * array(
	 *      'items' => array(
	 *           array(
	 *              'groupBy' => 01.01.1970 or 15 etc.
	 *              'title' => 'Some Title',
	 *              'value' => 1,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          ),
	 *          array(
	 *              'groupBy' => 01.01.1970 or 15 etc.
	 *              'title' => 'Some Title',
	 *              'value' => 2,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *      ),
	 *      'config' => array(
	 *          'groupsLabelMap' => array(
	 *              '01.01.1970' => 'Start of our internet evolution'
	 *              '15' =>  'Just a simple integer'
	 *          ),
	 *          'reportTitle' => 'Some title for this report'
	 *      )
	 * )
	 * @return array
	 */
	public function getMultipleGroupedData()
	{
		$calculatedData = $this->getCalculatedData();

		$grouping = $this->getFormElement('groupingBy');
		$groupingValue = $grouping ? $grouping->getValue() : null;

		$calculateField = $this->getFormElement('calculate');
		$calculateValue = $calculateField ? $calculateField->getValue() : null;

		$items = [];
		$config = [];
		if ($groupingValue == self::GROUPING_BY_DATE)
		{
			$config['mode'] = 'date';
		}

		$amount = [];
		$amount['value'] = 0;
		$amount['prefix'] = '';
		$amount['postfix'] = '';

		foreach ($calculatedData as $groupingKey => $item)
		{
			$resultItem = array(
				'groupBy' => $groupingKey,
				'label' => $item['title'],
				'value' => $item['value'],
				'slider' => true,
				'targetUrl' => $this->getTargetUrl('/crm/deal/analytics/list/', [
					'ASSIGNED_BY_ID_label' => $item['title'],
					'ASSIGNED_BY_ID_value' => $groupingKey,
					'ASSIGNED_BY_ID' => $groupingKey,
				]),
			);

			$amount['value'] += $item['value'];
			switch ($calculateValue)
			{
				case self::WHAT_WILL_CALCULATE_DEAL_CONVERSION:
					$resultItem['postfix'] = '%';
					$resultItem['slider'] = false;
					$amount['postfix'] = '%';
					break;
			}


			$items[] = $resultItem;
			$config['groupsLabelMap'][$groupingKey] = $item['title'];
			$config['groupsLogoMap'][$groupingKey] = $item['logo'];
			$config['groupsTargetUrlMap'][$groupingKey] = $item['targetUrl'];
		}



		$config['reportTitle'] = $this->getFormElement('label')->getValue();
		$config['reportColor'] = $this->getFormElement('color')->getValue();
		$config['reportTitleShort'] = 'dasda';
		$config['reportTitleMedium'] = 'meduim';
		$config['amount'] = $amount;
		$result =  [
			'items' => $items,
			'config' => $config,
		];
		return $result;
	}


	/**
	 * @return array
	 */
	public function getMultipleGroupedDemoData()
	{
		// TODO: Implement getMultipleGroupedDemoData() method.
	}
}