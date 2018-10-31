<?php

namespace Bitrix\Crm\Controller\DocumentGenerator;

use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\DocumentGenerator\Model\FileTable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Response\DataType\ContentUri;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;

class Template extends Base
{
	/**
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$configureActions['download'] = [
			'-prefilters' => [
				Csrf::class
			]
		];

		return $configureActions;
	}

	/**
	 * @param int $templateId
	 * @return Uri
	 */
	protected function getTemplateDownloadUrl($templateId)
	{
		$link = UrlManager::getInstance()->create(static::CONTROLLER_PATH.'.template.download', ['id' => $templateId]);
		$link = new ContentUri(UrlManager::getInstance()->getHostUrl().$link->getLocator());

		return $link;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::getFieldsAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @param $entityTypeId
	 * @param $entityId
	 * @param array $values
	 * @return \Bitrix\Main\Result|bool
	 */
	public function getFieldsAction(\Bitrix\DocumentGenerator\Template $template, $entityTypeId, $entityId, array $values = [])
	{
		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(!isset($providersMap[$entityTypeId]))
		{
			$this->errorCollection[] = new Error('No provider for entityTypeId');
			return null;
		}

		return $this->proxyAction('getFieldsAction', [$template, $providersMap[$entityTypeId], $entityId, $values]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::getAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return \Bitrix\Main\Result|bool
	 */
	public function getAction(\Bitrix\DocumentGenerator\Template $template)
	{
		$result = $this->proxyAction('getAction', [$template]);

		$data = false;
		if($result instanceof Result)
		{
			$data = $result->getData();
		}
		elseif(is_array($result))
		{
			$data = $result;
		}
		if($data)
		{
			$data['template'] = $this->prepareTemplateData($data['template']);

			if($result instanceof Result)
			{
				$result->setData($data);
			}
			else
			{
				$result = $data;
			}
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::getListAction()
	 * @param array $select
	 * @param array|null $order
	 * @param array|null $filter
	 * @param PageNavigation|null $pageNavigation
	 * @return array
	 */
	public function listAction(array $select = ['*'], array $filter = null, array $order = null, PageNavigation $pageNavigation = null)
	{
		if(!is_array($filter))
		{
			$filter = [];
		}
		$filter['moduleId'] = static::MODULE_ID;

		if(in_array('entityTypeId', $select))
		{
			$select[] = 'providers';
			unset($select[array_search('entityTypeId', $select)]);
		}

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(is_array($filter) && isset($filter['entityTypeId']))
		{
			$filterMap = array_map(function($item)
			{
				return str_replace('\\', '\\\\', strtolower($item));
			}, $providersMap);
			$filter['provider.provider'] = str_ireplace(array_keys($providersMap), $filterMap, $filter['entityTypeId']);
			unset($filter['entityTypeId']);
		}
		$result = $this->proxyAction('listAction', [$select, $order, $filter, $pageNavigation]);
		if($result instanceof Page)
		{
			$templates = $result->offsetGet('templates');
		}
		else
		{
			$templates = $result['templates'];
		}
		foreach($templates as &$template)
		{
			$template = $this->prepareTemplateData($template);
		}
		if($result instanceof Page)
		{
			$result->offsetSet('templates', $templates);
		}
		else
		{
			$result = ['templates' => $templates];
		}
		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::deleteAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return mixed
	 */
	public function deleteAction(\Bitrix\DocumentGenerator\Template $template)
	{
		return $this->proxyAction('deleteAction', [$template]);
	}

	/**
	 * @return \Bitrix\DocumentGenerator\Controller\Template
	 */
	protected function getDocumentGeneratorController()
	{
		return new \Bitrix\DocumentGenerator\Controller\Template();
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::addAction()
	 * @param array $fields
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public function addAction(array $fields)
	{
		$emptyFields = $this->checkArrayRequiredParams($fields, ['name', 'numeratorId', 'region', 'entityTypeId']);
		if(!empty($emptyFields))
		{
			$this->errorCollection[] = new Error('Empty required fields: '.implode(', ', $emptyFields));
			return null;
		}

		if(!isset($fields['users']) || !is_array($fields['users']))
		{
			$fields['users'] = [];
		}

		$fileId = $this->uploadFile($fields[static::FILE_PARAM_NAME]);
		if(!$fileId)
		{
			return null;
		}
		$fields['fileId'] = $fileId;
		$fileId['moduleId'] = static::MODULE_ID;

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		$fields['entityTypeId'] = str_ireplace(array_keys($providersMap), array_values($providersMap), $fields['entityTypeId']);

		$controller = $this->getDocumentGeneratorController();
		if(method_exists($controller, 'updateAction'))
		{
			$result = $this->proxyAction('addAction', [$fields]);
		}
		else
		{
			$result = $this->proxyAction('addAction', [$fields['name'], $fields['fileId'], $fields['numeratorId'], $fields['region'], $fields['entityTypeId'], $fields['users'], null, $fields['moduleId'], '', $fields['active'], $fields['withStamps']]);
		}
		if(is_array($result))
		{
			if(isset($result['template']['links']['download']))
			{
				$result['template']['links']['download'] = $this->getTemplateDownloadUrl($result['template']['id']);
			}
		}
		else
		{
			FileTable::delete($fileId);
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::updateAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @param array $fields
	 * @return bool|mixed
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public function updateAction(\Bitrix\DocumentGenerator\Template $template, array $fields)
	{
		$fileContent = null;
		if(isset($fields[static::FILE_PARAM_NAME]))
		{
			$fileContent = $fields[static::FILE_PARAM_NAME];
		}
		else
		{
			$fileContent = Application::getInstance()->getContext()->getRequest()->getFile(static::FILE_PARAM_NAME);
		}
		if($fileContent)
		{
			$fileId = $this->uploadFile($fileContent);
			if(!$fileId)
			{
				return null;
			}
			$fields['fileId'] = $fileId;
		}
		else
		{
			$fileId = $template->FILE_ID;
		}
		$fileId['moduleId'] = static::MODULE_ID;

		$controller = $this->getDocumentGeneratorController();
		if(method_exists($controller, 'updateAction'))
		{
			$result = $this->proxyAction('updateAction', [$template, $fields]);
		}
		else
		{
			$name = isset($fields['name']) ? $fields['name'] : $template->NAME;
			$numeratorId = isset($fields['numeratorId']) ? $fields['numeratorId'] : $template->NUMERATOR_ID;
			$region = isset($fields['region']) ? $fields['region'] : $template->REGION;
			$providers = isset($fields['providers']) ? $fields['providers'] : $template->getDataProviders();
			$users = isset($fields['users']) ? $fields['users'] : $template->getUsers();
			$active = isset($fields['active']) ? $fields['active'] : $template->ACTIVE;
			$withStamps = isset($fields['withStamps']) ? $fields['withStamps'] : $template->WITH_STAMPS;
			$result = $this->proxyAction('addAction', [$name, $fields['fileId'], $numeratorId, $region, $providers, $users, $template->ID, static::MODULE_ID, '', $active, $withStamps]);
		}

		if(is_array($result))
		{
			if(isset($result['template']['links']['download']))
			{
				$result['template']['links']['download'] = $this->getTemplateDownloadUrl($result['template']['id']);
			}
		}
		elseif($fileContent)
		{
			FileTable::delete($fileId);
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::downloadAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return array|false
	 */
	public function downloadAction(\Bitrix\DocumentGenerator\Template $template)
	{
		return $this->proxyAction('downloadAction', [$template]);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareTemplateData(array $data)
	{
		if(isset($data['providers']))
		{
			$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
			$providers = array_values($data['providers']);
			$data['entityTypeId'] = str_ireplace(array_values($providersMap), array_keys($providersMap), $providers);
			unset($data['providers']);
		}
		$data['links']['download'] = $this->getTemplateDownloadUrl($data['id']);

		return $data;
	}
}