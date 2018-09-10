<?php

namespace Bitrix\Crm\Order;

use Bitrix\Crm;
use Bitrix\Sale;
use Bitrix\Main;

if (!Main\Loader::includeModule('sale'))
{
	return;
}

/**
 * Class Payment
 * @package Bitrix\Crm\Order
 */
class Payment extends Sale\Payment
{
	/**
	 * @param $isNew
	 * @throws Main\ArgumentException
	 */
	protected function onAfterSave($isNew)
	{
		if ($isNew)
		{
			$this->addTimelineEntryOnCreate();
		}

		if ($this->fields->isChanged('PAID') && $this->isPaid())
		{
			Crm\Automation\Trigger\PaymentTrigger::execute(
				[['OWNER_TYPE_ID' => \CCrmOwnerType::Order, 'OWNER_ID' => $this->getOrderId()]],
				['PAYMENT' => $this]
			);
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void;
	 */
	private function addTimelineEntryOnCreate()
	{
		Crm\Timeline\OrderPaymentController::getInstance()->onCreate(
			$this->getId(),
			array('FIELDS' => $this->getFields()->getValues())
		);
	}
}