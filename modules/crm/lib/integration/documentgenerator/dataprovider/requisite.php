<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\EntityRequisite;
use Bitrix\Crm\RequisiteAddress;

class Requisite extends BaseRequisite
{
	/**
	 * Returns list of value names for this Provider.
	 *
	 * @return array
	 */
	public function getFields()
	{
		if($this->fields === null)
		{
			parent::getFields();
			$this->fields = array_merge($this->fields, $this->getAddressFields());
		}

		return $this->fields;
	}

	/**
	 * @return array
	 */
	public function getAddressFields()
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
						'COUNTRY_ID' => $this->getDocumentCountryId(),
					],
				],
				'REGISTERED_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_REGISTERED_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'REGISTERED_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 6,
						'COUNTRY_ID' => $this->getDocumentCountryId(),
					],
				],
				'HOME_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_HOME_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'HOME_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 4,
						'COUNTRY_ID' => $this->getDocumentCountryId(),
					],
				],
				'BENEFICIARY_ADDRESS' => [
					'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_REQUISITE_BENEFICIARY_ADDRESS_TITLE'),
					'PROVIDER' => Address::class,
					'VALUE' => 'BENEFICIARY_ADDRESS_RAW',
					'OPTIONS' => [
						'TYPE_ID' => 9,
						'COUNTRY_ID' => $this->getDocumentCountryId(),
					],
				],
			];
		}
		return $addressFields;
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
	 * @return array
	 */
	protected function getInterfaceLanguageTitles()
	{
		if($this->interfaceTitles === null)
		{
			$this->interfaceTitles = EntityRequisite::getSingleInstance()->getFieldsTitles($this->getInterfaceCountryId());
		}

		return $this->interfaceTitles;
	}

	/**
	 * @return array
	 */
	protected function getDocumentLanguageTitles()
	{
		if($this->documentTitles === null)
		{
			$documentRegion = $this->getDocumentCountryId();
			if($documentRegion == $this->getInterfaceCountryId())
			{
				$this->documentTitles = $this->getInterfaceLanguageTitles();
			}
			else
			{
				$this->documentTitles = EntityRequisite::getSingleInstance()->getFieldsTitles($documentRegion);
			}
		}

		return $this->documentTitles;
	}
}