<?php

namespace Bitrix\ImOpenLines;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Bitrix\Crm\EntityManageFacility,
	\Bitrix\Crm\Activity\BindingSelector,
	\Bitrix\Crm\Integrity\ActualEntitySelector,
	\Bitrix\Crm\Automation\Trigger\OpenLineTrigger,
	\Bitrix\Crm\Integration\Channel\IMOpenLineTracker;

use \Bitrix\Im\User as ImUser;

Loc::loadMessages(__FILE__);

class Crm
{
	const FIND_BY_CODE = 'IMOL';
	const FIND_BY_NAME = 'NAME';
	const FIND_BY_EMAIL = 'EMAIL';
	const FIND_BY_PHONE = 'PHONE';

	const ENTITY_NONE = 'NONE';
	const ENTITY_LEAD = 'LEAD';
	const ENTITY_COMPANY = 'COMPANY';
	const ENTITY_CONTACT = 'CONTACT';
	const ENTITY_DEAL = 'DEAL';
	const ENTITY_ACTIVITY = 'ACTIVITY';

	const FIELDS_COMPANY = 'COMPANY_ID';
	const FIELDS_CONTACT = 'CONTACT_IDS';

	private $error = null;

	protected $facility;

	/**
	 * @return EntityManageFacility
	 */
	public function getEntityManageFacility()
	{
		if(empty($this->facility))
		{
			$facility = $this->facility = new EntityManageFacility();
		}
		else
		{
			$facility = $this->facility;
		}

		return $facility;
	}

	public function __construct()
	{
		$this->error = new Error(null, '', '');
		Loader::includeModule("crm");
	}

	public static function getSourceName($userCode, $lineTitle = '')
	{
		$parsedUserCode = Session::parseUserCode($userCode);
		$messengerType = $parsedUserCode['CONNECTOR_ID'];

		$linename = Loc::getMessage('IMOL_CRM_LINE_TYPE_'.strtoupper($messengerType));
		if (!$linename && Loader::includeModule("imconnector"))
		{
			$linename = \Bitrix\ImConnector\Connector::getNameConnector($messengerType);
		}

		return ($linename? $linename: $messengerType).($lineTitle? ' - '.$lineTitle: '');
	}

	public static function getCommunicationType($userCode)
	{
		$parsedUserCode = Session::parseUserCode($userCode);
		$messengerType = $parsedUserCode['CONNECTOR_ID'];

		if ($messengerType == 'telegrambot')
		{
			$communicationType = 'TELEGRAM';
		}
		else if ($messengerType == 'facebook')
		{
			$communicationType = 'FACEBOOK';
		}
		else if ($messengerType == 'vkgroup')
		{
			$communicationType = 'VK';
		}
		else if ($messengerType == 'network')
		{
			$communicationType = 'BITRIX24';
		}
		else if ($messengerType == 'livechat')
		{
			$communicationType = 'OPENLINE';
		}
		else if ($messengerType == 'viber')
		{
			$communicationType = 'VIBER';
		}
		else if ($messengerType == 'instagram')
		{
			$communicationType = 'INSTAGRAM';
		}
		else
		{
			$communicationType = 'IMOL';
		}
		return $communicationType;
	}

	public static function hasAccessToEntity($entityType, $entityId)
	{
		if (!$entityType || !$entityId || $entityType == 'NONE')
			return true;

		return \CCrmAuthorizationHelper::CheckReadPermission($entityType, $entityId);
	}

	/**
	 * @deprecated
	 *
	 * @param string $type
	 * @param array $params
	 * @return array|bool
	 * @throws \Bitrix\Main\LoaderException
	 *
	 * TODO: Replace method everywhere
	 */
	public function find($type = self::FIND_BY_CODE, $params = Array())
	{
		if (!Loader::includeModule('crm') || empty($params))
		{
			return false;
		}

		$facility = $this->getEntityManageFacility();
		if ($type == self::FIND_BY_CODE)
		{
			$communicationType = self::getCommunicationType($params['CODE']);
			$facility->getSelector()->appendCommunicationCriterion($communicationType, 'imol|'.$params['CODE']);
		}
		else if ($type == self::FIND_BY_NAME)
		{
			if (empty($params['LAST_NAME']) || empty($params['NAME']))
				return false;

			$facility->getSelector()->appendPersonCriterion($params['LAST_NAME'], $params['NAME']);
		}
		else if ($type == self::FIND_BY_EMAIL)
		{
			if (empty($params[$type]))
				return false;

			$facility->getSelector()->appendEmailCriterion($params[$type]);
		}
		else if ($type == self::FIND_BY_PHONE)
		{
			if (empty($params[$type]))
				return false;

			$facility->getSelector()->appendPhoneCriterion($params[$type]);
		}
		else
		{
			return false;
		}

		$facility->getSelector()->search();

		if ($facility->getPrimaryId() <= 0)
			return false;

		return Array(
			'ENTITY_TYPE' => \CCrmOwnerType::ResolveName($facility->getPrimaryTypeId()),
			'ENTITY_ID' => $facility->getPrimaryId(),
			'BINDINGS' => $facility->getActivityBindings()
		);
	}

	/**
	 * Search by crm
	 *
	 * @param array $params
	 * 		array CODE
	 * 		array NAME
	 * 		array EMAIL
	 * 		array FULL_NAME
	 * 			string LAST_NAME
	 * 			string NAME
	 * 			string SECOND_NAME
	 * 		array PHONE
	 * @return bool|array
	 * @throws \Bitrix\Main\LoaderException
	 *
	 */
	public function finds($params = Array())
	{
		$result = false;
		$filter = false;

		if (Loader::includeModule('crm') && !empty($params) && (!empty($params['CODE']) || !empty($params['FULL_NAME']) || !empty($params['EMAIL']) || !empty($params['PHONE'])))
		{
			$facility = $this->getEntityManageFacility();

			$selector = $facility->getSelector();

			if(!empty($params['CODE']))
			{
				foreach ($params['CODE'] as $code)
				{
					$communicationType = self::getCommunicationType($code);
					$selector->appendCommunicationCriterion($communicationType, 'imol|'.$params['CODE']);
					$filter = true;
				}
			}

			if(!empty($params['FULL_NAME']))
			{
				foreach ($params['FULL_NAME'] as $fullName)
				{
					if(!empty($fullName['LAST_NAME']))
					{
						$lastName = $fullName['LAST_NAME'];
						$name = '';
						$secondName = '';

						if(!empty($fullName['NAME']))
							$name = $fullName['NAME'];

						if(!empty($fullName['SECOND_NAME']))
							$secondName = $fullName['SECOND_NAME'];

						$selector->appendPersonCriterion($lastName, $name, $secondName);

						$filter = true;
					}
				}
			}

			if(!empty($params['EMAIL']))
			{
				foreach ($params['EMAIL'] as $email)
				{
					$selector->appendEmailCriterion($email);

					$filter = true;
				}
			}

			if(!empty($params['PHONE']))
			{
				foreach ($params['PHONE'] as $phone)
				{
					$selector->appendPhoneCriterion($phone);

					$filter = true;
				}
			}

			if($filter !== false)
			{
				$selector->search();

				$bindings = $facility->getActivityBindings();

				if($companyId = $selector->getCompanyId())
				{
					$result['COMPANY'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName(\CCrmOwnerType::Company),
						'ENTITY_ID' => $companyId,
						'BINDINGS' => $bindings
					);
				};

				if($contactId = $selector->getContactId())
				{
					$result['CONTACT'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName(\CCrmOwnerType::Contact),
						'ENTITY_ID' => $contactId,
						'BINDINGS' => $bindings
					);
				};

				if($leadId = $selector->getLeadId())
				{
					$result['LEAD'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName(\CCrmOwnerType::Lead),
						'ENTITY_ID' => $leadId,
						'BINDINGS' => $bindings
					);
				};

				if($returnCustomerLeadId = $selector->getReturnCustomerLeadId())
				{
					$result['RETURN_CUSTOMER_LEAD'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName(\CCrmOwnerType::Lead),
						'ENTITY_ID' => $returnCustomerLeadId,
						'BINDINGS' => $bindings
					);
				};

				if($returnDealId = $selector->getDealId())
				{
					$result['DEAL'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName(\CCrmOwnerType::Deal),
						'ENTITY_ID' => $returnDealId,
						'BINDINGS' => $bindings
					);
				};

				if($primaryId = $selector->getPrimaryId())
				{
					$result['PRIMARY'] = array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName($selector->getPrimaryTypeId()),
						'ENTITY_ID' => $primaryId,
						'BINDINGS' => $bindings
					);
				};

				$result['CAN_ADD_LEAD'] = $facility->canAddLead();
			}
		}

		return $result;
	}

	/**
	 * @param $params
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function registerLead($params)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		$configManager = new Config();
		$config = $configManager->get($params['CONFIG_ID']);

		$communicationType = self::getCommunicationType($params['USER_CODE']);

		$user = ImUser::getInstance($params['USER_ID']);
		//$comments = Loc::getMessage('IMOL_CRM_CREATE_LEAD_COMMENTS_NEW', Array(
		//	'#LINE_NAME#' => strip_tags($config['LINE_NAME']),
		//	'#CONNECTOR_NAME#' => self::getSourceName($params['USER_CODE'])
		//));

		$facility = $this->getEntityManageFacility();
		$facility->getSelector()->appendCommunicationCriterion($communicationType, 'imol|'.$params['USER_CODE']);

		if (!$user->getLastName() && !$user->getName())
		{
			$userName = $user->getFullName(false);
		}
		else
		{
			$userName = $user->getName(false);
		}

		$limitRemainder = Limit::getTrackerLimitRemainder();
		if ($limitRemainder > 0 && $params['SKIP_FIND'] != 'Y')
		{
			$searchDuplicate = false;
			if ($user->getLastName() && $user->getName())
			{
				$facility->getSelector()->appendPersonCriterion($user->getLastName(), $user->getName());
				$searchDuplicate = true;
			}

			if ($user->getEmail())
			{
				$facility->getSelector()->appendEmailCriterion($user->getEmail());
				$searchDuplicate = true;
			}

			if ($user->getPhone())
			{
				$facility->getSelector()->appendPhoneCriterion($user->getPhone());
				$searchDuplicate = true;
			}

			if ($searchDuplicate)
			{
				\Bitrix\Imopenlines\Limit::increaseTracker();
			}

		}

		$fields = array(
			'TITLE' => $params['TITLE'],
			'LAST_NAME' => $user->getLastName(false),
			'NAME' => $userName,
			'OPENED' => 'Y',
			//'COMMENTS' => $comments,
			'EMAIL_WORK' => $user->getEmail(),
			'PHONE_MOBILE' => $user->getPhone(),
			'IM_'.$communicationType => 'imol|'.$params['USER_CODE'],
		);

		if (strlen($user->getWebsite()) > 250)
		{
			$fields['SOURCE_DESCRIPTION'] = $user->getWebsite();
		}
		else
		{
			$fields['WEB_HOME'] = $user->getWebsite();
		}

		// Get CRM source
		$statuses = \CCrmStatus::GetStatusList("SOURCE");
		if (
			$config['CRM_SOURCE'] == Config::CRM_SOURCE_AUTO_CREATE ||
			!isset($statuses[$config['CRM_SOURCE']])
		)
		{
			$params['CRM_SOURCE'] = $params['CONFIG_ID'].'|'.$communicationType;

			if (!isset($statuses[$config['CRM_SOURCE']]))
			{
				$entity = new \CCrmStatus("SOURCE");
				$entity->Add(array(
					'NAME' => self::getSourceName($params['USER_CODE'], $config['LINE_NAME']),
					'STATUS_ID' => $params['CRM_SOURCE'],
					'SORT' => 115,
					'SYSTEM' => 'N'
				));
			}
			$fields['SOURCE_ID'] = $params['CRM_SOURCE'];
		}
		else
		{
			$fields['SOURCE_ID'] = $config['CRM_SOURCE'];
		}

		$fields['FM'] = \CCrmFieldMulti::PrepareFields($fields);

		$facility->getSelector()->search();

		if ((($config['CRM_CREATE'] != Config::CRM_CREATE_LEAD || $params['SKIP_CREATE'] == 'Y') && $params['SKIP_CREATE'] != 'N')
		|| ($params['MODE'] == Session::MODE_OUTPUT && !empty($facility->getActivityBindings())))
		{
			$facility->setRegisterMode($facility::REGISTER_MODE_ONLY_UPDATE);
		}

		$facility->disableAutomationRun();

		$id = $facility->registerLead($fields, true, Array(
			'CURRENT_USER' => $params['OPERATOR_ID'],
			'DISABLE_USER_FIELD_CHECK' => true
		));

		if ($id)
		{
			$parsedUserCode = Session::parseUserCode($params['USER_CODE']);
			IMOpenLineTracker::getInstance()->registerLead($id, array(
				'ORIGIN_ID' => $parsedUserCode['CONFIG_ID'],
				'COMPONENT_ID' => $parsedUserCode['CONNECTOR_ID']
			));
			Log::write($fields, 'LEAD CREATED');
		}

		if (!$facility->getPrimaryId())
		{
			return false;
		}

		$entity_type = \CCrmOwnerType::LeadName;
		$entity_id = 0;

		if($id > 0)
		{
			$entity_id = $id;
		}
		elseif($facility->getSelector()->getReturnCustomerLeadId())
		{
			$entity_id = $facility->getSelector()->getReturnCustomerLeadId();
		}
		elseif($facility->getSelector()->getLeadId())
		{
			$entity_id = $facility->getSelector()->getLeadId();
		}

		if ($entity_id > 0)
		{
			$leadCreate = $id > 0;
		}
		else
		{
			$leadCreate = $id > 0 && $facility->getPrimaryTypeId() == \CCrmOwnerType::Lead && $facility->getPrimaryId() == $id;

			$entity_type = \CCrmOwnerType::ResolveName($facility->getPrimaryTypeId());
			$entity_id = $facility->getPrimaryId();
		}

		return Array(
			'LEAD_CREATE' => $leadCreate? 'Y': 'N',
			'LEAD_ID' => $leadCreate? $id: 0,
			'ENTITY_TYPE' => $entity_type,
			'ENTITY_ID' => $entity_id,
			'BINDINGS' => $facility->getActivityBindings(),
		);
	}

	/**
	 * @deprecated
	 *
	 * @param $params
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function addLead($params)
	{
		return $this->registerLead($params);
	}

	public function get($type, $id, $withMultiFields = false)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if ($type == self::ENTITY_LEAD)
		{
			$entity = new \CCrmLead(false);
		}
		else if ($type == self::ENTITY_COMPANY)
		{
			$entity = new \CCrmCompany(false);
		}
		else if ($type == self::ENTITY_CONTACT)
		{
			$entity = new \CCrmContact(false);
		}
		else if ($type == self::ENTITY_DEAL)
		{
			$entity = new \CCrmDeal(false);
		}
		else
		{
			return false;
		}
		$data = $entity->GetByID($id, false);

		if ($withMultiFields)
		{
			$multiFields = new \CCrmFieldMulti();
			$res = $multiFields->GetList(Array(), Array(
				'ENTITY_ID' => $type,
				'ELEMENT_ID' => $id
			));
			while ($row = $res->Fetch())
			{
				$data['FM'][$row['TYPE_ID']][$row['VALUE_TYPE']][] = $row['VALUE'];
			}
		}

		$assignedId = intval($data['ASSIGNED_BY_ID']);

		if (
			Loader::includeModule('im')
			&& (
				!ImUser::getInstance($assignedId)->isActive()
				|| ImUser::getInstance($assignedId)->isAbsent()
			)
		)
		{
			$data['ASSIGNED_BY_ID'] = 0;
		}

		return $data;
	}

	public static function getDealForLead($idLead)
	{
		$result = false;

		if (Loader::includeModule('crm'))
		{
			$raw = \Bitrix\Crm\DealTable::getList(array(
				'filter'  => array('LEAD_ID' => $idLead),
				'order'   => array('ID' => 'DESC')
			));

			$result = $raw->fetch();
		}

		return $result;
	}

	public static function getLink($type, $id = null)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		$defaultValue = false;
		if (is_null($id))
		{
			$defaultValue = true;
			$id = 10000000000000000;
		}

		$link = \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::ResolveID($type), $id, false);

		if ($defaultValue)
		{
			$link = str_replace($id, '#ID#', $link);
		}


		return $link;
	}

	public function update($type, $id, $updateFields)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if ($type == self::ENTITY_LEAD)
		{
			$entity = new \CCrmLead(false);
		}
		else if ($type == self::ENTITY_COMPANY)
		{
			$entity = new \CCrmCompany(false);
		}
		else if ($type == self::ENTITY_CONTACT)
		{
			$entity = new \CCrmContact(false);
		}
		else
		{
			return false;
		}

		$updateFields['FM'] = \CCrmFieldMulti::PrepareFields($updateFields);

		$entity->Update($id, $updateFields);

		return true;
	}

	public function delete($type, $id)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if ($type == self::ENTITY_LEAD)
		{
			$entity = new \CCrmLead(false);
		}
		else if ($type == self::ENTITY_COMPANY)
		{
			$entity = new \CCrmCompany(false);
		}
		else if ($type == self::ENTITY_CONTACT)
		{
			$entity = new \CCrmContact(false);
		}
		else
		{
			return false;
		}

		$entity->Delete($id);

		return true;
	}

	public function deleteMultiField($type, $id, $fieldType, $fieldValue)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		$crmFieldMulti = new \CCrmFieldMulti();
		$ar = \CCrmFieldMulti::GetList(Array(), Array(
			'TYPE_ID' => $fieldType,
			'RAW_VALUE' => $fieldValue,
			'ENTITY_ID' => $type,
			'ELEMENT_ID' => $id,
		));
		if ($row = $ar->Fetch())
		{
			$crmFieldMulti->Delete($row['ID']);
		}

		return true;
	}

	public function addActivity($params)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		Log::write($params, 'CRM ADD ACTIVITY');

		$params['SESSION_ID'] = intval($params['SESSION_ID']);
		if ($params['SESSION_ID'] <= 0)
		{
			return false;
		}

		$session = Model\SessionTable::getById($params['SESSION_ID'])->fetch();
		if (intval($session['CRM_ACTIVITY_ID']) > 0)
		{
			Log::write($session['CRM_ACTIVITY_ID'], 'CRM ACTIVITY LOADED');
			return $session['CRM_ACTIVITY_ID'];
		}

		$parsedUserCode = Session::parseUserCode($params['USER_CODE']);
		$connectorId = $parsedUserCode['CONNECTOR_ID'];
		$lineId = $parsedUserCode['CONFIG_ID'];

		$direction = $params['MODE'] == Session::MODE_INPUT? \CCrmActivityDirection::Incoming : \CCrmActivityDirection::Outgoing;
		$arFields = array(
			'TYPE_ID' => \CCrmActivityType::Provider,
			'PROVIDER_ID' => \Bitrix\Crm\Activity\Provider\OpenLine::getId(),
			'PROVIDER_TYPE_ID' => $lineId,
			'SUBJECT' => Loc::getMessage('IMOL_CRM_CREATE_ACTIVITY_2', Array('#LEAD_NAME#' => $params['TITLE'], '#CONNECTOR_NAME#' => self::getSourceName($params['USER_CODE']))),
			'ASSOCIATED_ENTITY_ID' => $params['SESSION_ID'],
			'START_TIME' => $params['DATE_CREATE'],
			'COMPLETED' => isset($params['COMPLETED']) && $params['COMPLETED'] == 'Y'? 'Y': 'N',
			'DIRECTION' => $direction,
			'NOTIFY_TYPE' => \CCrmActivityNotifyType::None,
			'BINDINGS' => $params['CRM_BINDINGS'],
			'SETTINGS' => array(),
			'AUTHOR_ID' => isset($params['AUTHOR_ID'])? $params['AUTHOR_ID']: $params['RESPONSIBLE_ID'],
			'RESPONSIBLE_ID' => $params['RESPONSIBLE_ID'],
			'PROVIDER_PARAMS' => Array('USER_CODE' => $params['USER_CODE']),
			'ORIGIN_ID' => 'IMOL_'.$params['SESSION_ID'],

			'RESULT_STATUS' => isset($params['ANSWERED']) && $params['ANSWERED'] == 'Y'? \Bitrix\Crm\Activity\StatisticsStatus::Answered: \Bitrix\Crm\Activity\StatisticsStatus::Unanswered,
			'RESULT_MARK' => \Bitrix\Crm\Activity\StatisticsMark::None,
			'RESULT_SOURCE_ID' => $connectorId,
		);

		if (isset($params['DATE_CLOSE']))
		{
			$arFields['END_TIME'] = $params['DATE_CLOSE'];
		}
		else
		{
			$arFields['END_TIME'] = Common::getWorkTimeEnd();
		}

		if(isset($params['CRM_ENTITY_ID']) && isset($params['CRM_ENTITY_TYPE']))
		{
			$arFields['COMMUNICATIONS'] = array(
				array(
					'ID' => 0,
					'TYPE' => 'IM',
					'VALUE' => 'imol|'.$params['USER_CODE'],
					'ENTITY_ID' => $params['CRM_ENTITY_ID'],
					'ENTITY_TYPE_ID' => \CCrmOwnerType::ResolveId($params['CRM_ENTITY_TYPE'])
				)
			);
		}

		$ID = \CCrmActivity::Add($arFields, false, true, array('REGISTER_SONET_EVENT' => true));

		if ($ID)
		{
			IMOpenLineTracker::getInstance()->registerActivity($ID, array('ORIGIN_ID' => $lineId, 'COMPONENT_ID' => $connectorId));

			Log::write($ID, 'CRM ACTIVITY CREATED');
		}
		else
		{
			if ($error = $GLOBALS["APPLICATION"]->GetException())
			{
				Log::write($error->GetString(), 'CRM ACTIVITY ERROR');
			}
		}

		return $ID;
	}

	/**
	 * @param $bindings
	 * @param $data
	 * @return bool
	 */
	public function executeAutomation($bindings, $data)
	{
		//Trigger crm
		$this->executeAutomationTrigger($bindings, $data);

		//run automation crm
		$this->runAutomationFacility();

		return true;
	}

	/**
	 * @return bool
	 */
	public function runAutomationFacility()
	{
		$facility = $this->getEntityManageFacility();

		if(!$facility->isAutomationRun())
		{
			$facility->runAutomation();
		}

		return true;
	}

	/**
	 * @param $bindings
	 * @param $data
	 * @return \Bitrix\Main\Result|bool
	 */
	public function executeAutomationTrigger($bindings, $data)
	{
		if (!is_array($bindings) || !is_array($data))
			return false;

		return OpenLineTrigger::execute($bindings, $data);
	}

	public function updateActivity($params)
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		Log::write($params, 'CRM UPDATE ACTIVITY');

		if (!isset($params['UPDATE']) || !is_array($params['UPDATE']))
		{
			return false;
		}

		if (isset($params['ID']))
		{
			$activity = \CCrmActivity::GetByID($params['ID'], false);
		}
		else if (isset($params['SESSION_ID']))
		{
			$activity = \CCrmActivity::GetByOriginID('IMOL_'.$params['SESSION_ID'], false);
		}
		else
		{
			return false;
		}

		if (!$activity)
		{
			return false;
		}

		if (isset($params['UPDATE']['ANSWERED']))
		{
			$params['UPDATE']['RESULT_STATUS'] = $params['UPDATE']['ANSWERED'] == 'Y'? \Bitrix\Crm\Activity\StatisticsStatus::Answered: \Bitrix\Crm\Activity\StatisticsStatus::Unanswered;
			unset($params['UPDATE']['ANSWERED']);
		}

		if (isset($params['UPDATE']['DATE_CLOSE']))
		{
			$params['UPDATE']['END_TIME'] = $params['UPDATE']['DATE_CLOSE'];
			unset($params['UPDATE']['DATE_CLOSE']);
		}

		\CCrmActivity::Update($activity['ID'], $params['UPDATE'], false, true, Array('REGISTER_SONET_EVENT' => true));

		return true;
	}

	public function getEntityCard($entityType, $entityId)
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		if (!in_array($entityType, Array(self::ENTITY_LEAD, self::ENTITY_CONTACT, self::ENTITY_COMPANY, self::ENTITY_DEAL)))
		{
			return null;
		}

		$entityData = $this->get($entityType, $entityId, true);

		$attach = new \CIMMessageParamAttach();

		$entityGrid = Array();
		if ($entityType == self::ENTITY_LEAD)
		{
			if (isset($entityData['TITLE']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['TITLE'],
					'LINK' => self::getLink($entityType, $entityData['ID']),
				));
			}

			if (!empty($entityData['FULL_NAME']) && strpos($entityData['TITLE'], $entityData['FULL_NAME']) === false)
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_FULL_NAME'), 'VALUE' => $entityData['FULL_NAME']);
			}
			if (!empty($entityData['COMPANY_TITLE']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_COMPANY_TITLE'), 'VALUE' => $entityData['COMPANY_TITLE']);
			}
			if (!empty($entityData['POST']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_POST'), 'VALUE' => $entityData['POST']);
			}

		}
		else if ($entityType == self::ENTITY_CONTACT)
		{
			if (isset($entityData['FULL_NAME']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['FULL_NAME'],
					'LINK' => self::getLink($entityType, $entityData['ID']),
				));
			}

			if (!empty($entityData['POST']))
			{
				$entityGrid[] = Array('DISPLAY' => 'COLUMN', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_POST'), 'VALUE' => $entityData['POST']);
			}
		}
		else if ($entityType == self::ENTITY_COMPANY || $entityType == self::ENTITY_DEAL)
		{
			if (isset($entityData['TITLE']))
			{
				$attach->AddLink(Array(
					'NAME' => $entityData['TITLE'],
					'LINK' => self::getLink($entityType, $entityData['ID']),
				));
			}
		}

		if ($entityData['HAS_PHONE'] == 'Y' && isset($entityData['FM']['PHONE']))
		{
			$fields = Array();
			foreach ($entityData['FM']['PHONE'] as $phones)
			{
				foreach ($phones as $phone)
				{
					$fields[] = $phone;
				}
			}
			$entityGrid[] = Array('DISPLAY' => 'LINE', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_PHONE'), 'VALUE' => implode('[br]', $fields), 'HEIGHT' => '20');
		}
		if ($entityData['HAS_EMAIL'] == 'Y' && $entityData['FM']['EMAIL'])
		{
			$fields = Array();
			foreach ($entityData['FM']['EMAIL'] as $emails)
			{
				foreach ($emails as $email)
				{
					$fields[] = $email;
				}
			}
			$entityGrid[] = Array('DISPLAY' => 'LINE', 'NAME' => Loc::getMessage('IMOL_CRM_CARD_EMAIL'), 'VALUE' => implode('[br]', $fields), 'HEIGHT' => '20');
		}
		$attach->AddGrid($entityGrid);

		return $attach;
	}

	public static function getEntityCaption($type, $id)
    {
        if(!Loader::includeModule('crm'))
            return '';

        return \CCrmOwnerType::GetCaption(\CCrmOwnerType::ResolveID($type), $id, false);
    }

	public function getError()
	{
		return $this->error;
	}
}
