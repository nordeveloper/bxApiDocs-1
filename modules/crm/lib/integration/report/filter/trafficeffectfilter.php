<?php

namespace Bitrix\Crm\Integration\Report\Filter;

use Bitrix\Crm\Analytics;
use Bitrix\Main\Localization\Loc;

/**
 * Class LeadAnalyticsFilter
 * @package Bitrix\Crm\Integration\Report\Filter
 */
class TrafficEffectFilter extends Base
{
	/**
	 * Get fields list.
	 *
	 * @return array
	 */
	public static function getFieldsList()
	{
		$fieldsList = parent::getFieldsList();

		$fieldsList[] = [
			'id' => 'PERIOD',
			"name" => Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_FILTER_PERIOD_FIELD_TITLE'),
			'default' => true,
			'type' => 'date',
		];

		$fieldsList[] = [
			'id' => 'SOURCE_CODE',
			"name" => Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_FILTER_SOURCE_FIELD_TITLE'),
			'params' => array('multiple' => 'Y'),
			'default' => true,
			'type' => 'list',
			'items' => Analytics\Internals\TestDataTable::getSourceList()
		];

		return $fieldsList;
	}

	/**
	 * Get filter parameters.
	 *
	 * @return array
	 */
	public function getFilterParameters()
	{
		$parameters = parent::getFilterParameters();

		$parameters['VALUE_REQUIRED_MODE '] = true;

		return $parameters;
	}

	/**
	 * @return array
	 */
	public static function getPresetsList()
	{
		return [
			'crm_analytics_period_last_30' => [
				'name' => Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_FILTER_LAST_30_DAYS_PRESET_TITLE'),
				'default' => true,
				'fields' => [
					'PERIOD_datesel' => \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
				]
			],
			'crm_analytics_period_curr_month' => [
				'name' => Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_FILTER_CURRENT_MONTH_PRESET_TITLE'),
				'fields' => [
					'PERIOD_datesel' => \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
				]
			],
		];
	}
}