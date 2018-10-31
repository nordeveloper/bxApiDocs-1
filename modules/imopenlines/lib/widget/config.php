<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage imopenlines
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Imopenlines\Widget;

use Bitrix\ImOpenLines\Error;
use Bitrix\Main\Localization\Loc;

class Config
{
	static private $error = null;

	public static function getByCode($code)
	{
		self::clearError();

		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			self::setError(__METHOD__, 'IM_NOT_FOUND', Loc::getMessage('IMOL_WIDGET_CONFIG_IM_NOT_FOUND'));
			return false;
		}
		if (!\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			self::setError(__METHOD__, 'IMCONNECTOR_NOT_FOUND', Loc::getMessage('IMOL_WIDGET_CONFIG_IMCONNECTOR_NOT_FOUND'));
			return false;
		}

		$result = \Bitrix\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => ['CONFIG_ID'],
			'filter' => ['=URL_CODE' => $code]
		))->fetch();
		if (!$result)
		{
			self::setError(__METHOD__, 'CONFIG_ERROR', Loc::getMessage('IMOL_WIDGET_CONFIG_CONFIG_ERROR'));
			return false;
		}

		$configManager = new \Bitrix\ImOpenLines\Config();
		$config = $configManager->get($result['CONFIG_ID']);

		$queue = [];
		foreach ($config['QUEUE'] as $userId)
		{
			$user = \Bitrix\Im\User::getInstance($userId);
			$userArray = Array(
				'ID' => $user->getId(),
				'NAME' => $user->getName(false),
				'LAST_NAME' => $user->getLastName(false),
				'GENDER' => $user->getGender(),
				'AVATAR' => $user->getAvatar()
			);
			if (function_exists('customImopenlinesOperatorNames') && !$user->isExtranet()) // Temporary hack :(
			{
				$userArray = customImopenlinesOperatorNames($result['CONFIG_ID'], $userArray);
			}

			$queue[] = $userArray;
		}

		$connectors = Array();
		$activeConnectors = \Bitrix\ImConnector\Connector::infoConnectorsLine($result['CONFIG_ID']);

		$classMap = \Bitrix\ImConnector\Connector::getIconClassMap();
		foreach ($activeConnectors as $code => $params)
		{
			if ($code == 'livechat' || empty($params['url']))
				continue;

			$connectors[] = Array(
				'TITLE' => $params['name']? $params['name']:'',
				'CODE' => $code,
				'ICON' => $classMap[$code],
				'LINK' => $params['url_im']? $params['url_im']: $params['url'],
			);
		}

		return [
			'CONFIG_ID' => (int)$config['ID'],
			'CONFIG_NAME' => $config['LINE_NAME'],
			'VOTE_ENABLE' => $config['VOTE_MESSAGE'] === 'Y',
			'CONSENT_URL' => $config['AGREEMENT_ID'] && $config['AGREEMENT_MESSAGE'] == 'Y'? \Bitrix\ImOpenLines\Common::getAgreementLink($config['AGREEMENT_ID'], true): '',
			'OPERATORS' => $queue,
			'ONLINE' => $config['QUEUE_ONLINE'] === 'Y',
			'CONNECTORS' => $connectors,
		];
	}


	/**
	 * @return Error
	 */
	public static function getError()
	{
		if (is_null(static::$error))
		{
			self::clearError();
		}

		return static::$error;
	}

	/**
	 * @param $method
	 * @param $code
	 * @param $msg
	 * @param array $params
	 * @return bool
	 */
	private static function setError($method, $code, $msg, $params = Array())
	{
		static::$error = new Error($method, $code, $msg, $params);
		return true;
	}

	private static function clearError()
	{
		static::$error = new Error(null, '', '');
		return true;
	}
}
