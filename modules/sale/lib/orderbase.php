<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Currency;
use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class OrderBase
 * @package Bitrix\Sale
 */
abstract class OrderBase extends Internals\Entity
{
	/** @var Internals\Fields */
	protected $calculatedFields = null;

	/** @var BasketBase */
	protected $basketCollection;

	/** @var PropertyValueCollectionBase */
	protected $propertyCollection;

	/** @var Discount $discount */
	protected $discount = null;

	/** @var Tax $tax */
	protected $tax = null;

	/** @var int */
	protected $internalId = 0;

	/** @var bool $isNew */
	protected $isNew = true;

	/** @var bool  */
	protected $isSaveExecuting = false;

	/** @var bool $isClone */
	protected $isClone = false;

	/** @var bool $isOnlyMathAction */
	protected $isOnlyMathAction = null;

	/** @var bool $isMeaningfulField */
	protected $isMeaningfulField = false;

	/** @var bool $isStartField */
	protected $isStartField = null;

	/** @var null $eventClassName */
	protected static $eventClassName = null;


	/** @var null|string $calculateType */
	protected $calculateType = null;

	const SALE_ORDER_CALC_TYPE_NEW = 'N';
	const SALE_ORDER_CALC_TYPE_CHANGE = 'C';
	const SALE_ORDER_CALC_TYPE_REFRESH = 'R';

	/**
	 * @param array $fields				Data.
	 */
	protected function __construct(array $fields = array())
	{
		parent::__construct($fields);
		$this->isNew = (empty($fields['ID']));
	}

	/**
	 * @return int
	 */
	public function getInternalId()
	{
		static $idPool = 0;
		if ($this->internalId == 0)
		{
			$idPool++;
			$this->internalId = $idPool;
		}

		return $this->internalId;
	}

	/**
	 * @return array
	 */
	public static function getSettableFields()
	{
		$result = array(
			"LID", "PERSON_TYPE_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID",  "DEDUCTED",
			"MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED",
			"PRICE", "DISCOUNT_VALUE",
			"DATE_INSERT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "COMMENTS", "TAX_VALUE",
			"STAT_GID", "RECURRING_ID", "LOCKED_BY",
			"DATE_LOCK", "RECOUNT_FLAG", "AFFILIATE_ID", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "UPDATED_1C",
			"STORE_ID", "ORDER_TOPIC", "RESPONSIBLE_ID", "DATE_BILL", "DATE_PAY_BEFORE", "ACCOUNT_NUMBER",
			"XML_ID", "ID_1C", "VERSION_1C", "VERSION", "EXTERNAL_ORDER", "COMPANY_ID",
		);

		return array_merge($result, static::getCalculatedFields());
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		$result = array(
			"LID", "PERSON_TYPE_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID",  "DEDUCTED",
			"MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED",
			"PRICE", "CURRENCY", "DISCOUNT_VALUE", "USER_ID",
			"DATE_INSERT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "COMMENTS", "TAX_VALUE",
			"STAT_GID", "RECURRING_ID", "LOCKED_BY", "IS_RECURRING",
			"DATE_LOCK", "RECOUNT_FLAG", "AFFILIATE_ID", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "UPDATED_1C",
			"STORE_ID", "ORDER_TOPIC", "RESPONSIBLE_ID", "DATE_BILL", "DATE_PAY_BEFORE", "ACCOUNT_NUMBER",
			"XML_ID", "ID_1C", "VERSION_1C", "VERSION", "EXTERNAL_ORDER", "COMPANY_ID",
		);

		return array_merge($result, static::getCalculatedFields());
	}

	/**
	 * @return array
	 */
	public static function getCalculatedFields()
	{
		return array(
			'PRICE_WITHOUT_DISCOUNT',
			'ORDER_WEIGHT',
			'DISCOUNT_PRICE',
			'BASE_PRICE_DELIVERY',

			'DELIVERY_LOCATION',
			'DELIVERY_LOCATION_ZIP',
			'TAX_LOCATION',
			'TAX_PRICE',

			'VAT_RATE',
			'VAT_VALUE',
			'VAT_SUM',
			'VAT_DELIVERY',
			'USE_VAT',
		);
	}

	/**
	 * @return array
	 */
	public static function getMeaningfulFields()
	{
		return array('PERSON_TYPE_ID', 'PRICE');
	}

	/**
	 * @param array $fields
	 * @throws Main\NotImplementedException
	 * @return Order
	 */
	private static function createOrderObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$orderClassName = $registry->getOrderClassName();

		return new $orderClassName($fields);
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $siteId
	 * @param null $userId
	 * @param null $currency
	 * @return Order
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public static function create($siteId, $userId = null, $currency = null)
	{
		$order = static::createOrderObject();
		$order->setFieldNoDemand('LID', $siteId);
		if (intval($userId) > 0)
			$order->setFieldNoDemand('USER_ID', $userId);

		if ($currency == null)
		{
			$currency = Internals\SiteCurrencyTable::getSiteCurrency($siteId);
		}

		if ($currency == null)
		{
			$currency = Currency\CurrencyManager::getBaseCurrency();
		}

		$order->setFieldNoDemand('CURRENCY', $currency);
		$order->setFieldNoDemand('STATUS_ID', static::getInitialStatus());
		$order->setFieldNoDemand('DATE_STATUS', new Type\DateTime());

		$order->calculateType = static::SALE_ORDER_CALC_TYPE_NEW;

		return $order;
	}

	/**
	 * @param $id
	 * @return null|static
	 * @throws Main\ArgumentNullException
	 */
	public static function load($id)
	{
		if (intval($id) <= 0)
			throw new Main\ArgumentNullException("id");

		$filter = array(
			'filter' => array('ID' => $id),
			'select' => array('*'),
		);

		$list = static::loadByFilter($filter);
		if (!empty($list) && is_array($list))
		{
			return reset($list);
		}

		return null;
	}

	/**
	 * @param array $parameters
	 * @return array|null
	 * @internal param array $filter
	 */
	public static function loadByFilter(array $parameters)
	{
		$list = array();

		$parameters = static::prepareParams($parameters);

		/** @var Main\DB\Result $res */
		$res = static::loadFromDb($parameters);
		while($orderData = $res->fetch())
		{
			$order = static::createOrderObject($orderData);

			$order->calculateType = static::SALE_ORDER_CALC_TYPE_CHANGE;
			$list[] = $order;
		}

		return (!empty($list) ? $list : null);
	}

	/**
	 * @param $parameters
	 * @return array
	 */
	private static function prepareParams($parameters)
	{
		$result = array(
			'select' => array('*')
		);

		if (isset($parameters['filter']))
			$result['filter'] = $parameters['filter'];
		if (isset($parameters['limit']))
			$result['limit'] = $parameters['limit'];
		if (isset($parameters['order']))
			$result['order'] = $parameters['order'];
		if (isset($parameters['offset']))
			$result['offset'] = $parameters['offset'];

		return $result;
	}

	/**
	 * @param string $value
	 * @return null|static
	 * @throws Main\ArgumentNullException
	 */
	public static function loadByAccountNumber($value)
	{
		if (strval(trim($value)) == '')
			throw new Main\ArgumentNullException("value");

		$filter = array(
			'filter' => array('=ACCOUNT_NUMBER' => $value),
			'select' => array('*'),
		);

		$list = static::loadByFilter($filter);
		if (!empty($list) && is_array($list))
		{
			return reset($list);
		}

		return null;
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\NotImplementedException
	 */
	static protected function loadFromDb(array $parameters)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param BasketBase $basket
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function setBasket(BasketBase $basket)
	{
		if ($this->getId())
		{
			throw new Main\NotSupportedException();
		}

		$result = new Result();

		$basket->setOrder($this);
		$this->basketCollection = $basket;

		if (!$this->isMathActionOnly())
		{
			/** @var Result $r */
			$r = $basket->refreshData(array('PRICE', 'QUANTITY', 'COUPONS'));
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param BasketBase $basket
	 *
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function appendBasket(BasketBase $basket)
	{
		if ($this->getId())
		{
			throw new Main\NotSupportedException();
		}

		$basket->setOrder($this);
		$this->basketCollection = $basket;

		return new Result();
	}

	/**
	 * Return order basket.
	 *
	 * @return BasketBase
	 */
	public function getBasket()
	{
		if (!isset($this->basketCollection) || empty($this->basketCollection))
			$this->basketCollection = $this->loadBasket();

		return $this->basketCollection;
	}

	/**
	 * Return basket exists.
	 *
	 * @return bool
	 */
	public function isNotEmptyBasket()
	{
		if (!isset($this->basketCollection) || empty($this->basketCollection))
			$this->basketCollection = $this->loadBasket();
		return !empty($this->basketCollection);
	}

	/**
	 * @return BasketBase
	 */
	protected function loadBasket()
	{
		if ((int)$this->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var BasketBase $basketClassName */
			$basketClassName = $registry->getBasketClassName();

			return $basketClassName::loadItemsForOrder($this);
		}

		return null;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	public function setField($name, $value)
	{
		$priceRoundedFields = array(
			'PRICE' => 'PRICE',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'SUM_PAID' => 'SUM_PAID',
			'PRICE_PAYMENT' => 'PRICE_PAYMENT',
			'DISCOUNT_VALUE' => 'DISCOUNT_VALUE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return new Result();
		}

		$r = parent::setField($name, $value);

		if (!$r->isSuccess())
		{
			return $r;
		}

		$fields = $this->fields->getChangedValues();
		if (!empty($fields) && !array_key_exists("UPDATED_1C", $fields) && $name != 'UPDATED_1C')
		{
			parent::setField("UPDATED_1C", "N");
		}

		return $r;
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$priceRoundedFields = array(
			'PRICE' => 'PRICE',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'SUM_PAID' => 'SUM_PAID',
			'PRICE_PAYMENT' => 'PRICE_PAYMENT',
			'DISCOUNT_VALUE' => 'DISCOUNT_VALUE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return;
		}

		if (!$this->fields->isChanged("UPDATED_1C") && $name != 'UPDATED_1C')
		{
			parent::setField("UPDATED_1C", "N");
		}

		if ($this->isSaveExecuting === false)
		{
			if ($name === 'ID')
			{
				$this->isNew = false;
			}
		}

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * @param $name
	 * @return null|string
	 */
	public function getField($name)
	{
		if ($this->isCalculatedField($name))
		{
			return $this->calculatedFields->get($name);
		}

		return parent::getField($name);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initField($name, $value)
	{
		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
		}

		if ($name === 'ID')
		{
			$this->isNew = false;
		}

		parent::initField($name, $value);
	}

	/**
	 * @return PropertyValueCollectionBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function getPropertyCollection()
	{
		if(empty($this->propertyCollection))
		{
			$this->propertyCollection = $this->loadPropertyCollection();
		}

		return $this->propertyCollection;
	}

	/**
	 * @return PropertyValueCollectionBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function loadPropertyCollection()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		/** @var PropertyValueCollectionBase $propertyCollectionClassName */
		$propertyCollectionClassName = $registry->getPropertyValueCollectionClassName();

		return $propertyCollectionClassName::load($this);
	}

	/**
	 * Modify property value collection.
	 *
	 * @param string $action Action.
	 * @param PropertyValueBase $property Property.
	 * @param null|string $name Field name.
	 * @param null|string|int|float $oldValue Old value.
	 * @param null|string|int|float $value New value.
	 * @return Result
	 */
	public function onPropertyValueCollectionModify($action, PropertyValueBase $property, $name = null, $oldValue = null, $value = null)
	{
		return new Result();
	}

	/**
	 * Full refresh order data.
	 *
	 * @param array $select
	 * @return Result
	 */
	public function refreshData($select = array())
	{
		return new Result();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return (int)$this->getField('ID');
	}

	/**
	 * @return int
	 */
	public function getPersonTypeId()
	{
		return $this->getField('PERSON_TYPE_ID');
	}

	/**
	 * @param $personTypeId
	 *
	 * @return Result
	 */
	public function setPersonTypeId($personTypeId)
	{
		return $this->setField('PERSON_TYPE_ID', intval($personTypeId));
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return floatval($this->getField('PRICE'));
	}

	/**
	 * @return float
	 */
	public function getSumPaid()
	{
		return floatval($this->getField('SUM_PAID'));
	}

	/**
	 * @return float
	 */
	public function getDeliveryPrice()
	{
		return floatval($this->getField('PRICE_DELIVERY'));
	}

	/**
	 * @return float
	 */
	public function getDeliveryLocation()
	{
		return $this->getField('DELIVERY_LOCATION');
	}

	/**
	 * @return float
	 */
	public function getTaxPrice()
	{
		return floatval($this->getField('TAX_PRICE'));
	}

	/**
	 * @return float
	 */
	public function getTaxValue()
	{
		return floatval($this->getField('TAX_VALUE'));
	}

	/**
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function syncOrderTax()
	{
		$result = new Result();

		/** @var Tax $tax */
		if (!$tax = $this->getTax())
		{
			throw new Main\ObjectNotFoundException('Entity "Tax" not found');
		}

		$this->resetTax();
		/** @var Result $r */
		$r = $tax->calculate();
		if ($r->isSuccess())
		{
			$taxResult = $r->getData();
			if (isset($taxResult['TAX_PRICE']) && floatval($taxResult['TAX_PRICE']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('TAX_PRICE', $taxResult['TAX_PRICE']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if (isset($taxResult['VAT_SUM']) && floatval($taxResult['VAT_SUM']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('VAT_SUM', $taxResult['VAT_SUM']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if (isset($taxResult['VAT_DELIVERY']) && floatval($taxResult['VAT_DELIVERY']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('VAT_DELIVERY', $taxResult['VAT_DELIVERY']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			/** @var Result $r */
			$r = $this->setField('TAX_VALUE', $this->isUsedVat()? $this->getVatSum() : $this->getField('TAX_PRICE'));
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return float
	 */
	public function getDiscountPrice()
	{
		return floatval($this->getField('DISCOUNT_PRICE'));
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->getField('CURRENCY');
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->getField('USER_ID');
	}

	/**
	 * @return null|string
	 */
	public function getSiteId()
	{
		return $this->getField('LID');
	}

	/**
	 * @return bool
	 */
	public function isUsedVat()
	{
		$useVat = $this->getField('USE_VAT');
		if ($useVat === null)
		{
			$this->refreshVat();
		}

		return $this->getField('USE_VAT') === 'Y';
	}

	/**
	 * @return mixed|null
	 */
	public function getVatRate()
	{
		$vatRate = $this->getField('VAT_RATE');
		if ($vatRate === null && $this->getId() > 0)
		{
			$this->refreshVat();
			return $this->getField('VAT_RATE');
		}
		return $vatRate;
	}

	/**
	 * @return float
	 */
	public function getVatSum()
	{
		$vatSum = $this->getField('VAT_SUM');
		if ($vatSum === null && $this->getId() > 0)
		{
			$this->refreshVat();
			return $this->getField('VAT_SUM');
		}
		return $vatSum;
	}

	/**
	 * @return null|string
	 */
	public function isMarked()
	{
		return ($this->getField('MARKED') == "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function resetVat()
	{
		$this->setFieldNoDemand('USE_VAT', 'N');
		$this->setFieldNoDemand('VAT_RATE', 0);

		$this->setFieldNoDemand('VAT_SUM', 0);
		$this->setFieldNoDemand('VAT_DELIVERY', 0);
	}

	/**
	 * @internal
	 */
	public function refreshVat()
	{
		$this->resetVat();

		$vatInfo = $this->calculateVat();
		if ($vatInfo && $vatInfo['VAT_RATE'] > 0)
		{
			return $this->applyCalculatedVat($vatInfo);
		}

		return new Result();
	}

	/**
	 * @return array
	 */
	protected function calculateVat()
	{
		$result = array();

		$basket = $this->getBasket();
		if ($basket)
		{
			$result['VAT_RATE'] = $basket->getVatRate();
			$result['VAT_SUM'] = $basket->getVatSum();
		}

		return $result;
	}

	/**
	 * @param array $vatInfo
	 * @return Result
	 */
	private function applyCalculatedVat(array $vatInfo)
	{
		$result = new Result();

		/** @var Result $r */
		$r = $this->setField('USE_VAT', 'Y');
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		/** @var Result $r */
		$r = $this->setField('VAT_RATE', $vatInfo['VAT_RATE']);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		/** @var Result $r */
		$r = $this->setField('VAT_SUM', $vatInfo['VAT_SUM']);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function isShipped()
	{
		return $this->getField('DEDUCTED') === 'Y';
	}

	/**
	 * @return bool
	 */
	public function isExternal()
	{
		return $this->getField('EXTERNAL_ORDER') == "Y";
	}

	/**
	 * @param $field
	 * @return bool
	 */
	protected function isCalculatedField($field)
	{
		if ($this->calculatedFields == null )
		{
			$this->calculatedFields = new Internals\Fields();
		}

		return (in_array($field, static::getCalculatedFields()));
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	protected static function getInitialStatus()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public function save()
	{
		if ($this->isSaveExecuting)
		{
			trigger_error("Order saving in recursion", E_USER_WARNING);
		}

		$this->isSaveExecuting = true;

		$result = new Result();

		$id = $this->getId();
		$this->isNew = ($id == 0);

		$r = $this->callEventOnBeforeOrderSaved();
		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}

		$r = $this->verify();
		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}

		$r = $this->onBeforeSave();
		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if ($id > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
			if ($r->getId() > 0)
			{
				$id = $r->getId();
			}
		}

		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		$this->callEventOnSaleOrderEntitySaved();

		$r = $this->saveEntities();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		/** @var Discount $discount */
		$discount = $this->getDiscount();

		/** @var Result $r */
		$r = $discount->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->completeSaving();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$this->callEventOnSaleOrderSaved();
		$this->callDelayedEvents();

		$this->onAfterSave();

		$this->isNew = false;
		$this->isSaveExecuting = false;
		$this->clearChanged();

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function onAfterSave()
	{
		return new Result();
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	protected function callDelayedEvents()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$notifyClassName = $registry->getNotifyClassName();

		$eventList = Internals\EventsPool::getEvents($this->getInternalId());
		if ($eventList)
		{
			foreach ($eventList as $eventName => $eventData)
			{
				$event = new Main\Event('sale', $eventName, $eventData);
				$event->send();

				$notifyClassName::callNotify($this, $eventName);
			}

			Internals\EventsPool::resetEvents($this->getInternalId());
		}

		$notifyClassName::callNotify($this, EventActions::EVENT_ON_ORDER_SAVED);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	protected function completeSaving()
	{
		$result = new Result();

		$currentDateTime = new Type\DateTime();
		$updateFields = array('RUNNING' => 'N');

		$changedFields = $this->fields->getChangedValues();
		if ($this->isNew
			|| (
				$this->isChanged()
				&& !array_key_exists('DATE_UPDATE', $changedFields)
			)
		)
		{
			$updateFields['DATE_UPDATE'] = $currentDateTime;
		}

		if ($this->isNew)
		{
			$updateFields['DATE_INSERT'] = $currentDateTime;
		}

		$this->setFieldsNoDemand($updateFields);
		$r = static::updateInternal($this->getId(), $updateFields);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function add()
	{
		global $USER;

		$result = new Result();

		$currentDateTime = new Type\DateTime();
		if (!$this->getField('DATE_INSERT'))
			$this->setField('DATE_INSERT', $currentDateTime);

		if (!$this->getField('DATE_UPDATE'))
			$this->setField('DATE_UPDATE', $currentDateTime);

		$fields = $this->fields->getValues();

		if (is_object($USER) && $USER->isAuthorized())
		{
			$fields['CREATED_BY'] = $USER->getID();
			$this->setFieldNoDemand('CREATED_BY', $fields['CREATED_BY']);
		}

		if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
		{
			$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);
		}

		$fields['RUNNING'] = 'Y';

		$r = $this->addInternal($fields);
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
			return $result;
		}

		if ($resultData = $r->getData())
			$result->setData($resultData);

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		$accountNumber = $this->setAccountNumber();
		if ($accountNumber)
		{
			$this->setField('ACCOUNT_NUMBER', $accountNumber);
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function update()
	{
		$result = new Result();

		$fields = $this->fields->getChangedValues();

		if ($this->isChanged())
		{
			if (array_key_exists('DATE_UPDATE', $fields) && $fields['DATE_UPDATE'] === null)
			{
				unset($fields['DATE_UPDATE']);
			}

			$fields['VERSION'] = intval($this->getField('VERSION')) + 1;
			$this->setFieldNoDemand('VERSION', $fields['VERSION']);

			if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
			{
				$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);
			}

			$r = static::updateInternal($this->getId(), $fields);

			if (!$r->isSuccess())
			{
				$result->addWarnings($r->getErrors());
				return $result;
			}

			if ($resultData = $r->getData())
				$result->setData($resultData);
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function callEventOnSaleOrderEntitySaved()
	{
		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		if (self::$eventClassName)
		{
			$oldEntityValues = $this->fields->getOriginalValues();

			if (!empty($oldEntityValues))
			{
				$eventManager = Main\EventManager::getInstance();
				if ($eventsList = $eventManager->findEventHandlers('sale', 'On'.self::$eventClassName.'EntitySaved'))
				{
					/** @var Main\Event $event */
					$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
						'ENTITY' => $this,
						'VALUES' => $oldEntityValues,
					));
					$event->send();
				}
			}
		}
	}

	/**
	 * @return void
	 */
	protected function callEventOnSaleOrderSaved()
	{
		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_SAVED))
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_SAVED, array(
				'ENTITY' => $this,
				'IS_NEW' => $this->isNew,
				'IS_CHANGED' => $this->isChanged(),
				'VALUES' => $this->fields->getOriginalValues(),
			));
			$event->send();
		}
	}

	/**
	 * @return Result
	 */
	protected function callEventOnBeforeOrderSaved()
	{
		$result = new Result();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED, array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR'), 'SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR');
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function saveEntities()
	{
		$result = new Result();

		/** @var BasketBase $basket */
		$basket = $this->getBasket();

		/** @var Result $r */
		$r = $basket->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		/** @var Tax $tax */
		$tax = $this->getTax();

		/** @var Result $r */
		$r = $tax->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		$propertyCollection = $this->getPropertyCollection();

		/** @var Result $r */
		$r = $propertyCollection->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}



		return $result;
	}

	/**
	 * Set account number.
	 *
	 * @return mixed
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function setAccountNumber()
	{
		$accountNumber = Internals\AccountNumberGenerator::generateForOrder($this);
		if ($accountNumber !== false)
		{
			static::updateInternal($this->getId(), ['ACCOUNT_NUMBER' => $accountNumber]);
		}

		return $accountNumber;
	}

	/**
	 * @param $price
	 */
	public function setVatSum($price)
	{
		$this->setField('VAT_SUM', $price);
	}

	/**
	 * @param $price
	 */
	public function setVatDelivery($price)
	{
		$this->setField('VAT_DELIVERY', $price);
	}

	/**
	 * @return mixed
	 */
	public function getDateInsert()
	{
		return $this->getField('DATE_INSERT');
	}

	/**
	 * @return null|string
	 */
	public function getCalculateType()
	{
		return $this->calculateType;
	}

	/**
	 * Modify order field.
	 *
	 * @param string $name				Field name.
	 * @param mixed|string|int|float $oldValue			Old value.
	 * @param mixed|string|int|float $value				New value.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name == "PRICE")
		{
			/** @var Result $r */
			$r = $this->refreshVat();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}
		elseif ($name == "CURRENCY")
		{
			throw new Main\NotImplementedException('field CURRENCY');
		}
		elseif ($name == "CANCELED")
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_CANCELED, array(
				'ENTITY' => $this
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(
							Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_CANCELED_ERROR'),
							'SALE_EVENT_ON_BEFORE_ORDER_CANCELED_ERROR'
						);
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}

			$r = $this->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$this->setField('DATE_CANCELED', new Type\DateTime());

			if (is_object($USER) && $USER->isAuthorized())
				$this->setField('EMP_CANCELED_ID', $USER->getID());

			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_CANCELED,
				array('ENTITY' => $this)
			);

			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_CANCELED_SEND_MAIL,
				array('ENTITY' => $this)
			);
		}
		elseif ($name == "USER_ID")
		{
			throw new Main\NotImplementedException('field USER_ID');
		}
		elseif($name == "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Type\DateTime());

				if ($USER->isAuthorized())
					$this->setField('EMP_MARKED_ID', $USER->getID());
			}
			elseif ($value == "N")
			{
				$this->setField('REASON_MARKED', '');
			}
		}
		elseif ($name == "STATUS_ID")
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(
							Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR'),
							'SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR'
						);
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}

			$this->setField('DATE_STATUS', new Type\DateTime());

			if ($USER && $USER->isAuthorized())
				$this->setField('EMP_STATUS_ID', $USER->GetID());

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE_SEND_MAIL, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 */
	protected function onOrderModify($name, $oldValue, $value)
	{
		return new Result();
	}

	/**
	 * Modify basket.
	 *
	 * @param string $action				Action.
	 * @param BasketItemBase $basketItem		Basket item.
	 * @param null|string $name				Field name.
	 * @param null|string|int|float $oldValue		Old value.
	 * @param null|string|int|float $value			New value.
	 * @return Result
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();
		if ($action != EventActions::UPDATE)
			return $result;

		if ($name == "QUANTITY")
		{
			if ($value == 0)
			{
				/** @var Result $r */
				$r = $this->refreshVat();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

				if ($tax = $this->getTax())
				{
					$tax->resetTaxList();
				}
			}

			/** @var Result $result */
			$r = $this->refreshOrderPrice();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif ($name == "PRICE")
		{
			/** @var Result $result */
			$r = $this->refreshOrderPrice();

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif ($name == "CURRENCY")
		{
			if ($value != $this->getField("CURRENCY"))
			{
				throw new Main\NotSupportedException("CURRENCY");
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @return Result
	 */
	public function onBeforeBasketRefresh()
	{
		return new Result();
	}

	/**
	 * @internal
	 * @return Result
	 */
	public function onAfterBasketRefresh()
	{
		return new Result();
	}

	/**
	 * Get the entity of taxes
	 *
	 * @return Tax
	 */
	public function getTax()
	{
		if ($this->tax === null)
		{
			$this->tax = $this->loadTax();
		}
		return $this->tax;
	}

	/**
	 * @internal
	 * @return null|bool
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * @return Tax
	 */
	abstract protected function loadTax();

	/**
	 * Reset the value of taxes
	 *
	 * @internal
	 */
	public function resetTax()
	{
		$this->setFieldNoDemand('TAX_PRICE', 0);
		$this->setFieldNoDemand('TAX_VALUE', 0);
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (parent::isChanged())
			return true;

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			if ($propertyCollection->isChanged())
			{
				return true;
			}
		}

		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			if ($basket->isChanged())
			{
				return true;
			}

		}

		return false;
	}

	/**
	 * @internal
	 * @return void
	 */
	public function clearChanged()
	{
		parent::clearChanged();

		if ($basket = $this->getBasket())
		{
			$basket->clearChanged();
		}

		if ($property = $this->getPropertyCollection())
		{
			$property->clearChanged();
		}

	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}

	/**
	 * @return bool
	 */
	public function isPaid()
	{
		return $this->getField('PAYED') === "Y";
	}

	/**
	 * @return bool
	 */
	public function isAllowDelivery()
	{
		return $this->getField('ALLOW_DELIVERY') === "Y";
	}

	/**
	 * @return bool
	 */
	public function isCanceled()
	{
		return $this->getField('CANCELED') === "Y";
	}

	/**
	 * @return mixed
	 */
	public function getHash()
	{
		/** @var Main\Type\DateTime $dateInsert */
		$dateInsert = $this->getDateInsert()->setTimeZone(new \DateTimeZone("Europe/Moscow"));
		$timestamp = $dateInsert->getTimestamp();
		return md5(
			$this->getId().
			$timestamp.
			$this->getUserId().
			$this->getField('ACCOUNT_NUMBER')
		);
	}

	/**
	 * @return Result
	 */
	public function verify()
	{
		$result = new Result();
		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			$r = $basket->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$r = $propertyCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
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
	 * @return null|string
	 */
	public function getTaxLocation()
	{
		if (strval(($this->getField('TAX_LOCATION')) == ""))
		{
			/** @var PropertyValueCollectionBase $propertyCollection */
			$propertyCollection = $this->getPropertyCollection();

			if ($property = $propertyCollection->getTaxLocation())
			{
				$this->setField('TAX_LOCATION', $property->getValue());
			}

		}

		return $this->getField('TAX_LOCATION');
	}

	/**
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		return $this->isOnlyMathAction;
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		return $this->isMeaningfulField;
	}

	/**
	 * @return void
	 */
	public function clearStartField()
	{
		$this->isStartField = null;
		$this->isMeaningfulField = false;
	}

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		if ($this->isStartField === null)
		{
			$this->isStartField = true;
		}
		else
		{
			$this->isStartField = false;
		}

		if ($isMeaningfulField === true)
		{
			$this->isMeaningfulField = true;
		}

		return $this->isStartField;
	}

	/**
	 * @internal
	 * @param bool $value
	 */
	public function setMathActionOnly($value = false)
	{
		$this->isOnlyMathAction = $value;
	}

	/**
	 * @internal
	 *
	 * Delete order without demands.
	 *
	 * @param int $id				Order id.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public static function deleteNoDemand($id)
	{
		$result = new Result();

		if (!static::isExists($id))
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		/** @var Result $deleteResult */
		$deleteResult = static::deleteEntitiesNoDemand($id);
		if (!$deleteResult->isSuccess())
		{
			$result->addErrors($deleteResult->getErrors());
			return $result;
		}

		$r = static::deleteInternal($id);
		if (!$r->isSuccess())
			$result->addErrors($r->getErrors());

		static::deleteExternalEntities($id);

		return $result;
	}

	/**
	 * Delete order.
	 *
	 * @param int $id				Order id.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var OrderBase $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		if (!$order = $orderClassName::load($id))
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		/** @var Notify $notifyClassName */
		$notifyClassName = $registry->getNotifyClassName();
		$notifyClassName::setNotifyDisable(true);

		/** @var Result $r */
		$r = $order->setField('CANCELED', 'Y');
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		static::deleteEntities($order);

		$event = new Main\Event(
			'sale',
			EventActions::EVENT_ON_BEFORE_ORDER_DELETE,
			array('ENTITY' => $order)
		);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$return = null;
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				if ($eventResultData = $eventResult->getParameters())
				{
					if (isset($eventResultData) && $eventResultData instanceof ResultError)
					{
						/** @var ResultError $errorMsg */
						$errorMsg = $eventResultData;
					}
				}

				if (!isset($errorMsg))
					$errorMsg = new ResultError('EVENT_ORDER_DELETE_ERROR');

				$result->addError($errorMsg);
				return $result;
			}
		}

		/** @var Result $r */
		$r = $order->save();
		if ($r->isSuccess())
		{
			/** @var Main\Entity\DeleteResult $r */
			$r = static::deleteInternal($id);
			if ($r->isSuccess())
				static::deleteExternalEntities($id);
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		$notifyClassName::setNotifyDisable(false);

		$event = new Main\Event(
			'sale',
			EventActions::EVENT_ON_ORDER_DELETED,
			array('ENTITY' => $order, 'VALUE' => $r->isSuccess())
		);
		$event->send();

		$result->addData(array('ORDER' => $order));

		return $result;
	}

	/**
	 * @param OrderBase $order
	 * @return void
	 */
	protected static function deleteEntities(OrderBase $order)
	{
		/** @var BasketBase $basketCollection */
		if ($basketCollection = $order->getBasket())
		{
			/** @var BasketItemBase $basketItem */
			foreach ($basketCollection as $basketItem)
			{
				$basketItem->delete();
			}
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $order->getPropertyCollection())
		{
			/** @var PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				$property->delete();
			}
		}
	}

	/**
	 * @param $orderId
	 * @throws Main\NotImplementedException
	 * @return bool
	 */
	protected static function isExists($orderId)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $orderId
	 */
	protected static function deleteExternalEntities($orderId)
	{
		return;
	}

	/**
	 * @param $oderId
	 * @return Result
	 */
	protected static function deleteEntitiesNoDemand($oderId)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var BasketBase $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		$r = $basketClassName::deleteNoDemand($oderId);
		if (!$r->isSuccess())
			return $r;

		/** @var PropertyValueCollectionBase $propertyValueCollectionClassName */
		$propertyValueCollectionClassName = $registry->getPropertyValueCollectionClassName();
		$propertyValueCollectionClassName::deleteNoDemand($oderId);
		if (!$r->isSuccess())
			return $r;

		return new Result();
	}

	/**
	 * @return Discount
	 */
	public function getDiscount()
	{
		if ($this->discount === null)
		{
			$this->discount = $this->loadDiscount();
		}

		return $this->discount;
	}

	/**
	 * @return Discount
	 */
	abstract protected function loadDiscount();

	/**
	 * @return Result
	 */
	private function refreshOrderPrice()
	{
		return $this->setField("PRICE", $this->calculatePrice());
	}

	/**
	 * @return float
	 */
	protected function calculatePrice()
	{
		$basket = $this->getBasket();
		$taxPrice = !$this->isUsedVat() ? $this->getField('TAX_PRICE') : 0;

		return $basket->getPrice() + $taxPrice;
	}

	/**
	 * @return Result
	 */
	protected function onBeforeSave()
	{
		return new Result();
	}

	/**
	 * @param bool $hasMeaningfulField
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function doFinalAction($hasMeaningfulField = false)
	{
		$result = new Result();

		$orderInternalId = $this->getInternalId();

		$r = Internals\ActionEntity::runActions($orderInternalId);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if (!$hasMeaningfulField)
		{
			$this->clearStartField();
			return $result;
		}


		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$currentIsMathActionOnly = $this->isMathActionOnly();

		$basket = $this->getBasket();
		if ($basket)
		{
			$this->setMathActionOnly(true);

			if (self::$eventClassName === null)
			{
				self::$eventClassName = static::getEntityEventName();
			}

			if (self::$eventClassName)
			{
				$eventManager = Main\EventManager::getInstance();
				$eventsList = $eventManager->findEventHandlers('sale', 'OnBefore'.self::$eventClassName.'FinalAction');
				if (!empty($eventsList))
				{
					$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'FinalAction', array(
						'ENTITY' => $this,
						'HAS_MEANINGFUL_FIELD' => $hasMeaningfulField,
						'BASKET' => $basket,
					));
					$event->send();

					if ($event->getResults())
					{
						/** @var Main\EventResult $eventResult */
						foreach($event->getResults() as $eventResult)
						{
							if($eventResult->getType() == Main\EventResult::ERROR)
							{
								$errorMsg = new ResultError(
									Main\Localization\Loc::getMessage(
										'SALE_EVENT_ON_BEFORE_'.strtoupper(self::$eventClassName).'_FINAL_ACTION_ERROR'
									),
									'SALE_EVENT_ON_BEFORE_'.strtoupper(self::$eventClassName).'_FINAL_ACTION_ERROR'
								);

								$eventResultData = $eventResult->getParameters();
								if ($eventResultData)
								{
									if (isset($eventResultData) && $eventResultData instanceof ResultError)
									{
										/** @var ResultError $errorMsg */
										$errorMsg = $eventResultData;
									}
								}

								$result->addError($errorMsg);
							}
						}
					}
				}

				if (!$result->isSuccess())
				{
					return $result;
				}
			}

			// discount
			$discount = $this->getDiscount();
			$r = $discount->calculate();
			if (!$r->isSuccess())
			{
//				$this->clearStartField();
//				$result->addErrors($r->getErrors());
//				return $result;
			}

			if ($r->isSuccess() && ($discountData = $r->getData()) && !empty($discountData) && is_array($discountData))
			{
				/** @var Result $r */
				$r = $this->applyDiscount($discountData);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}

			/** @var Tax $tax */
			$tax = $this->getTax();
			/** @var Result $r */
			$r = $tax->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$taxResult = $r->getData();

			$taxChanged = false;
			if (isset($taxResult['TAX_PRICE']) && floatval($taxResult['TAX_PRICE']) >= 0)
			{
				if (!$this->isUsedVat())
				{
					$taxChanged = $this->getField('TAX_PRICE') !== $taxResult['TAX_PRICE'];
					if ($taxChanged)
					{
						$this->setField('TAX_PRICE', $taxResult['TAX_PRICE']);
						$this->refreshOrderPrice();
					}
				}

			}

			if (array_key_exists('VAT_SUM', $taxResult))
			{
				if ($this->isUsedVat())
				{
					$this->setField('VAT_SUM', $taxResult['VAT_SUM']);
				}
			}

			if ($taxChanged || $this->isUsedVat())
			{
				$taxValue = $this->isUsedVat()? $this->getVatSum() : $this->getField('TAX_PRICE');
				if (floatval($taxValue) != floatval($this->getField('TAX_VALUE')))
				{
					$this->setField('TAX_VALUE', floatval($taxValue));
				}
			}

		}

		if (!$currentIsMathActionOnly)
			$this->setMathActionOnly(false);

		$this->clearStartField();

		if (self::$eventClassName)
		{
			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', 'OnAfter'.self::$eventClassName.'FinalAction'))
			{
				$event = new Main\Event(
					'sale',
					'OnAfter'.self::$eventClassName.'FinalAction',
					array('ENTITY' => $this)
				);
				$event->send();
			}
		}

		return $result;
	}

	/**
	 * Apply the result of the discounts to the order.
	 * @internal
	 * @param array $data			Order data.
	 * @return Result
	 */
	public function applyDiscount(array $data)
	{
		if (!empty($data['BASKET_ITEMS']) && is_array($data['BASKET_ITEMS']))
		{
			/** @var BasketBase $basket */
			$basket = $this->getBasket();
			$basketResult = $basket->applyDiscount($data['BASKET_ITEMS']);
			if (!$basketResult->isSuccess())
				return $basketResult;
			unset($basketResult, $basket);

			$this->refreshOrderPrice();
		}

		return new Result();
	}

	/**
	 * @param $value
	 *
	 * @return Result
	 */
	protected function setReasonMarked($value)
	{
		$result = new Result();

		if (!empty($value))
		{
			$orderReasonMarked = $this->getField('REASON_MARKED');
			if (is_array($value))
			{
				$newOrderReasonMarked = '';

				foreach ($value as $err)
				{
					$newOrderReasonMarked .= (strval($newOrderReasonMarked) != '' ? "\n" : "") . $err;
				}
			}
			else
			{
				$newOrderReasonMarked = $value;
			}

			/** @var Result $r */
			$r = $this->setField('REASON_MARKED', $orderReasonMarked. (strval($orderReasonMarked) != '' ? "\n" : ""). $newOrderReasonMarked);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return OrderBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function createClone()
	{
		$cloneEntity = new \SplObjectStorage();

		/** @var OrderBase $orderClone */
		$orderClone = clone $this;
		$orderClone->isClone = true;

		/** @var Internals\Fields $fields */
		if ($fields = $this->fields)
		{
			$orderClone->fields = $fields->createClone($cloneEntity);
		}

		/** @var Internals\Fields $calculatedFields */
		if ($calculatedFields = $this->calculatedFields)
		{
			$orderClone->calculatedFields = $calculatedFields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $orderClone;
		}

		$this->cloneEntities($cloneEntity);

		return $orderClone;
	}

	/**
	 * @param \SplObjectStorage $cloneEntity
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function cloneEntities(\SplObjectStorage $cloneEntity)
	{
		if (!$cloneEntity->contains($this))
		{
			throw new Main\SystemException();
		}

		$orderClone = $cloneEntity[$this];

		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			$orderClone->basketCollection = $basket->createClone($cloneEntity);
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$orderClone->propertyCollection = $propertyCollection->createClone($cloneEntity);
		}

		if ($tax = $this->getTax())
		{
			$orderClone->tax = $tax->createClone($cloneEntity);
		}

		if ($discount = $this->getDiscount())
		{
			$orderClone->discount = $discount->createClone($cloneEntity);
		}
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
	protected static function updateInternal($primary, array $data)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $primary
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return null
	 */
	public static function getUfId()
	{
		return null;
	}
}