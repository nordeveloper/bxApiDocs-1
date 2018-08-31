<?php

namespace Bitrix\Main\Mail;

class Sender
{

	public static function confirm($ids)
	{
		foreach ((array) $ids as $id)
		{
			Internal\SenderTable::update(
				(int) $id,
				array(
					'IS_CONFIRMED' => true,
				)
			);
		}
	}

	public static function delete($ids)
	{
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
