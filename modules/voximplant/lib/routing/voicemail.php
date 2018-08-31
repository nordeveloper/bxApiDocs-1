<?php

namespace Bitrix\Voximplant\Routing;

use Bitrix\Voximplant\Call;

class Voicemail extends Node
{
	protected $reason;

	public function __construct($reason = '')
	{
		parent::__construct();
		$this->reason = $reason;
	}

	public function getFirstAction(Call $call)
	{
		return new Action(Command::VOICEMAIL, ['REASON' => $this->reason]);
	}

	public function getNextAction(Call $call, array $request = [])
	{
		return new Action(Command::VOICEMAIL, ['REASON' => $this->reason]);
	}

}