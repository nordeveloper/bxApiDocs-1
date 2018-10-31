<?php
namespace Bitrix\Crm\Recurring\DateType;

use Bitrix\Main;
use Bitrix\Main\Type\Date;

class Year extends Base
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
		$year = new self($params);
		$year->setType($params['TYPE']);
		$year->setStartDate($startDate);
		$year->setInterval($params['INTERVAL_YEAR']);
		return $year->calculate();
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	protected function checkType($type)
	{
		return in_array((int)$type, [
			self::TYPE_DAY_OF_CERTAIN_MONTH,
			self::TYPE_WEEKDAY_OF_CERTAIN_MONTH,
			self::TYPE_ALTERNATING_YEAR,
		]);
	}

	/**
	 * Return the date with years interval.
	 *
	 * Example: repeat every {count years} years
	 *
	 * @return Date
	 */
	private function calculateAlternatingYears()
	{
		$value = $this->interval;
		if ($value <= 0)
		{
			$value = 1;
		}
		return $this->startDate->add("{$value} years");
	}

	/**
	 * Return the date with a year interval and month offset.
	 *
	 * Example:
	 * 		TYPE_DAY_OF_ALTERNATING_MONTHS: repeat every {number day in month} {working|usual} day of {calendar month} of every year
	 * 			#Repeat every the second working day of May of every year#
	 * 		TYPE_WEEKDAY_OF_CERTAIN_MONTH: repeat every {number} {weekday} of {calendar month} of every year
	 * 			#Repeat every the last of monday of April of every year#
	 *
	 * @return Date
	 */
	private function calculateAlternatingAnnual()
	{
		$params = $this->params;

		if ($this->type === self::TYPE_WEEKDAY_OF_CERTAIN_MONTH)
		{
			$monthType = Month::TYPE_WEEKDAY_OF_ALTERNATING_MONTHS;
		}
		elseif ($this->type === self::TYPE_DAY_OF_CERTAIN_MONTH)
		{
			$monthType = Month::TYPE_DAY_OF_ALTERNATING_MONTHS;
		}
		else
		{
			return $this->startDate;
		}

		$params['TYPE'] = $monthType;
		$month = (int)$params['INTERVAL_MONTH'];
		$params['INTERVAL_MONTH'] = (int)$params['INTERVAL_MONTH'] < 12 ? (int)$params['INTERVAL_MONTH'] : 12;

		$yearValue = (int)$this->startDate->format("Y");
		if ($month < (int)$this->startDate->format("n"))
		{
			$yearValue++;
		}

		$date = mktime(0, 0, 0, 12, 1, $yearValue - 1);
		$date = Date::createFromTimestamp($date);

		$month = new Month($params);
		$month->setInterval($params['INTERVAL_MONTH']);
		$month->setStartDate($date);
		$month->setType($monthType);
		$resultDate = $month->calculate();
		if ($this->startDate->getTimestamp() > $resultDate->getTimestamp())
		{
			$month->setStartDate($date->add("+1 year"));
			$resultDate = $month->calculate();
		}

		return $resultDate;
	}

	/**
	 * @return Date
	 */
	public function calculate()
	{
		if (empty($this->type))
		{
			return $this->startDate;
		}

		if ($this->type === self::TYPE_ALTERNATING_YEAR)
		{
			return $this->calculateAlternatingYears();
		}
		else
		{
			return $this->calculateAlternatingAnnual();
		}
	}
}