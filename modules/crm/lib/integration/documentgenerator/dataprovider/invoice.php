<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Discount;
use Bitrix\Crm\InvoiceTable;
use Bitrix\DocumentGenerator\Nameable;

class Invoice extends ProductsDataProvider implements Nameable
{
	protected $order;
	protected $payment;
	protected $basket;
	protected $linkData;

	public function getFields()
	{
		$fields = parent::getFields();
		$fields['CURRENCY_ID']['VALUE'] = function()
		{
			if(!$this->data['CURRENCY'])
			{
				$this->data['CURRENCY'] = \CCrmCurrency::GetBaseCurrencyID();
			}
			return $this->data['CURRENCY'];
		};
		$fields['CURRENCY_NAME'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_INVOICE_CURRENCY_NAME_TITLE'),
			'VALUE' => function()
			{
				return \CCrmCurrency::GetCurrencyName($this->getValue('CURRENCY'));
			}
		];
		$fields['ASSIGNED']['VALUE'] = function()
		{
			return $this->data['RESPONSIBLE_ID'];
		};

		return $fields;
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_INVOICE_TITLE');
	}

	/**
	 * @param int $userId
	 * @return bool
	 */
	public function hasAccess($userId)
	{
		if($this->isLoaded())
		{
			$userPermissions = new \CCrmPerms($userId);
			return \CCrmInvoice::CheckReadPermission($this->source, $userPermissions);
		}

		return false;
	}

	/**
	 * @return string
	 */
	protected function getTableClass()
	{
		return InvoiceTable::class;
	}

	/**
	 * @return array
	 */
	protected function getHiddenFields()
	{
		return array_merge(parent::getHiddenFields(), [
			'DATE_INS',
			'DATE_UPDATE_SHORT',
			'STATUS_ID',
			'STATUS_BY',
			'SUM_PAID_FORREP',
			'PAY_VOUCHER_DATE_SHORT',
			'DATE_BILL_SHORT',
			'DATE_PAY_BEFORE_SHORT',
			'DATE_MARKED_SHORT',
			'ASSIGNED_BY',
			'DATE_BEGIN_SHORT',
			'UF_DEAL_ID',
			'UF_QUOTE_ID',
			'UF_COMPANY_ID',
			'UF_CONTACT_ID',
			'UF_MYCOMPANY_ID',
		]);
	}

	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Invoice;
	}

	protected function getCrmProductOwnerType()
	{
		return 'I';
	}

	protected function getPersonTypeID()
	{
		return \CCrmInvoice::ResolvePersonTypeID($this->getValue('UF_MYCOMPANY_ID'), $this->getValue('UF_CONTACT_ID'));
	}

	protected function getCurrencyId()
	{
		return $this->getValue('CURRENCY');
	}

	protected function loadProducts()
	{
		if(!empty($this->data))
		{
			if($this->products === null)
			{
				$products = [];
				$productRows = \CCrmInvoice::GetProductRows($this->source);
				foreach($productRows as $product)
				{
					$products[] = new Product([
						'OWNER_ID' => $this->source,
						'OWNER_TYPE' => $this->getCrmProductOwnerType(),
						'PRODUCT_ID' => isset($product['PRODUCT_ID']) ? $product['PRODUCT_ID'] : 0,
						'NAME' => isset($product['PRODUCT_NAME']) ? $product['PRODUCT_NAME'] : '',
						'PRICE' => $product['PRICE'],
						'QUANTITY' => isset($product['QUANTITY']) ? $product['QUANTITY'] : 0,
						'DISCOUNT_TYPE_ID' => Discount::MONETARY,
						'DISCOUNT_SUM' => $product['DISCOUNT_PRICE'],
						'TAX_RATE' => $product['VAT_RATE'] * 100,
						'TAX_INCLUDED' => isset($product['VAT_INCLUDED']) ? $product['VAT_INCLUDED'] : 'N',
						'MEASURE_CODE' => isset($product['MEASURE_CODE']) ? $product['MEASURE_CODE'] : '',
						'MEASURE_NAME' => isset($product['MEASURE_NAME']) ? $product['MEASURE_NAME'] : '',
						'CUSTOMIZED' => isset($product['CUSTOM_PRICE']) ? $product['CUSTOM_PRICE'] : 'N',
					]);
				}
				$this->products = $products;
			}
		}

		return $this->products;
	}

	/**
	 * @return int|array
	 */
	protected function getMyCompanyId()
	{
		if(isset($this->data['UF_MYCOMPANY_ID']) && $this->data['UF_MYCOMPANY_ID'] > 0)
		{
			return $this->data['UF_MYCOMPANY_ID'];
		}

		return null;
	}

	/**
	 * @return int|null
	 */
	protected function getCompanyId()
	{
		if(isset($this->data['UF_COMPANY_ID']) && $this->data['UF_COMPANY_ID'] > 0)
		{
			return $this->data['UF_COMPANY_ID'];
		}

		return null;
	}

	/**
	 * @return int|null
	 */
	protected function getContactId()
	{
		if(isset($this->data['UF_CONTACT_ID']) && $this->data['UF_CONTACT_ID'] > 0)
		{
			return $this->data['UF_CONTACT_ID'];
		}

		return null;
	}

	/**
	 * @return string
	 */
	protected function getUserFieldEntityID()
	{
		return \CCrmInvoice::GetUserFieldEntityID();
	}
}