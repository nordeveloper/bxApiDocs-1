<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\EntityPreset;
use Bitrix\Crm\EntityRequisite;
use Bitrix\Crm\RequisiteAddress;
use Bitrix\DocumentGenerator\DataProvider;
use Bitrix\DocumentGenerator\DataProviderManager;

class Requisite extends DataProvider
{
	protected $countryId;

	/**
	 * Returns list of value names for this Provider.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = [];
		$titles = $this->getFieldsTitles();
		foreach(EntityRequisite::getSingleInstance()->getRqFieldsCountryMap() as $field => $countries)
		{
			if(in_array($this->getCountryId(), $countries))
			{
				$fields[$field] = ['TITLE' => $titles[$field]];
			}
		}
		foreach($titles as $placeholder => $title)
		{
			if(strpos($placeholder, 'UF_CRM') === 0)
			{
				$fields[$placeholder] = ['TITLE' => $title];
			}
		}
		$fields = array_merge($fields, $this->getAddressFields());

		return $fields;
	}

	/**
	 * @return array
	 */
	protected function getAddressFields()
	{
		static $addressFields = false;
		if($addressFields === false)
		{
			$addressFields = [
				'PRIMARY_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_PRIMARY_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'PRIMARY_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 1,
					],
				],
				'REGISTERED_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_REGISTERED_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'REGISTERED_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 6,
					],
				],
				'HOME_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_HOME_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'HOME_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 4,
					],
				],
				'BENEFICIARY_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_BENEFICIARY_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'BENEFICIARY_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 9,
					],
				],
			];
		}
		return $addressFields;
	}

	/**
	 * Returns value by its name.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getValue($name)
	{
		$this->fetchData();
		return parent::getValue($name);
	}

	/**
	 * Loads data from the database.
	 *
	 * @return array|false
	 */
	protected function fetchData()
	{
		if(!$this->isLoaded())
		{
			if($this->source > 0)
			{
				$this->data = EntityRequisite::getSingleInstance()->getList(['select' => ['*', 'UF_*',], 'filter' => ['ID' => $this->source]])->fetch();
				$this->loadAddresses();
			}
		}

		return $this->data;
	}

	protected function loadAddresses()
	{
		$addresses = EntityRequisite::getAddresses($this->source);
		foreach($addresses as $typeId => $address)
		{
			$fieldName = $this->getAddressFieldNameByTypeId($typeId);
			if($fieldName)
			{
				$this->data[$fieldName.'_RAW'] = $address;
			}
		}
	}

	/**
	 * @param int $addressTypeId
	 * @return string|null
	 */
	protected function getAddressFieldNameByTypeId($addressTypeId)
	{
		static $types = null;
		if($types === null)
		{
			$types = [
				RequisiteAddress::Primary => 'PRIMARY_ADDRESS',
				RequisiteAddress::Registered => 'REGISTERED_ADDRESS',
				RequisiteAddress::Home => 'HOME_ADDRESS',
				RequisiteAddress::Beneficiary => 'BENEFICIARY_ADDRESS',
			];
		}

		if(isset($types[$addressTypeId]))
		{
			return $types[$addressTypeId];
		}

		return null;
	}

	/**
	 * @return int
	 */
	protected function getCountryId()
	{
		if(!$this->countryId)
		{
			$currentRegion = DataProviderManager::getInstance()->getCurrentRegion($this);
			$this->countryId = $this->getCountryMap()[$currentRegion];
			if(!$this->countryId)
			{
				$this->countryId = 1;
			}
		}

		return $this->countryId;
	}

	/**
	 * @return array
	 */
	protected function getFieldsTitles()
	{
		static $titles = false;
		if(!$titles)
		{
			$titles = EntityRequisite::getSingleInstance()->getFieldsTitles($this->getCountryId());
		}

		return $titles;
	}

	/**
	 * @return array
	 */
	protected function getCountryMap()
	{
		return [
			'ru' => 1,
			'by' => 4,
			'kz' => 6,
			'ua' => 14,
			'de' => 46,
			'us' => 122,
		];
	}
}