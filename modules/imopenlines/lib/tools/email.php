<?php
namespace Bitrix\ImOpenLines\Tools;

use \Bitrix\Main\Loader;

use \Bitrix\Crm\Communication\Validator as CrmValidator,
	\Bitrix\Crm\Communication\Normalizer as CrmNormalizer;

/**
 * Class Email
 * @package Bitrix\ImOpenLines
 */
class Email
{
	/**
	 * Validate email.
	 *
	 * @param bool $email Email.
	 * @return string
	 */
	public static function validate($email)
	{
		$result = check_email($email);

		return $result;
	}

	/**
	 * Normalize email.
	 *
	 * @param string $email Email.
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function normalize($email)
	{
		if(Loader::includeModule('crm'))
		{
			$result = CrmNormalizer::normalizeEmail($email);
		}
		else
		{
			$result = trim(strtolower($email));
		}

		return $result;
	}

	/**
	 * @param $email1
	 * @param $email2
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isSame($email1, $email2)
	{
		$result = false;

		if(self::normalize($email1) == self::normalize($email2))
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * @param $emails
	 * @param $searchEmail
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isInArray($emails, $searchEmail)
	{
		$result = false;

		if(!empty($emails) && is_array($emails))
		{
			foreach ($emails as $email)
			{
				if(self::isSame($email, $searchEmail))
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $emails
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getArrayUniqueValidate($emails)
	{
		$resultEmails = [];

		if(!empty($emails) && is_array($emails))
		{
			foreach ($emails as $email)
			{
				if(self::validate($email) && !self::isInArray($resultEmails, $email))
				{
					$resultEmails[] = $email;
				}
			}
		}

		return $resultEmails;
	}
}