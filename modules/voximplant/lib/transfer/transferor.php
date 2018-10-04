<?php

namespace Bitrix\Voximplant\Transfer;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Voximplant\Call;
use Bitrix\Voximplant\Model\CallTable;
use Bitrix\Voximplant\Model\CallUserTable;
use Bitrix\Voximplant\Result;

class Transferor
{
	/**
	 * Creates child call, which will be used for the call transfer procedure.
	 *
	 * @param string $parentCallId Id of the parent call.
	 * @param int $userId Id of the user, who requested call transfer.
	 * @param string $targetType Transfer target type (@see Target).
	 * @param string $targetId Transfer target id.
	 * @return Result
	 */
	public static function initiateTransfer($parentCallId, $userId, $targetType, $targetId)
	{
		$result = new Result();
		$parentCall = Call::load($parentCallId);

		if(!$parentCall)
		{
			return $result->addError(new Error('Parent call is not found'));
		}

		$transferCall = Call::create([
			'CALL_ID' => static::createCallId(),
			'PARENT_CALL_ID' => $parentCallId,
			'CONFIG_ID' => $parentCall->getConfig()['ID'],
			'USER_ID' => $userId,
			'INCOMING' => \CVoxImplantMain::CALL_OUTGOING,
			'CALLER_ID' => static::createCallerId($targetType, $targetId),
			'ACCESS_URL' => $parentCall->getAccessUrl(),
			'STATUS' => CallTable::STATUS_CONNECTING,
			'DATE_CREATE' => new DateTime()
		]);

		$result->setData([
			'CALL' => $transferCall->toArray()
		]);

		$transferCall->addUsers([$userId], CallUserTable::ROLE_CALLER, CallUserTable::STATUS_CONNECTED);

		$transferCall->getScenario()->sendStartTransfer($userId, $transferCall->getCallId());
		return $result;
	}

	public static function startBlindTransfer($callId, $userId)
	{
		$transferCall = Call::load($callId);
		if(!$transferCall)
		{
			return false;
		}

		$parentCall = Call::load($transferCall->getParentCallId());
		$transferCall->removeUsers([$userId], false);
		$parentCall->removeUsers([$userId]);
	}

	/**
	 * Finishes and removes transfer sub-call.
	 *
	 * @param string $callId Id of the transfer sub-call.
	 * @param string $code SIP code of the call completion.
	 * @param string $reason Text description of the code of the call completion.
	 * @return bool
	 */
	public static function cancelTransfer($callId, $code, $reason)
	{
		$transferCall = Call::load($callId);
		if(!$transferCall)
		{
			return false;
		}

		$transferCall->finish([
			'failedCode' => $code,
			'failedReason' => $reason
		]);

		Call::delete($transferCall->getCallId());
		return true;
	}

	/**
	 * Performs actions when call transfer is complete:
	 * - update call responsible
	 * - update lead responsible
	 * - inform new responsible's browser
	 * - remove temporary call

	 * @param string $callId Id of the transfer sub-call.
	 * @param int $newUserId Id of the user, accepted the call.
	 * @params string $device Device type of the connected user.
	 * @return Result
	 * @throws \Exception
	 */
	public static function completeTransfer($callId, $newUserId, $device)
	{
		$result = new Result();
		$newUserId = (int)$newUserId;

		$transferCall = Call::load($callId);
		if(!$transferCall)
		{
			return $result->addError(new Error('Call ' . $callId . ' is not found'));
		}
		$parentCallId = $transferCall->getParentCallId();
		$parentCall = Call::load($parentCallId);
		if(!$parentCall)
		{
			return $result->addError(new Error('Parent call ' . $parentCallId . ' is not found'));
		}

		$transferorUserId = $transferCall->getUserId();

		if($newUserId == 0)
		{
			$newUserId = \CVoxImplantUser::GetByPhone($transferCall->getCallerId());
		}
		if($newUserId > 0)
		{
			if($parentCall->getUserId() == $transferorUserId)
			{
				$parentCall->updateUserId($newUserId);
			}
			else if($parentCall->getPortalUserId() == $transferorUserId)
			{
				$parentCall->updatePortalUserId($newUserId);
			}
			$parentCall->addUsers([$newUserId], CallUserTable::ROLE_CALLEE, CallUserTable::STATUS_CONNECTED);

			if ($parentCall->isCrmEnabled())
			{
				$config = $parentCall->getConfig();
				if (isset($config['CRM_TRANSFER_CHANGE']) && $config['CRM_TRANSFER_CHANGE'] == 'Y' && $parentCall->getCrmLead() > 0)
				{
					\CVoxImplantCrmHelper::UpdateLead($parentCall->getCrmLead(), Array('ASSIGNED_BY_ID' => $newUserId));
				}
			}

			$transferCall->getSignaling()->sendCompleteTransfer($newUserId, $parentCall->getCallId(), $device);

			\CVoxImplantHistory::TransferMessage($transferorUserId, $newUserId, $parentCall->getCallerId());
		}
		$transferCall->removeUsers([$transferorUserId], false);
		$parentCall->removeUsers([$transferorUserId]);

		Call::delete($callId);
		return $result;
	}

	/**
	 * Return id for the transfer sub-call.
	 *
	 * @return string
	 */
	public static function createCallId()
	{
		return 'transfer.' . uniqid();
	}

	/**
	 * Returns caller id for the transfer sub-call.
	 *
	 * @param string $targetType Transfer target type (@see Target).
	 * @param string $targetId Transfer target id.
	 * @return string
	 * @throws ArgumentException
	 */
	public static function createCallerId($targetType, $targetId)
	{
		if($targetType == Target::USER || $targetType == Target::QUEUE)
		{
			return $targetType . ':' . $targetId;
		}
		else if($targetType == Target::PSTN)
		{
			return $targetId;
		}
		else
		{
			throw new ArgumentException('Unknown target type ' . $targetType);
		}
	}

	/**
	 * Handler for the transfer completion event, when the transfer was performed using SIP phone.
	 *
	 * @param string $firstCallId Id of the transfer initiator call.
	 * @param string $secondCallId Id of the transfer target call.
	 * @param int $initiatorUserId Id of the user, who initiated call transfer.
	 * @throws \Exception
	 */
	public static function completePhoneTransfer($firstCallId, $secondCallId, $initiatorUserId)
	{
		$firstCall = Call::load($firstCallId);
		$secondCall = Call::load($secondCallId);

		if (!$firstCall || !$secondCall)
		{
			return;
		}

		$toUserId = $secondCall->getPortalUserId();
		\CVoxImplantHistory::TransferMessage($firstCall->getUserId(), $toUserId, $firstCall->getCallerId());

		$firstCall->removeUsers([$initiatorUserId]);
		$secondCall->removeUsers([$initiatorUserId]);

		if(!$firstCall->isInternalCall() && !$secondCall->isInternalCall())
		{
			// both calls are external, nothing to do
			return;
		}
		if($firstCall->isInternalCall() && $secondCall->isInternalCall())
		{
			// both calls are internal, nothing to do
			return;
		}
		if($firstCall->isInternalCall())
		{
			$toUserId = $firstCall->getPortalUserId();
			static::updateCrmData($secondCall, $firstCall, $toUserId);
		}
		else
		{
			//second call is internal call
			$toUserId = $secondCall->getPortalUserId();
			static::updateCrmData($firstCall, $secondCall, $toUserId);
		}
	}

	protected static function updateCrmData(Call $clientCall, Call $internalCall, $newUserId)
	{
		$clientCall->updateUserId($newUserId);

		$internalCall->update([
			'CRM' => $clientCall->isCrmEnabled() ? 'Y' : 'N',
			'CRM_ENTITY_TYPE' => $clientCall->getCrmEntityType(),
			'CRM_ENTITY_ID' => $clientCall->getCrmEntityId(),
			'CRM_ACTIVITY_ID' => $clientCall->getCrmActivityId()
		]);

		if($clientCall->isCrmEnabled())
		{
			$config = $clientCall->getConfig();
			if (isset($config['CRM_TRANSFER_CHANGE']) && $config['CRM_TRANSFER_CHANGE'] == 'Y' && $clientCall->getCrmLead() > 0 && $newUserId)
			{
				\CVoxImplantCrmHelper::UpdateLead($clientCall->getCrmLead(), ['ASSIGNED_BY_ID' => $newUserId]);
			}
		}

		$internalCall->getSignaling()->sendReplaceCallerId($newUserId, $clientCall->getCallerId());
	}
}