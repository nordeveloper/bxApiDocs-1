<?php
namespace Bitrix\Crm\Entity;
use Bitrix\Crm;

class LeadValidator extends EntityValidator
{
	/** @var array|null */
	protected $fieldInfos = null;
	/** @var int */
	protected $customerType = Crm\CustomerType::GENERAL;
	/** @var array */
	protected static $exclusiveFields = array(
		Crm\CustomerType::GENERAL => array(
			'HONORIFIC' => true,
			'LAST_NAME' => true,
			'NAME' => true,
			'SECOND_NAME' => true,
			'BIRTHDATE' => true,
			'POST' => true,
			'COMPANY_TITLE' => true,
			'ADDRESS' => true,
			'PHONE' => true,
			'EMAIL' => true,
			'WEB' => true,
			'IM' => true
		)
	);

	public function __construct($entityID, array $entityFields)
	{
		parent::__construct($entityID, $entityFields);
		$this->customerType = $this->entityID > 0
			? \CCrmLead::GetCustomerType($this->entityID) : Crm\CustomerType::GENERAL;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Lead;
	}

	public function getCustomerType()
	{
		return $this->customerType;
	}

	public function getFieldInfos()
	{
		if($this->fieldInfos === null)
		{
			$this->fieldInfos = \CCrmLead::GetFieldsInfo();
		}
		return $this->fieldInfos;
	}

	public function checkFieldAvailability($fieldName)
	{
		foreach(self::$exclusiveFields as $customerType => $fieldMap)
		{
			if($this->customerType === $customerType)
			{
				continue;
			}

			if(isset($fieldMap[$fieldName]))
			{
				return false;
			}
		}

		return true;
	}

	public function checkFieldPresence($fieldName)
	{
		//If field is not available ignore it.
		if(!$this->checkFieldAvailability($fieldName))
		{
			return true;
		}

		if($fieldName === 'OPPORTUNITY_WITH_CURRENCY')
		{
			return $this->innerCheckFieldPresence('OPPORTUNITY');
		}
		elseif($fieldName === 'ADDRESS')
		{
			return $this->checkAnyFieldPresence(
				array(
					'ADDRESS',
					'ADDRESS_2',
					'ADDRESS_CITY',
					'ADDRESS_REGION',
					'ADDRESS_PROVINCE',
					'ADDRESS_POSTAL_CODE',
					'ADDRESS_COUNTRY'
				)
			);
		}
		elseif(\CCrmFieldMulti::IsSupportedType($fieldName))
		{
			return $this->checkMultifieldPresence($fieldName);
		}
		return $this->innerCheckFieldPresence($fieldName);
	}
}