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
	 * @return void
	 * @throws \Exception
	 */
	public static function completeTransfer($callId, $newUserId)
	{
		$newUserId = (int)$newUserId;

		$transferCall = Call::load($callId);
		$parentCall = Call::load($transferCall->getParentCallId());

		$transferorUserId = $transferCall->getUserId();
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

			$transferCall->getSignaling()->sendCompleteTransfer($newUserId, $parentCall->getCallId());

			\CVoxImplantHistory::TransferMessage($transferorUserId, $newUserId, $parentCall->getCallerId());
		}
		$transferCall->removeUsers([$transferorUserId]);
		$parentCall->removeUsers([$transferorUserId]);

		Call::delete($callId);
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
	 * @param string $initiatorCallId Id of the transfer initiator call.
	 * @param string $targetCallId Id of the transfer target call.
	 * @throws \Exception
	 */
	public static function completePhoneTransfer($initiatorCallId, $targetCallId)
	{
		$initiatorCall = Call::load($initiatorCallId);
		$targetCall = Call::load($targetCallId);

		if (!$initiatorCall || !$targetCall)
		{
			return;
		}

		$initiatorUserId = $targetCall->getUserId();
		$toUserId = $targetCall->getPortalUserId();
		\CVoxImplantHistory::TransferMessage($initiatorCall->getUserId(), $toUserId, $initiatorCall->getCallerId());

		$initiatorCall->removeUsers([$initiatorUserId]);
		$initiatorCall->updateUserId($toUserId);

		$targetCall->update([
			'CRM' => $initiatorCall->isCrmEnabled() ? 'Y' : 'N',
			'CRM_LEAD' => $initiatorCall->getCrmLead(),
			'CRM_ENTITY_TYPE' => $initiatorCall->getCrmEntityType(),
			'CRM_ENTITY_ID' => $initiatorCall->getCrmEntityId(),
			'CRM_ACTIVITY_ID' => $initiatorCall->getCrmActivityId()
		]);

		if($initiatorCall->isCrmEnabled())
		{
			$config = $initiatorCall->getConfig();
			if (isset($config['CRM_TRANSFER_CHANGE']) && $config['CRM_TRANSFER_CHANGE'] == 'Y' && $initiatorCall->getCrmLead() > 0 && $toUserId)
			{
				\CVoxImplantCrmHelper::UpdateLead($initiatorCall->getCrmLead(), ['ASSIGNED_BY_ID' => $toUserId]);
			}
		}

		$targetCall->getSignaling()->sendReplaceCallerId($toUserId, $initiatorCall->getCallerId());
	}
}