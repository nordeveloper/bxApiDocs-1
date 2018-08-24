<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Crm\SiteButton\Channel;

use Bitrix\Crm\SiteButton\Manager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\ImOpenLines\Common;
use Bitrix\Imopenlines\Model\ConfigTable;
use Bitrix\ImConnector\Connector;
use Bitrix\ImOpenLines\LiveChatManager;
use Bitrix\Imopenlines\Model\LivechatTable;

Loc::loadMessages(__FILE__);

/**
 * Class ChannelOpenLine
 * @package Bitrix\Crm\SiteButton\Channel
 */
class ChannelOpenLine implements iProvider
{
	private static $excludedConnectors = array(
		'facebookcomments'
	);
	/**
	 * Return true if it can be used.
	 *
	 * @return bool
	 */
	public static function canUse()
	{
		return Loader::includeModule('imopenlines') && Loader::includeModule('imconnector');
	}

	/**
	 * Get presets.
	 *
	 * @return array
	 */
	public static function getPresets()
	{
		if (!self::canUse())
		{
			return array();
		}

		return LivechatTable::getList(array(
			'select' => array(
				'ID' => 'CONFIG_ID',
				'NAME' => 'CONFIG.LINE_NAME'
			),
			'filter' => array(
				'=CONFIG.ACTIVE' => 'Y'
			),
		))->fetchAll();
	}

	/**
	 * Get list.
	 *
	 * @return array
	 */
	public static function getList()
	{
		if (!self::canUse())
		{
			return array();
		}

		$list = ConfigTable::getList(array(
			'select' => array(
				'ID', 'NAME' => 'LINE_NAME',
				'WORKTIME_ENABLE',
				'WORKTIME_FROM', 'WORKTIME_TO', 'WORKTIME_TIMEZONE',
				'WORKTIME_HOLIDAYS', 'WORKTIME_DAYOFF'
			),
			'filter' => array(
				'=ACTIVE' => 'Y'
			),
		))->fetchAll();

		$result = array();
		foreach ($list as $line)
		{
			$connectors = self::getConnectors($line['ID']);
			if (count($connectors) > 0)
			{
				$workTime = null;
				if ($line['WORKTIME_ENABLE'] == 'Y')
				{
					$workTime = array(
						'ENABLED' => $line['WORKTIME_ENABLE'] == 'Y',
						'TIME_FROM' => (float) $line['WORKTIME_FROM'],
						'TIME_TO' => (float) $line['WORKTIME_TO'],
						'TIME_ZONE' => $line['WORKTIME_TIMEZONE'],
						'HOLIDAYS' => explode(',', $line['WORKTIME_HOLIDAYS']),
						'DAY_OFF' => explode(',', $line['WORKTIME_DAYOFF']),
					);
				}

				$result[] = array(
					'ID' => $line['ID'],
					'NAME' => $line['NAME'],
					'CONNECTORS' => $connectors,
					'WORK_TIME' => $workTime,
				);
			}
		}

		return $result;
	}

	/**
	 * Get widgets.
	 *
	 * @param string $id Channel id
	 * @param bool $removeCopyright Remove copyright
	 * @param string|null $lang Language ID
	 * @param array $config Config
	 * @return array
	 */
	public static function getWidgets($id, $removeCopyright = true, $lang = null, array $config = array())
	{
		Loc::loadMessages(__FILE__); // TODO: remove with dependence main: deeply lazy Load loc files
		Loc::loadMessages(\Bitrix\Main\Application::getDocumentRoot() . '/bitrix/modules/imconnector/lib/connector.php');

		$result = array();
		$lines = explode(',', $id);
		foreach ($lines as $lineId)
		{
			$lineConfig = isset($config[$lineId]) ? $config[$lineId] : array();
			$widgets = self::getWidgetsById($lineId, $removeCopyright, $lang, $lineConfig);
			$result = array_merge($result, $widgets);
		}

		return $result;
	}

	/**
	 * Get resources.
	 *
	 * @return array
	 */
	public static function getResources()
	{
		if (!self::canUse())
		{
			return array();
		}

		return array(
			array(
				'name' => 'ol_imcon_icon_style.css',
				'type' => 'text/css',
				'path' => '/bitrix/js/imconnector/icon.css',
			)
		);
	}

	/**
	 * Get edit path.
	 *
	 * @return array
	 */
	public static function getPathEdit()
	{
		if (!self::canUse())
		{
			return null;
		}

		return array(
			'path' => Common::getPublicFolder()."list/edit.php?ID=#ID#",
			'id' => '#ID#'
		);
	}

	/**
	 * Get add path.
	 * @return string
	 */
	public static function getPathAdd()
	{
		if (!self::canUse())
		{
			return null;
		}

		return Common::getPublicFolder()."list/edit.php?ID=0";
	}

	/**
	 * Get list path.
	 *
	 * @return string
	 */
	public static function getPathList()
	{
		if (!self::canUse())
		{
			return null;
		}

		return Common::getPublicFolder()."list/edit.php?ID=0";
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public static function getName()
	{
		return Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_' . strtoupper(self::getType()));
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public static function getType()
	{
		return 'openline';
	}

	protected static function getWidgetsById($lineId, $removeCopyright, $lang, array $config = array())
	{
		if (!self::canUse())
		{
			return array();
		}

		$excluded = isset($config['excluded']) ? $config['excluded'] : array();

		$widgets = array();
		$sort = 400;
		$type = self::getType();
		$connectors = self::getConnectors($lineId);
		foreach ($connectors as $connector)
		{
			if (in_array($connector['id'], $excluded))
			{
				continue;
			}

			$widget = array(
				'id' => $type . '_' . $connector['code'],
				'title' => $connector['title'],
				'script' => '',
				'show' => null,
				'hide' => null,
			);

			if ($connector['code'] == 'livechat')
			{
				$liveChatManager = new LiveChatManager($lineId);
				$widget['script'] = $liveChatManager->getWidget(
					LiveChatManager::TYPE_BUTTON,
					$lang,
					array(
						'copyright' => !$removeCopyright
					),
					true
				);

				if (!$widget['script'])
				{
					continue;
				}

				$widget['show'] = 'window.BX.LiveChat.openLiveChat();';
				$widget['hide'] = 'window.BX.LiveChat.closeLiveChat();';
				$widget['sort'] = 100;
				$widget['useColors'] = true;
				$widget['classList'] = array('b24-widget-button-' . $widget['id']);
			}
			else
			{
				$widget['classList'] = array(
					'connector-icon',
					'connector-icon-45',
					'connector-icon-' . $connector['id']
				);
				$widget['sort'] = $sort;
				$sort += 100;
				$widget['show'] = array(
					'url' => $connector['url']
				);
			}

			$widgets[] = $widget;
		}

		return $widgets;
	}

	protected static function getConnectors($lineId)
	{
		$nameList = Connector::getListConnectorReal(20);
		if (Manager::isWidgetSelectDisabled())
		{
			$connectors = [];
			$connectorList = Connector::getListConnectedConnector($lineId);
			$virtualId = 1;
			foreach ($connectorList as $connectorCode => $connectorName)
			{
				$connectors[$connectorCode] = [
					'id' => 'virtual:' . ($virtualId++),
					'url' => 'https://bitrix24.com/',
					'url_im' => 'https://bitrix24.com/',
					'name' => $connectorName,
					'connector_name' => $connectorName,
				];
			}
		}
		else
		{
			$connectors = Connector::infoConnectorsLine($lineId);
		}

		if (count($connectors) == 0)
		{
			return array();
		}

		$list = array();
		foreach ($connectors as $code => $connector)
		{
			if (in_array($code, self::$excludedConnectors))
			{
				continue;
			}

			if (empty($connector['url']) && empty($connector['url_im']))
			{
				continue;
			}

			$id = str_replace('.', '-', $code);
			if ($code == 'facebook')
			{
				$title = 'Facebook';
			}
			else if (isset($nameList[$code]))
			{
				$title = $nameList[$code];
			}
			else
			{
				$title = $connector['name'];
			}

			$list[] = array(
				'id' => $id,
				'code' => $code,
				'title' => $title,
				'name' => $connector['connector_name'],
				'desc' => $connector['name'],
				'url' => $connector['url_im'] ? $connector['url_im'] : $connector['url']
			);
		}

		return $list;
	}

	/**
	 * Get config from post.
	 *
	 * @param array $item Item
	 * @return string
	 */
	public static function getConfigFromPost(array $item = array())
	{
		$config = array();
		if(isset($item['EXTERNAL_CONFIG']) && is_array($item['EXTERNAL_CONFIG']))
		{
			$config = $item['EXTERNAL_CONFIG'];
		}

		$result = array();
		foreach ($config as $lineId => $connectors)
		{
			if (!$lineId || !is_array($connectors) || count($connectors) == 0)
			{
				continue;
			}

			$list = self::getConnectors($lineId);
			foreach ($list as $item)
			{
				if (!isset($result[$lineId]))
				{
					$result[$lineId] = array();
				}

				if (!isset($result[$lineId]['excluded']))
				{
					$result[$lineId]['excluded'] = array();
				}

				if (in_array($item['id'], $connectors))
				{
					continue;
				}

				$result[$lineId]['excluded'][] = $item['id'];
			}

		}

		return $result;
	}
}
