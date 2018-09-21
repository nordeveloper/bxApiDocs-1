<?php

namespace Bitrix\Voximplant\Rest;

use Bitrix\Crm\Integration\StorageType;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\IO;
use Bitrix\Main\Loader;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\EventTable;
use Bitrix\Voximplant\Call;
use Bitrix\Voximplant\Model\CallTable;
use Bitrix\Voximplant\HttpClientFactory;
use Bitrix\Voximplant\Integration\Im;
use Bitrix\Voximplant\Model\ExternalLineTable;
use Bitrix\Voximplant\PhoneTable;
use Bitrix\Voximplant\Result;
use Bitrix\Voximplant\Security;
use Bitrix\Voximplant\StatisticTable;

class Helper
{
	const EVENT_START_EXTERNAL_CALL = 'OnExternalCallStart';
	const EVENT_START_EXTERNAL_CALLBACK = 'OnExternalCallBackStart';
	const PLACEMENT_CALL_CARD = 'CALL_CARD';
	const FILE_FIELD = 'file';

	/**
	 * Returns user id of the user with given inner phone number, or false if user is not found.
	 * @param string $phoneNumber Inner phone number.
	 * @return int|false
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getUserByPhone($phoneNumber)
	{
		$row = PhoneTable::getList(array(
			'select' => array('USER_ID'),
			'filter' => array(
				'PHONE_NUMBER' => $phoneNumber,
				'PHONE_MNEMONIC' => 'UF_PHONE_INNER',
				'=USER.ACTIVE' => 'Y',
				'=USER.IS_REAL_USER' => 'Y'
			)
		))->fetch();

		return is_array($row) ? (int)$row['USER_ID'] : false;
	}

	/**
	 * Register call, started to perform in external PBX. Auto creates
	 * @param array $fields
	 * <li> USER_ID int
	 * <li> PHONE_NUMBER string
	 * <li> LINE_NUMBER string
	 * <li> TYPE int
	 * <li> CALL_START_DATE date
	 * <li> CRM bool
	 * <li> CRM_CREATE bool
	 * <li> CRM_SOURCE
	 * <li> CRM_ENTITY_TYPE
	 * <li> CRM_ENTITY_ID
	 * <li> CRM_ACTIVITY_ID
	 * <li> CRM_BINDINGS
	 * <li> REST_APP_ID
	 * <li> SHOW
	 * <li> CALL_LIST_ID
	 * @return Result
	 */
	public static function registerExternalCall(array $fields)
	{
		$result = new Result();
		$callId = 'externalCall.'.md5(uniqid($fields['REST_APP_ID'].$fields['USER_ID'].$fields['PHONE_NUMBER'])).'.'.time();
		$isCrmAvailable = Loader::includeModule('crm');

		$phoneNumber = \CVoxImplantPhone::stripLetters($fields['PHONE_NUMBER']);
		if(!$phoneNumber)
		{
			$result->addError(new Error('Unsupported phone number format'));
			return $result;
		}

		if(isset($fields['LINE_NUMBER']))
		{
			$fields['LINE_NUMBER'] = trim($fields['LINE_NUMBER']);
			if($fields['LINE_NUMBER'] != '')
			{
				$lineNumber = trim($fields['LINE_NUMBER']);
				$row = ExternalLineTable::getRow(array(
					'filter' => array(
						'=NUMBER' => $lineNumber,
						'=REST_APP_ID' => $fields['REST_APP_ID']
					)
				));
				if(!$row)
				{
					$result->addError(new Error('Could not find line with number ' . $lineNumber));
					return $result;
				}
				$lineId = $row['ID'];
			}
		}

		$initEventData = array(
			'CALL_ID' => $callId,
			'CALL_TYPE' => $fields['TYPE'],
			'CALLER_ID' => $phoneNumber,
			'REST_APP_ID' => $fields['REST_APP_ID']
		);
		$initEvent = new Event('voximplant', 'onCallInit', $initEventData);
		$initEvent->send();
		if ($initEvent->getResults() != null)
		{
			foreach($initEvent->getResults() as $eventResult)
			{
				if($eventResult->getType() === EventResult::SUCCESS)
				{
					$eventResultData = $eventResult->getParameters();
					if(isset($eventResultData['CALLER_ID']))
					{
						$phoneNumber = $eventResultData['CALLER_ID'];
					}
				}
			}
		}

		// checking for the internal call
		$portalCall = false;
		$portalUserId = null;
		$userData = PhoneTable::getList(array(
			'select' => array('USER_ID'),
			'filter' => array(
				'=PHONE_NUMBER' => $phoneNumber,
				'=PHONE_MNEMONIC' => 'UF_PHONE_INNER',
				'=USER.ACTIVE' => 'Y'
			),
		))->fetch();
		if ($userData)
		{
			$portalCall = true;
			$portalUserId = $userData['USER_ID'];
		}

		$duplicateFilter = [
			'=USER_ID' => $fields['USER_ID'],
			'=CALLER_ID' => $phoneNumber,
			'=INCOMING' => $fields['TYPE'],
			'>DATE_CREATE' => (new DateTime())->add('-T30M'),
			'=REST_APP_ID' => $fields['REST_APP_ID'],
		];
		if($lineId)
		{
			$duplicateFilter['=EXTERNAL_LINE_ID'] = $lineId;
		}

		$duplicateCall = CallTable::getRow([
			'filter' => $duplicateFilter
		]);

		if($duplicateCall)
		{
			if($fields['SHOW'])
			{
				self::showExternalCall(array(
					'CALL_ID' => $callId
				));
			}

			return $result->setData([
				'CALL_ID' => $duplicateCall['CALL_ID'],
				'CRM_CREATED_LEAD' => (int)$duplicateCall['CRM_LEAD'] ?: null,
				'CRM_ENTITY_TYPE' => $duplicateCall['CRM_ENTITY_TYPE'],
				'CRM_ENTITY_ID' => (int)$duplicateCall['CRM_ENTITY_ID'] ?: null,
			]);
		}

		$crmCreate = ($fields['CRM'] || $fields['CRM_CREATE']) && !$portalCall;
		$callFields = array(
			'USER_ID' => $fields['USER_ID'],
			'CALL_ID' => $callId,
			'INCOMING' => $fields['TYPE'],
			'DATE_CREATE' => ($fields['CALL_START_DATE'] ?: new DateTime()),
			'CALLER_ID' => $phoneNumber,
			'PORTAL_USER_ID' => $portalUserId,
			'CRM' => $portalCall ? 'N' : 'Y',
			'REST_APP_ID' => $fields['REST_APP_ID'],
			'EXTERNAL_LINE_ID' => isset($lineId) ? $lineId : null,
			'PORTAL_NUMBER' => $lineNumber ?: \CVoxImplantConfig::MODE_REST_APP . ":" . $fields['REST_APP_ID']
		);

		if(isset($fields['CRM_ENTITY_TYPE']) && isset($fields['CRM_ENTITY_ID']))
		{
			$callFields['CRM_ENTITY_TYPE'] = $fields['CRM_ENTITY_TYPE'];
			$callFields['CRM_ENTITY_ID'] = $fields['CRM_ENTITY_ID'];
		}
		else
		{
			$crmEntity = \CVoxImplantCrmHelper::GetCrmEntity($fields['PHONE_NUMBER']);
			if(is_array($crmEntity))
			{
				$callFields['CRM_ENTITY_TYPE'] = $crmEntity['ENTITY_TYPE_NAME'];
				$callFields['CRM_ENTITY_ID'] = $crmEntity['ENTITY_ID'];
			}
		}

		if($fields['CALL_LIST_ID'] > 0)
		{
			$callFields['CRM_CALL_LIST'] = (int)$fields['CALL_LIST_ID'];
		}
		if($fields['CRM_ACTIVITY_ID'] > 0)
		{
			$callFields['CRM_ACTIVITY_ID'] = (int)$fields['CRM_ACTIVITY_ID'];
		}
		if(is_array($fields['CRM_BINDINGS']))
		{
			$callFields['CRM_BINDINGS'] = $fields['CRM_BINDINGS'];
		}

		$call = Call::create($callFields);

		if($call->getIncoming() == \CVoxImplantMain::CALL_INCOMING)
		{
			\CVoxImplantCrmHelper::StartCallTrigger($callId);
		}

		if($crmCreate)
		{
			$createResult = \CVoxImplantCrmHelper::registerCallInCrm(
				$call,
				array(
					'CRM' => 'Y',
					'CRM_CREATE' => \CVoxImplantConfig::CRM_CREATE_LEAD,
					'CRM_CREATE_CALL_TYPE' => \CVoxImplantConfig::CRM_CREATE_CALL_TYPE_ALL,
					'CRM_SOURCE' => $fields['CRM_SOURCE']
				)
			);

			if(!$createResult)
			{
				$leadCreationError = \CVoxImplantCrmHelper::$lastError;
			}
		}

		\CVoxImplantMain::sendCallStartEvent(array(
			'CALL_ID' => $callId,
			'USER_ID' => $fields['USER_ID']
		));

		if($fields['SHOW'])
		{
			self::showExternalCall(array(
				'CALL_ID' => $callId
			));
		}

		$resultData = array(
			'CALL_ID' => $call->getCallId(),
			'CRM_CREATED_LEAD' => (int)$call->getCrmLead() ?: null,
			'CRM_ENTITY_TYPE' => $call->getCrmEntityType(),
			'CRM_ENTITY_ID' => (int)$call->getCrmEntityId() ?: null,
		);

		if(isset($leadCreationError))
			$resultData['LEAD_CREATION_ERROR'] = $leadCreationError;

		$result->setData($resultData);
		return $result;
	}

	/**
	 * Finishes call, initiated externally and updates crm lead and activity
	 * @param array $fields
	 * <li> CALL_ID
	 * <li> USER_ID
	 * <li> DURATION - call duration in seconds
	 * <li> COST - call's cost
	 * <li> COST_CURRENCY
	 * <li> STATUS_CODE
	 * <li> FAILED_REASON
	 * <li> RECORD_URL
	 * <li> VOTE
	 * <li> ADD_TO_CHAT
	 * @return Result
	 */
	public static function finishExternalCall(array $fields)
	{
		$result = new Result();
		$call = CallTable::getRow(array(
			'select' => array(
				'*',
				'EXTERNAL_LINE_NUMBER' => 'EXTERNAL_LINE.NUMBER',
				'EXTERNAL_LINE_NAME' => 'EXTERNAL_LINE.NAME'
			),
			'filter' => array(
				'=CALL_ID' => $fields['CALL_ID']
			)
		));
		if(!$call)
		{
			$result->addError(new Error('Call is not found (call should be registered prior to finishing'));
			return $result;
		}

		self::hideExternalCall(array(
			'CALL_ID' => $call['CALL_ID'],
			'USER_ID' => isset($fields['USER_ID']) ? (int)$fields['USER_ID'] : $call['USER_ID']
		));

		$fields['DURATION'] = (int)$fields['DURATION'];
		$fields['STATUS_CODE'] = $fields['STATUS_CODE'] ?: ($fields['DURATION'] > 0 ? '200' : '304');
		$fields['ADD_TO_CHAT'] = isset($fields['ADD_TO_CHAT']) ? (bool)$fields['ADD_TO_CHAT'] : true;

		$statisticRecord = array(
			'CALL_ID' => $call['CALL_ID'],
			'PORTAL_USER_ID' => isset($fields['USER_ID']) ? (int)$fields['USER_ID'] : $call['USER_ID'],
			'PHONE_NUMBER' => $call['CALLER_ID'],
			'PORTAL_NUMBER' => $call['PORTAL_NUMBER'],
			'INCOMING' => $call['INCOMING'],
			'CALL_DURATION' => $fields['DURATION'] ?: 0,
			'CALL_START_DATE' => $call['DATE_CREATE'],
			'CALL_STATUS' => $fields['DURATION'] > 0 ? 1 : 0,
			'CALL_VOTE' => $fields['VOTE'],
			'COST' => $fields['COST'],
			'COST_CURRENCY' => $fields['COST_CURRENCY'],
			'CALL_FAILED_CODE' => $fields['STATUS_CODE'],
			'CALL_FAILED_REASON' => $fields['FAILED_REASON'],
			'REST_APP_ID' => $call['REST_APP_ID'],
			'REST_APP_NAME' => self::getRestAppName($call['REST_APP_ID']),
			'CRM_ACTIVITY_ID' => (int)$call['CRM_ACTIVITY_ID'] ?: null,
			'COMMENT' => $call['COMMENT'],
		);

		if($call['CRM_ENTITY_TYPE'] && $call['CRM_ENTITY_ID'])
		{
			$statisticRecord['CRM_ENTITY_TYPE'] = $call['CRM_ENTITY_TYPE'];
			$statisticRecord['CRM_ENTITY_ID'] = $call['CRM_ENTITY_ID'];
		}
		else
		{
			$crmData = \CVoxImplantCrmHelper::GetCrmEntity($statisticRecord['PHONE_NUMBER']);
			if(is_array($crmData))
			{
				$statisticRecord['CRM_ENTITY_TYPE'] = $crmData['ENTITY_TYPE_NAME'];
				$statisticRecord['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];
			}
		}

		if($call['CRM_LEAD'] > 0)
		{
			\CVoxImplantCrmHelper::UpdateLead(
				$call['CRM_LEAD'],
				array('ASSIGNED_BY_ID' => $statisticRecord['PORTAL_USER_ID']),
				$statisticRecord['PORTAL_USER_ID']
			);
		}

		if($call['CRM_ACTIVITY_ID'] && \CVoxImplantCrmHelper::shouldAttachCallToActivity($statisticRecord, $call['CRM_ACTIVITY_ID']))
		{
			\CVoxImplantCrmHelper::attachCallToActivity($statisticRecord, $call['CRM_ACTIVITY_ID']);
			$statisticRecord['CRM_ACTIVITY_ID'] = $call['CRM_ACTIVITY_ID'];
		}
		else
		{
			$statisticRecord['CRM_ACTIVITY_ID'] = \CVoxImplantCrmHelper::AddCall($statisticRecord, array(
				'CRM_BINDINGS' => $call['CRM_BINDINGS']
			));
			if(!$statisticRecord['CRM_ACTIVITY_ID'])
				$activityCreationError = \CVoxImplantCrmHelper::$lastError;
		}

		if(isset($statisticRecord['CRM_ENTITY_TYPE']) && isset($statisticRecord['CRM_ENTITY_ID']))
		{
			$viMain = new \CVoxImplantMain($statisticRecord["PORTAL_USER_ID"]);
			$dialogData = $viMain->GetDialogInfo($statisticRecord['PHONE_NUMBER'], '', false);
			if(!$dialogData['UNIFIED'])
			{
				\CVoxImplantMain::UpdateChatInfo(
					$dialogData['DIALOG_ID'],
					array(
						'CRM' => $call['CRM'],
						'CRM_ENTITY_TYPE' => $statisticRecord['CRM_ENTITY_TYPE'],
						'CRM_ENTITY_ID' => $statisticRecord['CRM_ENTITY_ID']
					)
				);
			}
		}

		$insertResult = StatisticTable::add($statisticRecord);
		if(!$insertResult->isSuccess())
		{
			$result->addError(new Error('Unexpected database error'));
			$result->addErrors($insertResult->getErrors());
			return $result;
		}
		$statisticRecord['ID'] = $insertResult->getId();

		CallTable::delete($call['ID']);

		$hasRecord = ($fields['RECORD_URL'] != '');
		if($hasRecord)
			\CVoxImplantHistory::DownloadAgent($insertResult->getId(), $fields['RECORD_URL'], ($call['CRM'] === 'Y'));

		if($fields['ADD_TO_CHAT'])
		{
			$chatMessage = \CVoxImplantHistory::GetMessageForChat($statisticRecord, $hasRecord);
			if($chatMessage != '')
			{
				$attach = null;

				if(\CVoxImplantConfig::GetChatAction() == \CVoxImplantConfig::INTERFACE_CHAT_APPEND)
				{
					$attach = \CVoxImplantHistory::GetAttachForChat($statisticRecord, $hasRecord);
				}

				if($attach)
					\CVoxImplantHistory::SendMessageToChat($statisticRecord["PORTAL_USER_ID"], $statisticRecord["PHONE_NUMBER"], $statisticRecord["INCOMING"], null, $attach);
				else
					\CVoxImplantHistory::SendMessageToChat($statisticRecord["PORTAL_USER_ID"], $statisticRecord["PHONE_NUMBER"], $statisticRecord["INCOMING"], $chatMessage);
			}
		}

		if($call['CRM_LEAD'] > 0 && \CVoxImplantConfig::GetLeadWorkflowExecution() == \CVoxImplantConfig::WORKFLOW_START_DEFERRED)
		{
			\CVoxImplantCrmHelper::StartLeadWorkflow($call['CRM_LEAD'], [
				'LINE_NUMBER' => $call['PORTAL_NUMBER'],
				'START_TRIGGER' => ($call['INCOMING'] == \CVoxImplantMain::CALL_INCOMING)
			]);
		}

		\CVoxImplantHistory::sendCallEndEvent($statisticRecord);
		$resultData = $statisticRecord;
		if(isset($activityCreationError))
			$resultData['ERRORS']['ACTIVITY_CREATION'] = $activityCreationError;

		$result->setData($resultData);
		return $result;
	}

	/**
	 * Shows card with CRM info on a call to the user.
	 * @param array $params Function parameters:
	 * <li> CALL_ID
	 * <li> USER_ID
	 * @return bool
	 */
	public static function showExternalCall(array $params)
	{
		$callId = $params['CALL_ID'];
		$call = CallTable::getRow(array(
			'select' => array(
				'*',
				'EXTERNAL_LINE_NUMBER' => 'EXTERNAL_LINE.NUMBER',
				'EXTERNAL_LINE_NAME' => 'EXTERNAL_LINE.NAME'
			),
			'filter' => array(
				'=CALL_ID' => $callId
			)
		));
		if(!$call)
			return false;

		if(isset($params['USER_ID']))
		{
			if(is_array($params['USER_ID']))
				$userId = $params['USER_ID'];
			else
				$userId = array((int)$params['USER_ID']);
		}
		else
		{
			$userId = array($call['USER_ID']);
		}

		$portalCall = $call['PORTAL_USER_ID'] > 0;

		\CVoxImplantMain::SendPullEvent(array(
			'COMMAND' => 'showExternalCall',
			'CALL_ID' => $callId,
			'USER_ID' => $userId,
			'PHONE_NUMBER' => (string)$call['CALLER_ID'],
			'LINE_NUMBER' => $call['EXTERNAL_LINE_NUMBER'] ?: null,
			'COMPANY_PHONE_NUMBER' => $call['EXTERNAL_LINE_NAME'] ?: $call['EXTERNAL_LINE_NUMBER'] ?: null,
			'INCOMING' => $call['INCOMING'],
			'SHOW_CRM_CARD' => ($call['CRM'] == 'Y'),
			'CRM_ENTITY_TYPE' => $call['CRM_ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $call['CRM_ENTITY_ID'],
			'CRM_ACTIVITY_ID' => $call['CRM_ACTIVITY_ID'],
			'CRM_ACTIVITY_EDIT_URL' => \CVoxImplantCrmHelper::getActivityEditUrl($call['CRM_ACTIVITY_ID']),
			'CRM' => \CVoxImplantCrmHelper::GetDataForPopup($call['CALL_ID'], $call['CALLER_ID'], $userId),
			'CONFIG' => array(
				'CRM_CREATE' => 'none'
			),
			'PORTAL_CALL' => $portalCall > 0 ? 'Y' : 'N',
			'PORTAL_CALL_USER_ID' => $call['PORTAL_USER_ID'],
			'PORTAL_CALL_DATA' => $portalCall ? Im::getUserData(array('ID' => array($call['USER_ID'], $call['PORTAL_USER_ID']), 'DEPARTMENT' => 'N', 'HR_PHOTO' => 'Y')) : array()
		));
		return true;
	}

	/**
	 * Hides card with CRM info on a call.
	 * @param array $params Function parameters:
	 * <li> CALL_ID
	 * <li> USER_ID
	 * @return bool
	 */
	public static function hideExternalCall(array $params)
	{
		$callId = $params['CALL_ID'];
		$call = CallTable::getByCallId($callId);
		if(!$call)
			return false;

		if(isset($params['USER_ID']))
		{
			if(is_array($params['USER_ID']))
				$userId = $params['USER_ID'];
			else
				$userId = array((int)$params['USER_ID']);
		}
		else
		{
			$userId = array($call['USER_ID']);
		}

		\CVoxImplantMain::SendPullEvent(array(
			'COMMAND' => 'hideExternalCall',
			'USER_ID' => $userId,
			'CALL_ID' => $callId
		));
		return true;
	}

	/**
	 * Returns rest application name by its client id.
	 * @param string $clientId Application's client id.
	 * @return string|false
	 */
	public static function getRestAppName($clientId)
	{
		if(!Loader::includeModule('rest'))
			return false;

		$row = AppTable::getByClientId($clientId);

		if(!is_array($row))
			return false;

		if ($row['MENU_NAME'] != '')
			$result = $row['MENU_NAME'];
		else if ($row['MENU_NAME_DEFAULT'] != '')
			$result = $row['MENU_NAME_DEFAULT'];
		else if ($row['MENU_NAME_LICENSE'] != '')
			$result = $row['MENU_NAME_LICENSE'];
		else
			$result = $row['APP_NAME'];

		return $result;
	}

	/**
	 * Returns array of applications, capable of creating externally initiated calls
	 */
	public static function getExternalCallHandlers()
	{
		return static::getEventSubscribers(self::EVENT_START_EXTERNAL_CALL);
	}

	/**
	 * Returns array of applications, capable of starting callback
	 */
	public static function getExternalCallbackHandlers()
	{
		return static::getEventSubscribers(self::EVENT_START_EXTERNAL_CALLBACK);
	}

	protected static function getEventSubscribers($eventName)
	{
		$result = array();
		if(!Loader::includeModule('rest'))
			return $result;

		$cursor = EventTable::getList(array(
			'select' => array(
				'APP_ID' => 'APP_ID',
				'TITLE' => 'TITLE',
				'APP_NAME' => 'REST_APP.APP_NAME',
				'MENU_NAME' => 'REST_APP.LANG.MENU_NAME',
				'DEFAULT_MENU_NAME' => 'REST_APP.LANG_DEFAULT.MENU_NAME'
			),
			'filter' => array(
				'EVENT_NAME' => $eventName
			)
		));

		while($row = $cursor->fetch())
		{
			$appId = $row['APP_ID'];
			if($appId == 0)
				$appName = $row['TITLE'];
			else if ($row['MENU_NAME'] != '')
				$appName = $row['MENU_NAME'];
			else if ($row['DEFAULT_MENU_NAME'] != '')
				$appName = $row['DEFAULT_MENU_NAME'];
			else
				$appName = $row['APP_NAME'];

			$result[$appId] = $appName;
		}

		return $result;
	}

	/**
	 * Returns id of the rest application, set as external call handler, or false if the external call handler is not set.
	 * @param int $userId Id of the user.
	 * @return string|false
	 */
	public static function getExternalCallHandler($userId)
	{
		$defaultLineId = \CVoxImplantUser::getUserOutgoingLine($userId);
		$line = \CVoxImplantConfig::GetLine($defaultLineId);

		return ($line && $line['TYPE'] === 'REST') ? $line : false;
	}

	/**
	 * Sends event to start call to the configured rest application
	 * @param string $number Phone number to call.
	 * @param int $userId User id of the user, initiated the call.
	 * @param array $parameters Additional parameters.
	 * @return Result
	 */
	public static function startCall($number, $userId, $lineId = '', array $parameters = array())
	{
		$entityType = $parameters['ENTITY_TYPE'];
		$entityId = $parameters['ENTITY_ID'];
		if(strpos($entityType, 'CRM_') === 0)
		{
			$entityType = substr($entityType, 4);
		}
		else if (isset($parameters['ENTITY_TYPE_NAME']) && isset($parameters['ENTITY_ID']))
		{
			$entityType = $parameters['ENTITY_TYPE_NAME'];
			$entityId = $parameters['ENTITY_ID'];
		}
		else
		{
			$entityType = '';
			$entityId = null;
		}

		if($lineId)
		{
			$line = \CVoxImplantConfig::GetLine($lineId);
		}
		else
		{
			$line = self::getExternalCallHandler($userId);
		}
		if(!$line)
		{
			$result = new Result();
			return $result->addError(new Error("Outgoing line is not found", "LINE_NOT_FOUND"));
		}
		$lineNumber = substr($line['LINE_NUMBER'], 0, 8) === 'REST_APP' ? '' : $line['LINE_NUMBER'];

		list($extensionSeparator, $extension) = Parser::getInstance()->stripExtension($number);
		$eventFields = array(
			'PHONE_NUMBER' => $number,
			'PHONE_NUMBER_INTERNATIONAL' => Parser::getInstance()->parse($number)->format(Format::E164),
			'EXTENSION' => $extension,
			'USER_ID' => $userId,
			'CALL_LIST_ID' => (int)$parameters['CALL_LIST_ID'],
			'APP_ID' => $line['REST_APP_ID'],
			'LINE_NUMBER' => $lineNumber
		);

		$registerResult = static::registerExternalCall(array(
			'USER_ID' => $userId,
			'PHONE_NUMBER' => $number,
			'LINE_NUMBER' => $lineNumber,
			'TYPE' => \CVoxImplantMain::CALL_OUTGOING,
			'CRM_CREATE' => true,
			'CRM_ENTITY_TYPE' => $entityType,
			'CRM_ENTITY_ID' => $entityId,
			'CRM_ACTIVITY_ID' => (int)$parameters['SRC_ACTIVITY_ID'],
			'CRM_BINDINGS' => is_array($parameters['BINDINGS']) ? $parameters['BINDINGS'] : null,
			'REST_APP_ID' => $line['REST_APP_ID'],
			'CALL_LIST_ID' => (int)$parameters['CALL_LIST_ID'],
		));
		if($registerResult->isSuccess())
		{
			$callData = $registerResult->getData();
			$eventFields['CALL_ID'] = $callData['CALL_ID'];
			$eventFields['CRM_ENTITY_TYPE'] = $callData['CRM_ENTITY_TYPE'];
			$eventFields['CRM_ENTITY_ID'] = $callData['CRM_ENTITY_ID'];

			$event = new Event(
				'voximplant',
				'onExternalCallStart',
				$eventFields
			);
			$event->send();
		}

		return $registerResult;
	}

	/**
	 * Send event to start callback to the rest application.
	 * @param array $parameters Array of parameters.
	 * @return void
	 */
	public static function startCallBack(array $parameters)
	{
		$eventFields = array(
			'PHONE_NUMBER' => $parameters['PHONE_NUMBER'],
			'TEXT' => $parameters['TEXT'],
			'VOICE' => $parameters['VOICE'],
			'CRM_ENTITY_TYPE' => $parameters['CRM_ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $parameters['CRM_ENTITY_ID'],
			'APP_ID' => $parameters['APP_ID'],
			'LINE_NUMBER' => $parameters['LINE_NUMBER']
		);

		$event = new Event(
			'voximplant',
			'onExternalCallBackStart',
			$eventFields
		);
		$event->send();
	}

	/**
	 * @param string $callId Id of the call.
	 * @param string $fileName Name of file containing record.
	 * @param string $fileContent Base64-encoded string with file contents.
	 * @param \CRestServer $restServer Rest server.
	 * @return Result
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function attachRecord($callId, $fileName, $fileContent, $restServer)
	{
		$result = new Result();

		$statisticRecord = StatisticTable::getByCallId($callId);
		if(!$statisticRecord)
		{
			$result->addError(new Error("Call is not found in the statistic table. Looks like it is not finished yet."));
			return $result;
		}

		if($fileContent === null)
		{
			$result->setData(array(
				'uploadUrl' => \CRestUtil::getUploadUrl(array('callId' => $callId), $restServer),
				'fieldName' => static::FILE_FIELD
			));
			return $result;
		}

		if($fileName == '')
		{
			$result->addError(new Error("File name is empty"));
			return $result;
		}

		$allowedExtensions = array('wav', 'mp3');
		if(!in_array(GetFileExtension($fileName), $allowedExtensions))
		{
			$result->addError(new Error("Wrong file extension. Only wav and mp3 are allowed"));
			return $result;
		}

		$fileArray = \CRestUtil::saveFile($fileContent, $fileName);

		if ($fileArray === false)
		{
			$result->addError(new Error("File content is empty."));
			return $result;
		}

		if(is_null($fileArray))
		{
			$result->addError(new Error("File content is not properly encoded. Base64 encoding is expected."));
			return $result;
		}

		if(!is_array($fileArray))
		{
			$result->addError(new Error("Unknown error encountered while saving file."));
			return $result;
		}

		$saveResult = static::saveFile($statisticRecord['CALL_START_DATE']->format("Y-m"), $fileName, $fileArray, $statisticRecord['PORTAL_USER_ID']);
		if(!$saveResult->isSuccess())
		{
			$result->addErrors($saveResult->getErrors());
			return $result;
		}
		$saveResultData = $saveResult->getData();
		$file = $saveResultData['FILE'];

		$attachResult = static::attachFile($callId, $file);
		if(!$attachResult->isSuccess())
		{
			$result->addErrors($attachResult->getErrors());
			return $result;
		}

		$result->setData(array(
			'FILE_ID' => $file->getId()
		));

		return $result;
	}

	/**
	 * Downloads and attaches record to the existing call.
	 * @param string $callId Id of the call.
	 * @param string $recordUrl Url of the record.
	 * @params string $fileName [Optional] Name of the file. If omitted, file name will taken from the url.
	 * @return Result
	 */
	public static function attachRecordWithUrl($callId, $recordUrl, $fileName = '')
	{
		$result = new Result();

		$statisticRecord = StatisticTable::getByCallId($callId);
		if(!$statisticRecord)
		{
			$result->addError(new Error("Call is not found in the statistic table. Looks like it is not finished yet."));
			return $result;
		}

		$httpClient = HttpClientFactory::create(array(
			"disableSslVerification" => true
		));
		$queryResult = $httpClient->query('GET', $recordUrl);

		if ($queryResult === false)
		{
			$httpClientErrors = $httpClient->getError();
			if(count($httpClientErrors) > 0)
			{
				foreach ($httpClientErrors as $code => $message)
				{
					return $result->addError(new Error($code . ": " . $message, 'SERVER_NOT_AVAILABLE'));
				}
			}
		}

		if ($httpClient->getStatus() != 200)
		{
			return $result->addError(new Error('Server returns HTTP error code ' . $httpClient->getStatus()));
		}

		if($fileName == '')
			$fileName = $httpClient->getHeaders()->getFilename();

		$urlComponents = parse_url($recordUrl);
		if ($fileName != '')
		{
			$tempPath = \CFile::GetTempName('', bx_basename($fileName));
		}
		else if ($urlComponents && strlen($urlComponents["path"]) > 0)
		{
			$tempPath = \CFile::GetTempName('', bx_basename($urlComponents["path"]));
		}
		else
		{
			$tempPath = \CFile::GetTempName('', bx_basename($recordUrl));
		}

		IO\Directory::createDirectory(IO\Path::getDirectory($tempPath));
		if (IO\Directory::isDirectoryExists(IO\Path::getDirectory($tempPath)) === false)
		{
			return $result->addError(new Error('Could not create temporary directory', 'INTERNAL_ERROR'));
		}

		$file = new IO\File($tempPath);
		$handler = $file->open("w+");
		if ($handler === false)
		{
			return $result->addError(new Error('Could not open temporary file', 'INTERNAL_ERROR'));
		}

		$httpClient->setOutputStream($handler);
		$httpClient->getResult();
		$file->close();

		//check for http errors once more
		$httpClientErrors = $httpClient->getError();
		if(count($httpClientErrors) > 0)
		{
			foreach ($httpClientErrors as $code => $message)
			{
				return $result->addError(new Error($code . ": " . $message, 'SERVER_NOT_AVAILABLE'));
			}
		}

		$fileArray = \CFile::MakeFileArray($tempPath);
		$saveResult = static::saveFile($statisticRecord['CALL_START_DATE']->format("Y-m"), $fileName, $fileArray, $statisticRecord['PORTAL_USER_ID']);
		if(!$saveResult->isSuccess())
		{
			$result->addErrors($saveResult->getErrors());
			return $result;
		}
		$saveResultData = $saveResult->getData();
		$file = $saveResultData['FILE'];

		$attachResult = static::attachFile($callId, $file);
		if(!$attachResult->isSuccess())
		{
			$result->addErrors($attachResult->getErrors());
			return $result;
		}

		$result->setData(array(
			'FILE_ID' => $file->getId()
		));

		return $result;
	}

	/**
	 * @param string $callId
	 * @param string $fileName
	 * @return Result
	 * @throws SystemException
	 */
	public static function uploadRecord($callId)
	{
		$result = new Result();
		if(!is_array($_FILES[self::FILE_FIELD]))
		{
			$result->addError(new Error("Error: required parameter " . self::FILE_FIELD . " is not found"));
			return $result;
		}

		$fileArray = $_FILES[self::FILE_FIELD];
		$fileName = $fileArray['name'];

		$allowedExtensions = array('wav', 'mp3');
		if(!in_array(GetFileExtension($fileName), $allowedExtensions))
		{
			$result->addError(new Error("Wrong file extension. Only wav and mp3 are allowed"));
			return $result;
		}

		$statisticRecord = \Bitrix\Voximplant\StatisticTable::getByCallId($callId);
		if(!$statisticRecord)
		{
			$result->addError(new Error("Call is not found in the statistic table. Looks like it is not finished yet."));
			return $result;
		}

		$saveResult = static::saveFile($statisticRecord['CALL_START_DATE']->format("Y-m"), $fileName, $fileArray, $statisticRecord['PORTAL_USER_ID']);
		if(!$saveResult->isSuccess())
		{
			$result->addErrors($saveResult->getErrors());
			return $result;
		}
		$saveResultData = $saveResult->getData();
		$file = $saveResultData['FILE'];

		$attachResult = static::attachFile($callId, $file);
		if(!$attachResult->isSuccess())
		{
			$result->addErrors($attachResult->getErrors());
			return $result;
		}

		$result->setData(array(
			'FILE_ID' => $file->getId()
		));

		return $result;
	}

	public static function searchCrmEntities($phoneNumber)
	{
		$result = new Result();

		if(!Loader::includeModule('crm'))
		{
			$result->addError(new Error('CRM is not installed.'));
			return $result;
		}

		$timeManInstalled = Loader::includeModule('timeman');

		$userId = Security\Helper::getCurrentUserId();
		$searchResult = \CCrmSipHelper::findByPhoneNumber($phoneNumber, array('USER_ID' => $userId));
		$resultData = array();
		$userIds = array();

		$entityNames = array(\CCrmOwnerType::ContactName, \CCrmOwnerType::LeadName, \CCrmOwnerType::CompanyName);
		foreach ($entityNames as $entityName)
		{
			if(isset($searchResult[$entityName]))
			{
				foreach ($searchResult[$entityName] as $entityData)
				{
					$resultData[] = array(
						'CRM_ENTITY_TYPE' => $entityName,
						'CRM_ENTITY_ID' => $entityData['ID'],
						'ASSIGNED_BY_ID' => $entityData['ASSIGNED_BY_ID']
					);
					$userIds[] = $entityData['ASSIGNED_BY_ID'];
				}
			}
		}

		$userCursor = UserTable::getList(array(
			'select' => array('ID', 'UF_PHONE_INNER', 'WORK_PHONE', 'PERSONAL_PHONE' , 'PERSONAL_MOBILE'),
			'filter' => array(
				'=ID' => $userIds
			)
		));

		$userData = array();
		while ($row = $userCursor->fetch())
		{
			$userId = $row['ID'];
			$userData[$userId] = $row;

			if($timeManInstalled)
			{
				$tmUser = new \CTimeManUser($userId);
				$tmSettings = $tmUser->GetSettings(Array('UF_TIMEMAN'));
				if (!$tmSettings['UF_TIMEMAN'])
				{
					$userData[$userId]['TIMEMAN_STATUS'] = 'UNAVAILABLE';
				}
				else
				{
					$userData[$userId]['TIMEMAN_STATUS'] = $tmUser->State();
				}
			}
			else
			{
				$userData[$userId]['TIMEMAN_STATUS'] = 'NOT_INSTALLED';
			}
		}

		foreach ($resultData as $k => $v)
		{
			$row = $userData[$v['ASSIGNED_BY_ID']];
			$resultData[$k]['ASSIGNED_BY'] = array(
				'ID' => $row['ID'],
				'TIMEMAN_STATUS' => $row['TIMEMAN_STATUS'],
				'USER_PHONE_INNER' => $row['UF_PHONE_INNER'],
				'WORK_PHONE' => $row['WORK_PHONE'],
				'PERSONAL_PHONE' => $row['PERSONAL_PHONE'],
				'PERSONAL_MOBILE' => $row['PERSONAL_MOBILE'],
			);
		}

		$result->setData($resultData);
		return $result;
	}

	public static function addExternalLine($number, $name, $restAppId)
	{
		$result = new Result();
		$number = trim($number);
		if($number == '')
		{
			$result->addError(new Error('NUMBER should not be empty'));
			return $result;
		}

		$insertResult = ExternalLineTable::add(array(
			'NUMBER' => $number,
			'NAME' => $name,
			'REST_APP_ID' => $restAppId
		));

		if(!$insertResult->isSuccess())
		{
			$result->addErrors($insertResult->getErrors());
			return $result;
		}

		$result->setData(array(
			'ID' => $insertResult->getId()
		));
		return $result;
	}

	public static function updateExternalLine($number, $name, $restAppId)
	{
		$result = new Result();
		$number = trim($number);
		if($number == '')
		{
			$result->addError(new Error('NUMBER should not be empty'));
			return $result;
		}

		$row = ExternalLineTable::getRow(array(
			'filter' => array(
				'=NUMBER' => $number,
				'=REST_APP_ID' =>$restAppId
			)
		));

		if(!$row)
		{
			$result->addError(new Error('Could not find line with number ' . $number));
			return $result;
		}

		$updateResult = ExternalLineTable::update($row['ID'], array(
			'NAME' => $name
		));

		if(!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
			return $result;
		}

		$updateResult->setData(array(
			'ID' => $updateResult->getId()
		));
		return $updateResult;
	}

	public static function deleteExternalLine($number, $restAppId)
	{
		$result = new Result();
		$number = trim($number);
		if($number == '')
		{
			$result->addError(new Error('NUMBER should not be empty'));
			return $result;
		}

		$row = ExternalLineTable::getRow(array(
			'filter' => array(
				'=NUMBER' => $number,
				'=REST_APP_ID' =>$restAppId
			)
		));

		if(!$row)
		{
			$result->addError(new Error('Could not find line with number ' . $number));
			return $result;
		}

		$deleteResult = ExternalLineTable::delete($row['ID']);
		if(!$deleteResult->isSuccess())
		{
			$result->addErrors($deleteResult->getErrors());
			return $result;
		}

		return $result;
	}

	public static function getExternalLines($restAppId)
	{
		$result = new Result();

		$cursor = ExternalLineTable::getList(array(
			'select' => array('NUMBER', 'NAME'),
			'filter' => array(
				'=REST_APP_ID' =>$restAppId
			)
		));

		$data = array();
		while ($row = $cursor->fetch())
		{
			$data[] = $row;
		}
		$result->setData($data);
		return $result;
	}

	public static function onRestAppInstall($params)
	{
		if(!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return;
		}

		\CVoxImplantUser::clearCache();
	}

	public static function onRestAppDelete($params)
	{
		if(!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return;
		}
		$restAppId = $params['APP_ID'];
		$externalNumberIds = array();

		$cursor = ExternalLineTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=REST_APP_ID' => $restAppId
			)
		));
		while ($row = $cursor->fetch())
		{
			$externalNumberIds[] = $row['ID'];
		}

		foreach ($externalNumberIds as $externalNumberId)
		{
			ExternalLineTable::delete($externalNumberId);
		}

		\CVoxImplantUser::clearCache();
	}

	/**
	 * @param $folderName
	 * @param $fileName
	 * @param $fileArray
	 * @param $userId
	 * @return Result
	 */
	protected static function saveFile($folderName, $fileName, $fileArray, $userId)
	{
		$result = new Result();
		if(!Loader::includeModule('disk'))
		{
			return $result->addError(new Error('Disk module is not installed'));
		}

		$uploadFolder = \CVoxImplantDiskHelper::GetRecordsFolder($folderName);
		if(!$uploadFolder)
		{
			return $result->addError(new Error('Could not create shared folder for call records'));
		}

		$accessCodes = Array();
		$rightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();
		$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

		$accessCodes[] = Array(
			'ACCESS_CODE' => 'U'.intval($userId),
			'TASK_ID' => $fullAccessTaskId,
		);

		$file = $uploadFolder->uploadFile(
			$fileArray,
			array(
				'NAME' => $fileName,
				'CREATED_BY' => $userId
			),
			$accessCodes
		);

		if($file)
		{
			$result->setData(array(
				'FILE' => $file
			));
		}
		else
		{
			$result->addErrors($uploadFolder->getErrors());
		}

		return $result;
	}

	/**
	 * Attaches record to the call and call activity.
	 * @param string $callId
	 * @param \Bitrix\Disk\File $file
	 * @return Result
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Exception
	 */
	protected static function attachFile($callId, $file)
	{
		$result = new Result();

		if(!Loader::includeModule('crm'))
			return $result->addError(new Error("CRM is not installed"));

		$statisticRecord = StatisticTable::getByCallId($callId);
		if(!$statisticRecord)
			return $result->addError(new Error("Call is not found in the statistic table. Looks like it is not finished yet."));

		StatisticTable::update($statisticRecord['ID'], array(
			'CALL_RECORD_ID' => $file->getFileId(),
			'CALL_WEBDAV_ID' => $file->getId()
		));

		if($statisticRecord['CRM_ACTIVITY_ID'])
			$activity = \CCrmActivity::GetByID($statisticRecord['CRM_ACTIVITY_ID'], false);
		else
			$activity = \CCrmActivity::GetByOriginID('VI_'.$statisticRecord['CALL_ID'], false);

		if($activity)
		{
			$activityFields = array(
				'STORAGE_TYPE_ID' => StorageType::Disk,
				'STORAGE_ELEMENT_IDS' => array($file->getId())
			);

			$updateResult = \CCrmActivity::Update($activity['ID'], $activityFields, false);
			if(!$updateResult)
				return $result->addError(new Error(\CCrmActivity::GetLastErrorMessage()));
		}

		return $result;
	}
}
