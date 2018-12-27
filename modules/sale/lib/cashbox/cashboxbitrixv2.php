<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Main\Localization;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\PriceMaths;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBitrixV2
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBitrixV2 extends CashboxBitrix
{
	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$phone = \NormalizePhone($data['client_phone']);
		if (is_string($phone))
		{
			if ($phone[0] === '7')
				$phone = '+'.$phone;
		}
		else
		{
			$phone = '';
		}

		$client = $data['client_email'];
		if ($this->getValueFromSettings('CLIENT', 'INFO') === 'PHONE'
			&& $phone
		)
		{
			$client = $phone;
		}

		$result = array(
			'type' => $check::getCalculatedSign() === Check::CALCULATED_SIGN_INCOME ? 'sell' : 'sellReturn',
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'taxationType' => $this->getValueFromSettings('TAX', 'SNO'),
			'zn' => $this->getField('NUMBER_KKM'),
			'clientInfo' => [
				'emailOrPhone' => $client,
			],
			'payments' => array(),
			'items' => array(),
			'total' => (float)$data['total_sum']
		);

		foreach ($data['payments'] as $payment)
		{
			$result['payments'][] = array(
				'type' => $this->getValueFromSettings('PAYMENT_TYPE', $payment['type']),
				'sum' => (float)$payment['sum']
			);
		}

		$checkTypeMap = $this->getCheckTypeMap();
		foreach ($data['items'] as $i => $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			if ($vat === null)
			{
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
			}

			$result['items'][] = array(
				'name' => $item['name'],
				'price' => (float)$item['base_price'],
				'quantity' => $item['quantity'],
				'amount' => (float)$item['sum'],
				'paymentMethod' => $checkTypeMap[$check::getType()],
				'paymentObject' => 'commodity',
				'tax' => array(
					'type' => $vat
				),
			);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_TITLE');
	}


	/**
	 * @return array
	 */
	protected function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 'fullPayment',
			SellReturnCashCheck::getType() => 'fullPayment',
			SellReturnCheck::getType() => 'fullPayment',
			AdvancePaymentCheck::getType() => 'advance',
			AdvanceReturnCashCheck::getType() => 'advance',
			AdvanceReturnCheck::getType() => 'advance',
			CreditCheck::getType() => 'credit',
			CreditReturnCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'creditPayment',
			PrepaymentCheck::getType() => 'prepayment',
			PrepaymentReturnCheck::getType() => 'prepayment',
			PrepaymentReturnCashCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'fullPrepayment',
			FullPrepaymentReturnCheck::getType() => 'fullPrepayment',
			FullPrepaymentReturnCashCheck::getType() => 'fullPrepayment',
		);
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = parent::getSettings($modelId);

		$kkmList = static::getSupportedKkmModels();
		if (isset($kkmList[$modelId]))
		{
			$settings['TAX'] = array(
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_SNO'),
				'REQUIRED' => 'Y',
				'ITEMS' => array(
					'SNO' => array(
						'TYPE' => 'ENUM',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_SNO_LABEL'),
						'VALUE' => 'osn',
						'OPTIONS' => array(
							'osn' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_OSN'),
							'usn_income' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_UI'),
							'usn_income_outcome' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_UIO'),
							'envd' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_ENVD'),
							'esn' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_ESN'),
							'patent' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_PATENT')
						)
					)
				)
			);
		}

		$settings['CLIENT'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT'),
			'ITEMS' => array(
				'INFO' => array(
					'TYPE' => 'ENUM',
					'VALUE' => 'NONE',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_INFO'),
					'OPTIONS' => array(
						'EMAIL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_EMAIL'),
						'PHONE' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_PHONE'),
					)
				),
			)
		];

		return $settings;
	}

	/**
	 * @param Main\HttpRequest $request
	 * @return array
	 */
	public static function extractSettingsFromRequest(Main\HttpRequest $request)
	{
		return $request->get('SETTINGS');
	}

	/**
	 * @return array
	 */
	public static function getSupportedKkmModels()
	{
		return [
			'atol' => [
				'NAME' => 'ATOL',
				'SETTINGS' => [
					'VAT' => [
						'NOT_VAT' => 'none',
						0 => 'vat0',
						10 => 'vat10',
						18 => 'vat18',
						20 => 'vat20'
					],
					'PAYMENT_TYPE' => [
						Check::PAYMENT_TYPE_CASH => 'cash',
						Check::PAYMENT_TYPE_CASHLESS => 'electronically',
						Check::PAYMENT_TYPE_ADVANCE => 'prepaid',
						Check::PAYMENT_TYPE_CREDIT => 'credit'
					]
				]
			],
		];
	}

	/**
	 * @return bool
	 */
	public static function isSupportedFFD105()
	{
		return true;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getCashboxList(array $data)
	{
		$result = parent::getCashboxList($data);

		foreach ($result as $zn => $cashbox)
		{
			$current = Manager::getCashboxFromCache($cashbox['ID']);

			if ($current['HANDLER'] !== '\\'.static::class)
			{
				$cashbox['SETTINGS'] = [];

				$currentModel = static::getSupportedKkmModels()[$cashbox['KKM_ID']];

				foreach ($current['SETTINGS'] as $key => $setting)
				{
					if ($key === 'PAYMENT_TYPE')
					{
						$setting = $currentModel['SETTINGS']['PAYMENT_TYPE'];
					}
					elseif ($key === 'VAT')
					{
						$setting['NOT_VAT']= $currentModel['SETTINGS']['VAT']['NOT_VAT'];

						if (Main\Loader::includeModule('catalog'))
						{
							$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
							$vatList = $dbRes->fetchAll();
							if ($vatList)
							{
								foreach ($vatList as $vat)
								{
									if (isset($currentModel['SETTINGS']['VAT'][(int)$vat['RATE']]))
									{
										$setting[(int)$vat['ID']] = $currentModel['SETTINGS']['VAT'][(int)$vat['RATE']];
									}
									else
									{
										$setting[(int)$vat['ID']] = $currentModel['SETTINGS']['VAT']['NOT_VAT'];
									}
								}
							}
						}
					}

					$cashbox['SETTINGS'][$key] = $setting;
				}

				$cashbox['HANDLER'] = '\\'.static::class;

				$result[$zn] = $cashbox;
			}
		}

		return $result;
	}
}
