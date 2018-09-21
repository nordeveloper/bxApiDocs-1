<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Test\Typography;

use Bitrix\Main\ORM\Objectify\Collection;

/**
 * @package    bitrix
 * @subpackage main
 */
class Books extends Collection
{
	public static function dataClass()
	{
		return BookTable::class;
	}
}
