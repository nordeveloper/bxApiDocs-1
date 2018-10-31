<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage imopenlines
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Imopenlines\Widget;

class Auth
{
	const AUTH_TYPE = 'livechat';

	const AUTH_CODE_REGISTER = 'register';
	const AUTH_CODE_GUEST = 'guest';

	const METHODS_WITHOUT_AUTH = [
		'imopenlines.widget.user.register',
		'imopenlines.widget.user.get',
		'imopenlines.widget.config.get',
		'server.time',
		'pull.config.get',
	];

	const METHODS_WITH_AUTH = [
		// imopenlines
		'imopenlines.widget.dialog.get',
		'imopenlines.widget.user.get',
		'imopenlines.widget.user.consent.apply',
		'imopenlines.widget.user.vote',
		// pull
		'server.time',
		'pull.config.get',
		'pull.watch.extend',
		// im
		'im.counters.get',
		'im.message.add',
		'im.message.update',
		'im.message.delete',
		'im.message.like',
		'im.chat.sendtyping',
		'im.dialog.messages.get',
		'im.disk.folder.get',
		'im.disk.file.commit',
		// disk
		'disk.folder.uploadfile', // @documentation https://dev.1c-bitrix.ru/rest_help/disk/folder/disk_folder_uploadfile.php
	];

	protected static $authQueryParams = [
		'livechat_auth_id',
	];

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		$authCode = null;
		foreach(static::$authQueryParams as $key)
		{
			if(array_key_exists($key, $query))
			{
				$authCode = $query[$key];
				break;
			}
		}

		if($authCode === null)
		{
			return null;
		}

		global $USER;

		if ($authCode == self::AUTH_CODE_GUEST)
		{
			if (self::checkQueryMethod(self::METHODS_WITHOUT_AUTH))
			{
				if ($USER->IsAuthorized())
				{
					if ($USER->GetParam('EXTERNAL_AUTH_ID') == User::EXTERNAL_AUTH_ID && substr($USER->GetParam('XML_ID'), 0, strlen(self::AUTH_TYPE)) == self::AUTH_TYPE)
					{
						$res = array(
							'error' => 'LIVECHAT_AUTH_WIDGET_USER',
							'error_description' => 'Livechat: you are authorized with a different user',
							'additional' => array('hash' => substr($USER->GetParam('XML_ID'), strlen(self::AUTH_TYPE)+1))
						);
						return false;
					}
					else
					{
						$res = array(
							'error' => 'LIVECHAT_AUTH_PORTAL_USER',
							'error_description' => 'Livechat: you are authorized with a portal user',
							'additional' => array()
						);
						return false;
					}
				}
				else
				{
					$res = self::getSuccessfulResult();
					return true;
				}
			}
			else
			{
				$res = array(
					'error' => 'LIVECHAT_AUTH_METHOD_ERROR',
					'error_description' => 'Livechat: you don\'t have access to use this method [1]',
					'additional' => array()
				);
				return false;
			}
		}
		else if (
			!preg_match("/^[a-fA-F0-9]{32}$/i", $authCode)
			|| $_SESSION['LIVECHAT']['AUTH_ERROR'] > 3
		)
		{
			$res = array(
				'error' => 'LIVECHAT_AUTH_FAILED',
				'error_description' => 'LiveChat: user auth failed',
				'additional' => array()
			);

			return false;
		}

		if (!self::checkQueryMethod(array_merge(self::METHODS_WITH_AUTH, self::METHODS_WITHOUT_AUTH)))
		{
			$res = array(
				'error' => 'LIVECHAT_AUTH_METHOD_ERROR',
				'error_description' => 'Livechat: you don\'t have access to use this method [2]',
				'additional' => array()
			);
			return false;
		}

		$xmlId = self::AUTH_TYPE."|".$authCode;

		if ($USER->IsAuthorized())
		{
			if ($USER->GetParam('EXTERNAL_AUTH_ID') == User::EXTERNAL_AUTH_ID)
			{
				if ($USER->GetParam('XML_ID') == $xmlId)
				{
					$res = self::getSuccessfulResult();

					\CUser::SetLastActivityDate($USER->GetID(), true);

					return true;
				}
				else
				{
					$res = array(
						'error' => 'LIVECHAT_AUTH_WIDGET_USER',
						'error_description' => 'Livechat: you are authorized with a different user',
						'additional' => array('hash' => substr($USER->GetParam('XML_ID'), strlen(self::AUTH_TYPE)+1))
					);
					return false;
				}
			}
			else
			{
				$res = array(
					'error' => 'LIVECHAT_AUTH_PORTAL_USER',
					'error_description' => 'Livechat: you are authorized with a portal user',
					'additional' => array()
				);
				return false;
			}
		}

		$userData = \Bitrix\Main\UserTable::getList([
			'select' => ['ID', 'EXTERNAL_AUTH_ID'],
			'filter' => ['XML_ID' => $xmlId]
		])->fetch();

		if($userData && $userData['EXTERNAL_AUTH_ID'] == User::EXTERNAL_AUTH_ID)
		{
			$USER->Authorize($userData['ID'], false, false, 'public');

			$res = self::getSuccessfulResult();

			\CUser::SetLastActivityDate($USER->GetID(), true);

			return true;
		}

		$res = array(
			'error' => 'LIVECHAT_AUTH_FAILED',
			'error_description' => 'LiveChat: user auth failed',
			'additional' => array()
		);

		$_SESSION['LIVECHAT']['AUTH_ERROR'] += 1;

		return false;
	}

	private static function getSuccessfulResult()
	{
		global $USER;

		return [
			'user_id' => $USER->GetID(),
			'scope' => implode(',', \CRestUtil::getScopeList()),
			'parameters_clear' => static::$authQueryParams,
			'auth_type' => static::AUTH_TYPE,
		];
	}

	private static function checkQueryMethod($whiteListMethods)
	{
		if (\CRestServer::instance()->getMethod() == 'batch')
		{
			$result = false;
			foreach (\CRestServer::instance()->getQuery()['cmd'] as $key => $method)
			{
				$method = substr($method, 0, strrpos($method, '?'));
				$result = in_array(strtolower($method), $whiteListMethods);
				if (!$result)
				{
					break;
				}
			}
		}
		else
		{
			$result = in_array(\CRestServer::instance()->getMethod(), $whiteListMethods);
		}

		return $result;
	}
}