<?php


namespace Bitrix\Crm\Order;



use Bitrix\Main\Error;
use Bitrix\Sale\Helpers\Order\Builder\OrderBuilderNew;

/**
 * Class OrderBuilderRest
 * @package Bitrix\Crm\Order
 * @internal
 */
final class OrderBuilderRest extends \Bitrix\Sale\Helpers\Order\Builder\OrderBuilderRest
{
	/** @var  Order */
	protected $order;

	public function build($data)
	{
		$this->initFields($data)
			->delegate()
			->createOrder()
			->setDiscounts() //?
			->setFields()
			->buildClients()
			->setProperties()
			->setUser()
			->buildBasket()
			->buildPayments()
			->buildShipments()
			->buildTradeBindings()
			->setDiscounts() //?
			->finalActions();
	}

	/**
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->order;
	}

	public function buildClients()
	{
		if(is_array($this->formData["CLIENTS"]))
		{
			if(!$this->removeClients())
			{
				return $this;
			}

			$contactCompanyCollection = $this->getOrder()->getContactCompanyCollection();

			foreach($this->formData["CLIENTS"] as $clientFields)
			{
				$id = intval($clientFields['ID']);
				$isNew = ($id <= 0);

				$fields = array_intersect_key($clientFields, $this->getSettableClientFields());

				if($isNew)
				{
					$client = $clientFields['ENTITY_TYPE_ID'] == \CCrmOwnerType::Company ? $contactCompanyCollection->createCompany():$contactCompanyCollection->createContact();
				}
				else
				{
					$client = $contactCompanyCollection->getItemById($id);
				}

				if(!$client)
				{
					$this->errorsContainer->addError(new Error('Can\'t find Clients with id:"'.$id.'"', 'CLIENTS_NOT_EXISTS'));
					continue;
				}

				$r = $client->setFields($fields);
				if(!$r->isSuccess())
					$this->errorsContainer->addErrors($r->getErrors());
			}
		}

		return $this;
	}

	protected function removeClients()
	{
		if($this->getSettingsContainer()->getItemValue('deleteClientsIfNotExists'))
		{
			$contactCompanyCollection = $this->getOrder()->getContactCompanyCollection();

			$internalIx = [];
			foreach($this->formData["CLIENTS"] as $clientFields)
			{
				if(!isset($clientFields['ID']))
					continue;

				$client = $contactCompanyCollection->getItemById($clientFields['ID']);

				if ($client == null)
					continue;

				$internalIx[] = $client->getId();
			}

			foreach ($contactCompanyCollection as $client)
			{
				if(!in_array($client->getId(), $internalIx))
				{
					$r = $client->delete();
					if (!$r->isSuccess())
					{
						$this->errorsContainer->addErrors($r->getErrors());
						return false;
					}
				}
			}
		}

		return true;
	}

	public function buildRequisiteLink()
	{
		if(is_array($this->formData["REQUISITE_LINK"]))
		{
			if(!$this->removeRequisiteLink())
			{
				return $this;
			}

			$fields = array_intersect_key($this->formData["REQUISITE_LINK"], $this->getSettableRequisiteLinksFields());

			$this->getOrder()->setRequisiteLink($fields);
		}

		return $this;
	}

	protected function removeRequisiteLink()
	{
		if($this->getSettingsContainer()->getItemValue('deleteRequsiteLinkIfNotExists'))
		{
			if(count($this->formData["REQUISITE_LINK"])<=0)
			{
				//OrderRequisiteLink::unregister()
			}
		}
	}

	public function setProperties()
	{
		parent::setProperties();

		if ($this->delegate instanceof OrderBuilderNew)
		{
			$clientCollection = $this->getOrder()->getContactCompanyCollection();

			$primaryContact = $clientCollection->getPrimaryContact();
			if (!empty($primaryContact))
			{
				$this->setPropertiesByClient($primaryContact);
			}
			else
			{
				$contacts = $clientCollection->getContacts();
				foreach ($contacts as $contact)
				{
					$this->setPropertiesByClient($contact);
					break;
				}
			}

			$primaryCompany = $clientCollection->getPrimaryCompany();
			if (!empty($primaryCompany))
			{
				$this->setPropertiesByClient($primaryCompany);
			}
		}

		return $this;
	}

	protected function setPropertiesByClient(ContactCompanyEntity $entity)
	{
		$clientProperties = Matcher\FieldMatcher::getPropertyValues($entity->getField('ENTITY_TYPE_ID'), (int)$entity->getField('ENTITY_ID'));
		$propertyCollection = $this->getOrder()->getPropertyCollection();
		/**
		 * @var  \Bitrix\Crm\Order\PropertyValue $property
		 */
		foreach ($propertyCollection as $property)
		{
			if (
				isset($clientProperties[$property->getPropertyId()])
				&& !is_array($property->getValue())
				&& !strlen($property->getValue())
			)
			{
				$property->setValue($clientProperties[$property->getPropertyId()]);
			}
		}
	}

	protected function getSettableClientFields()
	{
		$binding = new \Bitrix\Crm\Order\Rest\Entity\OrderContactCompany();
		return array_flip($binding->getSettableFields());
	}

	protected function getSettableRequisiteLinksFields()
	{
		$requisiteLink = new \Bitrix\Crm\Order\Rest\Entity\OrderRequisiteLink();
		return array_flip($requisiteLink->getSettableFields());
	}

	protected function prepareFields(array $fields)
	{
		return array_merge(
			parent::prepareFields($fields),
			\Bitrix\Crm\Controller\OrderContactCompany::prepareFields($fields['ORDER']),
			\Bitrix\Crm\Controller\OrderRequisiteLink::prepareFields($fields['ORDER'])
		);
	}
}