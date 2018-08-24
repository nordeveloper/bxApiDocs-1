<?php
namespace Bitrix\Crm\Recurring;

use Bitrix\Main;
use Bitrix\Main\Type\Date;

class Calculator
{
	const SALE_TYPE_NON_ACTIVE_DATE = 'N';
	const SALE_TYPE_DAY_OFFSET = 1;
	const SALE_TYPE_WEEK_OFFSET = 2;
	const SALE_TYPE_MONTH_OFFSET = 3;
	const SALE_TYPE_YEAR_OFFSET = 4;

	/**
	 * @param array $params
	 * @param Date $startDate
	 *
	 * @return Date
	 */
	public static function getNextDate(array $params, Date $startDate = null)
	{
		if (empty($params))
			return null;
		if (is_null($startDate))
		{
			$startDate = new Date();
		}

		if ($params['PREPARE_PARAMS_CALCULATION'] !== 'N')
			$params = static::prepareCalculationDate($params);

		switch($params['PERIOD'])
		{
			case static::SALE_TYPE_DAY_OFFSET:
				return DateType\Day::calculateDate($params, $startDate);
			case static::SALE_TYPE_WEEK_OFFSET:
				return DateType\Week::calculateDate($params, $startDate);
			case static::SALE_TYPE_MONTH_OFFSET:
				return DateType\Month::calculateDate($params, $startDate);
			case static::SALE_TYPE_YEAR_OFFSET:
				return DateType\Year::calculateDate($params, $startDate);
			default:
				return null;
		}
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @deprecated
	 */
	public static function prepareCalculationDate(array $params)
	{
		$result = array(
			"PERIOD" => (int)$params['PERIOD'] ? (int)$params['PERIOD'] : null
		);

		if (isset($params['PERIOD_DEAL']) && (int)$params['EXECUTION_TYPE'] === Manager::MULTIPLY_EXECUTION)
		{
			$result['PERIOD'] = (int)$params['PERIOD_DEAL'];

			switch($result['PERIOD'])
			{
				case self::SALE_TYPE_DAY_OFFSET:
				{
					$params['DAILY_INTERVAL_DAY'] = 2;
					break;
				}
				case self::SALE_TYPE_WEEK_OFFSET:
				{
					$result['PERIOD'] = self::SALE_TYPE_DAY_OFFSET;
					$params['DAILY_INTERVAL_DAY'] = 8;
					break;
				}
				case self::SALE_TYPE_MONTH_OFFSET:
				{
					$params['MONTHLY_MONTH_NUM_1'] = 2;
					$params['MONTHLY_INTERVAL_DAY'] = date('j');
					$params['MONTHLY_TYPE'] = DateType\Month::TYPE_DAY_OF_ALTERNATING_MONTHS;
					break;
				}
				case self::SALE_TYPE_YEAR_OFFSET:
				{
					$params['YEARLY_TYPE'] = DateType\Year::TYPE_ALTERNATING_YEAR;
					$params['INTERVAL_YEARLY'] = 2;
					break;
				}
			}
		}
		elseif (isset($params['DEAL_TYPE_BEFORE']) && (int)$params['EXECUTION_TYPE'] === Manager::SINGLE_EXECUTION)
		{
			$result['PERIOD'] = (int)$params['DEAL_TYPE_BEFORE'];

			switch($result['PERIOD'])
			{
				case self::SALE_TYPE_DAY_OFFSET:
				{
					$params['DAILY_TYPE'] = DateType\Day::TYPE_A_FEW_DAYS_BEFORE;
					$params['DAILY_INTERVAL_DAY'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
				case self::SALE_TYPE_WEEK_OFFSET:
				{
					$params['WEEKLY_TYPE'] = DateType\Week::TYPE_A_FEW_WEEKS_BEFORE;
					$params['WEEKLY_INTERVAL_WEEK'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
				case self::SALE_TYPE_MONTH_OFFSET:
				{
					$params['MONTHLY_TYPE'] = DateType\Month::TYPE_A_FEW_MONTHS_BEFORE;
					$result['INTERVAL_MONTH'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
			}
		}

		switch($result['PERIOD'])
		{
			case static::SALE_TYPE_DAY_OFFSET:
				$result['INTERVAL_DAY'] = $params['DAILY_INTERVAL_DAY'];
				$result['IS_WORKDAY'] = $params['DAILY_WORKDAY_ONLY'];
				if (empty($params['DAILY_TYPE']))
				{
					$params['DAILY_TYPE'] = DateType\Day::TYPE_ALTERNATING_DAYS;
				}
				$result['TYPE'] = $params['DAILY_TYPE'];
				break;
			case static::SALE_TYPE_WEEK_OFFSET:
				
				$result['WEEKDAYS'] = $params['WEEKLY_WEEK_DAYS'];
				$result['INTERVAL_WEEK'] = $params['WEEKLY_INTERVAL_WEEK'];
				if (!isset($params['WEEKLY_TYPE']))
				{
					$params['WEEKLY_TYPE'] = DateType\Week::TYPE_ALTERNATING_WEEKDAYS;
				}
				$result['TYPE'] = $params['WEEKLY_TYPE'];
				break;
			case static::SALE_TYPE_MONTH_OFFSET:
				$result['INTERVAL_DAY'] = $params['MONTHLY_INTERVAL_DAY'];
				if ((int)$params['MONTHLY_TYPE'] === DateType\Month::TYPE_DAY_OF_ALTERNATING_MONTHS)
				{
					$result['INTERVAL_MONTH'] = $params['MONTHLY_MONTH_NUM_1'] - 1;
					$result['IS_WORKDAY'] = $params['MONTHLY_WORKDAY_ONLY'];
				}
				elseif ((int)$params['MONTHLY_TYPE'] === DateType\Month::TYPE_WEEKDAY_OF_ALTERNATING_MONTHS)
				{
					$result['INTERVAL_WEEK'] = $params['MONTHLY_WEEKDAY_NUM'];
					$result['INTERVAL_MONTH'] = $params['MONTHLY_MONTH_NUM_2'] - 1;
					$result['WEEKDAY'] = $params['MONTHLY_WEEK_DAY'];
				}
				$result['TYPE'] = $params['MONTHLY_TYPE'];
				break;
			case static::SALE_TYPE_YEAR_OFFSET:
				$result['INTERVAL_DAY'] = $params['YEARLY_INTERVAL_DAY'];

				if ((int)$params['YEARLY_TYPE'] === DateType\Year::TYPE_DAY_OF_CERTAIN_MONTH)
				{
					$result['INTERVAL_DAY'] = $params['YEARLY_INTERVAL_DAY'];
					$result['INTERVAL_MONTH'] = $params['YEARLY_MONTH_NUM_1'];
					$result['IS_WORKDAY'] = $params['YEARLY_WORKDAY_ONLY'];
				}
				elseif ((int)$params['YEARLY_TYPE'] === DateType\Year::TYPE_WEEKDAY_OF_CERTAIN_MONTH)
				{
					$result['INTERVAL_WEEK'] = $params['YEARLY_WEEK_DAY_NUM'];
					$result['INTERVAL_MONTH'] = $params['YEARLY_MONTH_NUM_2'];
					$result['WEEKDAY'] = $params['YEARLY_WEEK_DAY'];
				}
				$result['TYPE'] = (int)$params['YEARLY_TYPE'];
		}
		
		return $result;
	}
}