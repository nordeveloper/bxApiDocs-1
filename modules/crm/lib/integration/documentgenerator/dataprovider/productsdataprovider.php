<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\Main\Loader;

abstract class ProductsDataProvider extends CrmEntityDataProvider
{
	/** @var Product[] */
	protected $products;
	/** @var Tax[] */
	protected $taxes;

	abstract protected function getCrmProductOwnerType();

	public function getFields()
	{
		$fields = parent::getFields();

		$fields['PRODUCTS'] = [
			'PROVIDER' => ArrayDataProvider::class,
			'TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_PRODUCTS_TITLE'),
			'OPTIONS' => [
				'ITEM_PROVIDER' => Product::class,
				'ITEM_NAME' => 'PRODUCT',
				'ITEM_TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_PRODUCT_TITLE'),
			],
			'VALUE' => function()
			{
				return $this->loadProducts();
			},
		];
		$fields['TAXES'] = [
			'PROVIDER' => ArrayDataProvider::class,
			'TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TAXES_TITLE'),
			'OPTIONS' => [
				'ITEM_PROVIDER' => Tax::class,
				'ITEM_NAME' => 'TAX',
				'ITEM_TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TAX_TITLE'),
			],
			'VALUE' => function()
			{
				return $this->loadTaxes();
			}
		];
		$fields['CURRENCY_ID'] = [
			'VALUE' => function()
			{
				if(!$this->data['CURRENCY_ID'])
				{
					$this->data['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
				}
				return $this->data['CURRENCY_ID'];
			}
		];
		$fields['CURRENCY_NAME'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_CURRENCY_NAME_TITLE'),
			'VALUE' => function()
			{
				return \CCrmCurrency::GetCurrencyName($this->getValue('CURRENCY_ID'));
			}
		];
		$fields = array_merge($fields, $this->getTotalFields());

		return $fields;
	}

	protected function fetchData()
	{
		parent::fetchData();
		$this->loadProducts();
		$this->calculateTotalFields();
	}

	protected function loadProducts()
	{
		if($this->products === null)
		{
			$products = [];
			if($this->isLoaded())
			{
				$crmProducts = \CAllCrmProductRow::LoadRows($this->getCrmProductOwnerType(), $this->source);
				foreach($crmProducts as $crmProduct)
				{
					if($crmProduct['TAX_INCLUDED'] !== 'Y')
					{
						$crmProduct['PRICE'] = $crmProduct['PRICE_EXCLUSIVE'];
					}
					$product = new Product([
						'NAME' => $crmProduct['PRODUCT_NAME'],
						'PRODUCT_ID' => $crmProduct['PRODUCT_ID'],
						'QUANTITY' => $crmProduct['QUANTITY'],
						'PRICE' => $crmProduct['PRICE'],
						'DISCOUNT_RATE' => $crmProduct['DISCOUNT_RATE'],
						'DISCOUNT_SUM' => $crmProduct['DISCOUNT_SUM'],
						'TAX_RATE' => $crmProduct['TAX_RATE'],
						'TAX_INCLUDED' => $crmProduct['TAX_INCLUDED'],
						'SORT' => $crmProduct['SORT'],
						'MEASURE_CODE' => $crmProduct['MEASURE_CODE'],
						'MEASURE_NAME' => $crmProduct['MEASURE_NAME'],
						'OWNER_ID' => $this->source,
						'OWNER_TYPE' => $this->getCrmProductOwnerType(),
						'CUSTOMIZED' => $crmProduct['CUSTOMIZED'],
						'DISCOUNT_TYPE_ID' => $crmProduct['DISCOUNT_TYPE_ID'],
					]);
					$product->setParentProvider($this);
					$products[] = $product;
				}
			}
			$this->products = $products;
		}

		return $this->products;
	}

	protected function calculateTotalFields()
	{
		if(empty($this->products))
		{
			return;
		}
		$crmProducts = [];
		$this->data = array_merge($this->data, array_fill_keys(array_keys($this->getTotalFields()), 0));
		foreach($this->products as $product)
		{
			$this->data['TOTAL_DISCOUNT'] += $product->getValue('QUANTITY') * $product->getValue('DISCOUNT_SUM');
			$this->data['TOTAL_QUANTITY'] += $product->getValue('QUANTITY');
			$this->data['TOTAL_RAW'] += $product->getValue('PRICE_RAW_SUM');
			$crmProducts[] = DataProviderManager::getInstance()->getArray($product);
		}

		$currencyID = $this->getCurrencyId();
		$calculate = \CCrmSaleHelper::Calculate($crmProducts, $currencyID, $this->getPersonTypeID(), false, 's1');
		$this->data['TOTAL_SUM'] = $calculate['PRICE'];
		$this->data['TOTAL_TAX'] = $calculate['TAX_VALUE'];
		$this->data['TOTAL_BEFORE_TAX'] = $this->data['TOTAL_SUM'] - $this->data['TOTAL_TAX'];
		$this->data['TOTAL_BEFORE_DISCOUNT'] = $this->data['TOTAL_BEFORE_TAX'] + $this->data['TOTAL_DISCOUNT'];

		$this->data['TOTAL_ROWS_WORDS'] = $this->data['TOTAL_QUANTITY_WORDS'] = $this->data['TOTAL_SUM_WORDS'] = '';
		if(function_exists('Number2Word_Rus'))
		{
			$this->data['TOTAL_SUM_WORDS'] = Number2Word_Rus($this->data['TOTAL_SUM'], 'Y', $currencyID);
			$this->data['TOTAL_QUANTITY_WORDS'] = Number2Word_Rus($this->data['TOTAL_QUANTITY'], 'N');
			$this->data['TOTAL_ROWS_WORDS'] = Number2Word_Rus(count($this->products), 'N');
			$this->data['TOTAL_TAX_WORDS'] = Number2Word_Rus($this->data['TOTAL_TAX'], 'Y', $currencyID);
			$this->data['TOTAL_BEFORE_TAX_WORDS'] = Number2Word_Rus($this->data['TOTAL_BEFORE_TAX'], 'Y', $currencyID);
		}
		if($this->data['TOTAL_SUM_WORDS'] == '')
		{
			$this->data['TOTAL_SUM_WORDS'] = \CCrmCurrency::MoneyToString($this->data['TOTAL_SUM'], $currencyID);
		}
		if($this->data['TOTAL_TAX_WORDS'] == '')
		{
			$this->data['TOTAL_TAX_WORDS'] = \CCrmCurrency::MoneyToString($this->data['TOTAL_TAX'], $currencyID);
		}
		if($this->data['TOTAL_BEFORE_TAX_WORDS'] == '')
		{
			$this->data['TOTAL_BEFORE_TAX_WORDS'] = \CCrmCurrency::MoneyToString($this->data['TOTAL_BEFORE_TAX_WORDS'], $currencyID);
		}
		if($this->data['TOTAL_QUANTITY_WORDS'] == '')
		{
			$this->data['TOTAL_QUANTITY_WORDS'] = $this->data['TOTAL_QUANTITY'];
		}
		if($this->data['TOTAL_ROWS_WORDS'] == '')
		{
			$this->data['TOTAL_ROWS_WORDS'] = count($this->products);
		}
		foreach($this->getTotalFields() as $placeholder => $fieldName)
		{
			if(in_array($placeholder, ['TOTAL_SUM_WORDS', 'TOTAL_QUANTITY', 'TOTAL_QUANTITY_WORDS', 'TOTAL_ROWS_WORDS', 'TOTAL_TAX_WORDS', 'TOTAL_BEFORE_TAX_WORDS',]))
			{
				continue;
			}
//			if(Loader::includeModule('currency'))
//			{
//				\CCurrencyLang::disableUseHideZero();
//				\CCurrencyLang::disableUseHideZero();
//			}
			$this->data[$placeholder] = \CCrmCurrency::MoneyToString($this->data[$placeholder], $currencyID);
//			if(Loader::includeModule('currency'))
//			{
//				\CCurrencyLang::enableUseHideZero();
//			}
		}
	}

	/**
	 * @return array
	 */
	protected function getTotalFields()
	{
		static $totalFields = false;
		if($totalFields === false)
		{
			$totalFields = [
				'TOTAL_DISCOUNT' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_DISCOUNT_TITLE')],
				'TOTAL_SUM' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_SUM_TITLE')],
				'TOTAL_RAW' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_RAW_TITLE')],
				'TOTAL_TAX' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_TAX_TITLE')],
				'TOTAL_TAX_WORDS' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_TAX_WORDS_TITLE')],
				'TOTAL_BEFORE_TAX' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_BEFORE_TAX_TITLE')],
				'TOTAL_BEFORE_TAX_WORDS' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_BEFORE_TAX_WORDS_TITLE')],
				'TOTAL_BEFORE_DISCOUNT' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_BEFORE_DISCOUNT_TITLE')],
				'TOTAL_SUM_WORDS' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_SUM_WORDS_TITLE')],
				'TOTAL_QUANTITY' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_QUANTITY_TITLE')],
				'TOTAL_QUANTITY_WORDS' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_QUANTITY_WORDS_TITLE')],
				'TOTAL_ROWS_WORDS' => ['TITLE' => GetMessage('CRM_DOCGEN_PRODUCTSDATAPROVIDER_TOTAL_ROWS_WORDS_TITLE')],
			];
		}

		return $totalFields;
	}

	/**
	 * @return int
	 */
	protected function getPersonTypeID()
	{
		$personTypes = \CCrmPaySystem::getPersonTypeIDs();
		$personTypeId = $personTypes['CONTACT'];
		if($this->data['COMPANY_ID'] > 0)
		{
			$personTypeId = $personTypes['COMPANY'];
		}

		return $personTypeId;
	}

	/**
	 * @return string
	 */
	protected function getCurrencyId()
	{
		return $this->getValue('CURRENCY_ID');
	}

	protected function loadTaxes()
	{
		$this->loadProducts();
		if(!empty($this->data))
		{
			if($this->taxes === null)
			{
				$this->taxes = $taxes = [];
				foreach($this->products as $product)
				{
					if($product->getValue('TAX_RATE') > 0)
					{
						if(!isset($taxes[$product->getValue('TAX_RATE')]))
						{
							$taxes[$product->getValue('TAX_RATE')] = [
								'VALUE' => 0,
								'RATE' => $product->getValue('TAX_RATE'),
								'TAX_INCLUDED' => $product->getValue('TAX_INCLUDED'),
							];
						}
						$taxes[$product->getValue('TAX_RATE')]['VALUE'] += $product->getValue('TAX_VALUE_SUM');
					}
				}
				$currencyID = $this->getCurrencyId();
				foreach($taxes as $tax)
				{
					$tax['VALUE'] = \CCrmCurrency::MoneyToString($tax['VALUE'], $currencyID);
					$tax = new Tax($tax);
					$tax->setParentProvider($this);
					$this->taxes[] = $tax;
				}
			}
		}

		return $this->taxes;
	}

	protected function getHiddenFields()
	{
		return array_merge(parent::getHiddenFields(), [
			'CURRENCY_ID',
		]);
	}
}