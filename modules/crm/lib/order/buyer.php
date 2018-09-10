<?php

namespace Bitrix\Crm\Order;

class Buyer
{
	const AUTH_ID = 'shop';

	/**
	 * Event handler for buyer authorization while api checks external users.
	 * @see \CAllUser::Login
	 *
	 * @param $arParams
	 * @return int|null
	 */
	public static function onUserLoginExternalHandler(&$arParams)
	{
		if (isset($arParams['EXTERNAL_AUTH_ID']) && $arParams['EXTERNAL_AUTH_ID'] !== self::AUTH_ID)
		{
			return 0;
		}

		$loginParams = $arParams;
		$loginParams['EXTERNAL_AUTH_ID'] = self::AUTH_ID;

		$resultMessage = true;

		$userId = (int)\CUser::LoginInternal($loginParams, $resultMessage);

		if ($resultMessage !== true)
		{
			$arParams['RESULT_MESSAGE'] = $resultMessage;
		}

		return $userId;
	}
}