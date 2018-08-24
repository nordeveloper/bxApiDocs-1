<?php

namespace Bitrix\Mobile;

use Bitrix\Main\Localization\Loc;

if (!\Bitrix\Main\Loader::includeModule('rest'))
{
	return;
}

Loc::loadMessages(__FILE__);

class Rest extends \IRestService
{
	public static function onRestServiceBuildDescription()
	{
		return [
			'mobile' => [
				'mobile.browser.const.get' => ['callback' => [__CLASS__, 'browserConstGet'], 'options' => ['private' => true]],
				'mobile.user.group.get' => ['callback' => [__CLASS__, 'getUserGroup'], 'options' => ['private' => true]],
			]
		];
	}

	public static function browserConstGet($params, $n, \CRestServer $server)
	{
		if ($server->getAuthType() != \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Get access to browser const available only for session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$consts = \CJSCore::GetCoreMessages();
		$consts["LIMIT_ONLINE"] = \CUser::GetSecondsForLimitOnline();
		$consts["WEEK_START"] = \CSite::GetWeekStart();
		$consts["AMPM_MODE"] = \IsAmPmMode(true);
		$consts["phpPostMaxSize"] = \CUtil::Unformat(ini_get("post_max_size"));
		$consts["phpUploadMaxFilesize"] = \CUtil::Unformat(ini_get("upload_max_filesize"));
		$consts["bxQuota"] = \CDiskQuota::getInstance()->GetDiskQuota();
		$consts["can_perform_calls"] = \Bitrix\Main\Loader::includeModule('voximplant') && \Bitrix\Voximplant\Security\Helper::canCurrentUserPerformCalls() ? 'Y' : 'N';

		return $consts;
	}

	public static function getUserGroup($params, $navOffset, \CRestServer $server)
	{
		if ($server->getAuthType() != \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Get access to browser const available only for session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}


		return [];
	}
}
