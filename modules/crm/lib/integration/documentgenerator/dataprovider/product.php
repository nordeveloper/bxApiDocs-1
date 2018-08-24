<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Discount;
use Bitrix\DocumentGenerator\DataProvider\HashDataProvider;

class Product extends HashDataProvider
{
	protected $propertyIDs = [];
	protected $propertiesLoaded = false;

	public function __construct($data, array $options = [])
	{
		if(is_array($data) && !empty($data))
		{
			$data['PRICE_RAW'] = $data['PRICE'];
			$taxRate = isset($data['TAX_RATE']) ? (double)$data['TAX_RATE'] : 0.0;
			if($data['TAX_INCLUDED'] === 'Y')
			{
				$data['PRICE_EXCLUSIVE'] = \CCrmProductRow::CalculateExclusivePrice($data['PRICE'], $taxRate);
			}
			else
			{
				$data['PRICE_EXCLUSIVE'] = $data['PRICE'];
				$data['PRICE'] = \CCrmProductRow::CalculateInclusivePrice($data['PRICE_EXCLUSIVE'], $taxRate);
			}

			$data['DISCOUNT_RATE'] = Discount::calculateDiscountRate(($data['PRICE_EXCLUSIVE'] + $data['DISCOUNT_SUM']), $data['PRICE_EXCLUSIVE']);
			$data['PRICE_NETTO'] = $data['PRICE_EXCLUSIVE'] + $data['DISCOUNT_SUM'];

			if($data['DISCOUNT_SUM'] <= 0)
			{
				$data['PRICE_BRUTTO'] = $data['PRICE'];
			}
			else
			{
				$data['PRICE_BRUTTO'] = \CCrmProductRow::CalculateInclusivePrice($data['PRICE_NETTO'], $taxRate);
			}

			if($data['TAX_INCLUDED'] === 'Y')
			{
				$data['PRICE_RAW_NETTO'] = $data['PRICE_BRUTTO'];
			}
			else
			{
				$data['PRICE_RAW_NETTO'] = $data['PRICE_NETTO'];
			}
		}

		parent::__construct($data, $options);
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		$fields = [
			'NAME' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_NAME_TITLE'),],
			'PRODUCT_ID' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_ID_TITLE'),],
			'SORT' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_SORT_TITLE'),],
			'PRICE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_TITLE'),
			],
			'QUANTITY' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_QUANTITY_TITLE'),],
			'PRICE_EXCLUSIVE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_EXCLUSIVE_TITLE'),],
			'PRICE_EXCLUSIVE_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_EXCLUSIVE_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->data['PRICE_EXCLUSIVE'] * $this->data['QUANTITY'];
				}
			],
			'PRICE_NETTO' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_NETTO_TITLE'),],
			'PRICE_NETTO_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_NETTO_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->data['PRICE_NETTO'] * $this->data['QUANTITY'];
				}
			],
			'PRICE_BRUTTO' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_BRUTTO_TITLE'),],
			'PRICE_BRUTTO_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_BRUTTO_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->data['PRICE_BRUTTO'] * $this->data['QUANTITY'];
				}
			],
			'DISCOUNT_RATE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_RATE_TITLE'),],
			'DISCOUNT_SUM' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_SUM_TITLE'),],
			'DISCOUNT_TOTAL' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_TOTAL_TITLE'),
				'VALUE' => function()
				{
					return $this->data['DISCOUNT_SUM'] * $this->data['QUANTITY'];
				}
			],
			'TAX_RATE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_RATE_TITLE'),],
			'TAX_INCLUDED' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_INCLUDED_TITLE'),],
			'MEASURE_CODE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_MEASURE_CODE_TITLE'),],
			'MEASURE_NAME' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_MEASURE_NAME_TITLE'),],
			'PRICE_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->getValue('PRICE', false) * $this->data['QUANTITY'];
				}
			],
			'TAX_VALUE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_VALUE_TITLE'),
				'VALUE' => function()
				{
					if($this->data['TAX_RATE'] > 0)
					{
						return $this->data['PRICE'] - $this->getVatlessPrice();
					}

					return 0;
				}
			],
			'TAX_VALUE_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_VALUE_SUM_TITLE'),
				'VALUE' => function()
				{
					if($this->data['TAX_RATE'] > 0)
					{
						if($this->data['TAX_INCLUDED'] == 'Y')
						{
							return $this->getValue('PRICE_RAW_SUM', false) - $this->getValue('PRICE_RAW_SUM', false) / (1 + $this->data['TAX_RATE']/100);
						}
						else
						{
							return $this->getValue('PRICE_SUM', false) - $this->getValue('PRICE_EXCLUSIVE_SUM', false);
						}
					}

					return 0;
				}
			],
			'PRICE_RAW' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_TITLE'),
			],
			'PRICE_RAW_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->data['PRICE_RAW'] * $this->data['QUANTITY'];
				}
			],
			'PRICE_RAW_NETTO' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_NETTO_TITLE'),
			],
			'PRICE_RAW_NETTO_SUM' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_NETTO_SUM_TITLE'),
				'VALUE' => function()
				{
					return $this->getValue('PRICE_RAW_NETTO') * $this->data['QUANTITY'];
				}
			],
			'CUSTOMIZED' => [],
			'DISCOUNT_TYPE_ID' => [],
		];

		$fields = array_merge($fields, $this->loadProperties());

		return $fields;
	}

	public function getValue($name, $round = true)
	{
		$value = parent::getValue($name);

		if(is_double($value) && $round)
		{
			$value = $this->round($value);
		}

		return $value;
	}

	/**
	 * @return array
	 */
	protected function loadProperties()
	{
		$properties = [];

		$catalogId = \CCrmCatalog::GetDefaultID();
		if(!$catalogId)
		{
			return $properties;
		}

		$propertyTypes = $this->getPrintablePropertyTypes();
		$query = \CIBlock::GetProperties($catalogId, ['SORT' => 'ASC'], ['ACTIVE' => 'Y']);
		while($property = $query->Fetch())
		{
			if(!isset($propertyTypes[$property['PROPERTY_TYPE']]))
			{
				continue;
			}
			$this->propertyIDs[] = $property['ID'];
			$code = $property['ID'];
			$properties['PROPERTY_'.$code] = [
				'TITLE' => $property['NAME'],
				'VALUE' => function() use ($property, $code)
				{
					$this->loadPropertyValues();
					return $this->data['PROPERTY_'.$code];
				}
			];
			if($property['CODE'])
			{
				$code = $property['CODE'];
				$properties['PROPERTY_'.$code] = [
					'TITLE' => $property['NAME'],
					'VALUE' => function() use ($property, $code)
					{
						$this->loadPropertyValues();
						return $this->data['PROPERTY_'.$code];
					}
				];
			}
		}

		return $properties;
	}

	protected function loadPropertyValues()
	{
		if($this->propertiesLoaded === false)
		{
			$this->propertiesLoaded = true;
			if(!$this->data['ID'])
			{
				return;
			}
			$catalogId = \CCrmCatalog::GetDefaultID();
			if(!$catalogId)
			{
				return;
			}
			$this->propertyIDs = array_unique($this->propertyIDs);
			$propertyResult = \CIBlockElement::GetProperty(
				$catalogId,
				$this->data['ID'],
				array(
					'sort' => 'asc',
					'id' => 'asc',
					'enum_sort' => 'asc',
					'value_id' => 'asc',
				),
				array(
					'ACTIVE' => 'Y',
					'EMPTY' => 'N',
					'CHECK_PERMISSIONS' => 'N',
					'ID' => $this->propertyIDs,
				)
			);
			while($property = $propertyResult->Fetch())
			{
				$code = $property['ID'];
				if(isset($this->data['PROPERTY_'.$code]) && !empty($this->data['PROPERTY_'.$code]))
				{
					$this->data['PROPERTY_'.$code] .= ', ';
				}
				else
				{
					$this->data['PROPERTY_'.$code] = '';
				}
				$this->data['PROPERTY_'.$code] .= $property['VALUE'];
				if($property['CODE'])
				{
					$code = $property['CODE'];
					if(isset($this->data['PROPERTY_'.$code]) && !empty($this->data['PROPERTY_'.$code]))
					{
						$this->data['PROPERTY_'.$code] .= ', ';
					}
					else
					{
						$this->data['PROPERTY_'.$code] = '';
					}
					$this->data['PROPERTY_'.$code] .= $property['VALUE'];
				}
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getPrintablePropertyTypes()
	{
		return ['S' => 'S', 'N' => 'N'];
	}

	public function getVatlessPrice()
	{
		if($this->data['TAX_INCLUDED'] == 'Y')
		{
			return $this->data['PRICE_RAW'] / (1 + $this->data['TAX_RATE']/100);
		}
		else
		{
			return $this->data['PRICE_EXCLUSIVE'];
		}
	}

	protected function round($value, $precision = 2)
	{
		return round($value, $precision);
	}
}