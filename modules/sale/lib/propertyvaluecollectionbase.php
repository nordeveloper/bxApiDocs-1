<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\Input;
use Bitrix\Sale\Internals\OrderPropsGroupTable;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyValueCollectionBase
 * @package Bitrix\Sale
 */
abstract class PropertyValueCollectionBase extends Internals\EntityCollection
{
	/** @var OrderBase */
	protected $order;

	private $attributes = array(
		'IS_EMAIL'        => null,
		'IS_PAYER'        => null,
		'IS_LOCATION'     => null,
		'IS_LOCATION4TAX' => null,
		'IS_PROFILE_NAME' => null,
		'IS_ZIP'          => null,
		'IS_PHONE'        => null,
		'IS_ADDRESS'      => null,
	);

	protected $propertyGroupMap = array();
	protected $propertyGroups = null;

	private static $eventClassName = null;

	/**
	 * @param OrderBase $order
	 * @return PropertyValueCollectionBase
	 */
	public static function load(OrderBase $order)
	{
		/** @var PropertyValueCollectionBase $propertyCollection */
		$propertyCollection = static::createPropertyValueCollectionObject();
		$propertyCollection->setOrder($order);

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var PropertyValueBase $propertyValueClassName */
		$propertyValueClassName = $registry->getPropertyValueClassName();

		$props = $propertyValueClassName::loadForOrder($order);

		/** @var PropertyValueBase $prop */
		foreach ($props as $prop)
		{
			$prop->setCollection($propertyCollection);
			$propertyCollection->addItem($prop);
		}

		return $propertyCollection;
	}

	/**
	 * @return OrderBase
	 */
	protected function getEntityParent()
	{
		return $this->getOrder();
	}

	/**
	 * @param array $prop
	 * @return PropertyValueBase
	 */
	public function createItem(array $prop)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyValueBase $propertyValueClass */
		$propertyValueClass = $registry->getPropertyValueClassName();
		$property = $propertyValueClass::create($this, $prop);
		$this->addItem($property);

		return $property;
	}

	/**
	 * @param Internals\CollectableEntity $property
	 * @return Result
	 */
	public function addItem(Internals\CollectableEntity $property)
	{
		/** @var PropertyValueBase $property */
		$property = parent::addItem($property);

		$this->setAttributes($property);
		$this->addToGroupMap($property);

		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::ADD, $property);
	}

	/**
	 * @param PropertyValueBase $property
	 */
	private function addToGroupMap(PropertyValueBase $property)
	{
		$groups = $this->getGroups();

		if (isset($groups[$property->getGroupId()]))
			$this->propertyGroupMap[$property->getGroupId()][] = $property;
		else
			$this->propertyGroupMap[0][] = $property;
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return Result
	 */
	public function deleteItem($index)
	{
		$oldItem = parent::deleteItem($index);

		/** @var OrderBase $order */
		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::DELETE, $oldItem);
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		if (!$item instanceof PropertyValueBase)
			throw  new Main\NotSupportedException();

		$this->setAttributes($item);

		/** @var OrderBase $order */
		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 */
	public function onOrderModify($name, $oldValue, $value)
	{
		return new Result();
	}

	/**
	 * @return OrderBase
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @param OrderBase $order
	 */
	public function setOrder(OrderBase $order)
	{
		$this->order = $order;
	}

	/**
	 * @return static
	 */
	private static function createPropertyValueCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueCollectionClassName = $registry->getPropertyValueCollectionClassName();

		return new $propertyValueCollectionClassName();
	}

	/**
	 * @param PropertyValueBase $propValue
	 */
	private function setAttributes(PropertyValueBase $propValue)
	{
		$prop = $propValue->getProperty();
		foreach ($this->attributes as $k => $v)
		{
			if ($prop[$k] == 'Y')
				$this->attributes[$k] = $propValue;
		}
	}

	/**
	 * @param $name
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getAttribute($name)
	{
		if (!array_key_exists($name, $this->attributes))
			throw new ArgumentOutOfRangeException("name");

		if ($this->attributes[$name] !== null)
			return $this->attributes[$name];

		return null;
	}

	/**
	 * @return PropertyValueBase
	 */
	function getUserEmail()
	{
		return $this->getAttribute('IS_EMAIL');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getPayerName()
	{
		return $this->getAttribute('IS_PAYER');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getDeliveryLocation()
	{
		return $this->getAttribute('IS_LOCATION');
	}

	/**
	 * @return PropertyValueBase
	 */
	function getTaxLocation()
	{
		return $this->getAttribute('IS_LOCATION4TAX');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getProfileName()
	{
		return $this->getAttribute('IS_PROFILE_NAME');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getDeliveryLocationZip()
	{
		return $this->getAttribute('IS_ZIP');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getPhone()
	{
		return $this->getAttribute('IS_PHONE');
	}


	/**
	 * @return PropertyValueBase
	 */
	function getAddress()
	{
		return $this->getAttribute('IS_ADDRESS');
	}

	/**
	 * @param $post
	 * @param $files
	 * @return Result
	 */
	function setValuesFromPost($post, $files)
	{
		$post = Input\File::getPostWithFiles($post, $files);

		$result = new Result();

		/** @var PropertyValueBase $property */
		foreach ($this->collection as $property)
		{
			$r = $property->setValueFromPost($post);
			if (!$r->isSuccess())
				$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $fields
	 * @param $files
	 * @param $skipUtils
	 *
	 * @return Result
	 */
	public function checkErrors($fields, $files, $skipUtils = false)
	{
		$fields = Input\File::getPostWithFiles($fields, $files);

		$result = new Result();

		/** @var PropertyValueBase $property */
		foreach ($this->collection as $property)
		{
			if ($skipUtils && $property->isUtil())
				continue;

			$propertyData = $property->getProperty();

			$key = isset($propertyData["ID"]) ? $propertyData["ID"] : "n".$property->getId();
			$value = isset($fields['PROPERTIES'][$key]) ? $fields['PROPERTIES'][$key] : null;

			if (!isset($fields['PROPERTIES'][$key]))
			{
				$value = $property->getValue();
			}

			$r = $property->checkValue($key, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param array $rules
	 * @param array $fields
	 *
	 * @return Result
	 */
	public function checkRequired(array $rules, array $fields)
	{
		$result = new Result();

		/** @var PropertyValueBase $property */
		foreach ($this->collection as $property)
		{
			$propertyData = $property->getProperty();

			$key = isset($propertyData["ID"]) ? $propertyData["ID"] : "n".$property->getId();

			if (!in_array($key, $rules))
			{
				continue;
			}

			$value = isset($fields['PROPERTIES'][$key]) ? $fields['PROPERTIES'][$key] : null;
			if (!isset($fields['PROPERTIES'][$key]))
			{
				$value = $property->getValue();
			}

			$r = $property->checkRequiredValue($key, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getGroups()
	{
		if (!$this->propertyGroups)
		{
			$this->propertyGroups = array(
				0 => array('NAME' => Loc::getMessage('SOP_UNKNOWN_GROUP'), 'ID' => 0)
			);

			$order = $this->getOrder();
			if ($order)
			{
				$result = OrderPropsGroupTable::getList(array(
					'select' => array('ID', 'NAME', 'PERSON_TYPE_ID', 'SORT'),
					'filter' => array('=PERSON_TYPE_ID' => $order->getPersonTypeId()),
					'order' => array('SORT' => 'ASC'),
				));

				while ($row = $result->fetch())
					$this->propertyGroups[$row['ID']] = $row;
			}
		}

		return $this->propertyGroups;
	}

	/**
	 * @param $groupId
	 * @return mixed
	 */
	public function getGroupProperties($groupId)
	{
		return $this->propertyGroupMap[$groupId];
	}

	/**
	 * @return array
	 */
	function getArray()
	{
		$groups = $this->getGroups();

		$properties = array();

		/** @var PropertyValueBase $property */
		foreach ($this->collection as $k => $property)
		{
			$p = $property->getProperty();

			if (!isset($p["ID"]))
				$p["ID"] = "n".$property->getId();

			$value = $property->getValue();

			$value = $property->getValueId() ? $value : ($value ? $value : $p['DEFAULT_VALUE']);

			$value = array_values(Input\Manager::asMultiple($p, $value));

			$p['VALUE'] = $value;

			$properties[] = $p;
		}

		return array('groups' => $groups, 'properties' => $properties);
	}

	/**
	 * @param $orderPropertyId
	 * @return PropertyValueBase
	 */
	public function getItemByOrderPropertyId($orderPropertyId)
	{
		/** @var PropertyValueBase $property */
		foreach ($this->collection as $k => $property)
		{
			if ($property->getField('ORDER_PROPS_ID') == $orderPropertyId)
				return $property;
		}

		return null;
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var EntityMarker $entityMarker */
		$entityMarker = $registry->getEntityMarkerClassName();

		/** @var OrderBase $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}
		$entityMarker::deleteByFilter(
			array(
				"ORDER_ID" => $order->getId(),
				"ENTITY_TYPE" => $entityMarker::ENTITY_TYPE_PROPERTY_VALUE
			)
		);

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$r = $propertyValue->checkValue($propertyValue->getPropertyId(),$propertyValue->getValue());

			if (!$r->isSuccess() && (int)$propertyValue->getId() > 0)
			{
				$result->addWarnings($r->getWarnings());

				$entityMarker::addMarker($order, $propertyValue, $r);
				$order->setField('MARKED', 'Y');
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		$itemsFromDb = $this->getOriginalItemsValues();

		/** @var PropertyValue $property */
		foreach ($this->collection as $property)
		{
			$r = $property->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($itemsFromDb[$property->getId()]))
				unset($itemsFromDb[$property->getId()]);
		}

		foreach ($itemsFromDb as $k => $v)
		{
			$this->callEventOnBeforeSalePropertyValueDeleted($v);

			static::deleteInternal($k);

			$this->callEventOnSalePropertyValueDeleted($v);
		}

		return $result;
	}

	/**
	 * @param $values
	 * @throws Main\NotImplementedException
	 */
	private function callEventOnBeforeSalePropertyValueDeleted($values)
	{
		$eventClassName = $this->getItemEventName();

		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event(
			'sale',
			'On'.$eventClassName.'Deleted',
			array('VALUES' => $values)
		);

		$event->send();
	}

	/**
	 * @param $values
	 * @throws Main\NotImplementedException
	 */
	protected function callEventOnSalePropertyValueDeleted($values)
	{
		$eventClassName = $this->getItemEventName();

		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event(
			'sale',
			'OnBefore'.$eventClassName.'Deleted',
			array('VALUES' => $values)
		);

		$event->send();
	}

	/**
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	private function getOriginalItemsValues()
	{
		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$itemsFromDb = array();
		if ($order->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_ID" => $this->getOrder()->getId()),
					"select" => array("ID", "NAME", "CODE", "VALUE")
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
		}

		return $itemsFromDb;
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $primary
	 * @throws Main\NotImplementedException
	 * @return Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @internal
	 *
	 * Delete order properties.
	 *
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();

		$propertiesDataList = static::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID")
			)
		);

		while ($property = $propertiesDataList->fetch())
		{
			$r = static::deleteInternal($property['ID']);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getItemEventName()
	{
		if (self::$eventClassName === null)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var PropertyValueBase $propertyValueClassName */
			$propertyValueClassName = $registry->getPropertyValueClassName();

			self::$eventClassName = $propertyValueClassName::getEntityEventName();
		}

		return self::$eventClassName;
	}
}
