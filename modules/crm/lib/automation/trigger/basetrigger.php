<?php
namespace Bitrix\Crm\Automation\Trigger;

use Bitrix\Crm\Automation\Factory;
use Bitrix\Crm\EntityManageFacility;
use Bitrix\Main;

if (!Main\Loader::includeModule('bizproc'))
{
	return;
}

class BaseTrigger extends \Bitrix\Bizproc\Automation\Trigger\BaseTrigger
{
	protected $inputData;

	/**
	 * @param int $entityTypeId Target entity id
	 * @return bool
	 */
	public static function isSupported($entityTypeId)
	{
		$supported = [\CCrmOwnerType::Lead, \CCrmOwnerType::Deal, \CCrmOwnerType::Order, \CCrmOwnerType::Invoice];
		return in_array($entityTypeId, $supported, true);
	}

	public static function execute(array $bindings, array $inputData = null)
	{
		$triggersSent = false;
		$clientBindings = array();

		$result = new Main\Result();

		foreach ($bindings as $binding)
		{
			$entityTypeId = (int)$binding['OWNER_TYPE_ID'];
			$entityId = (int)$binding['OWNER_ID'];

			if (
				$binding['OWNER_TYPE_ID'] === \CCrmOwnerType::Contact
				|| $binding['OWNER_TYPE_ID'] === \CCrmOwnerType::Company
			)
			{
				$clientBindings[] = $binding;
				continue;
			}

			if (Factory::isSupported($entityTypeId))
			{
				$automationTarget = Factory::getTarget($entityTypeId, $entityId);

				$trigger = new static();
				$trigger->setTarget($automationTarget);
				if ($inputData !== null)
					$trigger->setInputData($inputData);

				$trigger->send();
				$triggersSent = true;
			}
		}

		if (!$triggersSent && $clientBindings)
		{
			$facilitySelector = (new EntityManageFacility())->getSelector();

			foreach ($clientBindings as $binding)
			{
				$facilitySelector
					->setEntity($binding['OWNER_TYPE_ID'], $binding['OWNER_ID'])
					->search();

				$documents = [];
				$dealId = $facilitySelector->getDealId();
				$orderIds = $facilitySelector->getOrders();

				if ($dealId)
				{
					$documents[] = [\CCrmOwnerType::Deal, $dealId];
				}
				foreach ($orderIds as $orderId)
				{
					$documents[] = [\CCrmOwnerType::Order, $orderId];
				}

				foreach ($documents as list($docTypeId, $docId))
				{
					$automationTarget = Factory::getTarget($docTypeId, $docId);

					$trigger = new static();
					$trigger->setTarget($automationTarget);
					if ($inputData !== null)
						$trigger->setInputData($inputData);

					$trigger->send();
					$triggersSent = true;
				}
			}
		}

		$result->setData(array('triggersSent' => $triggersSent));
		return $result;
	}

	public function setInputData($data)
	{
		$this->inputData = $data;
		return $this;
	}

	public function getInputData($key = null)
	{
		if ($key !== null)
		{
			return is_array($this->inputData) && isset($this->inputData[$key]) ? $this->inputData[$key] : null;
		}
		return $this->inputData;
	}

	public function send()
	{
		$applied = false;
		$triggers = $this->getTriggers();
		if ($triggers)
		{
			foreach ($triggers as $trigger)
			{
				if ($this->checkApplyRules($trigger))
				{
					$this->applyTrigger($trigger);
					$applied = true;
					break;
				}
			}
		}

		return $applied;
	}

	protected function getTriggers()
	{
		$triggers = array();

		$currentStatus = $this->getTarget()->getEntityStatus();
		$allStatuses = $this->getTarget()->getEntityStatuses();

		$needleKey = array_search($currentStatus, $allStatuses);

		if ($needleKey === false)
			return $triggers;

		$needleStatuses = array_slice($allStatuses, $needleKey + 1);

		if (count($needleStatuses) > 0)
		{
			$rows = array();
			$iterator = Entity\TriggerTable::getList(array(
				'filter' => array(
					'=CODE' => static::getCode(),
					'=ENTITY_TYPE_ID' => $this->getTarget()->getEntityTypeId(),
					'@ENTITY_STATUS' => $needleStatuses
				)
			));
			while ($row = $iterator->fetch())
			{
				$rows[$row['ENTITY_STATUS']][] = $row;
			}

			if ($rows)
			{
				// take only nearest to the current status
				foreach ($needleStatuses as $needleStatus)
				{
					if (isset($rows[$needleStatus]))
					{
						$triggers = array_merge($triggers, $rows[$needleStatus]);
					}
				}
			}
		}

		return $triggers;
	}

	protected function applyTrigger(array $trigger)
	{
		$statusId = $trigger['ENTITY_STATUS'];

		/** @var \Bitrix\Crm\Automation\Target\BaseTarget $target */
		$target = $this->getTarget();

		$target->setAppliedTrigger($trigger);
		$result = $target->setEntityStatus($statusId);
		if ($result !== false)
		{
			Factory::runOnStatusChanged($target->getEntityTypeId(), $target->getEntityId());
		}

		return true;
	}
}