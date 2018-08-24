<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Voximplant as VI;

class CVoxImplantTransfer
{
	const TYPE_PHONE = 'phone';
	const TYPE_USER = 'user';
	const DEVICE_WEBRTC = 'WEBRTC';
	const DEVICE_PHONE = 'PHONE';
	const DEVICE_EXTERNAL = 'EXTERNAL';

	public static function Invite($callId, $transferType, $transferUserId = 0, $transferPhone = '')
	{
		if($transferType !== self::TYPE_USER && $transferType !== self::TYPE_PHONE)
			throw new \Bitrix\Main\ArgumentException('unsupported parameter value', 'transferType');

		$transferUserId = intval($transferUserId);
		$transferPhone = CVoxImplantPhone::Normalize($transferPhone);

		if($transferType == self::TYPE_PHONE && $transferPhone == '')
			throw new \Bitrix\Main\ArgumentException('transferPhone is empty', 'transferPhone');

		if($transferType == self::TYPE_USER && $transferUserId == 0)
			throw new \Bitrix\Main\ArgumentException('transferUserId is empty', 'transferUserId');

		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		if ($call['TRANSFER_TYPE'] != '')
			self::Cancel($callId);


		$call['TRANSFER_USER_ID'] = $transferUserId;

		VI\CallTable::update(
			$call['ID'],
			array(
				'TRANSFER_TYPE' => $transferType,
				'TRANSFER_USER_ID' => $transferUserId,
				'TRANSFER_PHONE' => $transferPhone
			)
		);

		$call['USER_HAVE_PHONE'] = 'N';

		if($transferType === self::TYPE_USER)
		{
			$res = \Bitrix\Main\UserTable::getList(array(
				'select' => Array('ID', 'IS_ONLINE', 'UF_VI_PHONE', 'ACTIVE'),
				'filter' => Array('=ID' => $call['TRANSFER_USER_ID'], '=ACTIVE' => 'Y'),
			));
			if ($userData = $res->fetch())
			{
				$call['USER_HAVE_PHONE'] = $userData['UF_VI_PHONE'];
			}

			$crmData = Array();
			if ($call['CRM'] == 'Y')
				$crmData = CVoxImplantCrmHelper::GetDataForPopup($call['CALL_ID'], $call['CALLER_ID'], $transferUserId);

			self::SendPullEvent(Array(
				'COMMAND' => 'inviteTransfer',
				'USER_ID' => $transferUserId,
				'CALL_ID' => $call['CALL_ID'],
				'CALLER_ID' => $call['CALLER_ID'],
				'PORTAL_NUMBER' => $call['PORTAL_NUMBER'],
				'CRM' => $crmData,
			));
		}
		else if($transferType === self::TYPE_PHONE)
		{
			$outgoingConfig = static::getTransferConfig($call);
		}

		$command['COMMAND'] = 'inviteTransfer';
		$command['OPERATOR_ID'] = $call['USER_ID'];
		$command['TRANSFER_TYPE'] = $transferType;
		$command['TRANSFER_USER_ID'] = $call['TRANSFER_USER_ID'];
		$command['TRANSFER_PHONE'] = $transferPhone;
		$command['USER_HAVE_PHONE'] = $call['USER_HAVE_PHONE'];
		$command['CONFIG'] = $outgoingConfig;

		$http = new \Bitrix\Main\Web\HttpClient();
		$http->waitResponse(false);
		$http->post($call['ACCESS_URL'], json_encode($command));

		return true;
	}

	public static function Cancel($callId)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		VI\CallTable::update(
			$call['ID'],
			array(
				'TRANSFER_USER_ID' => 0,
				'TRANSFER_TYPE' => null,
				'TRANSFER_PHONE' => null
			)
		);

		$command['COMMAND'] = 'cancelTransfer';
		$command['OPERATOR_ID'] = $call['USER_ID'];
		$command['TRANSFER_USER_ID'] = $call['TRANSFER_USER_ID'];

		$http = new \Bitrix\Main\Web\HttpClient();
		$http->waitResponse(false);
		$http->post($call['ACCESS_URL'], json_encode($command));

		self::SendPullEvent(Array(
			'COMMAND' => 'cancelTransfer',
			'USER_ID' => $call['TRANSFER_USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		return true;
	}

	/**
	 * @param $callId
	 * @return \Bitrix\Main\Result
	 */
	public static function Wait($callId)
	{
		$result = new \Bitrix\Main\Result();
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
		{
			$result->addError(new \Bitrix\Main\Error('Call is not found', 'ERROR_NOT_FOUND'));
			return $result;
		}

		if ($call['TRANSFER_TYPE'] == '')
		{
			$result->addError(new \Bitrix\Main\Error('Wrong call state', 'ERROR_WRONG_STATE'));
			return $result;
		}

		$command = array(
			'COMMAND' => 'waitTransfer',
			'OPERATOR_ID' => $call['USER_ID']
		);

		$http = new \Bitrix\Main\Web\HttpClient(array(
			'waitResponse' => false
		));
		$http->post($call['ACCESS_URL'], \Bitrix\Main\Web\Json::encode($command));
		self::SendPullEvent(Array(
			'COMMAND' => 'waitTransfer',
			'USER_ID' => $call['USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		return $result;
	}

	public static function Answer($callId)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		$command['COMMAND'] = 'waitTransfer';
		$command['OPERATOR_ID'] = $call['USER_ID'];

		$http = new \Bitrix\Main\Web\HttpClient();
		$http->waitResponse(false);
		$http->post($call['ACCESS_URL'], json_encode($command));

		self::SendPullEvent(Array(
			'COMMAND' => 'waitTransfer',
			'USER_ID' => $call['USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		self::SendPullEvent(Array(
			'COMMAND' => 'timeoutTransfer',
			'USER_ID' => $call['TRANSFER_USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		return true;
	}

	public static function Ready($callId)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		$answer['COMMAND'] = 'transferConnect';
		$answer['OPERATOR_ID'] = $call['USER_ID'];

		$http = new \Bitrix\Main\Web\HttpClient();
		$http->waitResponse(false);
		$http->post($call['ACCESS_URL'], json_encode($answer));

		return true;
	}

	public static function Complete($callId, $device)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		VI\CallTable::update(
			$call['ID'],
			array(
				'USER_ID' => ($call['TRANSFER_USER_ID'] > 0 ? $call['TRANSFER_USER_ID'] : $call['USER_ID']),
				'TRANSFER_USER_ID' => 0,
				'TRANSFER_TYPE' => null,
				'TRANSFER_PHONE' => null
			)
		);

		CVoxImplantHistory::TransferMessage($call['USER_ID'], $call['TRANSFER_USER_ID'], $call['CALLER_ID'], $call['TRANSFER_PHONE']);

		self::SendPullEvent(Array(
			'COMMAND' => 'completeTransfer',
			'USER_ID' => $call['USER_ID'],
			'TRANSFER_USER_ID' => $call['TRANSFER_USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		$crmDataSend = false;
		if ($call['CRM'] == 'Y' && $call['CONFIG_ID'] > 0)
		{
			$config = CVoxImplantConfig::GetConfig($call['CONFIG_ID']);
			if (isset($config['CRM_TRANSFER_CHANGE']) && $config['CRM_TRANSFER_CHANGE'] == 'Y')
			{
				if ($call['CRM_LEAD'] > 0)
				{
					$crmData = Array(
						'LEAD_DATA' => Array(
							'ID' => $call['CRM_LEAD'],
							'ASSIGNED_BY_ID' => 0,
						)
					);
				}
				else
				{
					$crmData = CVoxImplantCrmHelper::GetDataForPopup($call['CALL_ID'], $call['CALLER_ID']);
				}

				if (isset($crmData['LEAD_DATA']) && $crmData['LEAD_DATA']['ASSIGNED_BY_ID'] >= 0 && $call['TRANSFER_USER_ID'] > 0 && $crmData['LEAD_DATA']['ASSIGNED_BY_ID'] != $call['TRANSFER_USER_ID'])
				{
					CVoxImplantCrmHelper::UpdateLead($crmData['LEAD_DATA']['ID'], Array('ASSIGNED_BY_ID' => $call['TRANSFER_USER_ID']));
					$crmDataSend = CVoxImplantCrmHelper::GetDataForPopup($call['CALL_ID'], $call['CALLER_ID'], $call['TRANSFER_USER_ID']);
				}
			}
		}

		if($device !== self::DEVICE_EXTERNAL)
		{
			self::SendPullEvent(Array(
				'COMMAND' => 'completeTransfer',
				'USER_ID' => $call['TRANSFER_USER_ID'],
				'TRANSFER_USER_ID' => $call['TRANSFER_USER_ID'],
				'CALL_DEVICE' => $device,
				'CALL_ID' => $call['CALL_ID'],
				'CRM' => $crmDataSend
			));
		}

		return true;
	}

	public static function Decline($callId, $send = true)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		VI\CallTable::update(
			$call['ID'],
			array(
				'TRANSFER_USER_ID' => 0,
				'TRANSFER_TYPE' => null,
				'TRANSFER_PHONE' => null
			)
		);

		if ($send)
		{
			$command['COMMAND'] = 'declineTransfer';
			$command['OPERATOR_ID'] = $call['USER_ID'];

			$http = new \Bitrix\Main\Web\HttpClient();
			$http->waitResponse(false);
			$http->post($call['ACCESS_URL'], json_encode($command));
		}

		self::SendPullEvent(Array(
			'COMMAND' => 'declineTransfer',
			'USER_ID' => $call['USER_ID'],
			'CALL_ID' => $call['CALL_ID']
		));

		if($call['TRANSFER_TYPE'] === self::TYPE_USER)
		{
			self::SendPullEvent(Array(
				'COMMAND' => 'timeoutTransfer',
				'USER_ID' => $call['TRANSFER_USER_ID'],
				'CALL_ID' => $call['CALL_ID']
			));
		}

		return true;
	}

	public static function Timeout($callId)
	{
		$call = VI\CallTable::getByCallId($callId);
		if (!$call)
			return false;

		VI\CallTable::update(
			$call['ID'],
			array(
				'TRANSFER_USER_ID' => 0,
				'TRANSFER_TYPE' => null,
				'TRANSFER_PHONE' => null
			)
		);

		if($call['TRANSFER_TYPE'] === self::TYPE_USER)
		{
			self::SendPullEvent(array(
				'COMMAND' => 'timeoutTransfer',
				'USER_ID' => $call['TRANSFER_USER_ID'],
				'CALL_ID' => $call['CALL_ID']
			));

			self::SendPullEvent(Array(
				'COMMAND' => 'declineTransfer',
				'USER_ID' => $call['USER_ID'],
				'CALL_ID' => $call['CALL_ID']
			));
		}

		return true;
	}

	public static function completePhoneTransfer($fromCallId, $toCallId)
	{
		$callFrom = VI\CallTable::getByCallId($fromCallId);
		$callTo = VI\CallTable::getByCallId($toCallId);

		if ($callFrom == false || $callTo == false)
			return false;


		$toUserId= $callTo['PORTAL_USER_ID'];
		CVoxImplantHistory::TransferMessage($callFrom['USER_ID'], $toUserId, $callFrom['CALLER_ID']);
		VI\CallTable::update(
			$callFrom['ID'],
			array(
				'USER_ID' => $toUserId
			)
		);

		if($callFrom['CRM_ENTITY_TYPE'] != '' && $callFrom['CRM_ENTITY_ID'] > 0)
		{
			$callTo['CRM_ENTITY_TYPE'] = $callFrom['CRM_ENTITY_TYPE'];
			$callTo['CRM_ENTITY_ID'] = $callFrom['CRM_ENTITY_ID'];
		}

		if($callFrom['CRM_ACTIVITY_ID'] > 0)
		{
			$callTo['CRM_ACTIVITY_ID'] = $callFrom['CRM_ACTIVITY_ID'];
		}

		VI\CallTable::update($callTo['ID'], $callTo);

		$crmData = false;
		if($callFrom['CRM'] === 'Y')
		{
			$crmData = CVoxImplantCrmHelper::GetDataForPopup($callFrom['CALL_ID'], $callFrom['CALLER_ID'], $callFrom['USER_ID']);
			if ($callFrom['CONFIG_ID'] > 0)
			{
				$config = CVoxImplantConfig::GetConfig($callFrom['CONFIG_ID']);
				if (isset($config['CRM_TRANSFER_CHANGE']) && $config['CRM_TRANSFER_CHANGE'] == 'Y')
				{
					if ($callFrom['CRM_LEAD'] > 0 && $toUserId)
					{
						CVoxImplantCrmHelper::UpdateLead(
							$callFrom['CRM_LEAD'],
							array('ASSIGNED_BY_ID' => $toUserId)
						);
					}
					else if (
						isset($crmData['LEAD_DATA'])
						&& $crmData['LEAD_DATA']['ASSIGNED_BY_ID'] >= 0
						&& $callTo['USER_ID'] > 0
						&& $crmData['LEAD_DATA']['ASSIGNED_BY_ID'] != $callTo['USER_ID']
					)
					{
						CVoxImplantCrmHelper::UpdateLead(
							$crmData['LEAD_DATA']['ID'],
							array('ASSIGNED_BY_ID' => $toUserId)
						);
					}
				}
			}
		}

		self::SendPullEvent(Array(
			'COMMAND' => 'replaceCallerId',
			'USER_ID' => $toUserId,
			'CALL_ID' => $callTo['CALL_ID'],
			'CALLER_ID' => $callFrom['CALLER_ID'],
			'CRM' => $crmData
		));

		return true;
	}

	public static function SendPullEvent($params)
	{
		if (!CModule::IncludeModule('pull') || !CPullOptions::GetQueueServerStatus() || $params['USER_ID'] <= 0)
			return false;

		if (empty($params['COMMAND']))
			return false;

		$config = Array();
		if ($params['COMMAND'] == 'inviteTransfer')
		{
			$config = Array(
				"callId" => $params['CALL_ID'],
				"callerId" => $params['CALLER_ID'],
				"lineNumber" => $params['PORTAL_NUMBER'],
				"phoneNumber" => $params['PHONE_NAME'],
				"chatId" => 0,
				"chat" => array(),
				"application" => $params['APPLICATION'],
				"CRM" => $params['CRM'],
			);
		}
		else if ($params['COMMAND'] == 'completeTransfer')
		{
			$config = Array(
				"callId" => $params['CALL_ID'],
				"transferUserId" => $params['TRANSFER_USER_ID'],
				"callDevice" => $params['CALL_DEVICE'],
				"CRM" => $params['CRM']? $params['CRM']: false,
			);
		}
		else if ($params['COMMAND'] == 'replaceCallerId')
		{
			$config = Array(
				"callId" => $params['CALL_ID'],
				"callerId" => $params['CALLER_ID'],
				"CRM" => $params['CRM']? $params['CRM']: false,
			);
		}
		else
		{
			$config["callId"] = $params['CALL_ID'];
		}
		if (isset($params['MARK']))
		{
			$config['mark'] = $params['MARK'];
		}

		$call = VI\CallTable::getByCallId($params['CALL_ID']);
		$config['showCrmCard'] = ($config['CRM'] !== false);
		if ($config['showCrmCard'])
		{
			$config['crmEntityType'] = $call['CRM_ENTITY_TYPE'];
			$config['crmEntityId'] = $call['CRM_ENTITY_ID'];
			$config['crmActivityId'] = $call['CRM_ACTIVITY_ID'];
			$config['crmActivityEditUrl'] = CVoxImplantCrmHelper::getActivityEditUrl($call['CRM_ACTIVITY_ID']);
		}

		$config['config'] = CVoxImplantConfig::getConfigForPopup($params['CALL_ID']);

		\Bitrix\Pull\Event::add($params['USER_ID'],
			Array(
				'module_id' => 'voximplant',
				'command' => $params['COMMAND'],
				'params' => $config
			)
		);

		return true;
	}

	/**
	 * Returns line config to create outgoing call.
	 * @param array $callFields Call record, as return from the b_voximplant_call table.
	 * @see \Bitrix\Voximplant\CallTable.
	 * Return array.
	 */
	protected static function getTransferConfig(array $callFields)
	{
		$callConfig = VI\ConfigTable::getById($callFields['CONFIG_ID'])->fetch();
		if($callConfig == false)
		{
			throw new \Bitrix\Main\SystemException('Config not found for call ' .$callFields['CALL_ID']);
		}

		if($callConfig['FORWARD_LINE'] ==  CVoxImplantConfig::FORWARD_LINE_DEFAULT)
		{
			$transferConfig = CVoxImplantConfig::GetBriefConfig(array(
				'ID' => $callFields['CONFIG_ID']
			));
		}
		else
		{
			$transferConfig = CVoxImplantConfig::GetBriefConfig(array(
				'SEARCH_ID' => $callConfig['FORWARD_LINE']
			));
		}

		return $transferConfig;
	}
}
?>