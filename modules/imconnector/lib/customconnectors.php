<?php
namespace Bitrix\ImConnector;

use \Bitrix\Main\Event,
	\Bitrix\Main\EventResult;

use \Bitrix\ImConnector\Input\ReceivingMessage,
	\Bitrix\ImConnector\Input\DeactivateConnector,
	\Bitrix\ImConnector\Input\ReceivingStatusReading,
	\Bitrix\ImConnector\Input\ReceivingStatusDelivery;

class CustomConnectors
{
	const PREFIX = 'custom_';

	const DEFAULT_DEL_EXTERNAL_MESSAGES = true;
	const DEFAULT_EDIT_INTERNAL_MESSAGES = true;
	const DEFAULT_DEL_INTERNAL_MESSAGES = true;
	const DEFAULT_NEWSLETTER = true;
	const DEFAULT_NEED_SYSTEM_MESSAGES = true;
	const DEFAULT_NEED_SIGNATURE = true;
	const DEFAULT_CHAT_GROUP = false;

	/** @var array(\Bitrix\ImConnector\CustomConnectors) */
	private static $instance;
	private static $customConnectors = array();

	public static function getInstance()
	{
		if (empty(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{
		$event = new Event(Library::MODULE_ID, Library::EVENT_REGISTRATION_CUSTOM_CONNECTOR);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult != EventResult::ERROR && $params = $eventResult->getParameters())
			{
				if (isset($params['ID']) && isset($params['NAME']) && isset($params['COMPONENT']) && isset($params['ICON']['DATA_IMAGE']))
				{
					self::$customConnectors[$params['ID']] = array(
						'ID' => $params['ID'],
						'NAME' => $params['NAME'],
						'COMPONENT' => $params['COMPONENT'],
						'ICON' => $params['ICON']
					);

					if (isset($params['ICON_DISABLED']))
						self::$customConnectors[$params['ID']]['ICON_DISABLED'] = $params['ICON_DISABLED'];

					if (isset($params['DEL_EXTERNAL_MESSAGES']) && ($params['DEL_EXTERNAL_MESSAGES'] === true || $params['DEL_EXTERNAL_MESSAGES'] === false))
						self::$customConnectors[$params['ID']]['DEL_EXTERNAL_MESSAGES'] = $params['DEL_EXTERNAL_MESSAGES'];
					else
						self::$customConnectors[$params['ID']]['DEL_EXTERNAL_MESSAGES'] = self::DEFAULT_DEL_EXTERNAL_MESSAGES;

					if (isset($params['EDIT_INTERNAL_MESSAGES']) && ($params['EDIT_INTERNAL_MESSAGES'] === true || $params['EDIT_INTERNAL_MESSAGES'] === false))
						self::$customConnectors[$params['ID']]['EDIT_INTERNAL_MESSAGES'] = $params['EDIT_INTERNAL_MESSAGES'];
					else
						self::$customConnectors[$params['ID']]['EDIT_INTERNAL_MESSAGES'] = self::DEFAULT_EDIT_INTERNAL_MESSAGES;

					if (isset($params['DEL_INTERNAL_MESSAGES']) && ($params['DEL_INTERNAL_MESSAGES'] === true || $params['DEL_INTERNAL_MESSAGES'] === false))
						self::$customConnectors[$params['ID']]['DEL_INTERNAL_MESSAGES'] = $params['DEL_INTERNAL_MESSAGES'];
					else
						self::$customConnectors[$params['ID']]['DEL_INTERNAL_MESSAGES'] = self::DEFAULT_DEL_INTERNAL_MESSAGES;

					if (isset($params['NEWSLETTER']) && ($params['NEWSLETTER'] === true || $params['NEWSLETTER'] === false))
						self::$customConnectors[$params['ID']]['NEWSLETTER'] = $params['NEWSLETTER'];
					else
						self::$customConnectors[$params['ID']]['NEWSLETTER'] = self::DEFAULT_NEWSLETTER;

					if (isset($params['NEED_SYSTEM_MESSAGES']) && ($params['NEED_SYSTEM_MESSAGES'] === true || $params['NEED_SYSTEM_MESSAGES'] === false))
						self::$customConnectors[$params['ID']]['NEED_SYSTEM_MESSAGES'] = $params['NEED_SYSTEM_MESSAGES'];
					else
						self::$customConnectors[$params['ID']]['NEED_SYSTEM_MESSAGES'] = self::DEFAULT_NEED_SYSTEM_MESSAGES;

					if (isset($params['NEED_SIGNATURE']) && ($params['NEED_SIGNATURE'] === true || $params['NEED_SIGNATURE'] === false))
						self::$customConnectors[$params['ID']]['NEED_SIGNATURE'] = $params['NEED_SIGNATURE'];
					else
						self::$customConnectors[$params['ID']]['NEED_SIGNATURE'] = self::DEFAULT_NEED_SIGNATURE;

					if (isset($params['CHAT_GROUP']) && ($params['CHAT_GROUP'] === true || $params['CHAT_GROUP'] === false))
						self::$customConnectors[$params['ID']]['CHAT_GROUP'] = $params['CHAT_GROUP'];
					else
						self::$customConnectors[$params['ID']]['CHAT_GROUP'] = self::DEFAULT_CHAT_GROUP;
				}
			}
		}
	}

	private function __clone()
	{

	}

	private function __wakeup()
	{

	}

	public function getCustomConnectors()
	{
		return self::$customConnectors;
	}

	public static function getListConnector()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			$result[$connector['ID']] = $connector['NAME'];

		return $result;
	}

	public static function getListConnectorReal()
	{
		return self::getListConnector();
	}

	public static function getListConnectorId()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			$result[] = $connector['ID'];

		return $result;
	}

	public static function getListComponentConnector()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			$result[$connector['ID']] = $connector['COMPONENT'];

		return $result;
	}

	public static function getListConnectorDelExternalMessages()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['DEL_EXTERNAL_MESSAGES'] === true)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListConnectorEditInternalMessages()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['EDIT_INTERNAL_MESSAGES'] === true)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListConnectorDelInternalMessages()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['DEL_INTERNAL_MESSAGES'] === true)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListConnectorNotNewsletter()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['NEWSLETTER'] === false)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListNotNeedSystemMessages()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['NEED_SYSTEM_MESSAGES'] === false)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListNotNeedSignature()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['NEED_SIGNATURE'] === false)
				$result[] = $connector['ID'];

		return $result;
	}

	public static function getListChatGroup()
	{
		$result = array();

		foreach (self::getInstance()->getCustomConnectors() as $connector)
			if($connector['CHAT_GROUP'] === true)
				$result[] = $connector['ID'];

		return $result;
	}

	//public function
	protected static function setMessages($connector, $line, $data, $type)
	{
		self::getInstance();

		foreach ($data as $cell => $message)
		{
			$data[$cell]['type_message'] = $type;
		}

		$receivingHandlers = new ReceivingMessage($connector, $line, $data);
		$result = $receivingHandlers->receiving();

		return $result;
	}

	public static function sendMessages($connector, $line, $data)
	{
		$result = self::setMessages($connector, $line, $data, 'message');

		return $result;
	}

	public static function updateMessages($connector, $line, $data)
	{
		$result = self::setMessages($connector, $line, $data, 'message_update');

		return $result;
	}

	public static function deleteMessages($connector, $line, $data)
	{
		$result = self::setMessages($connector, $line, $data, 'message_del');

		return $result;
	}

	public static function sendStatusDelivery($connector, $line, $data)
	{
		$receivingHandlers = new ReceivingStatusDelivery($connector, $line, $data);
		$result = $receivingHandlers->receiving();

		return $result;
	}

	public static function sendStatusReading($connector, $line, $data)
	{
		$receivingHandlers = new ReceivingStatusReading($connector, $line, $data);
		$result = $receivingHandlers->receiving();

		return $result;
	}

	public static function deactivateConnectors($connector, $line)
	{
		$receivingHandlers = new DeactivateConnector($connector, $line);
		$result = $receivingHandlers->receiving();

		return $result;
	}

	public static function getStyleCss()
	{
		$result = '';

		foreach (self::getInstance()->getCustomConnectors() as $connector)
		{
			$style = '';

			if(!empty($connector["ICON"]["DATA_IMAGE"]))
			{
				$style = '.connector-icon-' . str_replace('.', '_', $connector['ID']) . ' {
	' . (!empty($connector["ICON"]["COLOR"])? 'background-color: ' . $connector["ICON"]["COLOR"] : '') . ';
	' . (!empty($connector["ICON"]["SIZE"])? 'background-size: ' . $connector["ICON"]["SIZE"] : '') . ';
	' . (!empty($connector["ICON"]["POSITION"])? 'background-position: ' . $connector["ICON"]["POSITION"] : '') . ';
	background-image: url(\'' . $connector["ICON"]["DATA_IMAGE"] . '\');
}
';
			}

			if(!empty($style))
			{
				$result .= $style;
			}
		}

		return $result;
	}

	public static function getStyleCssDisabled()
	{
		$result = '';

		foreach (self::getInstance()->getCustomConnectors() as $connector)
		{
			$style = '.connector-icon-disabled.connector-icon-' . str_replace('.', '_', $connector['ID']) . ' {
	' . (!empty($connector["ICON_DISABLED"]["COLOR"])? 'background-color: ' . $connector["ICON_DISABLED"]["COLOR"] : 'background-color: #ebeff2') . ';
	' . (!empty($connector["ICON_DISABLED"]["SIZE"])? 'background-size: ' . $connector["ICON_DISABLED"]["SIZE"] : '') . ';
	' . (!empty($connector["ICON_DISABLED"]["POSITION"])? 'background-position: ' . $connector["ICON_DISABLED"]["POSITION"] : '') . ';
	' . (!empty($connector["ICON_DISABLED"]["DATA_IMAGE"])? 'background-image: url(\'' . $connector["ICON_DISABLED"]["DATA_IMAGE"] . '\'' : '') . ');
}
';

			if(!empty($style))
			{
				$result .= $style;
			}
		}

		return $result;
	}
}