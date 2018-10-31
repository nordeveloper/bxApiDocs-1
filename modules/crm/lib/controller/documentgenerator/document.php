<?php

namespace Bitrix\Crm\Controller\DocumentGenerator;

use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Binder;
use Bitrix\Main\Engine\Response\DataType\ContentUri;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class Document extends Base
{
	protected function init()
	{
		parent::init();

		Binder::registerParameterDependsOnName(
			'\Bitrix\DocumentGenerator\Template',
			function($className, $id)
			{
				/** @var \Bitrix\DocumentGenerator\Template $className */
				return $className::loadById($id);
			},
			function()
			{
				return 'templateId';
			}
		);
	}

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
		$configureActions['getImage'] = [
			'-prefilters' => [
				Csrf::class
			]
		];
		$configureActions['getPdf'] = [
			'-prefilters' => [
				Csrf::class
			]
		];

		return $configureActions;
	}
	/**
	 * @return \Bitrix\DocumentGenerator\Controller\Base
	 */
	protected function getDocumentGeneratorController()
	{
		return new \Bitrix\DocumentGenerator\Controller\Document();
	}

	protected function getDocumentFileLink($documentId, $action, $updateTime = null)
	{
		if(!$updateTime)
		{
			$updateTime = time();
		}
		$link = UrlManager::getInstance()->create(static::CONTROLLER_PATH.'.document.'.$action, ['id' => $documentId, 'ts' => $updateTime]);
		$link = new ContentUri(UrlManager::getInstance()->getHostUrl().$link->getLocator());

		return $link;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::getAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return mixed
	 */
	public function getAction(\Bitrix\DocumentGenerator\Document $document)
	{
		$result = $this->proxyAction('getAction', [$document]);

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
			$data['document'] = $this->prepareDocumentData($data['document']);

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
	 * @see \Bitrix\DocumentGenerator\Controller\Document::listAction()
	 * @param array $select
	 * @param array|null $order
	 * @param array|null $filter
	 * @param PageNavigation|null $pageNavigation
	 * @return \Bitrix\Main\Result
	 */
	public function listAction(array $select = ['*'], array $order = null, array $filter = null, PageNavigation $pageNavigation = null)
	{
		if(!is_array($filter))
		{
			$filter = [];
		}
		$filter['template.moduleId'] = static::MODULE_ID;

		if(is_array($select) && in_array('entityId', $select))
		{
			$select[] = 'value';
			unset($select[array_search('entityId', $select)]);
		}

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(is_array($filter))
		{
			if(isset($filter['entityTypeId']))
			{
				$filterMap = array_map(function($item)
				{
					return str_replace('\\', '\\\\', strtolower($item));
				}, $providersMap);
				$filter['provider'] = str_ireplace(array_keys($providersMap), $filterMap, $filter['entityTypeId']);
				unset($filter['entityTypeId']);
			}
			if(isset($filter['entityId']))
			{
				$filter['value'] = $filter['entityId'];
				unset($filter['entityId']);
			}
		}
		$result = $this->proxyAction('listAction', [$select, $order, $filter, $pageNavigation]);
		if($result instanceof Page)
		{
			$documents = $result->offsetGet('documents');
		}
		else
		{
			$documents = $result;
		}

		foreach($documents as &$document)
		{
			$document = $this->prepareDocumentData($document);
		}

		if($result instanceof Page)
		{
			$result->offsetSet('documents', $documents);
		}
		else
		{
			$result = $documents;
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::deleteAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return mixed
	 */
	public function deleteAction(\Bitrix\DocumentGenerator\Document $document)
	{
		return $this->proxyAction('deleteAction', [$document]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::addAction()
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @param $entityTypeId
	 * @param $entityId
	 * @param array $values
	 * @param int $stampsEnabled
	 * @return bool|mixed
	 */
	public function addAction(\Bitrix\DocumentGenerator\Template $template, $entityTypeId, $entityId, array $values = [], $stampsEnabled = 0)
	{
		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(!isset($providersMap[$entityTypeId]))
		{
			$this->errorCollection[] = new Error('No provider for entityTypeId');
			return null;
		}

		$result = $this->proxyAction('addAction', [$template, $providersMap[$entityTypeId], $entityId, $values, $stampsEnabled]);
		if(is_array($result))
		{
			$result['document'] = $this->prepareDocumentData($result['document']);
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::updateAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param array $values
	 * @param int $stampsEnabled
	 * @return array
	 */
	public function updateAction(\Bitrix\DocumentGenerator\Document $document, array $values = [], $stampsEnabled = 1)
	{
		$result = $this->proxyAction('updateAction', [$document, $values, $stampsEnabled]);

		if(is_array($result))
		{
			$result['document'] = $this->prepareDocumentData($result['document']);
		}

		return $result;
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::getFieldsAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param array $values
	 * @return array|false
	 */
	public function getFieldsAction(\Bitrix\DocumentGenerator\Document $document, array $values = [])
	{
		return $this->proxyAction('getFieldsAction', [$document, $values]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::enablePublicUrlAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param int $status
	 * @return array
	 */
	public function enablePublicUrlAction(\Bitrix\DocumentGenerator\Document $document, $status = 1)
	{
		return $this->proxyAction('enablePublicUrlAction', [$document, $status]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::getImageAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array
	 */
	public function getImageAction(\Bitrix\DocumentGenerator\Document $document)
	{
		return $this->proxyAction('getImageAction', [$document]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::getPdfAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array
	 */
	public function getPdfAction(\Bitrix\DocumentGenerator\Document $document)
	{
		return $this->proxyAction('getPdfAction', [$document]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::getFileAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array
	 */
	public function downloadAction(\Bitrix\DocumentGenerator\Document $document)
	{
		return $this->proxyAction('getFileAction', [$document]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::uploadAction()
	 * @param array $fields
	 * @param \CRestServer $restServer
	 * @return \Bitrix\Main\Result|bool
	 * @throws \Exception
	 */
	public function uploadAction(array $fields, \CRestServer $restServer)
	{
		$emptyFields = $this->checkArrayRequiredParams($fields, ['entityTypeId', 'fileContent', 'region', 'entityId', 'title', 'number']);
		if(!empty($emptyFields))
		{
			$this->errorCollection[] = new Error('Empty required fields: '.implode(', ', $emptyFields));
			return null;
		}

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(!isset($providersMap[$fields['entityTypeId']]))
		{
			$this->errorCollection[] = new Error('No provider for entityTypeId');
			return null;
		}
		$fields['providerClassName'] = $providersMap[$fields['entityTypeId']];
		unset($fields['entityTypeId']);

		$fields['fileId'] = $this->uploadFile($fields['fileContent']);
		if(!$fields['fileId'])
		{
			return null;
		}
		unset($fields['fileContent']);

		$fields['pdfId'] = $this->uploadFile($fields['pdfContent'], 'pdf', false);
		unset($fields['pdfContent']);
		$fields['imageId'] = $this->uploadFile($fields['imageContent'], 'image', false);
		unset($fields['imageContent']);
		$fields['moduleId'] = static::MODULE_ID;
		$fields['value'] = $fields['entityId'];
		unset($fields['entityId']);

		if($this->isFieldsAsArraySupportedInUpload())
		{
			$result = $this->proxyAction('uploadAction', [$fields, $restServer]);
		}
		else
		{
			$result = $this->proxyAction('uploadAction', [$restServer, $fields['fileId'], $fields['moduleId'], $fields['region'], $fields['providerClassName'], $fields['value'], $fields['title'], $fields['number']]);
		}
		if(is_array($result))
		{
			$result['document'] = $this->prepareDocumentData($result['document']);
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareDocumentData(array $data)
	{
		$data['links'] = [
			'download' => $this->getDocumentFileLink($data['id'], 'download', $data['updateTime']),
			'image' => $this->getDocumentFileLink($data['id'], 'getImage', $data['updateTime']),
			'pdf' => $this->getDocumentFileLink($data['id'], 'getPdf', $data['updateTime']),
			'public' => $data['publicUrl'],
		];
		unset($data['imageUrl']);
		unset($data['pdfUrl']);
		unset($data['printUrl']);
		unset($data['downloadUrl']);
		unset($data['publicUrl']);
		if(isset($data['value']))
		{
			$data['entityId'] = $data['value'];
			unset($data['value']);
		}
		if(isset($data['provider']))
		{
			$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
			$data['entityTypeId'] = str_ireplace(array_values($providersMap), array_keys($providersMap), $data['provider']);
			unset($data['provider']);
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	protected function isFieldsAsArraySupportedInUpload()
	{
		$template = new \Bitrix\DocumentGenerator\Controller\Template();
		return method_exists($template, 'updateAction');
	}
}
