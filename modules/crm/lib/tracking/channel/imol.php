<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Crm\Tracking\Channel;

use Bitrix\Main\Loader;
use Bitrix\ImConnector;

/**
 * Class Imol
 *
 * @package Bitrix\Crm\Tracking\Channel
 */
class Imol extends Base
{
	protected $code = self::Imol;

	/**
	 * Imol constructor.
	 *
	 * @param string $connectorCode Connector code.
	 */
	public function __construct($connectorCode)
	{
		$this->value = $connectorCode;
	}

	/**
	 * Return true if supports detecting trace.
	 *
	 * @return bool
	 */
	public function isSupportDetecting()
	{
		switch ($this->getValue())
		{
			case 'livechat':
				return false;

			default:
				return true;
		}
	}

	/**
	 * Return true if can use.
	 *
	 * @return bool
	 */
	public function canUse()
	{
		if (!Loader::includeModule('imopenlines') || ! Loader::includeModule('imconnector'))
		{
			return false;
		}

		return parent::canUse();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		if ($this->canUse())
		{
			$names = ImConnector\Connector::getListConnectorReal(20);
			if (isset($names[$this->getValue()]))
			{
				return $names[$this->getValue()];
			}
		}

		return parent::getName();
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return parent::getName();
	}
}