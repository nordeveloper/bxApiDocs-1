<?php

namespace Bitrix\Main\Mail;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Mail\Internal\SenderTable;

class Sender
{

	public static function add(array $fields)
	{
		if (empty($fields['OPTIONS']) || !is_array($fields['OPTIONS']))
		{
			$fields['OPTIONS'] = array();
		}

		if (empty($fields['IS_CONFIRMED']))
		{
			$fields['OPTIONS']['confirm_code'] = \Bitrix\Main\Security\Random::getStringByCharsets(5, '0123456789abcdefghjklmnpqrstuvwxyz');
			$fields['OPTIONS']['confirm_time'] = time();
		}

		$senderId = 0;
		$result = Internal\SenderTable::add($fields);
		if($result->isSuccess())
		{
			$senderId = $result->getId();
		}

		if (empty($fields['IS_CONFIRMED']))
		{
			$mailEventFields = array(
				'EMAIL_TO' => $fields['EMAIL'],
				'MESSAGE_SUBJECT' => getMessage('MAIN_MAIL_CONFIRM_MESSAGE_SUBJECT'),
				'CONFIRM_CODE' => strtoupper($fields['OPTIONS']['confirm_code']),
			);

			if (!empty($fields['OPTIONS']['smtp']))
			{
				$mailEventFields['DEFAULT_EMAIL_FROM'] = $fields['EMAIL'];

				\Bitrix\Main\EventManager::getInstance()->addEventHandlerCompatible(
					'main',
					'OnBeforeEventSend',
					function (&$eventFields, &$message, $context) use (&$fields)
					{
						$config = $fields['OPTIONS']['smtp'];
						$config = new Smtp\Config(array(
							'from' => $fields['EMAIL'],
							'host' => $config['server'],
							'port' => $config['port'],
							'login' => $config['login'],
							'password' => $config['password'],
						));

						$context->setSmtp($config);
					}
				);
			}

			\CEvent::sendImmediate('MAIN_MAIL_CONFIRM_CODE', SITE_ID, $mailEventFields);
		}
		else
		{
			if (isset($fields['OPTIONS']['__replaces']) && $fields['OPTIONS']['__replaces'] > 0)
			{
				Internal\SenderTable::delete(
					(int) $fields['OPTIONS']['__replaces']
				);
			}
		}

		return ['senderId' => $senderId];
	}

	public static function confirm($ids)
	{
		if (!empty($ids))
		{
			$res = Internal\SenderTable::getList(array(
				'filter' => array(
					'@ID' => (array) $ids,
				),
			));

			while ($item = $res->fetch())
			{
				Internal\SenderTable::update(
					(int) $item['ID'],
					array(
						'IS_CONFIRMED' => true,
					)
				);

				if (isset($item['OPTIONS']['__replaces']) && $item['OPTIONS']['__replaces'] > 0)
				{
					Internal\SenderTable::delete(
						(int) $item['OPTIONS']['__replaces']
					);
				}
			}
		}
	}

	public static function delete($ids)
	{
		if(!is_array($ids))
		{
			$ids = [$ids];
		}
		if(empty($ids))
		{
			return;
		}
		$smtpConfigs = [];

		$senders = SenderTable::getList([
			'order' => [
				'ID' => 'desc',
			],
			'filter' => [
				'=USER_ID' => CurrentUser::get()->getId(),
				'@ID' => $ids,
				'IS_CONFIRMED' => true]
			]
		)->fetchAll();
		foreach($senders as $sender)
		{
			if(!empty($sender['OPTIONS']['smtp']['server']) && empty($sender['OPTIONS']['smtp']['encrypted']) && !isset($smtpConfigs[$sender['EMAIL']]))
			{
				$smtpConfigs[$sender['EMAIL']] = $sender['OPTIONS']['smtp'];
			}
		}
		if(!empty($smtpConfigs))
		{
			$senders = SenderTable::getList([
				'order' => [
					'ID' => 'desc',
				],
				'filter' => [
					'@EMAIL' => array_keys($smtpConfigs),
					'!ID' => $ids
				]
			])->fetchAll();
			foreach($senders as $sender)
			{
				if(isset($smtpConfigs[$sender['EMAIL']]))
				{
					$options = $sender['OPTIONS'];
					$options['smtp'] = $smtpConfigs[$sender['EMAIL']];
					$result = SenderTable::update($sender['ID'], [
						'OPTIONS' => $options,
					]);
					if($result->isSuccess())
					{
						unset($smtpConfigs[$sender['EMAIL']]);
						static::clearCustomSmtpCache($sender['EMAIL']);
					}
					if(empty($smtpConfigs))
					{
						break;
					}
				}
			}
		}
		foreach ((array) $ids as $id)
		{
			Internal\SenderTable::delete(
				(int) $id
			);
		}
	}

	public static function clearCustomSmtpCache($email)
	{
		$cache = new \CPHPCache();
		$cache->clean($email, '/main/mail/smtp');
	}

	public static function getCustomSmtp($email)
	{
		static $smtp = array();

		if (!isset($smtp[$email]))
		{
			$config = false;

			$cache = new \CPHPCache();

			if ($cache->initCache(30*24*3600, $email, '/main/mail/smtp'))
			{
				$config = $cache->getVars();
			}
			else
			{
				$res = Internal\SenderTable::getList(array(
					'filter' => array(
						'IS_CONFIRMED' => true,
						'=EMAIL' => $email,
					),
					'order' => array(
						'ID' => 'DESC',
					),
				));
				while ($item = $res->fetch())
				{
					if (!empty($item['OPTIONS']['smtp']['server']) && empty($item['OPTIONS']['smtp']['encrypted']))
					{
						$config = $item['OPTIONS']['smtp'];
						break;
					}
				}

				$cache->startDataCache();
				$cache->endDataCache($config);
			}

			if ($config)
			{
				$config = new Smtp\Config(array(
					'from' => $email,
					'host' => $config['server'],
					'port' => $config['port'],
					'login' => $config['login'],
					'password' => $config['password'],
				));
			}

			$smtp[$email] = $config;
		}

		return $smtp[$email];
	}

	public static function applyCustomSmtp($event)
	{
		$headers = $event->getParameter('arguments')->additional_headers;
		$context = $event->getParameter('arguments')->context;

		if (empty($context) || !($context instanceof Context))
		{
			return;
		}

		if ($context->getSmtp() && $context->getSmtp()->getHost())
		{
			return;
		}

		if (preg_match('/X-Bitrix-Mail-SMTP-Host:/i', $headers))
		{
			return;
		}

		$eol = Mail::getMailEol();
		$eolh = preg_replace('/([a-f0-9]{2})/i', '\x\1', bin2hex($eol));

		if (preg_match(sprintf('/(^|%1$s)From:(.+?)(%1$s([^\s]|$)|$)/is', $eolh), $headers, $matches))
		{
			$address = new Address(preg_replace(sprintf('/%s\s+/', $eolh), '', $matches[2]));
			if ($address->validate())
			{
				if ($customSmtp = static::getCustomSmtp($address->getEmail()))
				{
					$context->setSmtp($customSmtp);
				}
			}
		}
	}

}
