<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 09.01.2015
 * Time: 17:41
 */

namespace Bitrix\Sale;

use	Bitrix\Sale\Internals\Input;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * Class PropertyValueBase
 * @package Bitrix\Sale
 */
abstract class PropertyValueBase extends Internals\CollectableEntity
{
	protected $property = array();
	protected $savedValue;
	protected $deletedValue;

	protected static $mapFields;

	/**
	 * @param PropertyValueCollectionBase $collection
	 * @param array $property
	 * @return static
	 */
	public static function create(PropertyValueCollectionBase $collection, array $property = array())
	{
		$propertyValue = static::createPropertyValueObject($property);
		$propertyValue->setCollection($collection);

		return $propertyValue;
	}

	/**
	 * PropertyValueBase constructor.
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @throws Main\SystemException
	 */
	protected function __construct(array $property = null, array $value = null, array $relation = null)
	{
		if (! $property && !$value)
			throw new Main\SystemException('invalid arguments', 0, __FILE__, __LINE__);

		if ($property)
		{
			if (is_array($property['SETTINGS']))
			{
				$property += $property['SETTINGS'];
				unset ($property['SETTINGS']);
			}
		}
		else
		{
			$property = array(
				'TYPE' => 'STRING',
				'PROPS_GROUP_ID' => 0,
				'NAME' => $value['NAME'],
				'CODE' => $value['CODE'],
			);
		}

		if (!$value)
		{
			$value = array(
				'ORDER_PROPS_ID' => $property['ID'],
				'NAME' => $property['NAME'],
				'CODE' => $property['CODE']
			);
		}

		if (!isset($value['VALUE']) && !empty($property['DEFAULT_VALUE']))
		{
			$value['VALUE'] = $property['DEFAULT_VALUE'];
		}

		if (!empty($relation))
			$property['RELATION'] = $relation;

		if ($value['ID'] > 0)
			$this->savedValue = $value['VALUE'];

		switch($property['TYPE'])
		{
			case 'ENUM':

				if ($propertyId = $property['ID'])
					$property['OPTIONS'] = static::loadOptions($propertyId);

				break;

			case 'FILE':

				if ($defaultValue = &$property['DEFAULT_VALUE'])
					$defaultValue = Input\File::loadInfo($defaultValue);

				if ($orderValue = &$value['VALUE'])
					$orderValue = Input\File::loadInfo($orderValue);

				break;
		}

		$this->property = $property;

		parent::__construct($value); //TODO field
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		return $this->savedValue !== $this->getValueForDB($this->getField('VALUE'));
	}

	/**
	 * @param $propertyId
	 * @return array
	 */
	public static function loadOptions($propertyId)
	{
		$options = array();

		$result = Internals\OrderPropsVariantTable::getList(array(
			'select' => array('VALUE', 'NAME'),
			'filter' => array('ORDER_PROPS_ID' => $propertyId),
			'order' => array('SORT' => 'ASC')
		));

		while ($row = $result->fetch())
			$options[$row['VALUE']] = $row['NAME'];

		return $options;
	}

	/**
	 * @param $personTypeId
	 * @param $request
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getMeaningfulValues($personTypeId, $request)
	{
		$personTypeId = intval($personTypeId);
		if ($personTypeId <= 0)
			throw new Main\ArgumentNullException("personTypeId");

		if (!is_array($request))
			throw new Main\ArgumentNullException("request");

		$result = array();

		$db = Internals\OrderPropsTable::getList(array(
			'select' => array('ID', 'IS_LOCATION', 'IS_EMAIL', 'IS_PROFILE_NAME',
				'IS_PAYER', 'IS_LOCATION4TAX', 'CODE', 'IS_ZIP', 'IS_PHONE', 'IS_ADDRESS',
			),
			'filter' => array(
				'ACTIVE' => 'Y',
				'UTIL' => 'N',
				'PERSON_TYPE_ID' => $personTypeId
			)
		));
		while ($row = $db->fetch())
		{
			if (array_key_exists($row["ID"], $request))
			{
				foreach ($row as $key => $value)
				{
					if (($value === "Y") && (substr($key, 0, 3) === "IS_"))
					{
						$result[substr($key, 3)] = $request[$row["ID"]];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $value
	 */
	public function setValue($value)
	{
		if ($value && $this->property['TYPE'] == 'FILE')
			$value = Input\File::loadInfo($value);

		if ($this->property['TYPE'] == "STRING")
		{
			if ($this->property['IS_EMAIL'] === "Y" && !empty($value))
			{
				$value = trim((string)$value);
			}

			if (Input\StringInput::isMultiple($value))
			{
				$fields = $this->getFields();
				$baseValuesData = $fields->getValues();
				$baseValues = null;
				if (!empty($baseValuesData['VALUE']) && is_array($baseValuesData['VALUE']))
				{
					$baseValues = array_values($baseValuesData['VALUE']);
				}
				foreach ($value as $key => $data)
				{
					if (Input\StringInput::isDeletedSingle($data))
					{
						$this->deletedValue[] = $key;
						if (is_array($baseValues) && array_key_exists($key, $baseValues))
						{
							$value[$key] = $baseValues[$key];
						}
						else
						{
							$value[$key] = '';
						}

					}
				}
			}
		}

		$this->setField('VALUE', $value);
	}

	/**
	 * @param $value
	 * @return array|mixed|null
	 */
	private function getValueForDB($value)
	{
		$property = $this->property;

		if ($property['TYPE'] == 'FILE')
		{
			$value = Input\File::asMultiple($value);

			foreach ($value as $i => $file)
			{
				if (Input\File::isDeletedSingle($file))
				{
					unset($value[$i]);
				}
				else
				{
					if (Input\File::isUploadedSingle($file)
						&& ($fileId = \CFile::SaveFile(array('MODULE_ID' => 'sale') + $file, 'sale/order/properties'))
						&& is_numeric($fileId))
					{
						$file = $fileId;
					}

					$value[$i] = Input\File::loadInfoSingle($file);
				}
			}

			$this->fields->set('VALUE', $value);
			$value = Input\File::getValue($property, $value);

			foreach (
				array_diff(
					Input\File::asMultiple(Input\File::getValue($property, $this->savedValue         )),
					Input\File::asMultiple(                                $value                     ),
					Input\File::asMultiple(Input\File::getValue($property, $property['DEFAULT_VALUE']))
				)
				as $fileId)
			{
				\CFile::Delete($fileId);
			}
		}
		elseif($property['TYPE'] == 'STRING')
		{
			if (!empty($this->deletedValue) && is_array($this->deletedValue))
			{
				if (!empty($value) && is_array($value))
				{
					foreach ($value as $i => $string)
					{
						if (in_array($i, $this->deletedValue))
						{
							unset($value[$i]);
							unset($this->deletedValue[$i]);
						}
					}
				}
			}
		}

		return $value;
	}

	/** @return Result */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
			return $result;

		if ($this->getId() > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$this->callEventOnPropertyValueEntitySaved();

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function update()
	{
		$result = new Result();

		$value = self::getValueForDB($this->fields->get('VALUE'));

		$r = static::updateInternal($this->getId(), array('VALUE' => $value));
		if ($r->isSuccess())
			$this->savedValue = $value;
		else
			$result->addErrors($r->getErrors());

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function add()
	{
		$result = new Result();

		$value = self::getValueForDB($this->fields->get('VALUE'));
		$property = $this->property;

		$r = static::addInternal(
			array(
				'ORDER_ID' => $this->getParentOrderId(),
				'ORDER_PROPS_ID' => $property['ID'],
				'NAME' => $property['NAME'],
				'VALUE' => $value,
				'CODE' => $property['CODE'],
			)
		);
		if ($r->isSuccess())
		{
			$this->savedValue = $value;
			$this->setFieldNoDemand('ID', $r->getId());
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return void
	 */
	private function callEventOnPropertyValueEntitySaved()
	{
		$eventName = static::getEntityEventName();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'On'.$eventName.'EntitySaved', array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		));

		$event->send();
	}

	/**
	 * @param array $post
	 * @return Result
	 */
	function setValueFromPost(array $post)
	{
		$result = new Result();
		$property = $this->property;

		$key = isset($property["ID"]) ? $property["ID"] : "n".$this->getId();

		if (is_array($post['PROPERTIES']) && array_key_exists($key, $post['PROPERTIES']))
		{
			$this->setValue($post['PROPERTIES'][$key]);
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return Result
	 */
	public function checkValue($key, $value)
	{
		static $errorsList = array();
		$result = new Result();
		$property = $this->getProperty();

		if ($property['TYPE'] == "STRING" && ((int)$property['MAXLENGTH'] <= 0))
		{
			$property['MAXLENGTH'] = 500;
		}
		$error = Input\Manager::getError($property, $value);

		if (!is_array($error))
			$error = array($error);

		foreach ($error as &$message)
		{
			$message = Loc::getMessage(
				"SALE_PROPERTY_ERROR",
				array("#PROPERTY_NAME#" => $property['NAME'], "#ERROR_MESSAGE#" => $message)
			);
		}

		if (!is_array($value) && $property['IS_EMAIL'] == 'Y' && trim($value) !== '' && !check_email(trim($value), true))
		{
			$error['EMAIL'] = str_replace(
				array("#EMAIL#", "#NAME#"),
				array(htmlspecialcharsbx($value), htmlspecialcharsbx($property['NAME'])),
				Loc::getMessage("SALE_GOPE_WRONG_EMAIL")
			);
		}

		foreach ($error as $e)
		{
			if (!empty($e) && is_array($e))
			{
				foreach ($e as $errorMsg)
				{
					if (isset($errorsList[$property['ID']]) && in_array($errorMsg, $errorsList[$property['ID']]))
						continue;

					$result->addError(new ResultError($errorMsg, "PROPERTIES[$key]"));
					$result->addError(new ResultWarning($errorMsg, "PROPERTIES[$key]"));

					$errorsList[$property['ID']][] = $errorMsg;
				}
			}
			else
			{
				if (isset($errorsList[$property['ID']]) && in_array($e, $errorsList[$property['ID']]))
					continue;

				$result->addError(new ResultError($e, "PROPERTIES[$key]"));
				$result->addError(new ResultWarning($e, "PROPERTIES[$key]"));

				$errorsList[$property['ID']][] = $e;
			}
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkRequiredValue($key, $value)
	{
		static $errorsList = array();
		$result = new Result();
		$property = $this->getProperty();

		$error = Input\Manager::getRequiredError($property, $value);

		foreach ($error as $e)
		{
			if (!empty($e) && is_array($e))
			{
				foreach ($e as $errorMsg)
				{
					if (isset($errorsList[$property['ID']]) && in_array($errorMsg, $errorsList[$property['ID']]))
						continue;

					$result->addError(new ResultError($property['NAME'].' '.$errorMsg, "PROPERTIES[".$key."]"));

					$errorsList[$property['ID']][] = $errorMsg;
				}
			}
			else
			{
				if (isset($errorsList[$property['ID']]) && in_array($e, $errorsList[$property['ID']]))
					continue;

				$result->addError(new ResultError($property['NAME'].' '.$e, "PROPERTIES[$key]"));

				$errorsList[$property['ID']][] = $e;
			}
		}
		return $result;
	}

	/**
	 * @return int
	 */
	private function getParentOrderId()
	{
		/** @var PropertyValueCollectionBase $collection */
		$collection = $this->getCollection();
		$order = $collection->getOrder();

		return $order->getId();
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array('VALUE');
	}

	/**
	 * @return array
	 */
	public static function getMeaningfulFields()
	{
		return array();
	}

	/**
	 * @return array
	 */
	function getProperty()
	{
		return $this->property;
	}

	/**
	 * @return string
	 */
	function getViewHtml()
	{
		return Input\Manager::getViewHtml($this->property, $this->getValue());
	}

	/**
	 * @return string
	 */
	function getEditHtml()
	{
		$key = isset($this->property["ID"]) ? $this->property["ID"] : "n".$this->getId();
		return Input\Manager::getEditHtml("PROPERTIES[".$key."]", $this->property, $this->getValue());
	}

	/**
	 * @return null|string
	 */
	function getValue()
	{
		return $this->getField("VALUE");
	}

	/**
	 * @return null|string
	 */
	function getValueId()
	{
		return $this->getField('ID');
	}

	/**
	 * @return mixed
	 */
	function getPropertyId()
	{
		return $this->property['ID'];
	}

	/**
	 * @return mixed
	 */
	function getPersonTypeId()
	{
		return $this->property['PERSON_TYPE_ID'];
	}

	/**
	 * @return mixed
	 */
	function getGroupId()
	{
		return $this->property['PROPS_GROUP_ID'];
	}

	/**
	 * @return mixed
	 */
	function getName()
	{
		return $this->property['NAME'];
	}

	/**
	 * @return mixed
	 */
	function getRelations()
	{
		return $this->property['RELATION'];
	}

	/**
	 * @return mixed
	 */
	function getDescription()
	{
		return $this->property['DESCRIPTION'];
	}

	/**
	 * @return mixed
	 */
	function getType()
	{
		return $this->property['TYPE'];
	}

	/**
	 * @return bool
	 */
	function isRequired()
	{
		return $this->property['REQUIRED'] === 'Y';
	}

	/**
	 * @return bool
	 */
	function isUtil()
	{
		return $this->property['UTIL'] === 'Y';
	}

	/**
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @return static
	 */
	protected static function createPropertyValueObject(array $property = null, array $value = null, array $relation = null)
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueClassName = $registry->getPropertyValueClassName();

		return new $propertyValueClassName($property, $value, $relation);
	}

	/**
	 * @param OrderBase $order
	 * @return array
	 */
	public static function loadForOrder(OrderBase $order)
	{
		$objects = array();

		$propertyValues = array();
		$propertyValuesMap = array();
		$properties = array();

		if ($order->getId() > 0)
		{
			$result = static::getList(array(
				'select' => array('ID', 'NAME', 'VALUE', 'CODE', 'ORDER_PROPS_ID'),
				'filter' => array('ORDER_ID' => $order->getId())
			));
			while ($row = $result->fetch())
			{
				$propertyValues[$row['ID']] = $row;
				$propertyValuesMap[$row['ORDER_PROPS_ID']] = $row['ID'];
			}
		}

		$filter = array();

		if ($order->getPersonTypeId() > 0)
			$filter[] = array('=PERSON_TYPE_ID' => $order->getPersonTypeId());

		$result = Internals\OrderPropsTable::getList(array(
			'select' => array('ID', 'PERSON_TYPE_ID', 'NAME', 'TYPE', 'REQUIRED', 'DEFAULT_VALUE', 'SORT',
				'USER_PROPS', 'IS_LOCATION', 'PROPS_GROUP_ID', 'DESCRIPTION', 'IS_EMAIL', 'IS_PROFILE_NAME',
				'IS_PAYER', 'IS_LOCATION4TAX', 'IS_FILTERED', 'CODE', 'IS_ZIP', 'IS_PHONE', 'IS_ADDRESS',
				'ACTIVE', 'UTIL', 'INPUT_FIELD_LOCATION', 'MULTIPLE', 'SETTINGS'
			),
			'filter' => $filter,
			'order' => array('SORT' => 'ASC')
		));

		while ($row = $result->fetch())
			$properties[$row['ID']] = $row;

		$result = Internals\OrderPropsRelationTable::getList(array(
			'select' => array(
				'PROPERTY_ID', 'ENTITY_ID', 'ENTITY_TYPE'
			),
			'filter' => array(
				'PROPERTY_ID' => array_keys($properties)
			)
		));

		$propRelation = array();
		while ($row = $result->fetch())
		{
			if (empty($row))
				continue;

			if (!isset($propRelation[$row['PROPERTY_ID']]))
				$propRelation[$row['PROPERTY_ID']] = array();

			$propRelation[$row['PROPERTY_ID']][] = $row;
		}

		foreach ($properties as $property)
		{
			$id = $property['ID'];

			if (isset($propertyValuesMap[$id]))
			{
				$fields = $propertyValues[$propertyValuesMap[$id]];
				unset($propertyValues[$propertyValuesMap[$id]]);
				unset($propertyValuesMap[$id]);
			}
			else
			{
				if ($property['ACTIVE'] == 'N') // || $property['UTIL'] == 'Y')
					continue;

				$fields = null;
			}
			if (isset($propRelation[$id]))
				$objects[] = static::createPropertyValueObject($property, $fields, $propRelation[$id]);
			else
				$objects[] = static::createPropertyValueObject($property, $fields);
		}

		foreach ($propertyValues as $propertyValue)
		{
			$objects[] = static::createPropertyValueObject(null, $propertyValue);
		}

		return $objects;
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
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\AddResult
	 */
	abstract protected function addInternal(array $data);

	/**
	 * @param $primary
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\UpdateResult
	 */
	abstract protected function updateInternal($primary, array $data);

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}
}
