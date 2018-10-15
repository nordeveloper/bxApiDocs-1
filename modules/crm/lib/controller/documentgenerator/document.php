<?php

namespace Bitrix\Crm\Controller\DocumentGenerator;

use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;

class Document extends Base
{
	/**
	 * @return \Bitrix\DocumentGenerator\Controller\Base
	 */
	protected function getDocumentGeneratorController()
	{
		return new \Bitrix\DocumentGenerator\Controller\Document();
	}

	protected function getDocumentFileLink(\Bitrix\DocumentGenerator\Document $document, $action)
	{
		$link = UrlManager::getInstance()->create(static::CONTROLLER_PATH.'.document.'.$action, ['documentId' => $document->ID, 'ts' => $document->getUpdateTime()->getTimestamp()]);
		$link = new Uri(UrlManager::getInstance()->getHostUrl().$link->getLocator());

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
			$data['document']['links'] = [
				'download' => $this->getDocumentFileLink($document, 'download'),
				'image' => $this->getDocumentFileLink($document, 'getImage'),
				'pdf' => $this->getDocumentFileLink($document, 'getPdf'),
				'public' => $data['document']['publicUrl'],
			];
			unset($data['document']['imageUrl']);
			unset($data['document']['pdfUrl']);
			unset($data['document']['printUrl']);
			unset($data['document']['downloadUrl']);
			unset($data['document']['publicUrl']);
			$data['document']['entityId'] = $data['document']['value'];
			unset($data['document']['value']);
			if(isset($data['document']['provider']))
			{
				$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
				$data['document']['provider'] = str_ireplace(array_values($providersMap), array_keys($providersMap), $data['document']['provider']);
			}

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

		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(is_array($filter))
		{
			if(isset($filter['provider']))
			{
				$filterMap = array_map(function($item)
				{
					return str_replace('\\', '\\\\', strtolower($item));
				}, $providersMap);
				$filter['provider'] = str_ireplace(array_keys($providersMap), $filterMap, $filter['provider']);
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
			if(isset($document['provider']))
			{
				$document['provider'] = str_ireplace(array_values($providersMap), array_keys($providersMap), $document['provider']);
			}
			if(isset($document['value']))
			{
				$document['entityId'] = $document['value'];
				unset($document['value']);
			}
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
			return false;
		}

		return $this->proxyAction('addAction', [$template, $providersMap[$entityTypeId], $entityId, $values, $stampsEnabled]);
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
		return $this->proxyAction('updateAction', [$document, $values, $stampsEnabled]);
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
	 * @see \Bitrix\DocumentGenerator\Controller\Document::downloadAction()
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array
	 */
	public function downloadAction(\Bitrix\DocumentGenerator\Document $document)
	{
		return $this->proxyAction('downloadAction', [$document]);
	}

	/**
	 * @see \Bitrix\DocumentGenerator\Controller\Document::uploadAction()
	 * @param \CRestServer $restServer
	 * @param string $fileContent
	 * @param string $region
	 * @param int $entityTypeId
	 * @param mixed $entityId
	 * @param string $title
	 * @param string $number
	 * @return \Bitrix\Main\Result|bool
	 * @throws \Exception
	 */
	public function uploadAction(\CRestServer $restServer, $fileContent, $region, $entityTypeId, $entityId, $title, $number)
	{
		$providersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		if(!isset($providersMap[$entityTypeId]))
		{
			$this->errorCollection[] = new Error('No provider for entityTypeId');
			return false;
		}

		$fileId = $this->uploadFile($fileContent);
		if(!$fileId)
		{
			return false;
		}

		return $this->proxyAction('uploadAction', [$restServer, $fileId, static::MODULE_ID, $region, $providersMap[$entityTypeId], $entityId, $title, $number]);
	}
}
