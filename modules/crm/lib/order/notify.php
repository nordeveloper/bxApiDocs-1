<?php

namespace Bitrix\Crm\Order;

use Bitrix\Sale;
use Bitrix\Main;

if (!Main\Loader::includeModule('sale'))
{
	return;
}

/**
 * Class Notify
 * @package Bitrix\Crm\Order
 */
class Notify extends Sale\Notify
{

}