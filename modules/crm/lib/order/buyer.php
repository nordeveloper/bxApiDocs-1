<?php

namespace Bitrix\Crm\Order;

class Buyer
{
	const AUTH_ID = 'shop';

	/**
	 * Event handler for buyer authorization when api checks external users.
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

	/**
	 * Event handler for buyer creation when api checks user fields.
	 * @see \CAllUser::CheckFields
	 *
	 * @param $fields
	 * @return bool
	 */
	public static function onBeforeUserAddHandler($fields)
	{
		if (isset($fields['EXTERNAL_AUTH_ID']) && $fields['EXTERNAL_AUTH_ID'] === self::AUTH_ID)
		{
			$errorMsg = \CUser::CheckInternalFields($fields);

			if ($errorMsg !== '')
			{
				global $APPLICATION;

				$APPLICATION->ThrowException($errorMsg);

				return false;
			}
		}

		return true;
	}

	/**
	 * Event handler for buyer editing when api checks user fields.
	 * @see \CAllUser::CheckFields
	 *
	 * @param $fields
	 * @return bool
	 */
	public static function onBeforeUserUpdateHandler($fields)
	{
		if (isset($fields['EXTERNAL_AUTH_ID']) && $fields['EXTERNAL_AUTH_ID'] === self::AUTH_ID)
		{
			$errorMsg = \CUser::CheckInternalFields($fields, $fields['ID']);

			if ($errorMsg !== '')
			{
				global $APPLICATION;

				$APPLICATION->ThrowException($errorMsg);

				return false;
			}
		}

		return true;
	}
}