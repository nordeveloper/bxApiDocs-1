<?php
namespace Bitrix\ImOpenLines\Tools;

use \Bitrix\Main\Loader,
	\Bitrix\Main\PhoneNumber;

use \Bitrix\Crm\Communication\Validator as CrmValidator,
	\Bitrix\Crm\Communication\Normalizer as CrmNormalizer;

/**
 * Class Phone
 * @package Bitrix\ImOpenLines
 */
class Phone
{
	/**
	 * Validate phone number.
	 *
	 * @param string $phone Phone number.
	 * @return bool
	 */
	public static function validate($phone)
	{
		$result = PhoneNumber\Parser::getInstance()
			->parse($phone)
			->isValid();

		return $result;
	}

	/**
	 * Normalize phone number.
	 *
	 * @param string $phone Phone number.
	 * @return string|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function normalize($phone)
	{
		if(Loader::includeModule('crm'))
		{
			$result = CrmNormalizer::normalizePhone($phone);
		}
		else
		{
			$result = PhoneNumber\Parser::getInstance()
				->parse($phone)
				->format(PhoneNumber\Format::E164);
		}

		return $result;
	}

	/**
	 * @param $phone1
	 * @param $phone2
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isSame($phone1, $phone2)
	{
		$result = false;

		if(self::normalize($phone1) == self::normalize($phone2))
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * @param $phones
	 * @param $searchPhone
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isInArray($phones, $searchPhone)
	{
		$result = false;

		if(!empty($phones) && is_array($phones))
		{
			foreach ($phones as $phone)
			{
				if(self::isSame($phone, $searchPhone))
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $phones
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getArrayUniqueValidate($phones)
	{
		$resultPhones = [];

		if(!empty($phones) && is_array($phones))
		{
			foreach ($phones as $phone)
			{
				if(self::validate($phone) && !self::isInArray($resultPhones, $phone))
				{
					$resultPhones[] = $phone;
				}
			}
		}

		return $resultPhones;
	}
}