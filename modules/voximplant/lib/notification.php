<?php

namespace Bitrix\Voximplant;

class Notification
{
	public static function isBalanceTooLow()
	{
		$account = new \CVoxImplantAccount();

		if (\CVoxImplantAccount::getPayedFlag() !== 'Y')
		{
			return false;
		}

		$balance = $account->getAccountBalance(false);
		$balanceThreshold = $account->getBalanceThreshold();

		$hasCallsInLastFiveDays = false;
		$lastPaidCallTimestamp = \CVoxImplantHistory::getLastPaidCallTimestamp();
		if($lastPaidCallTimestamp > 0)
		{
			$interval = time() - $lastPaidCallTimestamp;

			if($interval < 432000) // 5 days
			{
				$hasCallsInLastFiveDays = true;
			}
		}

		if (\CVoxImplantPhone::getRentedNumbersCount() == 0 && $hasCallsInLastFiveDays == false)
		{
			return false;
		}

		if ($balanceThreshold > 0 && $balance < $balanceThreshold)
		{
			return true;
		}

		return false;
	}
}