<?php

namespace Bitrix\Voximplant\Routing;

use Bitrix\Voximplant\Call;
use Bitrix\Voximplant\Security;


class SecurityCheck extends Node
{
	public function getFirstAction(Call $call)
	{
		if($call->getIncoming() != \CVoxImplantMain::CALL_OUTGOING)
		{
			return false;
		}

		$isCallAllowed = Security\Helper::canUserCallNumber(
			$call->getUserId(),
			$call->getCallerId()
		);

		if($isCallAllowed)
		{
			return false;
		}
		else
		{
			return new Action(Command::HANGUP, [
				'CODE' => 403,
				'REASON' => 'User ' . $call->getUserId() . ' is not allowed to call number ' . $call->getCallerId()
			]);
		}
	}

	public function getNextAction(Call $call, array $request = [])
	{
		return new Action(Command::HANGUP, [
			'REASON' => 'Security check failed'
		]);
	}
}