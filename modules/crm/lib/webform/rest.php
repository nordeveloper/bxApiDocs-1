<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Crm\WebForm;

use \Bitrix\Crm\WebForm\Internals\FormTable;

/**
 * Class Rest
 * @package Bitrix\Crm\WebForm
 */
class Rest
{
	public static function onRestServiceBuildDescription()
	{
		return array(
			'crm' => array(
				'crm.webform.list' => array(__CLASS__, 'getFormList'),
			)
		);
	}

	
	public static function getFormList()
	{
		$result = array();
		$res = FormTable::getList(array(
			'select' => array(
				'ID', 'NAME', 'SECURITY_CODE', 'IS_CALLBACK_FORM'
			),
			'filter' => array(
				'ACTIVE' => 'Y'
			),
			'order' => array(
				'ID' => 'DESC'
			)
		));
		while ($row = $res->fetch())
		{
			$result[] = $row;
		}

		return $result;
	}

}
