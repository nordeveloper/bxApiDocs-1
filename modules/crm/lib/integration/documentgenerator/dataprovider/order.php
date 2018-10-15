<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Discount;
use Bitrix\Crm\Requisite\EntityLink;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals;
use Bitrix\DocumentGenerator\Nameable;

class Order extends ProductsDataProvider implements Nameable
{
	protected $order;

	public function getFields()
	{
		if($this->fields === null)
		{
			parent::getFields();
			$this->fields['COMPANY']['VALUE'] = 'UF_COMPANY_ID';
			$this->fields['COMPANY']['OPTIONS'] = [
				'VALUES' => [
					'REQUISITE' => $this->getBuyerRequisiteId(),
					'BANK_DETAIL' => $this->getBuyerBankDetailId(),
				]
			];
			$this->fields['CONTACT']['VALUE'] = 'UF_CONTACT_ID';
		}

		return $this->fields;
	}

	/**
	 * @return int|string
	 */
	public function getAssignedId()
	{
		return $this->data['RESPONSIBLE_ID'];
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_ORDER_TITLE');
	}

	/**
	 * @return int
	 */
	protected function getBuyerRequisiteId()
	{
		static $requisiteId = 0;
		if($requisiteId > 0)
		{
			return $requisiteId;
		}
		if($this->isLoaded())
		{
			$linkData = $this->getLinkData();
			if($linkData['REQUISITE_ID'] > 0)
			{
				$requisiteId = $linkData['REQUISITE_ID'];
			}
		}

		return $requisiteId;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	protected function getLinkData()
	{
		if($this->linkData === null)
		{
			$this->linkData = EntityLink::getByEntity(\CCrmOwnerType::Order, $this->getSource());
		}

		return $this->linkData;
	}

	/**
	 * @return int
	 */
	protected function getSellerBankDetailId()
	{
		$sellerBankDeailId = 0;

		if($this->isLoaded())
		{
			$linkData = $this->getLinkData();
			$sellerBankDeailId = $linkData['MC_BANK_DETAIL_ID'];
		}
		return $sellerBankDeailId;
	}

	/**
	 * @return BankDetail|null
	 */
	protected function getBuyerBankDetailId()
	{
		$buyerBankDeailId = 0;

		if($this->isLoaded())
		{
			$linkData = $this->getLinkData();
			$buyerBankDeailId = $linkData['BANK_DETAIL_ID'];
		}
		return $buyerBankDeailId;
	}

	/**
	 * @param int $userId
	 * @return bool
	 */
	public function hasAccess($userId)
	{
		return true;
	}

	/**
	 * @return string
	 */
	protected function getTableClass()
	{
		Loader::includeModule('sale');

		return Internals\OrderTable::class;
	}

	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Order;
	}

	protected function getCrmProductOwnerType()
	{
		return 'O';
	}

	protected function getPersonTypeID()
	{
		return $this->getValue('PERSON_TYPE_ID');
	}

	public function getCurrencyId()
	{
		return $this->data['CURRENCY'];
	}

	/**
	 * @return null|string
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function getLocationId()
	{
		$order = $this->getOrder();

		if($order)
		{
			return $order->getTaxLocation();
		}
	}

	/**
	 * @return array
	 */
	protected function loadProductsData()
	{
		$result = [];

		if(Loader::includeModule('sale'))
		{
			$dbRes = Internals\BasketTable::getList([
				'filter' => [
					'=ORDER_ID' => $this->source
				]
			]);

			while($product = $dbRes->fetch())
			{
				$result[] = [
					'OWNER_ID' => $this->source,
					'OWNER_TYPE' => $this->getCrmProductOwnerType(),
					'PRODUCT_ID' => isset($product['PRODUCT_ID']) ? $product['PRODUCT_ID'] : 0,
					'NAME' => isset($product['NAME']) ? $product['NAME'] : '',
					'PRICE' => $product['PRICE'],
					'QUANTITY' => isset($product['QUANTITY']) ? $product['QUANTITY'] : 0,
					'DISCOUNT_TYPE_ID' => Discount::MONETARY,
					'DISCOUNT_SUM' => $product['DISCOUNT_PRICE'],
					'TAX_RATE' => $product['VAT_RATE'] * 100,
					'TAX_INCLUDED' => isset($product['VAT_INCLUDED']) ? $product['VAT_INCLUDED'] : 'N',
					'MEASURE_CODE' => isset($product['MEASURE_CODE']) ? $product['MEASURE_CODE'] : '',
					'MEASURE_NAME' => isset($product['MEASURE_NAME']) ? $product['MEASURE_NAME'] : '',
					'CUSTOMIZED' => isset($product['CUSTOM_PRICE']) ? $product['CUSTOM_PRICE'] : 'N',
					'CURRENCY_ID' => $this->getCurrencyId(),
				];
			}
		}

		return $result;
	}

	/**
	 * @return null|\Bitrix\Crm\Order\Order
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function getOrder()
	{
		if($this->order === null)
		{
			$this->order = \Bitrix\Crm\Order\Order::load($this->source);
		}

		return $this->order;
	}

	/**
	 * @return mixed
	 */
	protected function getUserFieldEntityID()
	{
		return Internals\OrderTable::getUfId();
	}

}