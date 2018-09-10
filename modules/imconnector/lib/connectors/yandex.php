<?php
namespace Bitrix\ImConnector\Connectors;

use \Bitrix\Im\User,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Web\Uri,
	\Bitrix\Main\Config\Option;
use \Bitrix\ImConnector\Library;

/**
 * Class Yandex
 * @package Bitrix\ImConnector\Connectors
 */
class Yandex
{
	/**
	 * @param $value
	 * @param $connector
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function sendMessageProcessing($value, $connector)
	{
		if($connector == Library::ID_YANDEX_CONNECTOR && !empty($value['chat']['id']) && !empty($value['message']['user_id']) && Loader::includeModule('im'))
		{
			//$user = User::getInstance($value['message']['user_id'])->getFields();
			$user = User::getInstance($value['message']['user_id']);

			if($user->getAvatarId() && $user->getAvatar())
			{
				if(!Library::isEmpty($user->getFullName(false)))
					$value['user']['name'] = $user->getFullName(false);

				$uri = new Uri($user->getAvatar());
				if($uri->getHost())
					$value['user']['picture'] = array('url' => $user->getAvatar());
				else
					$value['user']['picture'] = array('url' => Option::get(Library::MODULE_ID, "uri_client") . $user->getAvatar());
			}
		}

		return $value;
	}
}