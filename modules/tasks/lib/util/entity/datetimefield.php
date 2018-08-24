<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Tasks\Util\Entity;

/**
 * Entity field class for datetime data type
 * @package bitrix
 * @subpackage main
 */

class DateTimeField extends \Bitrix\Main\Entity\DateTimeField
{
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->addFetchDataModifier(array($this, 'assureValueObject'));
	}

	// tell ORM what kind of type we are, since ORM uses a simple array of standard types to determine
	public function getDataType()
	{
		return 'datetime';
	}

	public function assureValueObject($value)
	{
		if ($value)
		{
			return \Bitrix\Tasks\Util\Type\DateTime::createFromInstance($value);
		}

		return $value;
	}
}