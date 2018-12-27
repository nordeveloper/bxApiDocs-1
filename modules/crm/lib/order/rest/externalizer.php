<?php


namespace Bitrix\Crm\Order\Rest;

use Bitrix\Crm\Order\Rest\Entity\OrderContactCompany;
use Bitrix\Crm\Order\Rest\Entity\OrderRequisiteLink;

class Externalizer extends \Bitrix\Sale\Rest\Externalizer
{
	protected function prepareFields(array $data)
	{
		$fields = parent::prepareFields($data);

		if($this->getController() instanceof \Bitrix\Sale\Controller\Order)
		{
			$fields['CLIENTS'] = $this->flattenList($data['CLIENTS']);
			$fields['REQUISITE_LINK'] = $this->flattenItem($data['REQUISITE_LINK']);
		}

		return $fields;
	}

	protected function externalizeEntitiesCollectionFields($data)
	{
		$fields = parent::externalizeEntitiesCollectionFields($data);

		//TODO: доделать для случая когда OrderContactCompany вызывается как самостоятельный rest
		if(isset($data['CLIENTS']))
			$fields['CLIENTS'] = $this->externalizeListFields($data['CLIENTS'], new OrderContactCompany());

		//TODO: доделать для случая когда OrderRequisiteLink вызывается как самостоятельный rest
		if(isset($data['REQUISITE_LINK']))
			$fields['REQUISITE_LINK']['FIELDS'] = $this->externalizeFields($data['REQUISITE_LINK']['FIELDS'], new OrderRequisiteLink());

		return $fields;
	}
}