<?php
namespace Bitrix\Crm\Recurring\DateType;

use Bitrix\Main;
use Bitrix\Main\Type\Date;

class Week
{
	const TYPE_ALTERNATING_WEEKDAYS = 1;
	const TYPE_A_FEW_WEEKS_BEFORE = 2;
	const TYPE_A_FEW_WEEKS_AFTER = 3;
	/**
	 * @param array $params
	 * @param Date $date
	 *
	 * @return Date
	 */
	public static function calculateDate(array $params, Date $date)
	{
		$dataText = "";

		if ((int)$params['TYPE'] === self::TYPE_ALTERNATING_WEEKDAYS)
		{
			$days = is_array($params["WEEKDAYS"]) ? $params["WEEKDAYS"] : array(1);
			sort($days);
			$currentDay = (int)($date->format("N"));
			$nextDay = null;

			foreach ($days as $day)
			{
				if ($day >= $currentDay)
				{
					$nextDay = $day;
					break;
				}
			}

			if ($nextDay)
			{
				$dataText = "+" . ($nextDay - $currentDay) . " days";
				if ((int)$params["INTERVAL_WEEK"] > 1)
				{
					$dataText = " +" . (int)$params["INTERVAL_WEEK"] - 1 . " weeks ".$dataText;
				}
			}
			else
			{
				$dataText = " +" . (int)$params["INTERVAL_WEEK"] . " weeks +" . ($days[0] - $currentDay) . " days";
			}
		}
		elseif ((int)$params['TYPE'] === self::TYPE_A_FEW_WEEKS_BEFORE)
		{
			$dataText = " -" . (int)$params["INTERVAL_WEEK"] . " weeks";
		}
		elseif ((int)$params['TYPE'] === self::TYPE_A_FEW_WEEKS_AFTER)
		{
			$dataText = " +" . (int)$params["INTERVAL_WEEK"] . " weeks";
		}

		return $date->add($dataText);
	}
}