<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Imopenlines\Model\TrackerTable;
use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
use \Bitrix\Crm\Binding\LeadContactTable,
	\Bitrix\Crm\Binding\ContactCompanyTable;

Loc::loadMessages(__FILE__);

class Tracker
{
	const FIELD_PHONE = 'PHONE';
	const FIELD_EMAIL = 'EMAIL';
	const FIELD_IM = 'IM';
	const FIELD_ID_FM = 'FM';

	const ACTION_CREATE = 'CREATE';
	const ACTION_EXTEND = 'EXTEND';

	const MESSAGE_ERROR_CREATE = 'CREATE';
	const MESSAGE_ERROR_EXTEND = 'EXTEND';

	private $error = null;

	public function __construct()
	{
		$this->error = new Error(null, '', '');
	}

	private function checkMessage($messageText)
	{
		$result = Array(
			'PHONES' => Array(),
			'EMAILS' => Array(),
		);

		preg_match_all("/(\+)?([\d\-\(\) ]){6,}/i", $messageText, $matches);
		if ($matches)
		{
			foreach ($matches[0] as $phone)
			{
				$phoneNormalize = NormalizePhone(trim($phone), 6);
				if ($phoneNormalize)
				{
					$result['PHONES'][$phoneNormalize] = trim($phone);
				}
			}
		}

		preg_match_all("/[^\s]+@[^\s]+/i", $messageText, $matches);
		if ($matches)
		{
			foreach ($matches[0] as $email)
			{
				$email = trim($email);
				$result['EMAILS'][$email] = $email;
			}
		}

		return $result;
	}

	public function message($params)
	{
		if (!\Bitrix\Main\Loader::includeModule('crm'))
			return false;

		/* @var \Bitrix\ImOpenLines\Session $session */
		$session = $params['SESSION'];
		if (!($session instanceof \Bitrix\ImOpenLines\Session))
			return false;

		$messageOriginId = intval($params['MESSAGE']['ID']);
		$messageText = $this->prepareMessage($params['MESSAGE']['TEXT']);

		if (isset($params['MESSAGE']['ID']) && !$messageOriginId || strlen($messageText) <= 0)
			return false;

		if ($session->getConfig('CRM') != 'Y')
			return true;

		$limitRemainder = Limit::getTrackerLimitRemainder();
		if ($limitRemainder <= 0)
		{
			$this->sendLimitMessage(Array(
				'OPERATOR_ID' => $session->getData('OPERATOR_ID'),
				'CHAT_ID' => $session->getData('CHAT_ID'),
				'MESSAGE_TYPE' => self::MESSAGE_ERROR_EXTEND
			));

			return false;
		}

		$result = $this->checkMessage($messageText);
		$phones = $result['PHONES'];
		$emails = $result['EMAILS'];

		if (empty($phones) && empty($emails))
		{
			return false;
		}

		$crm = new Crm();

		$current = Array();
		$updateFm = Array();
		$updateFields = Array();
		$addLog = Array();

		if (isset($params['CRM']['ENTITY_TYPE']) && isset($params['CRM']['ENTITY_ID']))
		{
			$current['ACTION'] = self::ACTION_EXTEND;
			$current['CRM_ENTITY_TYPE'] = $params['CRM']['ENTITY_TYPE'];
			$current['CRM_ENTITY_ID'] = $params['CRM']['ENTITY_ID'];
			if(isset($params['CRM']['CRM_DEAL_ID']))
			{
				$current['CRM_DEAL_ID'] = $params['CRM']['DEAL_ID'];
			}
			else
			{
				$current['CRM_DEAL_ID'] = 0;
			}

			if (
				$session->getData('SOURCE') == Connector::TYPE_LIVECHAT &&
				\Bitrix\Im\User::getInstance($session->getData('USER_ID'))->isConnector() &&
				\Bitrix\Im\User::getInstance($session->getData('USER_ID'))->getName() == ''
			)
			{
				$current['CHANGE_NAME'] = 'Y';
			}
			$communicationType = Crm::getCommunicationType($session->getData('USER_CODE'));
			$updateFm['IM_'.$communicationType] = 'imol|'.$session->getData('USER_CODE');
			$addLog['im'] = Array(
				'ACTION' => $current['ACTION'],
				'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
				'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
				'FIELD_ID' => 'FM',
				'FIELD_TYPE' => self::FIELD_IM,
				'FIELD_VALUE' => $updateFm['IM_'.$communicationType]
			);

			$session->update(Array(
				'CRM_CREATE' => 'Y',
				'CRM' => 'Y',
				'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
				'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
				'CRM_DEAL_ID' => $current['CRM_DEAL_ID'],
			));

			$session->chat->setCrmFlag(Array(
				'ACTIVE' => 'Y',
				'ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
				'ENTITY_ID' => $current['CRM_ENTITY_ID'],
				'DEAL_ID' => $current['CRM_DEAL_ID'],
			));
		}
		else if ($session->getData('CRM') == 'Y')
		{
			$current['ACTION'] = self::ACTION_EXTEND;
			$current['CRM_ENTITY_TYPE'] = $session->getData('CRM_ENTITY_TYPE');
			$current['CRM_ENTITY_ID'] = $session->getData('CRM_ENTITY_ID');

			if($current['CRM_ENTITY_TYPE'] == Crm::ENTITY_LEAD)
			{
				$crmData = NULL;

				if(\Bitrix\Crm\Settings\LeadSettings::getCurrent()->isAutoGenRcEnabled())
				{
					$crmData = $crm->finds(array('PHONE' => $phones, 'EMAIL' => $emails));
				}

				if(!empty($crmData) && empty($crmData['DEAL']))
				{
					if(!empty($crmData['COMPANY']))
					{
						$updateFields['COMPANY_ID'] = $crmData['COMPANY']['ENTITY_ID'];

						$addLog[] = Array(
							'ACTION' => $current['ACTION'],
							'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
							'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
							'FIELD_ID' => Crm::FIELDS_COMPANY,
							'FIELD_VALUE' => $crmData['COMPANY']['ENTITY_ID']
						);
					}

					if(!empty($crmData['CONTACT']))
					{
						$contactIDs = LeadContactTable::getLeadContactIDs($current['CRM_ENTITY_ID']);

						if(!in_array($crmData['CONTACT']['ENTITY_ID'], $contactIDs))
						{
							$contactIDs[] = $crmData['CONTACT']['ENTITY_ID'];

							$addLog[] = Array(
								'ACTION' => $current['ACTION'],
								'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
								'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
								'FIELD_ID' => Crm::FIELDS_CONTACT,
								'FIELD_VALUE' => $crmData['CONTACT']['ENTITY_ID']
							);

							$updateFields['CONTACT_IDS'] = $contactIDs;
						}
					}

					if((!empty($crmData['COMPANY']) || !empty($crmData['CONTACT']['ENTITY_ID'])) && $session->getData('CRM_ACTIVITY_ID'))
					{
						$saveBindings = array();

						$bindings = \CAllCrmActivity::GetBindings($session->getData('CRM_ACTIVITY_ID'));

						foreach ($bindings as $key => $value)
						{
							unset($bindings[$key]['ID']);
						}

						if(!empty($crmData['COMPANY']))
						{
							$newBinding = array(
								"OWNER_TYPE_ID" => \CCrmOwnerType::ResolveID($crmData['COMPANY']['ENTITY_TYPE']),
								"OWNER_ID" => $crmData['COMPANY']['ENTITY_ID']
							);

							if(!in_array($newBinding, $bindings))
							{
								$saveBindings[] = $newBinding;

								$addLog[] = Array(
									'ACTION' => self::ACTION_EXTEND,
									'CRM_ENTITY_TYPE' => Crm::ENTITY_ACTIVITY,
									'CRM_ENTITY_ID' => $session->getData('CRM_ACTIVITY_ID'),
									'FIELD_ID' => $crmData['COMPANY']['ENTITY_TYPE'],
									'FIELD_VALUE' => $crmData['COMPANY']['ENTITY_ID']
								);
							}
						}

						if(!empty($crmData['CONTACT']))
						{
							$newBinding = array(
								"OWNER_TYPE_ID" => \CCrmOwnerType::ResolveID($crmData['CONTACT']['ENTITY_TYPE']),
								"OWNER_ID" => $crmData['CONTACT']['ENTITY_ID']
							);

							if(!in_array($newBinding, $bindings))
							{
								$saveBindings[] = $newBinding;

								$addLog[] = Array(
									'ACTION' => self::ACTION_EXTEND,
									'CRM_ENTITY_TYPE' => Crm::ENTITY_ACTIVITY,
									'CRM_ENTITY_ID' => $session->getData('CRM_ACTIVITY_ID'),
									'FIELD_ID' => $crmData['CONTACT']['ENTITY_TYPE'],
									'FIELD_VALUE' => $crmData['CONTACT']['ENTITY_ID']
								);
							}
						}

						if(!empty($saveBindings))
						{
							\CAllCrmActivity::SaveBindings($session->getData('CRM_ACTIVITY_ID'), array_merge($saveBindings, $bindings));
						}
					}
				}
				elseif($crmData['DEAL'])
				{
					$current['CRM_ENTITY_TYPE'] = $crmData['PRIMARY']['ENTITY_TYPE'];
					$current['CRM_ENTITY_ID'] = $crmData['PRIMARY']['ENTITY_ID'];
					$current['CRM_BINDINGS'] = $crmData['PRIMARY']['BINDINGS'];
					$current['CRM_DEAL_ID'] = $crmData['DEAL']['ENTITY_ID'];

					$session->update(Array(
						'CRM_CREATE' => 'Y',
						'CRM' => 'Y',
						'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
						'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
						'CRM_DEAL_ID' => $current['CRM_DEAL_ID'],
					));

					$session->chat->setCrmFlag(Array(
						'ACTIVE' => 'Y',
						'ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
						'ENTITY_ID' => $current['CRM_ENTITY_ID'],
						'DEAL_ID' => $current['CRM_DEAL_ID'],
					));

					$crm->updateActivity(Array(
						'ID' => $session->getData('CRM_ACTIVITY_ID'),
						'UPDATE' => Array(
							'OWNER_TYPE_ID' => $current['CRM_ENTITY_TYPE'],
							'OWNER_ID' => $current['CRM_ENTITY_ID'],
							'BINDINGS' => $current['CRM_BINDINGS'],
						)
					));

					$communicationType = Crm::getCommunicationType($session->getData('USER_CODE'));
					$updateFm['IM_'.$communicationType] = 'imol|'.$session->getData('USER_CODE');
					$addLog['im'] = Array(
						'ACTION' => $current['ACTION'],
						'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
						'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
						'FIELD_ID' => self::FIELD_ID_FM,
						'FIELD_TYPE' => self::FIELD_IM,
						'FIELD_VALUE' => $updateFm['IM_'.$communicationType]
					);
				}
			}
		}
		else
		{
			$crmData = false;

			$saveBindings = array();

			$crmDataSet = $crm->finds(array('PHONE' => $phones, 'EMAIL' => $emails));

			if(!$crmDataSet['CAN_ADD_LEAD'])
			{
				if(!empty($crmDataSet['LEAD']))
				{
					$crmData = $crmDataSet['LEAD'];
				}
				elseif(!empty($crmDataSet['RETURN_CUSTOMER_LEAD']))
				{
					$crmData = $crmDataSet['RETURN_CUSTOMER_LEAD'];
				}
				else
				{
					$crmData = $crmDataSet['PRIMARY'];
				}
			}

			if((!empty($crmDataSet['COMPANY']) || !empty($crmDataSet['CONTACT'])) && \Bitrix\Crm\Settings\LeadSettings::getCurrent()->isAutoGenRcEnabled())
			{
				if(!empty($crmDataSet['COMPANY']))
				{
					$updateFields['COMPANY_ID'] = $crmDataSet['COMPANY']['ENTITY_ID'];
				}

				if(!empty($crmDataSet['CONTACT']))
				{
					$updateFields['CONTACT_IDS'][] = $crmDataSet['CONTACT']['ENTITY_ID'];
				}

				$saveBindings = $updateFields['PRIMARY']['BINDINGS'];
			}

			if ($crmData)
			{
				$current['ACTION'] = self::ACTION_EXTEND;
				$current['CRM_ENTITY_TYPE'] = $crmData['ENTITY_TYPE'];
				$current['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];

				if (
					$session->getData('SOURCE') == Connector::TYPE_LIVECHAT &&
					\Bitrix\Im\User::getInstance($session->getData('USER_ID'))->isConnector() &&
					\Bitrix\Im\User::getInstance($session->getData('USER_ID'))->getName() == ''
				)
				{
					$current['CHANGE_NAME'] = 'Y';
				}
				$communicationType = Crm::getCommunicationType($session->getData('USER_CODE'));
				$updateFm['IM_'.$communicationType] = 'imol|'.$session->getData('USER_CODE');
				$addLog['im'] = Array(
					'ACTION' => $current['ACTION'],
					'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
					'FIELD_ID' => self::FIELD_ID_FM,
					'FIELD_TYPE' => self::FIELD_IM,
					'FIELD_VALUE' => $updateFm['IM_'.$communicationType]
				);

				$current['CRM_BINDINGS'][] = array(
					'OWNER_ID' => $current['CRM_ENTITY_ID'],
					'OWNER_TYPE_ID' => \CCrmOwnerType::ResolveID($current['CRM_ENTITY_TYPE'])
				);
			}
			else
			{
				$crmData = $crm->registerLead(array(
					'CONFIG_ID' => $session->getData('CONFIG_ID'),
					'USER_CODE' => $session->getData('USER_CODE'),
					'USER_ID' => $session->getData('USER_ID'),
					'TITLE' => $session->chat->getData('TITLE'),
					'OPERATOR_ID' => $session->getData('OPERATOR_ID'),
					//'SKIP_FIND' => 'Y',
					'SKIP_CREATE' => 'N'
				));

				$current['CRM_BINDINGS'] = $crmData['BINDINGS'];
				$current['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];
				$current['CRM_ENTITY_TYPE'] = $crmData['ENTITY_TYPE'];
				$current['ACTION'] = $crmData['LEAD_CREATE'] == 'Y'? self::ACTION_CREATE: self::ACTION_EXTEND;
			}

			if(!empty($saveBindings) && !empty($current['CRM_BINDINGS']))
			{
				$current['CRM_BINDINGS'] = \Bitrix\Crm\Activity\BindingSelector::sortBindings(array_merge($current['CRM_BINDINGS'], $saveBindings));
			}

			$updateSession = Array(
				'CRM_CREATE' => 'Y',
				'CRM' => 'Y',
				'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
				'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
			);

			if ($session->getData('CRM_ACTIVITY_ID'))
			{
				if (
					$session->getData('CRM_ENTITY_TYPE') != $current['CRM_ENTITY_TYPE']
					|| $session->getData('CRM_ENTITY_ID') !=  $current['CRM_ENTITY_ID']
				)
				{
					$crm->updateActivity(Array(
						'ID' => $session->getData('CRM_ACTIVITY_ID'),
						'UPDATE' => Array(
							'OWNER_TYPE_ID' => $current['CRM_ENTITY_TYPE'],
							'OWNER_ID' => $current['CRM_ENTITY_ID'],
							'BINDINGS' => $current['CRM_BINDINGS'],
						)
					));
				}
			}
			else
			{
				$current['CRM_ACTIVITY_ID'] = $crm->addActivity(Array(
					'TITLE' => $session->chat->getData('TITLE'),
					'MODE' => $session->getData('MODE'),
					'USER_CODE' => $session->getData('USER_CODE'),
					'SESSION_ID' => $session->getData('SESSION_ID'),
					'COMPLETED' => 'N',
					'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
					'AUTHOR_ID' => $session->getData('OPERATOR_ID'),
					'RESPONSIBLE_ID' => $session->getData('OPERATOR_ID'),
					'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
					'CRM_BINDINGS' => $current['CRM_BINDINGS'],
					'ANSWERED' => $session->getData('ANSWERED') == 'Y'? 'Y': 'N',
				));

				$crm->executeAutomation($current['CRM_BINDINGS'], array(
					'CONFIG_ID' => $session->getData('CONFIG_ID')
				));

				$updateSession['CRM_ACTIVITY_ID'] = $current['CRM_ACTIVITY_ID'];
			}

			if ($crmData && $crmData['LEAD_CREATE'] == 'Y' && $crmData['ENTITY_TYPE'] == Crm::ENTITY_LEAD && !\Bitrix\Crm\Settings\LeadSettings::isEnabled())
			{
				$leadData = Crm::get('LEAD', $crmData['ENTITY_ID']);

				$current['COMPANY_ID'] = $leadData['COMPANY_ID'];
				$current['CONTACT_ID'] = $leadData['CONTACT_ID'];

				$dealData = Crm::getDealForLead($crmData['ENTITY_ID']);

				$updateSession['CRM_DEAL_ID'] = $current['CRM_DEAL_ID'] = $dealData['ID'];
			}

			$session->update($updateSession);
			$session->chat->setCrmFlag(Array(
				'ACTIVE' => 'Y',
				'ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
				'ENTITY_ID' => $current['CRM_ENTITY_ID'],
				'DEAL_ID' => $current['CRM_DEAL_ID'],
			));
		}

		$entityData = $crm->get($current['CRM_ENTITY_TYPE'], $current['CRM_ENTITY_ID'], true);
		if (!$entityData)
		{
			return false;
		}

		if ($current['CHANGE_NAME'] == 'Y' && $entityData['NAME'] && $entityData['LAST_NAME'])
		{
			$user = new \CUser();
			$user->Update($session->getData('USER_ID'), Array(
				'NAME' => $entityData['NAME'],
				'LAST_NAME' => $entityData['LAST_NAME'],
			));

			$relations = \CIMChat::GetRelationById($session->getData('CHAT_ID'));
			\Bitrix\Pull\Event::add(array_keys($relations), Array(
				'module_id' => 'im',
				'command' => 'updateUser',
				'params' => Array(
					'user' => \Bitrix\Im\User::getInstance($session->getData('USER_ID'))->getFields()
				),
				'extra' => method_exists('\Bitrix\Im\Common', 'getPullExtra') ?
					\Bitrix\Im\Common::getPullExtra() :
					Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
			));
		}


		if (!empty($entityData['FM']['IM']) && !empty($updateFm))
		{
			foreach ($updateFm as $key => $updateCode)
			{
				foreach ($entityData['FM']['IM'] as $type)
				{
					foreach ($type as $code)
					{
						if (trim($updateCode) == trim($code))
						{
							unset($updateFm[$key]);
							unset($addLog['im']);
						}
					}
				}
			}
		}
		if (!empty($entityData['FM']['PHONE']))
		{
			foreach ($entityData['FM']['PHONE'] as $fmPhones)
			{
				foreach ($fmPhones as $phone)
				{
					$phone = NormalizePhone($phone, 6);
					if (isset($phones[$phone]))
					{
						unset($phones[$phone]);
					}
				}
			}
		}
		if (!empty($entityData['FM']['EMAIL']))
		{
			foreach ($entityData['FM']['EMAIL'] as $fmEmails)
			{
				foreach ($fmEmails as $email)
				{
					$email = trim($email);
					if (isset($emails[$email]))
					{
						unset($emails[$email]);
					}
				}
			}
		}

		if (!empty($phones))
		{
			$updateFm['PHONE_WORK'] = implode(';', $phones);
			foreach ($phones as $phone)
			{
				$addLog[] = Array(
					'ACTION' => $current['ACTION'],
					'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
					'FIELD_ID' => self::FIELD_ID_FM,
					'FIELD_TYPE' => self::FIELD_PHONE,
					'FIELD_VALUE' => $phone
				);
			}
		}
		if (!empty($emails))
		{
			$updateFm['EMAIL_WORK'] = implode(';', $emails);
			foreach ($emails as $email)
			{
				$addLog[] = Array(
					'ACTION' => $current['ACTION'],
					'CRM_ENTITY_TYPE' => $current['CRM_ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $current['CRM_ENTITY_ID'],
					'FIELD_ID' => self::FIELD_ID_FM,
					'FIELD_TYPE' => self::FIELD_EMAIL,
					'FIELD_VALUE' => $email
				);
			}
		}

		if (!empty($updateFm) || !empty($updateFields))
		{
			$crm->update(
				$current['CRM_ENTITY_TYPE'],
				$current['CRM_ENTITY_ID'],
				array_merge($updateFields, $updateFm)
			);
		}

		if(\Bitrix\Crm\Settings\LeadSettings::isEnabled())
		{
			$attach = $crm->getEntityCard($current['CRM_ENTITY_TYPE'], $current['CRM_ENTITY_ID']);
			if ($current['ACTION'] == self::ACTION_CREATE)
			{
				$message =  Loc::getMessage('IMOL_TRACKER_'.$current['CRM_ENTITY_TYPE'].'_ADD');
				$keyboard = new \Bitrix\Im\Bot\Keyboard();
				$keyboard->addButton(Array(
					"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CHANGE'),
					"FUNCTION" => "BX.MessengerCommon.linesChangeCrmEntity(#MESSAGE_ID#);",
					"DISPLAY" => "LINE",
					"CONTEXT" => "DESKTOP",
				));
				$keyboard->addButton(Array(
					"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CANCEL'),
					"FUNCTION" => "BX.MessengerCommon.linesCancelCrmExtend(#MESSAGE_ID#);",
					"DISPLAY" => "LINE",
				));
			}
			else
			{
				$message =  Loc::getMessage('IMOL_TRACKER_'.$current['CRM_ENTITY_TYPE'].'_EXTEND');
				$keyboard = new \Bitrix\Im\Bot\Keyboard();
				$keyboard->addButton(Array(
					"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CHANGE'),
					"FUNCTION" => "BX.MessengerCommon.linesChangeCrmEntity(#MESSAGE_ID#);",
					"DISPLAY" => "LINE",
					"CONTEXT" => "DESKTOP",
				));
				$keyboard->addButton(Array(
					"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CANCEL'),
					"FUNCTION" => "BX.MessengerCommon.linesCancelCrmExtend(#MESSAGE_ID#);",
					"DISPLAY" => "LINE",
				));
			}

			$messageId = 0;
			if ($message)
			{
				if ($params['UPDATE_ID'])
				{
					$messageId = $params['UPDATE_ID'];

					\CIMMessenger::DisableMessageCheck();
					\CIMMessageParam::Set($messageId, Array('ATTACH' => $attach));
					\CIMMessenger::Update($messageId, $message, true, false);
					\CIMMessenger::EnableMessageCheck();
				}
				else
				{
					$userViewChat = \CIMContactList::InRecent($session->getData('OPERATOR_ID'), IM_MESSAGE_OPEN_LINE, $session->getData('CHAT_ID'));

					$messageId = Im::addMessage(Array(
						"TO_CHAT_ID" => $session->getData('CHAT_ID'),
						"MESSAGE" => '[b]'.$message.'[/b]',
						"SYSTEM" => 'Y',
						"ATTACH" => $attach,
						"KEYBOARD" => $keyboard,
						"RECENT_ADD" => $userViewChat? 'Y': 'N'
					));
				}
			}

			if (!empty($updateFm) || !empty($updateFields) && !empty($keyboard))
			{
				foreach ($addLog as $log)
				{
					TrackerTable::add(Array(
						'SESSION_ID' => $session->getData('ID'),
						'CHAT_ID' => $session->getData('CHAT_ID'),
						'MESSAGE_ID' => $messageId,
						'MESSAGE_ORIGIN_ID' => $messageOriginId,
						'USER_ID' => $session->getData('USER_ID'),
						'ACTION' => $log['ACTION'],
						'CRM_ENTITY_TYPE' => $log['CRM_ENTITY_TYPE'],
						'CRM_ENTITY_ID' => $log['CRM_ENTITY_ID'],
						'FIELD_ID' => $log['FIELD_ID'],
						'FIELD_TYPE' => $log['FIELD_TYPE'],
						'FIELD_VALUE' => $log['FIELD_VALUE'],
					));
				}
			}
		}
		else
		{
			$userViewChat = \CIMContactList::InRecent($session->getData('OPERATOR_ID'), IM_MESSAGE_OPEN_LINE, $session->getData('CHAT_ID'));

			if(!empty($current['COMPANY_ID']))
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $session->getData('CHAT_ID'),
					"MESSAGE" => '[b]'.Loc::getMessage('IMOL_TRACKER_SESSION_COMPANY').'[/b]',
					"SYSTEM" => 'Y',
					"ATTACH" => $crm->getEntityCard(Crm::ENTITY_COMPANY, $current['COMPANY_ID']),
					"RECENT_ADD" => $userViewChat? 'Y': 'N'
				));
			}

			if(!empty($current['CONTACT_ID']))
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $session->getData('CHAT_ID'),
					"MESSAGE" => '[b]'.Loc::getMessage('IMOL_TRACKER_SESSION_CONTACT').'[/b]',
					"SYSTEM" => 'Y',
					"ATTACH" => $crm->getEntityCard(Crm::ENTITY_CONTACT, $current['CONTACT_ID']),
					"RECENT_ADD" => $userViewChat? 'Y': 'N'
				));
			}

			if(!empty($current['CRM_DEAL_ID']))
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $session->getData('CHAT_ID'),
					"MESSAGE" => '[b]'.Loc::getMessage('IMOL_TRACKER_SESSION_DEAL').'[/b]',
					"SYSTEM" => 'Y',
					"ATTACH" => $crm->getEntityCard(Crm::ENTITY_DEAL, $current['CRM_DEAL_ID']),
					"RECENT_ADD" => $userViewChat? 'Y': 'N'
				));
			}
		}

		\Bitrix\Imopenlines\Limit::increaseTracker();

		return true;
	}

	public function user($params)
	{
		if (!\Bitrix\Main\Loader::includeModule('crm'))
			return false;

		$limitRemainder = Limit::getTrackerLimitRemainder();
		if ($limitRemainder <= 0)
		{
			$this->sendLimitMessage(Array(
				'CHAT_ID' => $params['CHAT_ID'],
				'MESSAGE_TYPE' => self::MESSAGE_ERROR_EXTEND
			));

			return false;
		}

		$user = \Bitrix\Im\User::getInstance($params['USER_ID']);

		$crm = new Crm();
		$crmData = $crm->find(Crm::FIND_BY_NAME, Array('NAME' => $user->getName(false), 'LAST_NAME' => $user->getLastName(false)));

		if (!$crmData && $user->getEmail())
		{
			$crmData = $crm->find(Crm::FIND_BY_EMAIL, Array('EMAIL' => $user->getEmail()));
		}
		if (!$crmData && $user->getPhone())
		{
			$crmData = $crm->find(Crm::FIND_BY_PHONE, Array('PHONE' => $user->getPhone()));
		}

		if ($crmData)
		{
			$entityData = $crm->get($crmData['ENTITY_TYPE'], $crmData['ENTITY_ID'], true);

			$keyboard = new \Bitrix\Im\Bot\Keyboard();
			$keyboard->addButton(Array(
				"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CHANGE'),
				"FUNCTION" => "BX.MessengerCommon.linesChangeCrmEntity(#MESSAGE_ID#);",
				"DISPLAY" => "LINE",
				"CONTEXT" => "DESKTOP",
			));
			$keyboard->addButton(Array(
				"TEXT" => Loc::getMessage('IMOL_TRACKER_BUTTON_CANCEL'),
				"FUNCTION" => "BX.MessengerCommon.linesCancelCrmExtend(#MESSAGE_ID#);",
				"DISPLAY" => "LINE",
			));

			$userViewChat = \CIMContactList::InRecent($params['OPERATOR_ID'], IM_MESSAGE_OPEN_LINE, $params['SESSION_ID']);

			$messageId = Im::addMessage(Array(
				"TO_CHAT_ID" => $params['CHAT_ID'],
				"MESSAGE" => '[b]'.Loc::getMessage('IMOL_TRACKER_'.$crmData['ENTITY_TYPE'].'_EXTEND').'[/b]',
				"SYSTEM" => 'Y',
				"ATTACH" => $crm->getEntityCard($crmData['ENTITY_TYPE'], $crmData['ENTITY_ID']),
				"KEYBOARD" => $keyboard,
				"RECENT_ADD" => $userViewChat? 'Y': 'N'
			));

			$result = TrackerTable::add(Array(
				'SESSION_ID' => intval($params['SESSION_ID']),
				'CHAT_ID' => $params['CHAT_ID'],
				'MESSAGE_ID' => $messageId,
				'USER_ID' => $params['USER_ID'],
				'ACTION' => self::ACTION_EXTEND,
				'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
				'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
				'FIELD_TYPE' => self::FIELD_IM,
				'FIELD_VALUE' => 'imol|'.$params['USER_CODE'],
			));
			$crmData['CRM_TRACK_ID'] = $result->getId();

			$updateFields = Array();

			$imTypeKey = 'IM_'. Crm::getCommunicationType($params['USER_CODE']);
			$updateFields[$imTypeKey] = 'imol|'.$params['USER_CODE'];

			if (!empty($entityData['FM']['IM']))
			{
				foreach ($entityData['FM']['IM'] as $fmIm)
				{
					foreach ($fmIm as $im)
					{
						if ($updateFields[$imTypeKey] == $im)
						{
							unset($updateFields[$imTypeKey]);
						}
					}
				}
			}

			if ($user->getEmail())
			{
				$updateFields['EMAIL_WORK'] = $user->getEmail();
				if (!empty($entityData['FM']['EMAIL']))
				{
					foreach ($entityData['FM']['EMAIL'] as $fmEmails)
					{
						foreach ($fmEmails as $email)
						{
							if (trim($updateFields['EMAIL_WORK']) == trim($email))
							{
								unset($updateFields['EMAIL_WORK']);
							}
						}
					}
				}
			}
			if ($user->getPhone())
			{
				$updateFields['PHONE_MOBILE'] = $user->getPhone();
				if (!empty($entityData['FM']['PHONE']))
				{
					foreach ($entityData['FM']['PHONE'] as $fmPhones)
					{
						foreach ($fmPhones as $phone)
						{
							if (NormalizePhone($updateFields['PHONE_MOBILE'], 6) == NormalizePhone($phone, 6))
							{
								unset($updateFields['PHONE_MOBILE']);
							}
						}
					}
				}
			}
			if ($user->getWebsite())
			{
				if (strlen($user->getWebsite()) > 255)
				{
					if ($user->getWebsite() != $entityData['SOURCE_DESCRIPTION'])
					{
						$entityData['SOURCE_DESCRIPTION'] = $entityData['SOURCE_DESCRIPTION'].' '.$user->getWebsite();
						$entityData['SOURCE_DESCRIPTION'] = trim($entityData['SOURCE_DESCRIPTION']);
					}
				}
				else
				{
					$updateFields['WEB_HOME'] = $user->getWebsite();
					if (!empty($entityData['FM']['WEB']))
					{
						foreach ($entityData['FM']['WEB'] as $fmWeb)
						{
							foreach ($fmWeb as $web)
							{
								if ($updateFields['WEB_HOME'] == $web)
								{
									unset($updateFields['WEB_HOME']);
								}
							}
						}
					}
				}
			}

			$crm->update($crmData['ENTITY_TYPE'], $crmData['ENTITY_ID'], $updateFields);
			\Bitrix\Imopenlines\Limit::increaseTracker();
		}

		return $crmData;
	}

	public function cancel($messageId)
	{
		$return = false;

		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			$log = Array();
			$delete = Array();

			$chatId = 0;
			$sessionId = 0;

			$orm = Model\TrackerTable::getList(Array(
				'filter' => Array('=MESSAGE_ID' => $messageId)
			));

			while ($row = $orm->fetch())
			{
				$entityType = $row['CRM_ENTITY_TYPE'];
				$entityId = $row['CRM_ENTITY_ID'];
				$action = $row['ACTION'];
				$fieldId = $row['FIELD_ID'];
				$fieldType = $row['FIELD_TYPE'];

				$chatId = $row['CHAT_ID'];
				$sessionId = $row['SESSION_ID'];

				$log[$entityType][$entityId][$action][$fieldId][$fieldType][] = $row['FIELD_VALUE'];
				$delete[] = $row['ID'];
			}

			if (!empty($delete))
			{
				foreach ($log as $entityType => $entityTypeValue)
				{
					if($entityType == Crm::ENTITY_ACTIVITY)
					{
						self::cancelActivity($entityTypeValue);
					}
					else
					{
						self::cancelLeadContactCompany($chatId, $sessionId, $entityType, $entityTypeValue);
					}
				}

				foreach ($delete as $id)
				{
					Model\TrackerTable::delete($id);
				}

				\CIMMessenger::DisableMessageCheck();
				\CIMMessenger::Delete($messageId, null, true);
				\CIMMessenger::EnableMessageCheck();

				$return = true;
			}
		}

		return $return;
	}

	static protected function cancelLeadContactCompany($chatId, $sessionId, $entityType, $params)
	{
		$crm = new Crm();

		foreach ($params as $entityId => $entityIdValue)
		{
			foreach ($entityIdValue as $action => $actionValue)
			{
				$updateCrm = true;

				if ($action == self::ACTION_CREATE)
				{
					$entityData = $crm->get($entityType, $entityId);

					$currentTime = new \Bitrix\Main\Type\DateTime();
					$entityTime = new \Bitrix\Main\Type\DateTime($entityData['DATE_CREATE']);
					$entityTime->add('1 DAY');
					if ($currentTime < $entityTime)
					{
						$crm->delete($entityType, $entityId);

						$chat = new Chat($chatId);
						$chat->updateFieldData(Chat::FIELD_SESSION, Array(
							'CRM' => 'N',
							'CRM_ENTITY_TYPE' => Crm::ENTITY_NONE,
							'CRM_ENTITY_ID' => 0
						));

						Model\SessionTable::update($sessionId, Array(
							'CRM' => 'N',
							'CRM_CREATE' => 'N',
							'CRM_ENTITY_TYPE' => Crm::ENTITY_NONE,
							'CRM_ENTITY_ID' => 0
						));

						$updateCrm = false;
					}
				}

				if($updateCrm)
				{
					foreach ($actionValue as $fieldId => $fieldIdValue)
					{
						if($fieldId == self::FIELD_ID_FM)
						{
							foreach ($fieldIdValue as $fieldType => $fieldTypeValue)
							{
								foreach ($fieldTypeValue as $value)
								{
									$crm->deleteMultiField($entityType, $entityId, $fieldType, $value);
								}
							}
						}
						else
						{
							$updateFields = array();

							foreach ($fieldIdValue as $fieldType => $fieldTypeValue)
							{
								foreach ($fieldTypeValue as $value)
								{
									if($fieldId == Crm::FIELDS_CONTACT)
									{
										$contactIDs = array();

										if($entityType == Crm::ENTITY_LEAD)
										{
											$contactIDs = LeadContactTable::getLeadContactIDs($entityId);
										}
										elseif($entityType == Crm::ENTITY_COMPANY)
										{
											$contactIDs = ContactCompanyTable::getCompanyContactIDs($entityId);
										}

										foreach ($contactIDs as $key => $id)
										{
											if($id == $value)
											{
												unset($contactIDs[$key]);
											}
										}

										$updateFields[$fieldId] = $contactIDs;
									}
									else
									{
										$updateFields[$fieldId] = '';
									}
								}
							}

							if(!empty($updateFields))
							{
								$crm->update(
									$entityType,
									$entityId,
									$updateFields
								);
							}
						}
					}
				}
			}
		}
	}

	protected static function cancelActivity($params)
	{
		foreach ($params as $entityId => $entityIdValue)
		{
			foreach ($entityIdValue as $action => $actionValue)
			{
				if ($action == self::ACTION_CREATE)
				{
					\CCrmActivity::Delete($entityId);
				}
				else
				{
					$bindings = \CAllCrmActivity::GetBindings($entityId);

					foreach ($bindings as $key => $value)
					{
						unset($bindings[$key]['ID']);
					}

					foreach ($actionValue as $fieldId => $fieldIdValue)
					{
						foreach ($fieldIdValue as $fieldType => $fieldTypeValue)
						{
							foreach ($fieldTypeValue as $value)
							{
								$deleteBinding = array(
									"OWNER_TYPE_ID" => \CCrmOwnerType::ResolveID($fieldId),
									"OWNER_ID" => $value
								);

								if(in_array($deleteBinding, $bindings))
								{
									$key = array_search($deleteBinding, $bindings);

									unset($bindings[$key]);
								}
							}
						}
					}

					\CAllCrmActivity::SaveBindings($entityId, $bindings);
				}
			}
		}
	}

	public function change($messageId, $newEntityType, $newEntityId)
	{
		$return = false;
		$messageId = intval($messageId);
		$newEntityId = intval($newEntityId);

		if (\Bitrix\Main\Loader::includeModule('crm') && $messageId > 0 && in_array($newEntityType, Array(Crm::ENTITY_COMPANY, Crm::ENTITY_LEAD, Crm::ENTITY_CONTACT)) && $newEntityId > 0)
		{
			$log = Array();
			$delete = Array();

			$sessionId = 0;
			$messageOriginId = 0;

			$action = '';
			$entityType = '';
			$entityId = 0;

			$orm = Model\TrackerTable::getList(Array(
				'filter' => Array('=MESSAGE_ID' => $messageId)
			));

			$return = true;

			while ($row = $orm->fetch())
			{
				$entityType = $row['CRM_ENTITY_TYPE'];
				$entityId = $row['CRM_ENTITY_ID'];
				$action = $row['ACTION'];
				$fieldId = $row['FIELD_ID'];
				$fieldType = $row['FIELD_TYPE'];

				$sessionId = $row['SESSION_ID'];
				$messageOriginId = $row['MESSAGE_ORIGIN_ID'];

				if ($newEntityType == $entityType && $newEntityId == $entityId)
					$return = false;

				$log[$entityType][$entityId][$action][$fieldId][$fieldType][] = $row['FIELD_VALUE'];
				$delete[] = $row['ID'];
			}

			if($return && !empty($delete))
			{
				foreach ($log as $entityType => $entityTypeValue)
				{
					if($entityType == Crm::ENTITY_ACTIVITY)
					{
						self::cancelActivity($entityTypeValue);
					}
					else
					{
						self::changeLeadContactCompany($entityType, $entityTypeValue);
					}
				}

				foreach ($delete as $id)
				{
					Model\TrackerTable::delete($id);
				}

				$return = true;

				if ($messageOriginId)
				{
					$sessionData = Model\SessionTable::getByIdPerformance($sessionId)->fetch();

					$session = new Session();
					$result = $session->load(Array(
						'USER_CODE' => $sessionData['USER_CODE']
					));
					if ($result)
					{
						$messageData = \Bitrix\Im\Model\MessageTable::getById($messageOriginId)->fetch();
						$this->message(Array(
							'SESSION' => $session,
							'MESSAGE' => Array(
								'ID' => $messageData["ID"],
								'TEXT' => $messageData["MESSAGE"],
							),
							'UPDATE_ID' => $messageId,
							'CRM' => Array(
								'ENTITY_TYPE' => $newEntityType,
								'ENTITY_ID' => $newEntityId,
							)
						));
					}
				}
			}
			else
			{
				$return = false;
			}
		}

		return $return;
	}

	static protected function changeLeadContactCompany($entityType, $params)
	{
		$crm = new Crm();

		foreach ($params as $entityId => $entityIdValue)
		{
			foreach ($entityIdValue as $action => $actionValue)
			{
				$updateCrm = true;

				if ($action == self::ACTION_CREATE)
				{
					$entityData = $crm->get($entityType, $entityId, true);

					$currentTime = new \Bitrix\Main\Type\DateTime();
					$entityTime = new \Bitrix\Main\Type\DateTime($entityData['DATE_CREATE']);
					$entityTime->add('1 DAY');
					if ($currentTime < $entityTime)
					{
						$crm->delete($entityType, $entityId);
						$updateCrm = false;
					}
				}

				if($updateCrm)
				{
					foreach ($actionValue as $fieldId => $fieldIdValue)
					{
						if($fieldId == self::FIELD_ID_FM)
						{
							foreach ($fieldIdValue as $fieldType => $fieldTypeValue)
							{
								foreach ($fieldTypeValue as $value)
								{
									$crm->deleteMultiField($entityType, $entityId, $fieldType, $value);
								}
							}
						}
						else
						{
							$updateFields = array();

							foreach ($fieldIdValue as $fieldType => $fieldTypeValue)
							{
								foreach ($fieldTypeValue as $value)
								{
									if($fieldId == Crm::FIELDS_CONTACT)
									{
										$contactIDs = array();

										if($entityType == Crm::ENTITY_LEAD)
										{
											$contactIDs = LeadContactTable::getLeadContactIDs($entityId);
										}
										elseif($entityType == Crm::ENTITY_COMPANY)
										{
											$contactIDs = ContactCompanyTable::getCompanyContactIDs($entityId);
										}

										foreach ($contactIDs as $key => $id)
										{
											if($id == $value)
											{
												unset($contactIDs[$key]);
											}
										}

										$updateFields[$fieldId] = $contactIDs;
									}
									else
									{
										$updateFields[$fieldId] = '';
									}
								}
							}

							if(!empty($updateFields))
							{
								$crm->update(
									$entityType,
									$entityId,
									$updateFields
								);
							}
						}
					}
				}
			}
		}
	}

	public function updateLog($params)
	{
		$id = intval($params['ID']);
		if ($id <= 0)
		{
			return false;
		}

		$update = $params['UPDATE'];
		if (!is_array($update))
		{
			return false;
		}

		$map = Model\TrackerTable::getMap();
		foreach ($update as $key => $value)
		{
			if (!isset($map[$key]))
			{
				unset($update[$key]);
			}
		}
		if (count($update) <= 0)
		{
			return false;
		}

		Model\TrackerTable::update($params['ID'], $params['UPDATE']);

		return true;
	}

	public function sendLimitMessage($params)
	{
		$chatId = intval($params['CHAT_ID']);
		if ($chatId <= 0)
			return false;

		if ($params['MESSAGE_TYPE'] == self::MESSAGE_ERROR_CREATE)
		{
			$message =  Loc::getMessage('IMOL_TRACKER_LIMIT_1');
		}
		else
		{
			$message =  Loc::getMessage('IMOL_TRACKER_LIMIT_2');
		}

		$message = str_replace(Array('#LINK_START#', '#LINK_END#'), '', $message);

		$keyboard = new \Bitrix\Im\Bot\Keyboard();
		$keyboard->addButton(Array(
			"TEXT" => Loc::getMessage('IMOL_TRACKER_LIMIT_BUTTON'),
			"LINK" => "/settings/license_all.php",
			"DISPLAY" => "LINE",
			"CONTEXT" => "DESKTOP",
		));

		$userViewChat = \CIMContactList::InRecent($params['OPERATOR_ID'], IM_MESSAGE_OPEN_LINE, $chatId);

		Im::addMessage(Array(
			"TO_CHAT_ID" => $chatId,
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
			"KEYBOARD" => $keyboard,
			"RECENT_ADD" => $userViewChat? 'Y': 'N'
		));

		return true;
	}

	private function prepareMessage($text)
	{
		$textParser = new \CTextParser();
		$textParser->allow = array("HTML" => "N", "USER" => "N",  "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "Y", "VIDEO" => "N", "TABLE" => "N", "CUT_ANCHOR" => "N", "ALIGN" => "N");

		$text = preg_replace("/\[[buis]\](.*?)\[\/[buis]\]/i", "$1", $text);
		$text = $textParser->convertText($text);

		$text = preg_replace('/<([\w]+)[^>]*>(.*?)<\/\1>/i', "", $text);
		$text = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $text);
		$text = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $text);
		$text = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", " ", $text);
		$text = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", " ", $text);
		$text = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", " ", $text);
		$text = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", " ", $text);
		$text = preg_replace("/\[ATTACH=([0-9]{1,})\]/i", " ", $text);
		$text = preg_replace("/\[ICON\=([^\]]*)\]/i", " ", $text);
		$text = preg_replace('#\-{54}.+?\-{54}#s', " ", str_replace(array("#BR#"), Array(" "), $text));

		return $text;
	}

	public function getError()
	{
		return $this->error;
	}
}
