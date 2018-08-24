<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Crm\SiteButton;

use Bitrix\Crm\SiteButton\Internals\ButtonTable;
use \Bitrix\Main\Application;
use \Bitrix\Main\Context;
use \Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;

/**
 * Class Script
 * @package Bitrix\Crm\SiteButton
 */
class Script
{
	const UPLOAD_PATH = '/crm';
	const UPLOAD_PATH_ADD = '/site_button/';
	const UPLOAD_FILE_NAME = 'loader_#id#_#sec#.js';
	const LOADER_PATH = '/bitrix/js/crm/button_loader.js';

	protected $id = null;
	protected $languageId = null;
	protected $button = null;
	protected $errors = array();
	protected $widgets = null;
	protected $resources = array(
		array(
			'name' => 'style.css',
			'type' => 'text/css',
			'path' => '/bitrix/components/bitrix/crm.button.button/templates/.default/style.css',
		),
		array(
			'name' => 'webform_style.css',
			'type' => 'text/css',
			'path' => '/bitrix/components/bitrix/crm.button.webform/templates/.default/style.css',
		),
		array(
			'name' => 'guest_tracker.js',
			'type' => 'text/javascript',
			'path' => '/bitrix/js/crm/guest_tracker.js',
			'loadMode' => 'manual' // auto - after load, manual - on demand, always - on init
		)
	);

	public function __construct(Button $button)
	{
		$this->button = $button;
		if ($this->button->getLanguageId() != Context::getCurrent()->getLanguage())
		{
			$this->languageId = $this->button->getLanguageId();
		}
	}

	protected function getLayout()
	{
		$data = $this->button->getData();
		$typeList = Manager::getTypeList();
		$widgets = array();
		foreach ($typeList as $typeId => $typeName)
		{
			if ($this->button->hasActiveItem($typeId))
			{
				$widgets[] = $typeId;
			}
		}

		ob_start();

		/*@var $APPLICATION CMain*/
		global $APPLICATION;
		$APPLICATION->IncludeComponent("bitrix:crm.button.button", ".default", array(
			'PREVIEW' => false,
			'WIDGETS' => $widgets,
			'LOCATION' => (int) $data['LOCATION'],
			'COLOR_ICON' => $data['ICON_COLOR'],
			'COLOR_BACKGROUND' => $data['BACKGROUND_COLOR'],
		));

		return ob_get_clean();
	}

	public function getLoaderFileName()
	{
		$buttonData = $this->button->getData();
		return str_replace(
			array('#id#', '#sec#'),
			array($this->button->getId(), $buttonData['SECURITY_CODE']),
			self::UPLOAD_FILE_NAME
		);
	}

	public static function removeCache(Button $button)
	{
		$resourceLoader = self::getResourceLoader($button->getId(), $button);
		ResourceManager::removeFiles($resourceLoader);
	}

	public static function saveCache(Button $button)
	{
		$resourceLoader = self::getResourceLoader($button->getId(), $button);
		$result = ResourceManager::uploadFiles($resourceLoader);

		//$resourceFiles = self::getResourceFiles();
		//$result = ResourceManager::uploadFiles($resourceFiles);

		/*
		$result = $this->saveFile(
			str_replace('#id#', $this->button->getId(), self::UPLOAD_FILE_NAME),
			$this->getCache()
		);
		foreach ($this->resources as $resourceName => $resourcePath)
		{
			$result = $this->saveFile(
				$resourceName,
				File::getFileContents(Application::getDocumentRoot() . $resourcePath)
			);
		}
		*/

		return $result;
	}

	public static function getScript(Button $button)
	{
		//$loaderLink = self::getDomainPath(self::UPLOAD_PATH . self::UPLOAD_PATH_ADD . str_replace('#id#', $id, self::UPLOAD_FILE_NAME));
		$instance = new static($button);
		$fileName = $instance->getLoaderFileName();
		$loaderLink = ResourceManager::getFileUrl($fileName);

		if(!$loaderLink)
		{
			return null;
		}

		return
			"<script data-skip-moving=\"true\">
	(function(w,d,u){
		var s=d.createElement('script');s.async=1;s.src=u+'?'+(Date.now()/60000|0);
		var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
	})(window,document,'" . $loaderLink . "');
</script>";
	}

	public function getCache()
	{
		$loaderContents = File::getFileContents(Application::getDocumentRoot() . self::LOADER_PATH);
		if(!$loaderContents)
		{
			$this->errors[] = 'Can\'t find button_loader.js';
			return null;
		}

		return $loaderContents . "\n\n" . $this->getLoaderStarter();
	}

	/*
		Bitrix24ButtonLoader.init({
			resources: [
				'/bitrix/components/bitrix/crm.button.button/templates/.default/style.css'
			],
			delay: 0,
			widgets: [
				{
					id: '',
					script: '',
					show: 'BX.LiveChat.openLiveChat()',
					pages: {
						mode: 'INCLUDE',
						list: [
							''
						]
					}
				}
			],
			layout: ''
		});
	 * */
	public function getWidgets()
	{
		if ($this->widgets !== null)
		{
			return $this->widgets;
		}

		$this->widgets = array();
		$typeList = Manager::getTypeList();
		foreach ($typeList as $typeId => $typeName)
		{
			if(!$this->button->hasActiveItem($typeId))
			{
				continue;
			}

			$item = $this->button->getItemByType($typeId);
			$config = isset($item['CONFIG']) ? $item['CONFIG'] : array();
			$typeWidgets = ChannelManager::getWidgets(
				$typeId,
				$item['EXTERNAL_ID'],
				$this->button->isCopyrightRemoved(),
				$this->languageId,
				$config
			);

			if(count($typeWidgets) <= 0)
			{
				continue;
			}

			$pages = array(
				'mode' => 'EXCLUDE',
				'list' => array()
			);
			if($this->button->hasItemPages($typeId))
			{
				$pages['mode'] = $item['PAGES']['MODE'];
				$pages['list'] = $item['PAGES']['LIST'][$pages['mode']];
			}

			$workTime = $this->button->getItemWorkTime($typeId);
			if ($workTime['ENABLED'])
			{
				$workTime = WorkTime::convertToJS($workTime);
			}
			else
			{
				$workTime = null;
			}

			foreach ($typeWidgets as $typeWidget)
			{
				$typeWidget['type'] = $typeId;
				$typeWidget['pages'] = $pages;
				$typeWidget['workTime'] = $workTime;
				$this->widgets[] = $typeWidget;
			}
		}

		return $this->widgets;
	}

	protected function getWidgetResources()
	{
		$resources = array();
		$widgetList = Manager::getWidgetList();
		foreach ($widgetList as $item)
		{
			if(!$this->button->hasActiveItem($item['TYPE']))
			{
				continue;
			}

			if (empty($item['RESOURCES']) || !is_array($item['RESOURCES']))
			{
				continue;
			}

			$resources = array_merge($resources, $item['RESOURCES']);
		}

		return $resources;
	}

	protected function getHelloData()
	{
		$widgetOrderList = array(
			Manager::ENUM_TYPE_OPEN_LINE,
			Manager::ENUM_TYPE_OPEN_LINE . '_livechat',
			Manager::ENUM_TYPE_CALLBACK,
			Manager::ENUM_TYPE_CRM_FORM,
		);
		$showWidgetId = '';
		$widgetIdList = array();
		$widgets = $this->getWidgets();
		foreach ($widgets as $widget)
		{
			$widgetIdList[] = $widget['id'];
		}

		foreach ($widgetOrderList as $widgetOrderId)
		{
			if (in_array($widgetOrderId, $widgetIdList))
			{
				$showWidgetId = $widgetOrderId;
				break;
			}
		}

		if (!$showWidgetId && $widgetIdList[0])
		{
			$showWidgetId = $widgetIdList[0];
		}

		$buttonData = $this->button->getData();
		$settings = is_array($buttonData['SETTINGS']) ? $buttonData['SETTINGS'] : array();
		$hello = is_array($settings['HELLO']) ? $settings['HELLO'] : array();
		$hello['CONDITIONS'] = is_array($hello['CONDITIONS']) ? $hello['CONDITIONS'] : array();
		$conditions = array();

		if ($hello['ACTIVE'])
		{
			foreach ($hello['CONDITIONS'] as $condition)
			{
				if ($condition['PAGES'] && is_array($condition['PAGES']['LIST']))
				{
					$condition['PAGES']['LIST'] = array_values($condition['PAGES']['LIST']);
				}

				$conditions[] = array(
					'icon' => $condition['ICON'],
					'name' => $condition['NAME'],
					'text' => $condition['TEXT'],
					'pages' => $condition['PAGES'],
					'delay' => $condition['DELAY'],
				);
			}

			if ($hello['MODE'] == 'INCLUDE' && isset($conditions[0]))
			{
				unset($conditions[0]);
				sort($conditions);
			}
		}

		return array(
			'delay' => 1,
			'showWidgetId' => $showWidgetId,
			'conditions' => $conditions
		);
	}

	public function getLoaderStarter()
	{
		if ($this->languageId)
		{
			Loc::setCurrentLang($this->languageId);
		}

		$widgets = $this->getWidgets();
		$data = $this->button->getData();


		if (isset($data['SETTINGS']) && isset($data['SETTINGS']['DISABLE_ON_MOBILE']) && $data['SETTINGS']['DISABLE_ON_MOBILE'] == 'Y')
		{
			$disableOnMobile = true;
		}
		else
		{
			$disableOnMobile = false;
		}

		$resources = array();
		$widgetResources = array_merge($this->resources, $this->getWidgetResources());
		foreach ($widgetResources as $resource)
		{
			if (isset($resources[$resource['path']]))
			{
				continue;
			}

			$resources[$resource['path']] = array(
				'name' => $resource['name'],
				'type' => $resource['type'],
				'loadMode' => isset($resource['loadMode']) ? $resource['loadMode'] : null,
				'content' => self::getFileContents($resource['path']),
			);
		}
		$resources = array_values($resources);

		$params = array(
			'isActivated' => $data['ACTIVE'] != 'N',
			'disableOnMobile' => $disableOnMobile,
			'serverAddress' => ResourceManager::getServerAddress(),
			'resources' => $resources,
			'location' => (int) $data['LOCATION'],
			'delay' => (int) $data['DELAY'],
			'bgColor' => $data['BACKGROUND_COLOR'],
			'iconColor' => $data['ICON_COLOR'],
			'widgets' => $widgets,
			'layout' => $this->getLayout(),
			'hello' => $this->getHelloData()
		);

		if ($this->languageId)
		{
			Loc::setCurrentLang(LANGUAGE_ID);
		}

		return 'window.BX.SiteButton.init(' . Json::encode($params) . ');';
	}

	protected static function getFileContents($path)
	{
		$path = Application::getDocumentRoot() . $path;

		$minPathPos = strrpos($path, '.');
		if ($minPathPos !== false)
		{
			$minPathSub = substr($path, 0, $minPathPos);
			$minPathExt = substr($path, $minPathPos);
			$minPath = $minPathSub . '.min' . $minPathExt;
			if (File::isFileExists($minPath))
			{
				$path = $minPath;
			}
		}

		return File::getFileContents($path);
	}

	protected static function getDomainPath($path)
	{
		//TODO: upload path from settings
		$host = Application::getInstance()->getContext()->getServer()->getHttpHost();
		return 'http://' . $host . '/upload' . $path;
	}

	protected function isB24()
	{
		return (bool) Loader::includeModule('bitrix24');
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public static function getResourceLoader($buttonId, Button $button = null)
	{
		if(!$button)
		{
			$button = new Button($buttonId);
		}

		$instance = new static($button);

		return array(
			array(
				'name' => $instance->getLoaderFileName(),
				'type' => 'text/javascript',
				'path' => '',
				'contents' => $instance->getCache(),
				'provider_function' => '\\Bitrix\\Crm\\SiteButton\\Script::getResourceLoader',
				'provider_params' => $buttonId,
				'provider_module_id' => 'crm',
			)
		);
	}

	public static function getResourceFiles($fileName = null)
	{
		$resources = array(
			array(
				'name' => 'style.css',
				'type' => 'text/css',
				'path' => '/bitrix/components/bitrix/crm.button.button/templates/.default/style.css',
			),
			array(
				'name' => 'webform_style.css',
				'type' => 'text/css',
				'path' => '/bitrix/components/bitrix/crm.button.webform/templates/.default/style.css',
			),
		);

		$result = array();
		$resourcesLength = count($resources);
		for ($i = 0; $i < $resourcesLength; $i++)
		{
			if($fileName && $resources[$i]['name'] != $fileName)
			{
				continue;
			}

			$resources[$i]['content'] = null;
			$resources[$i]['provider_function'] = '\\Bitrix\\Crm\\SiteButton\\Script::getResourceFiles';
			$resources[$i]['provider_params'] = $resources[$i]['name'];
			$resources[$i]['provider_module_id'] = 'crm';

			$result[] = $resources[$i];
		}

		return $result;
	}
}
