<?php

namespace Bitrix\Crm\Controller\DocumentGenerator;

use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;

class Template extends Base
{
	/**
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return Uri
	 */
	protected function getTemplateDownloadUrl(\Bitrix\DocumentGenerator\Template $template)
	{
		$link = UrlManager::getInstance()->create(static::CONTROLLER_PATH.'.template.download', ['templateId' => $template->ID, 'ts' => $template->getModificationTime()]);
		$link = new Uri(UrlManager::getInstance()->getHostUrl().$link->getLocator());

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
			return false;
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
			if(isset($data['template']['providers']))
			{
				$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
				$providers = array_values($data['template']['providers']);
				$data['template']['providers'] = str_ireplace(array_values($providersMap), array_keys($providersMap), $providers);
			}
			$data['template']['links']['download'] = $this->getTemplateDownloadUrl($template);

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

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(is_array($filter) && isset($filter['provider']))
		{
			$filterMap = array_map(function($item)
			{
				return str_replace('\\', '\\\\', strtolower($item));
			}, $providersMap);
			$filter['provider.provider'] = str_ireplace(array_keys($providersMap), $filterMap, $filter['provider']);
			unset($filter['provider']);
		}
		$result = $this->proxyAction('listAction', [$select, $order, $filter, $pageNavigation]);
		if($result instanceof Page)
		{
			$templates = $result->offsetGet('templates');
		}
		else
		{
			$templates = $result;
		}
		foreach($result['templates'] as &$template)
		{
			if(isset($template['providers']))
			{
				$template['providers'] = str_ireplace(array_values($providersMap), array_keys($providersMap), array_values($template['providers']));
			}
		}
		if($result instanceof Page)
		{
			$result->offsetSet('templates', $templates);
		}
		else
		{
			$result = $templates;
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
		$emptyFields = $this->checkArrayRequiredParams($fields, ['name', 'numeratorId', 'region', 'providers']);
		if(!empty($emptyFields))
		{
			$this->errorCollection[] = new Error('Empty required fields: ',implode(', ', $emptyFields));
			return false;
		}

		$fileId = $this->uploadFile($fields[static::FILE_PARAM_NAME]);
		if(!$fileId)
		{
			return false;
		}

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		$fields['providers'] = str_ireplace(array_keys($providersMap), array_values($providersMap), $fields['providers']);

		return $this->proxyAction('addAction', [$fields['name'], $fileId, $fields['numeratorId'], $fields['region'], $fields['providers'], $fields['users'], null, static::MODULE_ID, '', $fields['active'], $fields['stamps']]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Template::addAction()
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
				return false;
			}
		}
		else
		{
			$fileId = $template->FILE_ID;
		}

		$name = isset($fields['name']) ? $fields['name'] : $template->NAME;
		$numeratorId = isset($fields['numeratorId']) ? $fields['numeratorId'] : $template->NUMERATOR_ID;
		$region = isset($fields['region']) ? $fields['region'] : $template->REGION;
		$providers = isset($fields['providers']) ? $fields['providers'] : $template->getDataProviders();
		$users = isset($fields['users']) ? $fields['users'] : $template->getUsers();
		$active = isset($fields['active']) ? $fields['active'] : $template->ACTIVE;
		$stamps = isset($fields['stamps']) ? $fields['stamps'] : $template->WITH_STAMPS;

		return $this->proxyAction('addAction', [$name, $fileId, $numeratorId, $region, $providers, $users, $template->ID, static::MODULE_ID, '', $active, $stamps]);
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
}