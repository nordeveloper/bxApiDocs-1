<?php
namespace Bitrix\Crm\Recurring\Entity;

use Bitrix\Main,
	Bitrix\Main\Result,
	Bitrix\Main\Type\Date,
	Bitrix\Main\Localization\Loc,
	Bitrix\Crm\DealRecurTable,
	Bitrix\Crm\Automation,
	Bitrix\Crm\Timeline\DealRecurringController,
	Bitrix\Crm\Recurring\Calculator,
	Bitrix\Crm\Recurring\DateType,
	Bitrix\Crm\Recurring\Manager;

class Deal extends Base
{
	public function getList(array $parameters = array())
	{
		return DealRecurTable::getList($parameters);
	}
	
	public function createEntity(array $dealFields, array $recurringParams)
	{
		$result = new Main\Result();
		$newDeal = new \CCrmDeal(false);
		if ((int)$dealFields['ID'] > 0)
		{
			$recurringParams['BASED_ID'] = $dealFields['ID'];
		}
		$parentDealId = $dealFields['ID'];
		unset($dealFields['ID']);
		try
		{
			$dealFields['DATE_BILL'] = new Date();
			$dealFields['IS_RECURRING'] = 'Y';
			$reCalculate = false;
			$idRecurringDeal = $newDeal->Add($dealFields, $reCalculate, array('DISABLE_TIMELINE_CREATION' => 'Y'));
			if (!$idRecurringDeal)
			{
				$result->addError(new Main\Error($newDeal->LAST_ERROR));
				return $result;
			}

			if ((int)$parentDealId > 0)
			{
				$productRows = \CCrmDeal::LoadProductRows($parentDealId);
				if (is_array($productRows) && !empty($productRows))
				{
					foreach ($productRows as &$product)
					{
						unset($product['ID'], $product['OWNER_ID']);
					}
					\CCrmDeal::SaveProductRows($idRecurringDeal, $productRows, true, true, false);
				}
			}

			$recurParams = $this->prepareDates($recurringParams);
			$recurringParams = $this->prepareActivity($recurParams);

			$recurringParams['DEAL_ID'] = $idRecurringDeal;

			$r = DealRecurTable::add($recurringParams);

			if ($r->isSuccess())
			{
				Manager::initCheckAgent(Manager::DEAL);

				DealRecurringController::getInstance()->onCreate(
					$idRecurringDeal,
					array(
						'FIELDS' => $dealFields,
						'RECURRING' => $recurringParams
					)
				);
				$recurringParams['MODIFY_BY_ID'] = $dealFields['CREATED_BY_ID'];
				DealRecurringController::getInstance()->onModify(
					$idRecurringDeal,
					$this->prepareTimelineModify($recurringParams)
				);

				$result->setData(
					array(
						"DEAL_ID" => $idRecurringDeal,
						"ID" => $r->getId()
					)
				);

				$event = new Main\Event("crm", "OnCrmRecurringEntityModify", $recurringParams);
				$event->send();
			}
			else
			{
				$result->addErrors($r->getErrors());
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	/**
	 * @param $primary
	 * @param array $data
	 *
	 * @return Main\Result
	 */
	public function update($primary, array $data)
	{
		$result = new Main\Result();

		$primary = (int)$primary;
		if ($primary <= 0)
		{
			$result->addError(new Main\Error("Wrong primary ID"));
			return $result;
		}

		$data['NEXT_EXECUTION'] = null;

		$recur = DealRecurTable::getById($primary);
		$recurData = $recur->fetch();

		if (!$recurData)
		{
			$result->addError(new Main\Error("Entity isn't recurring"));
			return $result;
		}

		$data = array_merge($recurData, $data);

		$recurringParams = $data['PARAMS'];

		if (is_array($recurringParams))
		{
			$today = new Date();

			if ($data['START_DATE'] instanceof Date)
			{
				$startDay = $today->getTimestamp() > $data['START_DATE']->getTimestamp() ? $today : $data['START_DATE'];
			}
			else
			{
				$startDay = $today;
			}

			$data['NEXT_EXECUTION'] = $this->getNextDate($recurringParams, $startDay);
		}

		$data = $this->prepareActivity($data);

		$resultUpdate = DealRecurTable::update($primary, $data);

		if ($resultUpdate->isSuccess())
		{
			$data['MODIFY_BY_ID'] = \CCrmSecurityHelper::GetCurrentUserID();
			DealRecurringController::getInstance()->onModify(
				$recurData['DEAL_ID'],
				$this->prepareTimelineModify($data, $recurData)
			);

			$event = new Main\Event("crm", "OnCrmRecurringEntityModify", $data);
			$event->send();
		}

		return $resultUpdate;
	}

	/**
	 * @param array $filter
	 * @param null $limit
	 *
	 * @return Result
	 */
	public function expose(array $filter, $limit = null)
	{
		global $USER_FIELD_MANAGER;

		$result = new Main\Result();

		$idDealsList = array();
		$recurParamsList = array();
		$newDealIds = array();

		$getParams['filter'] = $filter;
		if ((int)$limit > 0)
		{
			$getParams['limit'] = (int)$limit;
		}

		$recurring = DealRecurTable::getList($getParams);

		while ($recurData = $recurring->fetch())
		{
			$recurData['DEAL_ID'] = (int)$recurData['DEAL_ID'];
			$idDealsList[] = $recurData['DEAL_ID'];
			$recurParamsList[$recurData['DEAL_ID']] = $recurData;
		}

		if (empty($idDealsList))
		{
			return $result;
		}

		try
		{
			$newDeal = new \CCrmDeal(false);
			$today = new Date();
			$userType = new \CCrmUserType($USER_FIELD_MANAGER, \CCrmDeal::GetUserFieldEntityID());
			$idListChunks = array_chunk($idDealsList, 999);

			foreach ($idListChunks as $idList)
			{
				$products = array();
				$productRowData = \CCrmDeal::LoadProductRows($idList);

				foreach ($productRowData as $row)
				{
					$ownerId = $row['OWNER_ID'];
					unset($row['OWNER_ID'],$row['ID']);
					$products[$ownerId][] = $row;
				}

				unset($row);

				$dealsData = \CCrmDeal::GetList(
					array(),
					array(
						"=ID" => $idList,
						"CHECK_PERMISSIONS" => 'N'
					)
				);

				while ($deal = $dealsData->Fetch())
				{
					$recurData = $recurParamsList[$deal['ID']];
					$deal['IS_RECURRING'] = 'N';
					$deal['IS_NEW'] = 'Y';
					if (isset($recurData['CATEGORY_ID']))
					{
						$deal['CATEGORY_ID'] = $recurData['CATEGORY_ID'];
					}
					$deal['PRODUCT_ROWS'] = $products[$deal['ID']];
					$deal['STAGE_ID'] = \CCrmDeal::GetStartStageID($deal['CATEGORY_ID']);
					$recurParam = $recurData['PARAMS'];
					$recurringDealId = $deal['ID'];

					$userFields = $userType->GetEntityFields($recurringDealId);
					foreach($userFields as $key => $field)
					{
						if ($field["USER_TYPE"]["BASE_TYPE"] == "file" && !empty($field['VALUE']))
						{
							if (is_array($field['VALUE']))
							{
								$deal[$key] = array();
								foreach ($field['VALUE'] as $value)
								{
									$fileData = \CFile::MakeFileArray($value);
									if (is_array($fileData))
									{
										$deal[$key][] = $fileData;
									}
								}
							}
							else
							{
								$fileData = \CFile::MakeFileArray($field['VALUE']);
								if (is_array($fileData))
								{
									$deal[$key] = $fileData;
								}
								else
								{
									$deal[$key] = $field['VALUE'];
								}
							}
						}
						else
						{
							$deal[$key] = $field['VALUE'];
						}
					}

					unset($deal['ID'], $deal['DATE_CREATE']);
					$reCalculate = false;
					$resultId = $newDeal->Add($deal, $reCalculate, array(
						'DISABLE_TIMELINE_CREATION' => 'Y',
						'DISABLE_USER_FIELD_CHECK' => true
					));

					if ($resultId)
					{
						if (!empty($products[$recurringDealId]))
						{
							$newDeal::SaveProductRows($resultId, $products[$recurringDealId], true, true, false);
						}

						$productRowSettings = \CCrmProductRow::LoadSettings('D', $recurringDealId);
						if (!empty($productRowSettings))
							\CCrmProductRow::SaveSettings('D', $resultId, $productRowSettings);

						\CCrmBizProcHelper::AutoStartWorkflows(
							\CCrmOwnerType::Deal,
							$resultId,
							\CCrmBizProcEventType::Create,
							$arErrors
						);

						Automation\Factory::runOnAdd(\CCrmOwnerType::Deal, $resultId);

						$newDealIds[] = $resultId;

						$deal['RECURRING_ID'] = $recurringDealId;
						DealRecurringController::getInstance()->onExpose(
							$resultId,
							array(
								'FIELDS' => $deal
							)
						);
						$previousRecurData = $recurData;

						$nextData = $this->getNextDate($recurParam);

						$recurData["LAST_EXECUTION"] = $today;
						$recurData["COUNTER_REPEAT"] = (int)$recurData['COUNTER_REPEAT'] + 1;
						$recurData["NEXT_EXECUTION"] = $nextData;
						$recurData = $this->prepareActivity($recurData);

						$updateData = array(
							"LAST_EXECUTION" => $recurData["LAST_EXECUTION"],
							"COUNTER_REPEAT" => $recurData["COUNTER_REPEAT"],
							"NEXT_EXECUTION" => $recurData["NEXT_EXECUTION"],
							"ACTIVE" => $recurData["ACTIVE"]
						);

						DealRecurTable::update($recurData['ID'], $updateData);

						$updateData['MODIFY_BY_ID'] = $deal['MODIFY_BY_ID'];
						DealRecurringController::getInstance()->onModify(
							$recurData['DEAL_ID'],
							$this->prepareTimelineModify($updateData, $previousRecurData)
						);

						$event = new Main\Event("crm", "OnCrmRecurringEntityModify", $updateData);
						$event->send();
					}
					else
					{
						$result->addError(new Main\Error($newDeal->LAST_ERROR));
					}
				}
			}

			unset($idListChunks, $idList);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		if (!empty($newDealIds))
		{
			$result->setData(array("ID" => $newDealIds));
		}

		return $result;
	}

	/**
	 * @param $dealId
	 * @param string $reason
	 *
	 * @throws Main\ArgumentException
	 */
	public function cancel($dealId, $reason = "")
	{
		self::deactivate($dealId);
	}

	/**
	 * @param $dealId
	 *
	 * @return Main\Result
	 */
	public function activate($dealId)
	{
		$result = new Result();

		if ((int)$dealId > 0)
		{
			$dealId = (int)$dealId;
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_WRONG_ID')));
			return $result;
		}

		$dealData = DealRecurTable::getList(
			array(
				"filter" => array("DEAL_ID" => $dealId)
			)
		);
		if ($deal = $dealData->fetch())
		{
			$recurringParams = $deal['PARAMS'];
			$deal['NEXT_EXECUTION'] = $this->getNextDate($recurringParams);
			$deal["COUNTER_REPEAT"] = (int)$deal["COUNTER_REPEAT"] + 1;
			$isActive = $this->isActive($deal);
			if ($isActive)
			{
				$result = DealRecurTable::update(
					$dealId,
					array(
						"ACTIVE" => 'Y',
						"NEXT_EXECUTION" => $deal['NEXT_EXECUTION'],
						"COUNTER_REPEAT" => $deal['COUNTER_REPEAT']
					)
				);
			}
			else
			{
				if ((int)$deal['COUNTER_REPEAT'] > (int)$deal['LIMIT_REPEAT'])
				{
					$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_ACTIVATE_LIMIT_REPEAT')));
				}
				else
				{
					$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_ACTIVATE_LIMIT_DATA')));
				}
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_WRONG_ID')));
		}
		return $result;
	}

	/**
	 * @param $dealId
	 *
	 * @throws Main\ArgumentException
	 */
	public function deactivate($dealId)
	{
		$dealId = (int)$dealId;
		if ($dealId <= 0)
		{
			throw new Main\ArgumentException('Wrong deal id.');
		}

		$recurringData = DealRecurTable::getList(
			array(
				"filter" => array("=DEAL_ID" => $dealId)
			)
		);

		while ($recurring = $recurringData->fetch())
		{
			$this->update(
				$recurring['ID'],
				array(
					"ACTIVE" => "N",
					"NEXT_EXECUTION" => null
				)
			);
		}
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function prepareActivity($data)
	{
		$today = new Date();
		$todayTimestamp = $today->getTimestamp();

		if ($data['NEXT_EXECUTION'] instanceof Date
			&& $todayTimestamp > $data['NEXT_EXECUTION']->getTimestamp()
		)
		{
			$data['NEXT_EXECUTION'] = null;
			$data['ACTIVE'] = "N";
			return $data;
		}
		else if ($data['NEXT_EXECUTION'] instanceof Date
			&& $data['LAST_EXECUTION'] instanceof Date
			&& $data['LAST_EXECUTION'] >= $data['NEXT_EXECUTION']->getTimestamp()
			&& $todayTimestamp >= $data['LAST_EXECUTION']->getTimestamp()
		)
		{
			$data['NEXT_EXECUTION'] = null;
			$data['ACTIVE'] = "N";
			return $data;
		}

		return parent::prepareActivity($data);
	}


	/**
	 * @param $currentFields
	 * @param $previousFields
	 *
	 * @return array
	 */
	private function prepareTimelineModify(array $currentFields, array $previousFields = array())
	{
		$preparedCurrent = array();

		if (!empty($currentFields['MODIFY_BY_ID']))
			$preparedCurrent['MODIFY_BY_ID'] = $currentFields['MODIFY_BY_ID'];

		if (!empty($currentFields['CREATED_BY_ID']))
			$preparedCurrent['CREATED_BY_ID'] = $currentFields['CREATED_BY_ID'];

		if ($currentFields["ACTIVE"] == 'Y' && $currentFields["NEXT_EXECUTION"] instanceof Main\Type\Date)
		{
			$preparedCurrent['VALUE'] = $currentFields["NEXT_EXECUTION"]->toString();

			$controllerFields = array(
				'FIELD_NAME' => "NEXT_EXECUTION",
				'CURRENT_FIELDS' => $preparedCurrent
			);

			if ($previousFields['NEXT_EXECUTION'] instanceof Main\Type\Date)
				$controllerFields['PREVIOUS_FIELDS']["VALUE"] = $previousFields['NEXT_EXECUTION']->toString();
		}
		else
		{
			$preparedCurrent['VALUE'] = $currentFields["ACTIVE"];
			$controllerFields = array(
				'FIELD_NAME' => "ACTIVE",
				'CURRENT_FIELDS' => $preparedCurrent,
				'PREVIOUS_FIELDS' => array('VALUE' => $previousFields["ACTIVE"])
			);
		}

		return $controllerFields;
	}

	/**
	 * @param array $params
	 * @param null $startDate
	 *
	 * @return Date
	 */
	protected function getNextDate(array $params, $startDate = null)
	{
		$result = array(
			"PERIOD" => (int)$params['PERIOD'] ? (int)$params['PERIOD'] : null
		);

		if (isset($params['PERIOD_DEAL']) && (int)$params['EXECUTION_TYPE'] === Manager::MULTIPLY_EXECUTION)
		{
			$result['PERIOD'] = (int)$params['PERIOD_DEAL'];

			switch($result['PERIOD'])
			{
				case Calculator::SALE_TYPE_DAY_OFFSET:
				{
					$result['INTERVAL_DAY'] = 2;
					$result['TYPE'] = DateType\Day::TYPE_ALTERNATING_DAYS;
					break;
				}
				case Calculator::SALE_TYPE_WEEK_OFFSET:
				{
					$result['PERIOD'] = Calculator::SALE_TYPE_DAY_OFFSET;
					$result['TYPE'] = DateType\Day::TYPE_ALTERNATING_DAYS;
					$result['INTERVAL_DAY'] = 8;
					break;
				}
				case Calculator::SALE_TYPE_MONTH_OFFSET:
				{
					$result['INTERVAL_MONTH'] = 1;
					$result['INTERVAL_DAY'] = date('j');
					$result['TYPE'] = DateType\Month::TYPE_DAY_OF_ALTERNATING_MONTHS;
					break;
				}
				case Calculator::SALE_TYPE_YEAR_OFFSET:
				{
					$result['TYPE'] = DateType\Year::TYPE_ALTERNATING_YEAR;
					$result['INTERVAL_YEARLY'] = 2;
					break;
				}
			}
		}
		elseif (isset($params['DEAL_TYPE_BEFORE']) && (int)$params['EXECUTION_TYPE'] === Manager::SINGLE_EXECUTION)
		{
			$result['PERIOD'] = (int)$params['DEAL_TYPE_BEFORE'];

			switch($result['PERIOD'])
			{
				case Calculator::SALE_TYPE_DAY_OFFSET:
				{
					$result['TYPE'] = DateType\Day::TYPE_A_FEW_DAYS_BEFORE;
					$result['INTERVAL_DAY'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
				case Calculator::SALE_TYPE_WEEK_OFFSET:
				{
					$result['TYPE'] = DateType\Week::TYPE_A_FEW_WEEKS_BEFORE;
					$result['INTERVAL_WEEK'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
				case Calculator::SALE_TYPE_MONTH_OFFSET:
				{
					$result['TYPE'] = DateType\Month::TYPE_A_FEW_MONTHS_BEFORE;
					$result['INTERVAL_MONTH'] = (int)$params['DEAL_COUNT_BEFORE'];
					break;
				}
			}
		}

		return parent::getNextDate($result, $startDate);
	}


	/**
	 * @return bool
	 */
	public function isAllowedExpose()
	{
		if (Main\Loader::includeModule('bitrix24'))
			return !in_array(\CBitrix24::getLicenseType(), array('project', 'tf', 'retail')) || Main\Config\Option::get('crm', 'recurring_deal_enabled', 'N') === 'Y';

		return true;
	}

}
