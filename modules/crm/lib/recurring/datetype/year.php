<?php
namespace Bitrix\Crm\Recurring\DateType;

use Bitrix\Main;
use Bitrix\Main\Type\Date;

class Year
{
	const TYPE_DAY_OF_CERTAIN_MONTH = 1;
	const TYPE_WEEKDAY_OF_CERTAIN_MONTH = 2;
	const TYPE_ALTERNATING_YEAR = 3;
	/**
	 * @param array $params
	 * @param Date $startDate
	 * 
	 * @return Date
	 */
	public static function calculateDate(array $params, Date $startDate)
	{
		if ((int)$params['TYPE'] === self::TYPE_ALTERNATING_YEAR)
		{
			if ((int)$params['INTERVAL_YEAR'] <= 0)
				$params['INTERVAL_YEAR'] = 1;
			return $startDate->add(" +". (int)$params['INTERVAL_YEAR']. " years");
		}
		elseif ((int)$params['TYPE'] === self::TYPE_WEEKDAY_OF_CERTAIN_MONTH)
		{
			$params['TYPE'] = Month::TYPE_WEEKDAY_OF_ALTERNATING_MONTHS;
		}
		else
		{
			$params['TYPE'] = Month::TYPE_DAY_OF_ALTERNATING_MONTHS;
		}

		$month = (int)$params['INTERVAL_MONTH'];
		$params['INTERVAL_MONTH'] = (int)$params['INTERVAL_MONTH'] < 12 ? (int)$params['INTERVAL_MONTH'] : 12;

		$yearValue = (int)$startDate->format("Y");
		if ($month < (int)$startDate->format("n"))
		{
			$yearValue++;
		}

		$date = mktime(0, 0, 0, 12, 1, $yearValue - 1);
		$date = Date::createFromTimestamp($date);
		/** @var Date $resultDate */
		$resultDate = Month::calculateDate($params, $date);
		if ($startDate->getTimestamp() > $resultDate->getTimestamp())
			$resultDate = Month::calculateDate($params, $date->add("+1 year"));

		return $resultDate;
	}
}