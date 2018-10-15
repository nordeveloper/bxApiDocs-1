<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 10.08.18
 * Time: 12:44
 */

namespace Bitrix\Tasks\Rest\Controllers\Task;

use Bitrix\Tasks\Rest\Controllers\Base;

use \Bitrix\Main\Error;

class Counters extends Base
{
	/**
	 * @param string $type
	 * @see \Bitrix\Tasks\Internals\Counter\Role
	 *
	 * @param int $groupId
	 * @param array $params
	 *
	 * @return array
	 */
	public function getAction($type, $groupId = 0, array $params = array())
	{
		return [];
	}
}