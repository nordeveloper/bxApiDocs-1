<?php

namespace Bitrix\Crm\Integration;

use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Company;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Contact;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\CrmEntityDataProvider;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Invoice;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Lead;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Quote;
use Bitrix\DocumentGenerator\Document;
use Bitrix\DocumentGenerator\Driver;
use Bitrix\DocumentGenerator\Nameable;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\DocumentGenerator\Model\TemplateTable;
use Bitrix\Main\UI\Spotlight;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

class DocumentGeneratorManager
{
	const PROVIDER_LIST_EVENT_NAME = 'onGetDataProviderList';
	const DOCUMENT_CREATE_EVENT_NAME = 'onCreateDocument';
	const DOCUMENT_DELETE_EVENT_NAME = '\Bitrix\DocumentGenerator\Model\Document::OnBeforeDelete';

	protected static $instance;
	protected $isEnabled;

	protected function __construct()
	{
		if(Loader::includeModule('documentgenerator'))
		{
			$this->isEnabled = true;
		}
		else
		{
			$this->isEnabled = false;
		}
	}

	/**
	 * @return DocumentGeneratorManager
	 */
	public static function getInstance()
	{
		if(static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->isEnabled;
	}

	/**
	 * @param string $className
	 * @param string|int $value
	 * @return array|false
	 */
	public function getPreviewList($className, $value)
	{
		$templates = [];

		if($this->isEnabled())
		{
			\CJSCore::init(["sidepanel", "documentpreview"]);
			$componentPath = \CComponentEngine::makeComponentPath('bitrix:crm.document.view');
			if(!empty($componentPath))
			{
				$loaderPath = getLocalPath('components'.$componentPath.'/templates/.default/images/document_view.svg');
				$componentPath = getLocalPath('components'.$componentPath.'/slider.php');
				Loc::loadMessages(__FILE__);
				$templates = TemplateTable::getListByClassName($className, Driver::getInstance()->getUserId(), $value);
				if($templates)
				{
					foreach($templates as &$template)
					{
						$uri = new Uri($componentPath);
						$template['text'] = $template['NAME'];
						$params = ['templateId' => $template['ID'], 'providerClassName' => $className, 'value' => $value, 'analyticsLabel' => 'generateDocument', 'templateCode' => $template['CODE']];
						$href = $uri->addParams($params)->getLocator();
						$template['onclick'] = 'BX.DocumentGenerator.Document.onBeforeCreate(\''.\CUtil::JSEscape($href).'\', '.\CUtil::PhpToJSObject($params).', \''.\CUtil::JSEscape($loaderPath).'\');';
					}
				}
				$addTemplateUrl = $this->getAddTemplateUrl($className);
				if($addTemplateUrl)
				{
					$templates[] = ['delimiter' => true];
					$templates[] = [
						'text' => Loc::getMessage('CRM_DOCUMENTGENERATOR_ADD_NEW_TEMPLATE'),
						'onclick' => 'BX.SidePanel.Instance.open("'.$addTemplateUrl.'", {width: 930}); BX.PopupMenu.getCurrentMenu().popupWindow.close();',
					];
				}
			}
		}

		return $templates;
	}

	/**
	 * @param string $className
	 * @return bool|string
	 */
	protected function getAddTemplateUrl($className = '')
	{
		$componentPath = \CComponentEngine::makeComponentPath('bitrix:documentgenerator.templates');
		if(!empty($componentPath))
		{
			$componentPath = getLocalPath('components'.$componentPath.'/slider.php');
			$uri = new Uri($componentPath);
			if($className)
			{
				$uri->addParams([
					'PROVIDER' => $className,
				]);
			}
			$uri->addParams(['MODULE' => 'crm',]);
			return $uri->getLocator();
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function getDataProviders()
	{
		$result = [];
		if(static::getInstance()->isEnabled())
		{
			$providers = [
				Company::class,
				Contact::class,
				Deal::class,
				Invoice::class,
				Lead::class,
				Quote::class,
			];
			foreach($providers as $provider)
			{
				/** @var Nameable $provider */
				$className = strtolower($provider);
				$result[$className] = [
					'NAME' => $provider::getLangName(),
					'CLASS' => $className,
					'MODULE' => 'crm',
				];
			}
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @return bool
	 */
	public static function onCreateDocument(Event $event)
	{
		$document = $event->getParameter('document');
		/** @var Document $document */
		if($document)
		{
			$provider = $document->getProvider();
			if($provider && $provider instanceof CrmEntityDataProvider)
			{
				$provider->onDocumentCreate($document);
			}
		}

		return true;
	}

	/**
	 * @param Event $event
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onDeleteDocument(Event $event)
	{
		$document = Document::loadById($event->getParameter('primary'));
		if($document)
		{
			$provider = $document->getProvider();
			if($provider && $provider instanceof CrmEntityDataProvider)
			{
				$provider->onDocumentDelete($document);
			}
		}

		return true;
	}

	public function showSpotlight($targetElement)
	{
		global $APPLICATION;

		if(!$targetElement)
		{
			return;
		}

		Loc::loadMessages(__FILE__);
		$APPLICATION->includeComponent("bitrix:spotlight", "", [
			"ID" => "crm-documents-feature",
			"USER_TYPE" => Spotlight::USER_TYPE_OLD,
			"JS_OPTIONS" => [
				"targetElement" => $targetElement,
				"content" => Loc::getMessage('CRM_DOCUMENTGENERATOR_SPOTLIGHT_TEXT'),
				"targetVertex" => "middle-center",
				"zIndex" => 2000,
			]
		]);
	}
}