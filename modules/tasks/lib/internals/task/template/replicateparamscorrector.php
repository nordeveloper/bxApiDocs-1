<?php
/**
 * Created by PhpStorm.
 * User: varchak
 * Date: 23.03.2018
 * Time: 17:44
 */

namespace Bitrix\Tasks\Internals\Task\Template;

use \Bitrix\Tasks\UI;
use \Bitrix\Tasks\Util\User;

/**
 * Corrects replicate parameters
 *
 * Class ReplicateParamsCorrector
 * @package Bitrix\Tasks\Internals\Task\Template
 */
final class ReplicateParamsCorrector
{
	/**
	 * Corrects replicate parameters (time, start date, end date) if replicate == 'Y'
	 *
	 * @param $templateData
	 * @return mixed replicateParams
	 */
	public static function correctReplicateParamsByTemplateData($templateData)
	{
		$replicateParams = $templateData['REPLICATE_PARAMS'];

		if ($templateData['REPLICATE'] == 'Y')
		{
			$userTime = $replicateParams['TIME'];
			$userOffset = User::getTimeZoneOffset($templateData['CREATED_BY']);
			$userStartDate = MakeTimeStamp($replicateParams['START_DATE']);
			$userEndDate = MakeTimeStamp($replicateParams['END_DATE']);

			$replicateParams['TIME'] = static::correctTime($userTime, $userOffset);
			$replicateParams['START_DATE'] = static::correctStartDate($userTime, $userStartDate, $userOffset);
			$replicateParams['END_DATE'] = static::correctEndDate($userTime, $userEndDate, $userOffset);
		}

		return $replicateParams;
	}

	/**
	 * Corrects time based on $resultTimeType
	 *
	 * @param $time
	 * @param $offset
	 * @param string $resultTimeType
	 * @return false|string
	 */
	public static function correctTime($time, $offset, $resultTimeType = 'server')
	{
		switch ($resultTimeType)
		{
			case 'server':
				$result = static::getServerTime($time, $offset);
				break;

			case 'user':
				$result = static::getUserTime($time, $offset);
				break;

			default:
				$result = static::getServerTime($time, $offset);
				break;
		}

		return $result;
	}

	/**
	 * Corrects start date based on $resultStartDateType
	 *
	 * @param $time
	 * @param $startDate
	 * @param $offset
	 * @param string $resultStartDateType
	 * @return false|string
	 */
	public static function correctStartDate($time, $startDate, $offset, $resultStartDateType = 'server')
	{
		if (!$startDate)
		{
			return '';
		}

		switch ($resultStartDateType)
		{
			case 'server':
				$result = static::getServerStartDate($time, $startDate, $offset);
				break;

			case 'user':
				$result = static::getUserStartDate($time, $startDate, $offset);
				break;

			default:
				$result = static::getServerStartDate($time, $startDate, $offset);
				break;
		}

		return $result;
	}

	/**
	 * Correct end date based on $resultEndDateType
	 *
	 * @param $time
	 * @param $endDate
	 * @param $offset
	 * @param string $resultEndDateType
	 * @return false|string
	 */
	public static function correctEndDate($time, $endDate, $offset, $resultEndDateType = 'server')
	{
		if (!$endDate)
		{
			return '';
		}

		switch ($resultEndDateType)
		{
			case 'server':
				$result = static::getServerEndDate($time, $endDate, $offset);
				break;

			case 'user':
				$result = static::getUserEndDate($time, $endDate, $offset);
				break;

			default:
				$result = static::getServerEndDate($time, $endDate, $offset);
				break;
		}

		return $result;
	}

	/**
	 * Converts user time to server time
	 *
	 * @param $userTime
	 * @param $userOffset
	 * @return false|string
	 */
	private static function getServerTime($userTime, $userOffset)
	{
		return date('H:i', strtotime($userTime) - $userOffset);
	}

	/**
	 * Converts server time to user time
	 *
	 * @param $serverTime
	 * @param $currentTimeZoneOffset
	 * @return false|string
	 */
	private static function getUserTime($serverTime, $currentTimeZoneOffset)
	{
		return date('H:i', strtotime($serverTime) + $currentTimeZoneOffset);
	}

	/**
	 * Converts user start date to server start date
	 *
	 * @param $userTime
	 * @param $userStartDate
	 * @param $userOffset
	 * @return false|string
	 */
	private static function getServerStartDate($userTime, $userStartDate, $userOffset)
	{
		$userTime = UI::parseTimeAmount($userTime, 'HH:MI');
		$serverStartDateTime = $userStartDate + $userTime - $userOffset;
		$serverStartDate = date('d.m.Y 00:00:00', $serverStartDateTime);

		return ($serverStartDate? $serverStartDate : '');
	}

	/**
	 * Convert server start date to user start date
	 *
	 * @param $serverTime
	 * @param $serverStartDate
	 * @param $currentTimeZoneOffset
	 * @return false|string
	 */
	private static function getUserStartDate($serverTime, $serverStartDate, $currentTimeZoneOffset)
	{
		$serverTime = UI::parseTimeAmount($serverTime, 'HH:MI');
		$userStartDateTime = $serverStartDate + $serverTime + $currentTimeZoneOffset;
		$userStartDate = date('d.m.Y 00:00:00', $userStartDateTime);

		return ($userStartDate? $userStartDate : '');
	}

	/**
	 * Convert user end date to server end date
	 *
	 * @param $userTime
	 * @param $userEndDate
	 * @param $userOffset
	 * @return false|string
	 */
	private static function getServerEndDate($userTime, $userEndDate, $userOffset)
	{
		$userTime = UI::parseTimeAmount($userTime, 'HH:MI');
		$serverEndDateTime = $userEndDate + $userTime - $userOffset;
		$serverEndDate = date('d.m.Y 00:00:00', $serverEndDateTime);

		return ($serverEndDate? $serverEndDate : '');
	}

	/**
	 * Convert server end date to user end date
	 *
	 * @param $serverTime
	 * @param $serverEndDate
	 * @param $currentTimeZoneOffset
	 * @return false|string
	 */
	private static function getUserEndDate($serverTime, $serverEndDate, $currentTimeZoneOffset)
	{
		$serverTime = UI::parseTimeAmount($serverTime, 'HH:MI');
		$userEndDateTime = $serverEndDate + $serverTime + $currentTimeZoneOffset;
		$userEndDate = date('d.m.Y 00:00:00', $userEndDateTime);

		return ($userEndDate? $userEndDate : '');
	}
}