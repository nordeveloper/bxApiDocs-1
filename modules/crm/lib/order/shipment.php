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
 * Class Shipment
 * @package Bitrix\Crm\Order
 */
class Shipment extends Sale\Shipment
{
	/**
	 * @param $isNew
	 * @throws Main\ArgumentException
	 */
	protected function onAfterSave($isNew)
	{
		if ($isNew && !$this->isSystem())
		{
			$this->addTimelineEntryOnCreate();
		}
		elseif ($this->fields->isChanged('STATUS_ID'))
		{
			$this->addTimelineEntryOnStatusModify();
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void;
	 */
	private function addTimelineEntryOnCreate()
	{
		Crm\Timeline\OrderShipmentController::getInstance()->onCreate(
			$this->getId(),
			array('FIELDS' => $this->getFields()->getValues())
		);
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void;
	 */
	private function addTimelineEntryOnStatusModify()
	{
		$fields = $this->getFields();
		$originalValues  = $fields->getOriginalValues();

		$modifyParams = array(
			'PREVIOUS_FIELDS' => array('STATUS_ID' => $originalValues['STATUS_ID']),
			'CURRENT_FIELDS' => array('STATUS_ID' => $this->getField('STATUS_ID')),
			'ORDER_ID' => $fields['ORDER_ID']
		);

		Crm\Timeline\OrderShipmentController::getInstance()->onModify($this->getId(), $modifyParams);
	}
}