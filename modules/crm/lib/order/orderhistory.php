<?php

namespace Bitrix\Crm\Order;

use Bitrix\Sale;
use Bitrix\Main;

if (!Main\Loader::includeModule('sale'))
{
	return;
}

/**
 * Class OrderHistory
 * @package Bitrix\Crm\Order
 */
class OrderHistory extends Sale\OrderHistory
{
	/**
	 * @param $entityName
	 * @param $orderId
	 * @param $type
	 * @param null $id
	 * @param null|Entity $entity
	 * @param array $data
	 */
	protected static function addRecord($entityName, $orderId, $type, $id = null, $entity = null, array $data = array())
	{
		global $USER;

		if ($entity !== null
			&& ($operationType = static::getOperationType($entityName, $type))
			&& (!empty($operationType["DATA_FIELDS"]) && is_array($operationType["DATA_FIELDS"])))
		{
			foreach ($operationType["DATA_FIELDS"] as $fieldName)
			{
				if (!array_key_exists($fieldName, $data) && ($value = $entity->getField($fieldName)))
				{
					$data[$fieldName] = TruncateText($value, 128);
				}

			}
		}

		$userId = (is_object($USER)) ? intval($USER->GetID()) : 0;

		$fields = array(
			"ORDER_ID" => intval($orderId),
			"TYPE" => $type,
			"DATA" => (is_array($data) ? serialize($data) : $data),
			"USER_ID" => $userId,
			"ENTITY" => $entityName,
			"ENTITY_ID" => $id,
		);

		static::addInternal($fields);

		if (empty($operationType))
		{
			return;
		}

		if ($entity instanceof BasketItem)
		{
			$entityType = \CCrmOwnerType::OrderName;
			$entityId = $entity->getField('ORDER_ID');
		}
		elseif ($entity instanceof Order)
		{
			$entityType = \CCrmOwnerType::OrderName;
			$entityId = $entity->getId();
		}
		elseif ($entity instanceof ShipmentItem)
		{
			$entityType = \CCrmOwnerType::OrderShipmentName;
			$entityId = $entity->getField('ORDER_DELIVERY_ID');
			$basketItem = $entity->getBasketItem();
			if ($basketItem)
			{
				$data['NAME'] = $basketItem->getField('NAME');
				$data['PRODUCT_ID'] = $basketItem->getField('PRODUCT_ID');
			}
		}
		elseif ($entity instanceof Payment)
		{
			$entityType = \CCrmOwnerType::OrderPaymentName;
			$entityId = $entity->getId();
		}
		else
		{
			return;
		}

		$orderChange = new \CSaleOrderChange();
		$operationResult = $orderChange->GetRecordDescription($type, serialize($data));

		$event = new \CCrmEvent();
		$crmEventData = [
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'EVENT_TYPE' => \CCrmEvent::TYPE_CHANGE,
			'USER_ID' => $userId,
			'ENTITY_FIELD' => is_array($operationType['TRIGGER_FIELDS']) ? current($operationType['TRIGGER_FIELDS']) : "",
			'EVENT_NAME' => $operationResult['NAME'],
			'EVENT_TEXT_1' => $operationResult['INFO']
		];

		$event->Add($crmEventData, false);
	}
}