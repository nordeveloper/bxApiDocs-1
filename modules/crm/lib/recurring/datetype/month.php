<?php
namespace Bitrix\Crm\Recurring\DateType;

use Bitrix\Main;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class Month
{
	const TYPE_DAY_OF_ALTERNATING_MONTHS = 1;
	const TYPE_WEEKDAY_OF_ALTERNATING_MONTHS = 2;
	const TYPE_A_FEW_MONTHS_BEFORE = 3;
	const TYPE_A_FEW_MONTHS_AFTER = 4;
	const FIRST_MONTH_DAY = 1;
	const LAST_MONTH_DAY = 0;
	/**
	 * @param array $params
	 * @param Date $startDate
	 *
	 * @return Date
	 */
	public static function calculateDate(array $params, Date $startDate)
	{
		$baseData = self::prepareCalculation($params, $startDate);

		$month = $baseData['MONTH'];
		$yearValue = $baseData['YEAR'];
		$intervalMonth = $baseData['INTERVAL_MONTH'];
		
		switch ((int)$params['TYPE'])
		{
			case self::TYPE_DAY_OF_ALTERNATING_MONTHS:
				{
					if ($params['IS_WORKDAY'] !== 'Y')
					{
						$day = self::getDayNumber($params, $month, $yearValue);
						if ($day == self::LAST_MONTH_DAY)
						{
							$month++;
						}
						$timestamp = mktime(0, 0, 0, $month, $day, $yearValue);
						$date = Date::createFromTimestamp($timestamp);
						if ($timestamp < $startDate->getTimestamp())
						{
							$date->add('+ 1 month');
						}
					}
					else
					{
						$firstMonthDayTimestamp = mktime(0, 0, 0, $month, 1, $yearValue);
						$firstMonthDay = Date::createFromTimestamp($firstMonthDayTimestamp);
						$date = clone($firstMonthDay);
						$date = Day::calculateForWorkingDays($params,	$date, $firstMonthDay->format('t'));

						if ($startDate->getTimestamp() > $date->getTimestamp())
						{
							$date = $firstMonthDay->add('+ 1 month');
							$date = Day::calculateForWorkingDays($params,	$date, $firstMonthDay->format('t'));
						}
					}
				}
				break;
			case self::TYPE_WEEKDAY_OF_ALTERNATING_MONTHS:
				{
					$firstMonthDay = mktime(0, 0, 0, $month, 1, $yearValue);
					$clearDate = Date::createFromTimestamp($firstMonthDay);
					$date = self::calculateForWeekdayType($params, $clearDate);
					if ($startDate->getTimestamp() > $date->getTimestamp())
					{
						$date = self::calculateForWeekdayType($params ,$clearDate->add("+1 months"));
					}
				}
				break;
			case self::TYPE_A_FEW_MONTHS_BEFORE:
				{
					$date = $startDate->add(" -".$intervalMonth." months");
				}
				break;
			case self::TYPE_A_FEW_MONTHS_AFTER:
				{
					$date = $startDate->add(" +".$intervalMonth." months");
				}
				break;
			default:
				$date = $startDate;
		}

		return $date;
	}

	/**
	 * @param array $params
	 * @param Date $startDate
	 *
	 * @return Date
	 */
	private static function calculateForWeekdayType(array $params, Date $startDate)
	{
		$date = clone($startDate);

		$numWeekDay = (int)$date->format('N');

		if ($numWeekDay <= $params['WEEKDAY'])
		{
			$offset = $params['WEEKDAY'] - $numWeekDay;
		}
		else
		{
			$offset = 7 + $params['WEEKDAY'] - $numWeekDay;
		}

		$date->add("+ " . $offset . "days");

		if ((int)$params['INTERVAL_WEEK'] <= 3)
		{
			$date->add("+" . (int)$params['INTERVAL_WEEK'] . " weeks");
		}
		else
		{
			$date->add("+3 weeks");
			$restDays = (int)(date('t', mktime(0, 0, 0, (int)$startDate->format("n"), 1, (int)$startDate->format("Y")))) - (int)($date->format('j'));
			if ($restDays >= 7)
			{
				$date->add("+1 weeks");
				return $date;
			}
		}
		return $date;
	}

	/**
	 * @param array $params
	 * @param Date $startDate
	 *
	 * @return array
	 */
	private static function prepareCalculation(array $params, Date $startDate)
	{
		$interval = (int)$params['INTERVAL_MONTH'];
		if ($interval < 0) 
		{
			$interval = 0;
		}
		$month = (int)$startDate->format("n") + $interval;

		$year = (int)$startDate->format("Y");
		
		if ($month > 12)
		{
			$month = $month - 12;
			$year++;
		}
		
		return array(
			"INTERVAL_MONTH" => $interval, 
			"MONTH" => $month, 
			"YEAR" => $year
		);
	}

	/**
	 * @param array $params
	 * @param $month
	 * @param $yearValue
	 *
	 * @return int
	 */
	private static function getDayNumber(array $params, $month, $yearValue)
	{
		$countMonthDays = date('t', mktime(0, 0, 0, $month, 1, $yearValue));
		
		if ((int)$params['INTERVAL_DAY'] > $countMonthDays) 
		{
			$day = self::LAST_MONTH_DAY;
		} 
		elseif ((int)$params['INTERVAL_DAY'] <= 0 || $params['IS_WORKDAY'] === 'Y')
		{
			$day = self::FIRST_MONTH_DAY;
		} 
		else 
		{
			$day = (int)$params['INTERVAL_DAY'];
		}

		return $day;
	}
}