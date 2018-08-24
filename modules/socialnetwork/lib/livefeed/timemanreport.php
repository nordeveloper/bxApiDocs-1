<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class TimemanReport extends Provider
{
	const PROVIDER_ID = 'TIMEMAN_REPORT';
	const CONTENT_TYPE_ID = 'TIMEMAN_REPORT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('report');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		return $provider;
	}

	public function initSourceFields()
	{
		$timemanReportId = $this->entityId;

		if (
			$timemanReportId > 0
			&& Loader::includeModule('timeman')
		)
		{
			$res = \CTimeManReport::getById(intval($timemanReportId));
			if ($timemanReport = $res->fetch())
			{
				$this->setSourceFields($timemanReport);
//				$this->setSourceDescription();
//				$this->setSourceTitle();
			}
		}
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
	{
		$pathToTimemanReport = '';
		if (
			($timemanReport = $this->getSourceFields())
			&& !empty($timemanReport)
		)
		{
			$pathToTimemanReport = Option::get("timeman", "WORK_REPORT_PATH", "/timeman/work_report.php");
		}

		return $pathToTimemanReport;
	}
}