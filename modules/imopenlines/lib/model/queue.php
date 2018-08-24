<?php
namespace Bitrix\ImOpenLines\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class QueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CONFIG_ID int mandatory
 * <li> USER_ID int mandatory
 * <li> LAST_ACTIVITY_DATE datetime optional
 * </ul>
 *
 * @package Bitrix\Imopenlines
 **/

class QueueTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_queue';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('QUEUE_ENTITY_ID_FIELD'),
			),
			'CONFIG_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('QUEUE_ENTITY_CONFIG_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('QUEUE_ENTITY_USER_ID_FIELD'),
			),
			'LAST_ACTIVITY_DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('QUEUE_ENTITY_LAST_ACTIVITY_DATE_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
		);
	}

	/**
	 * Return current date for DATE_CREATE field.
	 *
	 * @return array
	 */
	public static function getCurrentDate()
	{
		return new \Bitrix\Main\Type\DateTime();
	}
}