<?php

namespace Bitrix\Voximplant\Routing;

use Bitrix\Voximplant\Call;

class Pstn extends Node
{
	protected $phoneNumber;
	protected $failureRule;

	public function __construct($phoneNumber, $failureRule)
	{
		parent::__construct();
		$this->phoneNumber = $phoneNumber;
		$this->failureRule = $failureRule;
	}

	public function getFirstAction(Call $call)
	{
		return new Action(Command::PSTN, [
			'PHONE_NUMBER' => NormalizePhone($this->phoneNumber, 1),
		]);
	}

	public function getNextAction(Call $call, array $request = [])
	{
		if($this->failureRule == Command::VOICEMAIL)
		{
			return new Action(Command::VOICEMAIL);
		}
		else
		{
			return new Action(Command::HANGUP);
		}
	}
}