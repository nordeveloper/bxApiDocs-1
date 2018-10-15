<?php
namespace Bitrix\Crm\Entity;

class DealValidator extends EntityValidator
{
	/** @var array|null */
	protected $fieldInfos = null;

	public function __construct($entityID, array $entityFields)
	{
		parent::__construct($entityID, $entityFields);
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Deal;
	}

	public function getFieldInfos()
	{
		if($this->fieldInfos === null)
		{
			$this->fieldInfos = \CCrmDeal::GetFieldsInfo();
		}
		return $this->fieldInfos;
	}

	public function checkFieldPresence($fieldName)
	{
		if($fieldName === 'OPPORTUNITY_WITH_CURRENCY')
		{
			return $this->innerCheckFieldPresence('OPPORTUNITY');
		}
		elseif($fieldName === 'CLIENT')
		{
			return $this->checkAnyFieldPresence(array('COMPANY_ID', 'CONTACT_ID', 'CONTACT_IDS'));
		}
		return $this->innerCheckFieldPresence($fieldName);
	}
}