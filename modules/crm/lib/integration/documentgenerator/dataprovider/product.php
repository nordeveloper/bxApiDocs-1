<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Discount;
use Bitrix\Crm\Integration\DocumentGenerator\Value\Money;
use Bitrix\DocumentGenerator\DataProvider\HashDataProvider;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Value;

class Product extends HashDataProvider
{
	protected $properties;
	protected $propertyIDs;
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
		if($this->fields === null)
		{
			$currencyId = null;
			if(isset($this->source['CURRENCY_ID']))
			{
				$currencyId = $this->source['CURRENCY_ID'];
			}
			$this->fields = [
				'NAME' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_NAME_TITLE'),],
				'DESCRIPTION' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DESCRIPTION_TITLE'),],
				'SECTION' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_SECTION_TITLE'),],
				'PREVIEW_PICTURE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PREVIEW_PICTURE_TITLE'),
					'TYPE' => static::FIELD_TYPE_IMAGE,
				],
				'DETAIL_PICTURE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DETAIL_PICTURE_TITLE'),
					'TYPE' => static::FIELD_TYPE_IMAGE,
				],
				'PRODUCT_ID' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_ID_TITLE'),],
				'SORT' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_SORT_TITLE'),],
				'PRICE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'QUANTITY' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_QUANTITY_TITLE'),],
				'PRICE_EXCLUSIVE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_EXCLUSIVE_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_EXCLUSIVE_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_EXCLUSIVE_SUM_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_NETTO' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_NETTO_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_NETTO_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_NETTO_SUM_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_BRUTTO' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_BRUTTO_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_BRUTTO_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_BRUTTO_SUM_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'DISCOUNT_RATE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_RATE_TITLE'),],
				'DISCOUNT_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_SUM_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'DISCOUNT_TOTAL' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_TOTAL_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'TAX_RATE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_RATE_TITLE'),],
				'TAX_INCLUDED' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_INCLUDED_TITLE'),],
				'MEASURE_CODE' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_MEASURE_CODE_TITLE'),],
				'MEASURE_NAME' => ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_MEASURE_NAME_TITLE'),],
				'PRICE_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_SUM_TITLE'),
					'VALUE' => [$this, 'getPriceSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'TAX_VALUE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_VALUE_TITLE'),
					'VALUE' => [$this, 'getTaxValue'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'TAX_VALUE_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_TAX_VALUE_SUM_TITLE'),
					'VALUE' => [$this, 'getTaxValueSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_RAW' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_RAW_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_SUM_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_RAW_NETTO' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_NETTO_TITLE'),
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'PRICE_RAW_NETTO_SUM' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_PRICE_RAW_NETTO_SUM_TITLE'),
					'VALUE' => [$this, 'getSum'],
					'TYPE' => Money::class,
					'FORMAT' => ['CURRENCY_ID' => $currencyId, 'NO_SIGN' => true, 'WITH_ZEROS' => false],
				],
				'CUSTOMIZED' => [],
				'DISCOUNT_TYPE_ID' => [],
				'CURRENCY_ID' => [],
				'DISCOUNT_TYPE' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PRODUCT_DISCOUNT_TYPE_TITLE'),
					'VALUE' => [$this, 'getDiscountType'],
				],
			];

			$this->fields = array_merge($this->fields, $this->getProperties());
		}

		return $this->fields;
	}

	/**
	 * @return array
	 */
	protected function getMoneyFields()
	{
		static $fields = null;
		if($fields === null)
		{
			$fields = [
				'PRICE' => 'PRICE',
				'PRICE_EXCLUSIVE' => 'PRICE_EXCLUSIVE',
				'PRICE_EXCLUSIVE_SUM' => 'PRICE_EXCLUSIVE_SUM',
				'PRICE_NETTO' => 'PRICE_NETTO',
				'PRICE_NETTO_SUM' => 'PRICE_NETTO_SUM',
				'PRICE_BRUTTO' => 'PRICE_BRUTTO',
				'PRICE_BRUTTO_SUM' => 'PRICE_BRUTTO_SUM',
				'DISCOUNT_SUM' => 'DISCOUNT_SUM',
				'DISCOUNT_TOTAL' => 'DISCOUNT_TOTAL',
				'PRICE_SUM' => 'PRICE_SUM',
				'PRICE_RAW' => 'PRICE_RAW',
				'PRICE_RAW_SUM' => 'PRICE_RAW_SUM',
				'PRICE_RAW_NETTO' => 'PRICE_RAW_NETTO',
				'PRICE_RAW_NETTO_SUM' => 'PRICE_RAW_NETTO_SUM',
			];
		}

		return $fields;
	}

	/**
	 * @param string $placeholder
	 * @return float
	 */
	public function getSum($placeholder)
	{
		if($placeholder == 'DISCOUNT_TOTAL')
		{
			$placeholder = 'DISCOUNT_SUM';
		}
		else
		{
			$placeholder = str_replace('_SUM', '', $placeholder);
		}
		$value = $this->data[$placeholder];
		if($value instanceof Value)
		{
			$value = $value->getValue();
		}

		return $value * $this->data['QUANTITY'];
	}

	/**
	 * @return float
	 */
	public function getPriceSum()
	{
		return $this->getRawValue('PRICE') * $this->data['QUANTITY'];
	}

	/**
	 * @return float
	 */
	public function getTaxValue()
	{
		$value = 0;
		if($this->data['TAX_RATE'] > 0)
		{
			$value = $this->data['PRICE'] - $this->getVatlessPrice();
		}

		return $value;
	}

	/**
	 * @return Money
	 */
	public function getTaxValueSum()
	{
		$value = 0;

		if($this->data['TAX_RATE'] > 0)
		{
			if($this->data['TAX_INCLUDED'] == 'Y')
			{
				$value = $this->getRawValue('PRICE_RAW_SUM') - $this->getRawValue('PRICE_RAW_SUM') / (1 + $this->data['TAX_RATE']/100);
			}
			else
			{
				$value = $this->getRawValue('PRICE_SUM') - $this->getRawValue('PRICE_EXCLUSIVE_SUM');
			}
		}

		return $value;
	}

	/**
	 * @return string
	 */
	public function getDiscountType()
	{
		if($this->data['DISCOUNT_TYPE_ID'] == Discount::PERCENTAGE)
		{
			return '%';
		}
		else
		{
			return Money::getCurrencySymbol($this->data['CURRENCY_ID'], DataProviderManager::getInstance()->getRegionLanguageId());
		}
	}

	/**
	 * @return array
	 */
	protected function getProperties()
	{
		if($this->properties === null)
		{
			$this->properties = [];
			$propertyTypes = $this->getPrintablePropertyTypes();

			$catalogId = \CCrmCatalog::GetDefaultID();
			if(!$catalogId)
			{
				return $this->properties;
			}

			foreach($this->loadProperties() as $property)
			{
				if(!isset($propertyTypes[$property['PROPERTY_TYPE']]))
				{
					continue;
				}
				$this->propertyIDs[] = $property['ID'];
				$code = $property['ID'];
				$this->properties['PROPERTY_'.$code] = [
					'TITLE' => $property['NAME'],
					'VALUE' => [$this, 'getPropertyValue'],
				];
				if($property['CODE'])
				{
					$code = $property['CODE'];
					$this->properties['PROPERTY_'.$code] = [
						'TITLE' => $property['NAME'],
						'VALUE' => [$this, 'getPropertyValue'],
					];
				}
			}
		}

		return $this->properties;
	}

	/**
	 * @return array
	 */
	protected function loadProperties()
	{
		static $properties = null;
		if($properties === null)
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
				$properties[] = $property;
			}
		}

		return $properties;
	}

	/**
	 * Fills data with property values.
	 */
	protected function loadPropertyValues()
	{
		if($this->propertiesLoaded === false)
		{
			$this->propertiesLoaded = true;
			if(!$this->data['PRODUCT_ID'])
			{
				return;
			}
			$catalogId = \CCrmCatalog::GetDefaultID();
			if(!$catalogId)
			{
				return;
			}
			$propertyResult = \CIBlockElement::GetProperty(
				$catalogId,
				$this->data['PRODUCT_ID'],
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
				if($property['PROPERTY_TYPE'] === 'F')
				{
					$property['VALUE'] = \CFile::GetPath($property['VALUE']);
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
	 * @param string $code
	 * @return mixed
	 */
	public function getPropertyValue($code)
	{
		$this->loadPropertyValues();
		return $this->data[$code];
	}

	/**
	 * @return array
	 */
	protected function getPrintablePropertyTypes()
	{
		return ['S' => 'S', 'N' => 'N', 'F' => 'F'];
	}

	/**
	 * @return float|int
	 */
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

	/**
	 * @param float $value
	 * @return Money
	 */
	protected function toMoney($value)
	{
		if($value instanceof Money)
		{
			return $value;
		}
		return new Money($value, ['CURRENCY_ID' => $this->data['CURRENCY_ID'], 'NO_SIGN' => true, 'WITH_ZEROS' => false]);
	}
}