<?php
namespace Bitrix\Voximplant;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class BlacklistTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PHONE_NUMBER string(20) optional
 * </ul>
 *
 * @package Bitrix\Voximplant
 **/

class BlacklistTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_voximplant_blacklist';
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
				'title' => Loc::getMessage('BLACKLIST_ENTITY_ID_FIELD'),
			),
			'PHONE_NUMBER' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePhoneNumber'),
				'title' => Loc::getMessage('BLACKLIST_ENTITY_PHONE_NUMBER_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for PHONE_NUMBER field.
	 *
	 * @return array
	 */
	public static function validatePhoneNumber()
	{
		return array(
			new Entity\Validator\Length(null, 20),
		);
	}
}
?>