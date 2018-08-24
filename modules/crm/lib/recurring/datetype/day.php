<?php
namespace Bitrix\Crm\Recurring\DateType;

use Bitrix\Main;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Loader;

class Day 
{
	const TYPE_ALTERNATING_DAYS = 1;
	const TYPE_A_FEW_DAYS_BEFORE = 2;
	const TYPE_A_FEW_DAYS_AFTER = 3;

	/**
	 * @param array $params
	 * @param Date $date
	 *
	 * @return Date
	 */
	public static function calculateDate(array $params, Date $date)
	{
		if ($params['IS_WORKDAY'] === 'Y' && (int)$params['TYPE'] === self::TYPE_ALTERNATING_DAYS)
		{
			$date = self::calculateForWorkingDays($params, $date);
		}
		elseif ((int)$params['TYPE'] === self::TYPE_A_FEW_DAYS_BEFORE)
		{
			$date = $date->add(" -". (int)$params['INTERVAL_DAY']. " days");
		}
		elseif ((int)$params['TYPE'] === self::TYPE_A_FEW_DAYS_AFTER)
		{
			$date = $date->add(" +". (int)$params['INTERVAL_DAY']. " days");
		}
		else
		{
			if ((int)$params['INTERVAL_DAY'] <= 0)
				$params['INTERVAL_DAY'] = 1;

			$date = $date->add(" +". ((int)$params['INTERVAL_DAY'] - 1). " days");
		}

		return $date;
	}

	/**
	 * @param array $params
	 * @param Date $date
	 * @param int $limit
	 *
	 * @return Date $date
	 */
	public static function calculateForWorkingDays(array $params, Date $date, $limit = null)
	{
		$dayNumber = 0;
		$limit = (int)$limit;
		$isLimit = $limit > 0;
		$weekDays = array('SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6);

		Loader::includeModule('calendar');
		$calendarSettings = \CCalendar::GetSettings();
		$weekHolidays = array_keys(array_intersect(array_flip($weekDays), $calendarSettings['week_holidays']));
		$yearHolidays = explode(',', $calendarSettings['year_holidays']);
		$lastWorkingDateInLimit = $date;
		$interval = (int)$params['INTERVAL_DAY'];

		while ($interval > 0)
		{
			if (!in_array($date->format("j.m"), $yearHolidays) && !in_array($date->format("w"), $weekHolidays))
			{
				if ($isLimit && $dayNumber < $limit)
				{
					$lastWorkingDateInLimit = clone($date);
				}
				$interval--;
			}

			if ($isLimit && $dayNumber == $limit)
			{
				return $lastWorkingDateInLimit;
			}

			if ($interval > 0)
			{
				$date->add("+ 1 days");
				$dayNumber++;
			}			
		}

		return $date;
	}
}