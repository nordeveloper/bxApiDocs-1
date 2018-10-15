<?php
namespace Bitrix\Crm\Controller;
use Bitrix\Main;
use Bitrix\Main\Web\Uri;

use Bitrix\Crm;
use Bitrix\Crm\Search\SearchEnvironment;

class Order extends Main\Engine\Controller
{
	//BX.ajax.runAction("crm.api.order.searchBuyer", { data: { search: "John Smith", options: {} } });
	public function searchBuyerAction($search, $options)
	{
		if (!is_array($options))
		{
			$options = [];
		}

		$filter = \Bitrix\Main\UserUtils::getAdminSearchFilter([
			'FIND' => $search
		]);
		$filter['=IS_REAL_USER'] = 'Y';
		$filter['=ACTIVE'] = 'Y';
		$userData = \Bitrix\Main\UserTable::getList(array(
			'filter' => $filter,
			'select' => ["ID", "LOGIN", "ACTIVE", "EMAIL", "NAME", "LAST_NAME", "SECOND_NAME"],
			'data_doubling' => false
		));

		$result = [];
		$nameFormat = \CSite::getNameFormat(false);
		while ($user = $userData->fetch())
		{
			$result[] = [
				'title' => \CUser::FormatName(
					$nameFormat,
					array(
						'LOGIN' => $user['LOGIN'],
						'NAME' => $user['NAME'],
						'LAST_NAME' => $user['LAST_NAME'],
						'SECOND_NAME' => $user['SECOND_NAME']
					),
					true,
					false
				),
				'subtitle' => $user['LOGIN'],
				'email' => $user['EMAIL'],
				'id' => $user['ID']
			];
		}

		if (empty($result) && is_array($options['emptyItem']) && !empty($options['emptyItem']))
		{
			$result[] = $options['emptyItem'];
		}

		return $result;
	}
}
