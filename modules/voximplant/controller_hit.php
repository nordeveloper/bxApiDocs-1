<?php

use Bitrix\Main\Web\Json;

if(!CModule::IncludeModule("voximplant"))
	return false;

if (is_object($APPLICATION))
	$APPLICATION->RestartBuffer();

while(ob_end_clean());

CVoxImplantHistory::WriteToLog($_POST, 'PORTAL HIT');

$params = $_POST;
$hash = $params["BX_HASH"];
unset($params["BX_HASH"]);

// VOXIMPLANT CLOUD HITS
if(
	!isset($params['BX_TYPE']) && isset($_GET['b24_direct']) && CVoxImplantHttp::CheckDirectRequest($params) ||
	$params['BX_TYPE'] == 'B24' && CVoxImplantHttp::RequestSign($params['BX_TYPE'], md5(implode("|", $params)."|".BX24_HOST_NAME)) === $hash ||
	$params['BX_TYPE'] == 'CP' && CVoxImplantHttp::RequestSign($params['BX_TYPE'], md5(implode("|", $params))) === $hash
)
{
	if ($params["BX_COMMAND"] != "add_history" && !in_array($params["COMMAND"], Array("OutgoingRegister", "AddCallHistory")) && isset($params['PHONE_NUMBER']) && isset($params['ACCOUNT_SEARCH_ID']))
	{
		$params['PHONE_NUMBER'] = $params['ACCOUNT_SEARCH_ID'];
	}

	if (isset($_GET['b24_direct']) && isset($params['PORTAL_USER_ID']) && isset($params['USER_ID']))
	{
		$params['USER_ID'] = $params['PORTAL_USER_ID'];
	}

	if($params["COMMAND"] == "OutgoingRegister")
	{
		if (isset($params['CALLER_ID']) && isset($params['ACCOUNT_SEARCH_ID']))
		{
			$params['CALLER_ID'] = $params['ACCOUNT_SEARCH_ID'];
		}

		$result = CVoxImplantOutgoing::Init(Array(
			'ACCOUNT_SEARCH_ID' => $params['ACCOUNT_SEARCH_ID'],
			'CONFIG_ID' => $params['CONFIG_ID'],
			'USER_ID' => $params['USER_ID'],
			'USER_DIRECT_CODE' => $params['USER_DIRECT_CODE'],
			'PHONE_NUMBER' => $params['PHONE_NUMBER'],
			'CALL_ID' => $params['CALL_ID'],
			'CALL_ID_TMP' => $params['CALL_ID_TMP']? $params['CALL_ID_TMP']: '',
			'CALL_DEVICE' => $params['CALL_DEVICE'],
			'CALLER_ID' => $params['CALLER_ID'],
			'ACCESS_URL' => $params['ACCESS_URL'],
			'CRM' => $params['CRM'],
			'CRM_ENTITY_TYPE' => $params['CRM_ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $params['CRM_ENTITY_ID'],
			'CRM_ACTIVITY_ID' => $params['CRM_ACTIVITY_ID'],
			'CRM_CALL_LIST' => $params['CRM_CALL_LIST'],
			'CRM_BINDINGS' => $params['CRM_BINDINGS'] == '' ? array() : Json::decode($params['CRM_BINDINGS']),
			'SESSION_ID' => $params['SESSION_ID']
		));

		foreach(GetModuleEvents("voximplant", "onCallInit", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, Array(Array(
				'CALL_ID' => $params['CALL_ID'],
				'CALL_ID_TMP' => $params['CALL_ID_TMP']? $params['CALL_ID_TMP']: '',
				'CALL_TYPE' => 1,
				'ACCOUNT_SEARCH_ID' => $params['ACCOUNT_SEARCH_ID'],
				'PHONE_NUMBER' => $params['PHONE_NUMBER'],
				'CALLER_ID' => $params['CALLER_ID'],
			)));
		}

		CVoxImplantHistory::WriteToLog($result, 'OUTGOING REGISTER');

		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "IncomingInvite")
	{
		$result = CVoxImplantIncoming::Init(Array(
			'SEARCH_ID' => $params['PHONE_NUMBER'],
			'CALL_ID' => $params['CALL_ID'],
			'CALLER_ID' => $params['CALLER_ID'],
			'DIRECT_CODE' => $params['DIRECT_CODE'],
			'ACCESS_URL' => $params['ACCESS_URL'],
			'CALLBACK_MODE' => ($params['CALLBACK_MODE'] === 'Y'),
			'LAST_TYPE_CONNECT' => $params['LAST_TYPE_CONNECT'],
			'QUEUE_ID' => $params['QUEUE_ID'],
			'USER_ID' => $params['USER_ID'],
			'SIP_TO' => $params['SIP_TO'],
			'SESSION_ID' => $params['SESSION_ID']
		));

		CVoxImplantHistory::WriteToLog($result, 'INCOMING INVITE: ANSWER');

		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "FailAnswer")
	{
		CVoxImplantMain::SendPullEvent(Array(
			'COMMAND' => 'timeout',
			'USER_ID' => $params['USER_ID'],
			'CALL_ID' => $params['CALL_ID'],
			'MARK' => 'timeout_hit_1'
		));
		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "TransferTimeout")
	{
		CVoxImplantTransfer::Timeout($params['CALL_ID']);

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "TransferCancel")
	{
		CVoxImplantTransfer::Decline($params['CALL_ID'], false);

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "TransferComplete")
	{
		CVoxImplantTransfer::Complete($params['CALL_ID'], $params['CALL_DEVICE']);

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "CompletePhoneTransfer")
	{
		CVoxImplantTransfer::completePhoneTransfer($params['FROM_CALL_ID'], $params['TO_CALL_ID']);

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "StartCall")
	{
		CVoxImplantMain::CallStart($params['CALL_ID'], $params['USER_ID'], $params['CALL_DEVICE'], $params['EXTERNAL'] == 'Y');

		$call = \Bitrix\Voximplant\CallTable::getByCallId($params['CALL_ID']);
		$usersToTimeout = array();
		$usersToCancelPush = array();

		if($call['PORTAL_USER_ID'] > 0)
		{
			$usersToCancelPush[] = $call['PORTAL_USER_ID'];
		}

		if ($call && (int)$call['PORTAL_USER_ID'] === 0)
		{
			$cursor = \Bitrix\Voximplant\Model\QueueUserTable::getList(Array(
				'filter' => Array('=QUEUE_ID' => $call['QUEUE_ID']),
			));
			while ($queue = $cursor->fetch())
			{
				if($params['USER_ID'] == $queue['USER_ID'])
				{
					$usersToCancelPush[] = $queue['USER_ID'];
				}
				else
				{
					$usersToTimeout[] = $queue['USER_ID'];
				}
			}
		}

		if(count($usersToCancelPush) > 0)
		{
			CVoxImplantMain::SendPullEvent(Array(
				'COMMAND' => 'answer_phone',
				'USER_ID' => $usersToCancelPush,
				'CALL_ID' => $call['CALL_ID'],
			));
		}

		if(count($usersToTimeout) > 0)
		{
			CVoxImplantMain::SendPullEvent(Array(
				'COMMAND' => 'timeout',
				'USER_ID' => $usersToTimeout,
				'CALL_ID' => $call['CALL_ID'],
				'MARK' => 'timeout_hit_2'
			));
		}

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "HangupCall")
	{
		$call = \Bitrix\Voximplant\CallTable::getByCallId($params['CALL_ID']);
		$userTimeout = Array();
		if ($call)
		{
			$res = \Bitrix\Voximplant\Model\QueueUserTable::getList(Array(
				'filter' => Array('=QUEUE_ID' => $call['QUEUE_ID']),
			));
			while ($queue = $res->fetch())
			{
				if ($call['TRANSFER_USER_ID'] == $queue['USER_ID'])
					continue;

				$userTimeout[$queue['USER_ID']] = true;
				CVoxImplantMain::SendPullEvent(Array(
					'COMMAND' => 'timeout',
					'USER_ID' => $queue['USER_ID'],
					'CALL_ID' => $call['CALL_ID'],
					'MARK' => 'timeout_hit_3'
				));
			}
			if ($call['TRANSFER_USER_ID'] > 0)
			{
				$userTimeout[$call['TRANSFER_USER_ID']] = true;
				CVoxImplantTransfer::SendPullEvent(Array(
					'COMMAND' => 'timeoutTransfer',
					'USER_ID' => $call['TRANSFER_USER_ID'],
					'CALL_ID' => $call['CALL_ID'],
				));
			}
			if ($call['PORTAL_USER_ID'] > 0 && !$userTimeout[$call['PORTAL_USER_ID']])
			{
				$userTimeout[$call['PORTAL_USER_ID']] = true;
				CVoxImplantMain::SendPullEvent(Array(
					'COMMAND' => 'timeout',
					'USER_ID' => $call['PORTAL_USER_ID'],
					'CALL_ID' => $call['CALL_ID'],
					'MARK' => 'timeout_hit_4'
				));
			}
			if ($call['USER_ID'] > 0 && !$userTimeout[$call['USER_ID']])
			{
				CVoxImplantMain::SendPullEvent(Array(
					'COMMAND' => 'timeout',
					'USER_ID' => $call['USER_ID'],
					'CALL_ID' => $call['CALL_ID'],
					'MARK' => 'timeout_hit_5'
				));
			}
		}
		else
		{
			CVoxImplantMain::SendPullEvent(Array(
				'COMMAND' => 'timeout',
				'USER_ID' => $params['USER_ID'],
				'CALL_ID' => $params['CALL_ID'],
				'MARK' => 'timeout_hit_6'
			));
		}

		CVoxImplantHistory::WriteToLog($call, 'PORTAL HANGUP');

		echo Json::encode(Array('result' => 'OK'));
	}
	else if($params["COMMAND"] == "GetNextAction")
	{
		$result = CVoxImplantIncoming::GetNextAction(Array(
			'SEARCH_ID' => $params['PHONE_NUMBER'],
			'CALL_ID' => $params['CALL_ID'],
			'QUEUE_ID' => $params['QUEUE_ID'],
			'CALLER_ID' => $params['CALLER_ID'],
			'LAST_USER_ID' => $params['LAST_USER_ID'],
			'LAST_TYPE_CONNECT' => $params['LAST_TYPE_CONNECT'],
			'LAST_ANSWER_USER_ID' => $params['LAST_ANSWER_USER_ID'],
		));
		CVoxImplantHistory::WriteToLog($result, 'GET NEXT ACTION');
		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "GetNextInQueue")
	{
		if ($params['QUEUE_ID'] > 0)
		{
			$queueId = (int)$params['QUEUE_ID'];
		}
		else if(isset($params['CALL_ID']))
		{
			$call = \Bitrix\Voximplant\CallTable::getByCallId($params['CALL_ID']);
			if(is_array($call))
			{
				if($call['QUEUE_ID'] > 0)
				{
					$queueId = (int)$call['QUEUE_ID'];
				}
				else
				{
					$config = \Bitrix\Voximplant\ConfigTable::getById($call['CONFIG_ID'])->fetch();
					if(is_array($config) && $config['QUEUE_ID'] > 0)
					{
						$queueId = (int)$config['QUEUE_ID'];
					}
				}
			}
		}

		if($queueId > 0)
			$queueParams = \Bitrix\Voximplant\Model\QueueTable::getById($queueId)->fetch();

		if (in_array($params['LAST_TYPE_CONNECT'], Array(CVoxImplantIncoming::TYPE_CONNECT_DIRECT, CVoxImplantIncoming::TYPE_CONNECT_CRM)))
		{
			$result = CVoxImplantIncoming::GetNextAction(Array(
				'SEARCH_ID' => $params['PHONE_NUMBER'],
				'CALL_ID' => $params['CALL_ID'],
				'QUEUE_ID' => $queueId,
				'CALLER_ID' => $params['CALLER_ID'],
				'LAST_USER_ID' => $params['LAST_USER_ID'],
				'LAST_TYPE_CONNECT' => $params['LAST_TYPE_CONNECT'],
				'LAST_ANSWER_USER_ID' => $params['LAST_ANSWER_USER_ID'],
			));
			CVoxImplantHistory::WriteToLog($result, 'GET NEXT ACTION');
		}
		else if (isset($queueParams['TYPE']) && $queueParams['TYPE'] == CVoxImplantConfig::QUEUE_TYPE_ALL)
		{
			$result = CVoxImplantIncoming::GetQueue(Array(
				'SEARCH_ID' => $params['PHONE_NUMBER'],
				'CALL_ID' => $params['CALL_ID'],
				'CALLER_ID' => $params['CALLER_ID'],
				'LAST_TYPE_CONNECT' => $params['LAST_TYPE_CONNECT'],
			));
			CVoxImplantHistory::WriteToLog($result, 'RESEND IN QUEUE');
		}
		else
		{
			$result = CVoxImplantIncoming::GetNextInQueue(Array(
				'SEARCH_ID' => $params['PHONE_NUMBER'],
				'CALL_ID' => $params['CALL_ID'],
				'CALLER_ID' => $params['CALLER_ID'],
				'LAST_USER_ID' => $params['LAST_USER_ID'],
				'LAST_TYPE_CONNECT' => $params['LAST_TYPE_CONNECT'],
				'LAST_ANSWER_USER_ID' => $params['LAST_ANSWER_USER_ID'],
			));
			CVoxImplantHistory::WriteToLog($result, 'GET NEXT IN QUEUE');
		}

		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "RouteToQueue")
	{
		$result = CVoxImplantIncoming::routeToQueue(array(
			'CALL_ID' => $params['CALL_ID'],
			'QUEUE_ID' => $params['QUEUE_ID']
		));

		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "RouteToUser")
	{
		$routeParams = array(
			'CALL_ID' => $params['CALL_ID'],
		);
		if(isset($params['USER_ID']))
			$routeParams['USER_ID'] = $params['USER_ID'];
		
		if(isset($params['DIRECT_CODE']))
			$routeParams['DIRECT_CODE'] = $params['DIRECT_CODE'];
		
		$result = CVoxImplantIncoming::routeToUser($routeParams);
		echo Json::encode($result);
	}
	else if($params["COMMAND"] == "InterceptCall")
	{
		$result = CVoxImplantIncoming::interceptCall($params['USER_ID'], $params['CALL_ID']);
		echo Json::encode(array(
			'RESULT' => $result ? 'Y' : 'N'
		));
	}
	elseif($params["COMMAND"] == "InviteUsers")
	{
		$callId = $params["CALL_ID"];
		$users = $params["USERS"];

		\Bitrix\Voximplant\Integration\Pull::sendInvite($users, $callId);
	}

	// CONTROLLER OR EMERGENCY HITS
	else if($params["BX_COMMAND"] == "add_history" || $params["COMMAND"] == "AddCallHistory")
	{
		CVoxImplantHistory::WriteToLog($params, 'PORTAL ADD HISTORY');

		if (isset($params['PORTAL_NUMBER']) && isset($params['ACCOUNT_SEARCH_ID']))
		{
			$params['PORTAL_NUMBER'] = $params['ACCOUNT_SEARCH_ID'];
		}

		CVoxImplantHistory::Add($params);

		if (isset($params["balance"]))
		{
			$ViAccount = new CVoxImplantAccount();
			$ViAccount->SetAccountBalance($params["balance"]);
		}

		echo "200 OK";
	}
	elseif($params["COMMAND"] == "IncomingGetConfig")
	{
		$result = CVoxImplantIncoming::GetConfig($params);
		
		echo Json::encode($result);
	}
	elseif($params["COMMAND"] == "OutgoingGetConfig")
	{
		$phoneNumber = (string)$params['PHONE_NUMBER'];
		$lineId = (string)$params['LINE_ID'];

		$specialNumberHandler = CVoxImplantOutgoing::getSpecialNumberHandler($phoneNumber);
		if ($specialNumberHandler)
		{
			$result = $specialNumberHandler->getResponse($params['CALL_ID'], $params['USER_ID'], $phoneNumber);
		}
		else
		{
			$result = CVoxImplantOutgoing::GetConfig($params['USER_ID'], $lineId, $phoneNumber);
		}

		CVoxImplantHistory::WriteToLog($result, 'PORTAL GET OUTGOING CONFIG');

		echo Json::encode($result);
	}

	// CONTROLLER HITS
	elseif (isset($params['BX_TYPE']))
	{
		if($params["COMMAND"] == "AddPhoneNumber")
		{
			$result = CVoxImplantConfig::AddConfigBySearchId($params['PHONE_NUMBER'], $params['COUNTRY_CODE']);

			CVoxImplantHistory::WriteToLog($result, 'CONTROLLER ADD NEW PHONE NUMBER');

			echo Json::encode($result);
		}
		elseif($params["COMMAND"] == "UnlinkExpirePhoneNumber")
		{
			$result = CVoxImplantConfig::DeleteConfigBySearchId($params['PHONE_NUMBER']);
			CVoxImplantHistory::WriteToLog($result, 'CONTROLLER UNLINK EXPIRE PHONE NUMBER');

			echo Json::encode($result);
		}
		elseif($params["COMMAND"] == "UpdateOperatorRequest")
		{
			$params['OPERATOR_CONTRACT'] = \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($params['OPERATOR_CONTRACT']);
			CVoxImplantPhoneOrder::Update($params);

			$result = Array('RESULT' => 'OK');
			CVoxImplantHistory::WriteToLog($result, 'UPDATE OPERATOR REQUEST');

			echo Json::encode($result);
		}
		else if($params["COMMAND"] == "ExternalHungup")
		{
			$res = Bitrix\Voximplant\CallTable::getList(Array(
				'filter' => Array('=CALL_ID' => $params['CALL_ID_TMP']),
			));
			if ($call = $res->fetch())
			{
				Bitrix\Voximplant\CallTable::delete($call['ID']);

				CVoxImplantOutgoing::SendPullEvent(Array(
					'COMMAND' => 'timeout',
					'USER_ID' => $call['USER_ID'],
					'CALL_ID' => $call['CALL_ID'],
					'FAILED_CODE' => intval($params['CALL_FAILED_CODE']),
					'MARK' => 'timeout_hit_7'
				));
				CVoxImplantHistory::WriteToLog($call, 'EXTERNAL CALL HANGUP');
			}
		}
		else if($params["COMMAND"] == "VerifyResult")
		{
			$params['REVIEWER_COMMENT'] = \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($params['REVIEWER_COMMENT']);

			$ViDocs = new CVoxImplantDocuments();
			$ViDocs->SetVerifyResult($params);
			$ViDocs->notifyUserWithVerifyResult($params);
		}
		else if($params["COMMAND"] == "SetSipStatus")
		{
			$sipStatus = ($params["SIP_PAID"] === 'Y');
			CVoxImplantConfig::SetModeStatus(CVoxImplantConfig::MODE_SIP, $sipStatus);
			CVoxImplantHistory::WriteToLog('Sip status set');
		}
		else if($params["COMMAND"] == "AddressVerified")
		{
			$addressVerification = new \Bitrix\VoxImplant\AddressVerification();
			$addressVerification->notifyUserWithVerifyResult($params);
		}
		else if($params["COMMAND"] == "NotifyAdmins")
		{
			$message = (string)$params["MESSAGE"];
			$buttons = Json::decode($params["BUTTONS"]);
			if(!is_array($buttons))
				$buttons = array();
			\Bitrix\Voximplant\Integration\Im::notifyAdmins($message, $buttons);
		}
		else if($params["COMMAND"] == "TranscriptionComplete")
		{
			\Bitrix\Voximplant\Transcript::onTranscriptionComplete(array(
				'SESSION_ID' => $params['SESSION_ID'],
				'TRANSCRIPTION_URL' => $params['TRANSCRIPTION_URL'],
				'COST' => $params['COST'],
				'COST_CURRENCY' => $params['COST_CURRENCY']
			));
		}

		else if($params["COMMAND"] == "StartCallback")
		{
			$callbackParameters = $params["PARAMETERS"];
			if(!is_array($callbackParameters))
			{
				CVoxImplantHistory::WriteToLog('Callback parameters is not an array');
			}

			CVoxImplantOutgoing::restartCallback($callbackParameters);
		}
	}
	else
	{
		CVoxImplantHistory::WriteToLog('Command not found');
		echo "Requested command is not found.";
	}
}
else
{
	CVoxImplantHistory::WriteToLog('request not authorized');
	echo "You don't have access to this page.";
}

CMain::FinalActions();
die();