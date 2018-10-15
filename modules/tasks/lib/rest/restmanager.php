<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 10.08.18
 * Time: 12:36
 */
namespace Bitrix\Tasks\Rest;

class RestManager extends \IRestService
{
	public static function onRestGetModule()
	{
		return ['MODULE_ID'=>'tasks'];
	}
}