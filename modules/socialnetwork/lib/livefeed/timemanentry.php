<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class TimemanEntry extends Provider
{
	const PROVIDER_ID = 'TIMEMAN_ENTRY';
	const CONTENT_TYPE_ID = 'TIMEMAN_ENTRY';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('timeman_entry');
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
		$timemanEntryId = $this->entityId;

		if (
			$timemanEntryId > 0
			&& Loader::includeModule('timeman')
		)
		{
			$res = \CTimeManEntry::getById(intval($timemanEntryId));
			if ($timemanEntry = $res->fetch())
			{
				$this->setSourceFields($timemanEntry);
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
		$pathToTimemanEntry = '';
		if (
			($timemanEntry = $this->getSourceFields())
			&& !empty($timemanEntry)
		)
		{
			$pathToTimemanEntry = Option::get("timeman", "TIMEMAN_REPORT_PATH", "/timeman/timeman.php");
		}

		return $pathToTimemanEntry;
	}
}