<?php
namespace Bitrix\Crm\Recurring\Entity;

use Bitrix\Main\Type\Date,
	Bitrix\Crm\Recurring\Calculator;

abstract class Base
{
	public function __construct(){}

	const NO_LIMITED = 'N';
	const LIMITED_BY_DATE = 'D';
	const LIMITED_BY_TIMES = 'T';

	abstract public function createEntity(array $entityFields, array $recurringParams);
	abstract public function update($primary, array $data);
	abstract public function expose(array $entityFields, $recurringParams);
	abstract public function cancel($entityId, $reason = "");
	abstract public function activate($invoiceId);
	abstract public function deactivate($invoiceId);

	/**
	 * @param array $parameters
	 *
	 * @return \Bitrix\Main\DB\Result
	 */
	abstract public function getList(array $parameters = array());

	/**
	 * Check date of next invoicing by params.
	 *
	 * @param $params
	 * @return bool
	 */
	protected function isActive(array $params)
	{
		if ($params['NEXT_EXECUTION'] instanceof Date)
		{
			$nextTimeStamp = $params['NEXT_EXECUTION']->getTimestamp();
		}
		else
		{
			return false;
		}

		$endTimeStamp = ($params['LIMIT_DATE'] instanceof Date) ? $params['LIMIT_DATE']->getTimestamp() : 0;

		if ($params['IS_LIMIT'] === static::LIMITED_BY_TIMES)
			return (int)$params['LIMIT_REPEAT'] > (int)$params['COUNTER_REPEAT'];
		elseif ($params['IS_LIMIT'] === static::LIMITED_BY_DATE)
			return $nextTimeStamp <= $endTimeStamp;

		return true;
	}

	/**
	 * @param $params
	 *
	 * @return mixed
	 */
	protected function prepareDates($params)
	{
		if (!($params['NEXT_EXECUTION'] instanceof Date))
		{
			if (!($params['START_DATE'] instanceof Date))
				$params['START_DATE'] = new Date($params['START_DATE']);
			$params['NEXT_EXECUTION'] = Calculator::getNextDate($params['PARAMS'], $params['START_DATE']);
		}

		return $params;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function prepareActivity($data)
	{
		if ($data['IS_LIMIT'] !== static::NO_LIMITED || empty($data['NEXT_EXECUTION']))
		{
			$isActive = $this->isActive($data);
			if (!$isActive)
			{
				$data['NEXT_EXECUTION'] = null;
				$data['ACTIVE'] = "N";
			}
			else
			{
				$data['ACTIVE'] = "Y";
			}
		}
		else
		{
			$data['ACTIVE'] = "Y";
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	public function isAllowedExpose()
	{
		return true;
	}

	/**
	 * @param array $params
	 * @param null $startDate
	 *
	 * @return Date
	 */
	protected function getNextDate(array $params, $startDate = null)
	{
		$params['PREPARE_PARAMS_CALCULATION'] = 'N';
		return Calculator::getNextDate($params, $startDate);
	}
}
