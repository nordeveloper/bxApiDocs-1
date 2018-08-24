<?php
namespace Bitrix\Timeman;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Type\Date;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;

Loader::includeModule('rest');

class Rest extends \IRestService
{
	const SCOPE = 'timeman';

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE => array(
				'timeman.settings' => array(
					'callback' => array(__CLASS__, 'getSettings')
				),
				'timeman.status' => array(
					'callback' => array(__CLASS__, 'getStatus')
				),
				'timeman.open' => array(
					'callback' => array(__CLASS__, 'openDay')
				),
				'timeman.close' => array(
					'callback' => array(__CLASS__, 'closeDay')
				),
				'timeman.pause' => array(
					'callback' => array(__CLASS__, 'pauseDay')
				),
			)
		);
	}

	public static function getSettings($query, $n, \CRestServer $server)
	{
		global $USER;

		$query = static::prepareQuery($query);
		$tmUser = static::getUserInstance($query);

		$currentSettings = $tmUser->getSettings();

		// temporary fix timeman bug
		if(strpos($currentSettings['UF_TM_ALLOWED_DELTA'], ':') !== false)
		{
			$currentSettings['UF_TM_ALLOWED_DELTA'] = \CTimeMan::MakeShortTS($currentSettings['UF_TM_ALLOWED_DELTA']);
		}

		$result = array(
			'UF_TIMEMAN' => $currentSettings['UF_TIMEMAN'],
			'UF_TM_FREE' => $currentSettings['UF_TM_FREE'],
			'UF_TM_MAX_START' => static::formatTime($currentSettings['UF_TM_MAX_START']),
			'UF_TM_MIN_FINISH' => static::formatTime($currentSettings['UF_TM_MIN_FINISH']),
			'UF_TM_MIN_DURATION' => static::formatTime($currentSettings['UF_TM_MIN_DURATION']),
			'UF_TM_ALLOWED_DELTA' => static::formatTime($currentSettings['UF_TM_ALLOWED_DELTA']),
		);

		if($USER->GetID() == $tmUser->GetID())
		{
			$result['ADMIN'] = \CTimeMan::IsAdmin();
		}

		return $result;
	}

	public static function getStatus($query, $n, \CRestServer $server)
	{
		$query = static::prepareQuery($query);
		$tmUser = static::getUserInstance($query);

		$currentInfo = $tmUser->getCurrentInfo();

		$result = array(
			'STATUS' => $tmUser->State(),
		);

		$userOffset = $tmUser->getDayStartOffset($currentInfo) + date('Z');
		static::setCurrentTimezoneOffset($userOffset);

		if($currentInfo['DATE_START'])
		{

			$currentInfo['DATE_START'] = ConvertTimeStamp(MakeTimeStamp($currentInfo['DATE_START'], FORMAT_DATETIME), 'SHORT');

			if($currentInfo['DATE_FINISH'])
			{
				$currentInfo['DATE_FINISH'] = ConvertTimeStamp(MakeTimeStamp($currentInfo['DATE_FINISH'], FORMAT_DATETIME), 'SHORT');
			}

			$result['TIME_START'] = static::convertTimeToISO(intval($currentInfo['TIME_START']), $currentInfo['DATE_START'], $userOffset);
			$result['TIME_FINISH'] = $currentInfo['TIME_FINISH'] > 0 ? static::convertTimeToISO(intval($currentInfo['TIME_FINISH']), $currentInfo['DATE_FINISH'], $userOffset) : null;
			$result['DURATION'] = static::formatTime(intval($currentInfo['DURATION']));
			$result['TIME_LEAKS'] = static::formatTime(intval($currentInfo['TIME_LEAKS']));
			$result['ACTIVE'] = $currentInfo['ACTIVE'] == 'Y';
			$result['IP_OPEN'] = $currentInfo['IP_OPEN'];
			$result['IP_CLOSE'] = $currentInfo['IP_CLOSE'];
			$result['LAT_OPEN'] = doubleval($currentInfo['LAT_OPEN']);
			$result['LON_OPEN'] = doubleval($currentInfo['LON_OPEN']);
			$result['LAT_CLOSE'] = doubleval($currentInfo['LAT_CLOSE']);
			$result['LON_CLOSE'] = doubleval($currentInfo['LON_CLOSE']);
			$result['TZ_OFFSET'] = $userOffset;
		}

		if($result['STATUS'] == 'EXPIRED')
		{
			$result['TIME_FINISH_DEFAULT'] = static::convertTimeToISO($tmUser->getExpiredRecommendedDate(), $currentInfo['DATE_START'], $userOffset);
		}

		return $result;
	}

	public static function pauseDay($query, $n, \CRestServer $server)
	{
		$query = static::prepareQuery($query);
		$tmUser = static::getUserInstance($query);

		$currentInfo = $tmUser->getCurrentInfo();

		$userOffset = $tmUser->getDayStartOffset($currentInfo) + date('Z');
		static::setCurrentTimezoneOffset($userOffset);

		$tmUser->PauseDay();

		return static::getStatus($query, $n, $server);
	}

	public static function openDay($query, $n, \CRestServer $server)
	{
		$query = static::prepareQuery($query);
		$tmUser = static::getUserInstance($query);

		$openAction = $tmUser->OpenAction();

		$result = false;
		if($openAction)
		{
			if($openAction === 'OPEN')
			{
				if(isset($query['TIME']))
				{
					$timeInfo = static::convertTimeFromISO($query['TIME']);
					static::setCurrentTimezoneOffset($timeInfo['OFFSET']);

					if(!static::checkDate($timeInfo, ConvertTimeStamp()))
					{
						throw new DateTimeException('Day open date should correspond to the current date', DateTimeException::ERROR_WRONG_DATETIME);
					}

					$result = $tmUser->OpenDay($timeInfo['TIME'], $query['REPORT']);
				}
				else
				{
					$result = $tmUser->OpenDay();
				}

				if($result !== false)
				{
					static::setDayGeoPosition($result['ID'], $query, 'open');
				}
			}
			elseif($openAction === 'REOPEN')
			{
				if(isset($query['TIME']))
				{
					throw new ArgumentException('Unable to set time, work day is paused', 'TIME');
				}

				$currentInfo = $tmUser->getCurrentInfo();
				$userOffset = $tmUser->getDayStartOffset($currentInfo) + date('Z');

				static::setCurrentTimezoneOffset($userOffset);

				$result = $tmUser->ReopenDay();
			}
		}

		if(!$result)
		{
			global $APPLICATION;
			$ex = $APPLICATION->GetException();
			if($ex)
			{
				throw new RestException($ex->GetString(), $ex->GetID());
			}
		}

		return static::getStatus($query, $n, $server);
	}

	public static function closeDay($query, $n, \CRestServer $server)
	{
		$query = static::prepareQuery($query);
		$tmUser = static::getUserInstance($query);

		if(isset($query['TIME']))
		{
			$currentInfo = $tmUser->getCurrentInfo();
			$userOffset = $tmUser->getDayStartOffset($currentInfo) + date('Z');

			static::setCurrentTimezoneOffset($userOffset);

			$timeInfo = static::convertTimeFromISO($query['TIME']);

			static::correctTimeOffset($userOffset, $timeInfo);

			if(!static::checkDate($timeInfo, ConvertTimeStamp(MakeTimeStamp($currentInfo['DATE_START'], FORMAT_DATETIME))))
			{
				throw new DateTimeException('Day close date should correspond to the day open date', DateTimeException::ERROR_WRONG_DATETIME);
			}

			$result = $tmUser->CloseDay($timeInfo['TIME'], trim($query['REPORT']));
		}
		else
		{
			$result = $tmUser->CloseDay();
		}

		if(!$result)
		{
			global $APPLICATION;
			$ex = $APPLICATION->GetException();
			if($ex)
			{
				throw new RestException($ex->GetString(), $ex->GetID());
			}
		}
		else
		{
			static::setDayGeoPosition($result['ID'], $query, 'close');

			$currentInfo = $tmUser->GetCurrentInfo();

			$reportData = $tmUser->SetReport('', 0, $currentInfo['ID']);

			$dailyReportFields = array(
				'ENTRY_ID' => $currentInfo['ID'],
				'REPORT_DATE' => $currentInfo['DATE_START'],
				'ACTIVE' => $currentInfo['ACTIVE'],
				'REPORT' => $reportData['REPORT'],
			);

			\CTimeManReportDaily::Add($dailyReportFields);
		}

		return static::getStatus($query, $n, $server);
	}

	protected static function prepareQuery(array $query)
	{
		return array_change_key_case($query, CASE_UPPER);
	}

	/**
	 * @param array $query
	 *
	 * @return \CTimeManUser
	 * @throws AccessException
	 */
	protected static function getUserInstance(array $query)
	{
		global $USER;

		if(array_key_exists('USER_ID', $query) && $query['USER_ID'] != $USER->getId())
		{
			if(!\CTimeMan::isAdmin())
			{
				throw new AccessException('User does not have access to managing other users work time');
			}

			if(!static::checkUser($query['USER_ID']))
			{
				throw new ObjectNotFoundException('User not found');
			}

			return new \CTimeManUser($query['USER_ID']);
		}
		else
		{
			return \CTimeManUser::instance();
		}
	}

	protected static function checkUser($userId)
	{
		$dbRes = \CUser::getById($userId);
		return is_array($dbRes->fetch());
	}

	protected static function correctTimeOffset($offsetTo, &$timeInfo)
	{
		$timeInfo['TIME'] = $timeInfo['TIME'] - $timeInfo['OFFSET'] + $offsetTo;

		if($timeInfo['TIME'] < 0)
		{
			$timeInfo['TIME'] += 86400;

			$dt = new Date($timeInfo['DATE']);
			$dt->add('-1 day');
			$timeInfo['DATE'] = $dt->toString();
		}

		if($timeInfo['TIME'] >= 86400)
		{
			$timeInfo['TIME'] -= 86400;

			$dt = new Date($timeInfo['DATE']);
			$dt->add('1 day');
			$timeInfo['DATE'] = $dt->toString();
		}

		$timeInfo['OFFSET'] = $offsetTo;
	}

	/**
	 * Returns full datetime in ISO format (Y-m-dTH:i:sP) in user's timezone
	 *
	 * @param int $ts Short timestamp in timeman format (num of seconds from the day start)
	 * @param string $date Date in site format
	 * @param int $userOffset User's timezone offset
	 *
	 * @return string
	 */
	protected static function convertTimeToISO($ts, $date, $userOffset)
	{
		return static::formatDateToISO($date, $userOffset).'T'.static::formatTimeToISO($ts, $userOffset);
	}

	/**
	 * Returns date in ISO format in user's timezone
	 *
	 * @param string $date Date in site format
	 * @param int $userOffset User offset
	 *
	 * @return false|string
	 */
	protected static function formatDateToISO($date, $userOffset)
	{
		return date('Y-m-d', MakeTimeStamp($date)-date('Z')+$userOffset);
	}

	/**
	 * Returns time in ISO format with offset (H:i:sP) in user's timezone
	 *
	 * @param int $ts Short timestamp in timeman format (num of seconds from the day start)
	 * @param int $offset User's timezone offset
	 *
	 * @return string
	 */
	protected static function formatTimeToISO($ts, $offset)
	{
		$offsetSign = $offset >= 0 ? '+' : '-';

		return static::formatTime($ts)
			.$offsetSign
			.str_pad(abs(intval($offset / 3600)), 2, '0', STR_PAD_LEFT).':'.str_pad(abs(intval($offset % 3600 / 60)), 2, '0', STR_PAD_LEFT);
	}

	protected static function formatTime($ts)
	{
		return str_pad(intval($ts / 3600), 2, '0', STR_PAD_LEFT)
			.':'.str_pad(intval(($ts % 3600) / 60), 2, '0', STR_PAD_LEFT)
			.':'.str_pad(intval($ts % 60), 2, '0', STR_PAD_LEFT);
	}

	protected static function convertTimeFromISO($isoTime)
	{
		global $DB;

		$date = \DateTime::createFromFormat(\DateTime::ATOM, $isoTime);
		if(!$date)
		{
			throw new DateTimeException('Wrong datetime format', DateTimeException::ERROR_WRONG_DATETIME_FORMAT);
		}

		return array(
			'DATE' => $date->format($DB->DateFormatToPHP(FORMAT_DATE)),
			'TIME' => 3600*$date->format('G') + 60 * $date->format('i') + intval($date->format('s')),
			'OFFSET' => $date->getOffset(),
		);
	}

	protected static function setCurrentTimezoneOffset($offset)
	{
		\CTimeZone::SetCookieValue(intval(-$offset/60));
	}

	protected static function setDayGeoPosition($entryId, $query, $action = 'open')
	{
		$updateFields = array(
			'LAT_'.ToUpper($action) => isset($query['LAT']) ? doubleval($query['LAT']) : '',
			'LON_'.ToUpper($action) => isset($query['LON']) ? doubleval($query['LON']) : '',
		);

		\CTimeManEntry::Update($entryId, $updateFields);
		static::getUserInstance($query)->GetCurrentInfo(true);
	}

	protected static function checkDate(array $timeInfo, $compareDate)
	{
		return $timeInfo['DATE'] === $compareDate;
	}
}
