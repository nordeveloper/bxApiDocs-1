<?php

namespace Bitrix\DocumentGenerator\Controller;

use Bitrix\DocumentGenerator\Body\Docx;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Driver;
use Bitrix\DocumentGenerator\Engine\CheckAccess;
use Bitrix\DocumentGenerator\Integration\Bitrix24Manager;
use Bitrix\DocumentGenerator\Model\DocumentTable;
use Bitrix\DocumentGenerator\Model\FileTable;
use Bitrix\DocumentGenerator\Model\TemplateTable;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\APAuth\PasswordTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\OAuth\Auth;

class Document extends Base
{
	const ERROR_ACCESS_DENIED = 'DOCGEN_ACCESS_ERROR';

	/**
	 * @return array
	 */
	public function getDefaultPreFilters()
	{
		$preFilters = parent::getDefaultPreFilters();
		$preFilters[] = new CheckAccess();

		return $preFilters;
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$configureActions['getImage'] = [
			'-prefilters' => [
				Csrf::class
			]
		];
		$configureActions['getFile'] = [
			'-prefilters' => [
				Csrf::class
			]
		];
		$configureActions['getPdf'] = [
			'-prefilters' => [
				Csrf::class
			]
		];
		$configureActions['showPdf'] = [
			'-prefilters' => [
				Csrf::class
			]
		];

		return $configureActions;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array|bool
	 */
	public function getImageAction(\Bitrix\DocumentGenerator\Document $document)
	{
		if($document->IMAGE_ID > 0)
		{
			return FileTable::download($document->IMAGE_ID);
		}
		else
		{
			Loc::loadLanguageFile(__FILE__);
			$this->errorCollection[] = new Error(Loc::getMessage('DOCGEN_CONTROLLER_DOCUMENT_NO_IMAGE'));
		}

		return false;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param string $fileName
	 * @return mixed
	 */
	public function getFileAction(\Bitrix\DocumentGenerator\Document $document, $fileName = '')
	{
		if($fileName === '')
		{
			$fileName = $document->getFileName();
		}
		return FileTable::download($document->FILE_ID, $fileName);
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param string $fileName
	 * @return array|mixed
	 */
	public function getPdfAction(\Bitrix\DocumentGenerator\Document $document, $fileName = '')
	{
		if($document->PDF_ID > 0)
		{
			if($fileName === '')
			{
				$fileName = $document->getFileName('pdf');
			}
			return FileTable::download($document->PDF_ID, $fileName);
		}
		else
		{
			Loc::loadLanguageFile(__FILE__);
			$this->errorCollection[] = new Error(Loc::getMessage('DOCGEN_CONTROLLER_DOCUMENT_NO_PDF'));
		}

		return false;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param string $print
	 * @return HttpResponse
	 */
	public function showPdfAction(\Bitrix\DocumentGenerator\Document $document, $print = 'y')
	{
		$response = new HttpResponse();
		if($document->PDF_ID > 0)
		{
			global $APPLICATION;
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:pdf.viewer',
				'',
				[
					'PATH' => $document->getPdfUrl(),
					'IFRAME' => 'Y',
					'PRINT' => ($print === 'y' ? 'Y' : 'N'),
				]
			);
			$response->setContent(ob_get_contents());
			ob_end_clean();
		}
		else
		{
			Loc::loadLanguageFile(__FILE__);
			$this->errorCollection[] = new Error(Loc::getMessage('DOCGEN_CONTROLLER_DOCUMENT_NO_PDF'));
		}
		return $response;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @throws \Exception
	 */
	public function deleteAction(\Bitrix\DocumentGenerator\Document $document)
	{
		$result = DocumentTable::delete($document->ID);
		if(!$result->isSuccess())
		{
			$this->errorCollection = $result->getErrorCollection();
		}
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @param $providerClassName
	 * @param $value
	 * @param array $values
	 * @param int $stampsEnabled
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function addAction(\Bitrix\DocumentGenerator\Template $template, $providerClassName, $value, array $values = [], $stampsEnabled = 0)
	{
		$template->setSourceType($providerClassName);
		$document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $value);
		if(!$document->hasAccess(Driver::getInstance()->getUserId()))
		{
			$this->errorCollection[] = new Error('Access denied', static::ERROR_ACCESS_DENIED);
			return false;
		}
		if(Bitrix24Manager::isEnabled() && Bitrix24Manager::isDocumentsLimitReached())
		{
			$this->errorCollection[] = new Error('Maximum count of documents has been reached', Bitrix24Manager::LIMIT_ERROR_CODE);
			return false;
		}
		$result = $document->enableStamps($stampsEnabled == 1)->setValues($values)->getFile();
		if(!$result->isSuccess())
		{
			$this->errorCollection = $result->getErrorCollection();
			return false;
		}

		return ['document' => $result->getData()];
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param array $values
	 * @param int $stampsEnabled
	 * @return array
	 */
	public function updateAction(\Bitrix\DocumentGenerator\Document $document, array $values = [], $stampsEnabled = 1)
	{
		$result = $document->enableStamps($stampsEnabled == 1)->update($values);
		if(!$result->isSuccess())
		{
			$this->errorCollection = $result->getErrorCollection();
		}

		return ['document' => $result->getData()];
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param array $values
	 * @return array|false
	 */
	public function getFieldsAction(\Bitrix\DocumentGenerator\Document $document, array $values = [])
	{
		$fields = $document->setValues($values)->getFields([], true, true);
		foreach($fields as &$field)
		{
			$field = $this->convertArrayKeysToCamel($field, 3);
		}
		return ['documentFields' => $fields];
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @return array|false
	 */
	public function getAction(\Bitrix\DocumentGenerator\Document $document)
	{
		$result = $document->getFile();
		if($result->isSuccess())
		{
			return ['document' => $result->getData()];
		}
		else
		{
			$this->errorCollection = $result->getErrorCollection();
		}

		return false;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Document $document
	 * @param int $status
	 * @return array
	 */
	public function enablePublicUrlAction(\Bitrix\DocumentGenerator\Document $document, $status = 1)
	{
		$result = $document->enablePublicUrl($status == 1);
		if($result->isSuccess())
		{
			return [
				'publicUrl' => $document->getPublicUrl(),
			];
		}
		else
		{
			$this->errorCollection = $result->getErrorCollection();
			return [];
		}
	}

	/**
	 * @param array $select
	 * @param array|null $order
	 * @param array|null $filter
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 */
	public function listAction(array $select = ['*'], array $order = null, array $filter = null, PageNavigation $pageNavigation = null)
	{
		if(is_array($filter))
		{
			$filter = $this->convertArrayKeysToUpper($filter, 1);
		}
		if(is_array($order))
		{
			$order = $this->convertArrayKeysToUpper($order, 1);
		}
		if(is_array($select))
		{
			$select = $this->convertArrayValuesToUpper($select, 1);
		}

		return new Page('documents', $this->convertArrayKeysToCamel(DocumentTable::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
		])->fetchAll(), 1), function() use ($filter)
		{
			return DocumentTable::getCount($filter);
		});
	}

	/**
	 * @param \CRestServer $restServer
	 * @param int $fileId
	 * @param string $moduleId
	 * @param string $region
	 * @param string $providerClassName
	 * @param mixed $value
	 * @param string $title
	 * @param string $number
	 * @return array|bool
	 */
	public function uploadAction(\CRestServer $restServer, $fileId, $moduleId, $region, $providerClassName, $value, $title, $number)
	{
		if(empty($moduleId) || !is_string($moduleId))
		{
			$this->errorCollection[] = new Error('Wrong moduleId');
			return false;
		}

		if(!Loader::includeModule($moduleId))
		{
			$this->errorCollection[] = new Error('Module '.$moduleId.' is not installed');
			return false;
		}

		if(!DataProviderManager::checkProviderName($providerClassName))
		{
			$this->errorCollection[] = new Error('Wrong provider '.$providerClassName);
			return false;
		}

		$restTemplate = $this->getRestTemplate($restServer, $moduleId, $region);
		if(!$restTemplate)
		{
			$this->errorCollection[] = new Error('Error getting template');
			return false;
		}
		$restTemplate->setSourceType($providerClassName);

		$result = \Bitrix\DocumentGenerator\Document::upload($restTemplate, $value, $title, $number, $fileId);
		if($result->isSuccess())
		{
			return ['document' => $result->getData()];
		}
		else
		{
			$this->errorCollection->add($result->getErrors());
			return false;
		}
	}

	/**
	 * @param \CRestServer $restServer
	 * @param string $moduleId
	 * @param string $region
	 * @return \Bitrix\DocumentGenerator\Template|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	protected function getRestTemplate(\CRestServer $restServer, $moduleId, $region)
	{
		$appInfo = $this->getRestAppInfo($restServer);
		if(!$appInfo)
		{
			$this->errorCollection[] = new Error('Application not found');
			return false;
		}

		$templateId = 0;
		$template = TemplateTable::getList(['select' => ['ID'], 'order' => ['ID' => 'desc',],'filter' => ['MODULE_ID' => $moduleId, 'CODE' => $appInfo['CODE'], 'REGION' => $region]])->fetch();
		if(!$template)
		{
			$fileResult = FileTable::saveFile($this->generateStubFile());
			if(!$fileResult->isSuccess())
			{
				$this->errorCollection[] = new Error('Error generating file for template');
				return false;
			}
			$data = [
				'NAME' => $appInfo['TITLE'],
				'CODE' => $appInfo['CODE'],
				'REGION' => $region,
				'CREATED_BY' => CurrentUser::get()->getId(),
				'UPDATED_BY' => CurrentUser::get()->getId(),
				'MODULE_ID' => $moduleId,
				'FILE_ID' => $fileResult->getId(),
				'BODY_TYPE' => Docx::class,
				'IS_DELETED' => 'Y',
			];
			$addResult = TemplateTable::add($data);
			if($addResult->isSuccess())
			{
				$templateId = $addResult->getId();
			}
		}
		else
		{
			$templateId = $template['ID'];
		}

		return \Bitrix\DocumentGenerator\Template::loadById($templateId);
	}

	/**
	 * @param \CRestServer $server
	 * @return array|false
	 */
	protected function getRestAppInfo(\CRestServer $server)
	{
		if($server->getAuthType() === Auth::AUTH_TYPE)
		{
			$app = AppTable::getByClientId($server->getClientId());
			if($app)
			{
				return [
					'TITLE' => $app['APP_NAME'],
					'CODE' => 'rest_'.Auth::AUTH_TYPE.'_'.$app['ID'],
				];
			}
		}
		elseif($server->getAuthType() === \Bitrix\Rest\APAuth\Auth::AUTH_TYPE)
		{
			$hook = PasswordTable::getById($server->getPasswordId())->fetch();
			if($hook)
			{
				return [
					'TITLE' => $hook['TITLE'],
					'CODE' => 'rest_'.\Bitrix\Rest\APAuth\Auth::AUTH_TYPE.'_'.$hook['ID'],
				];
			}
		}

		return false;
	}

	/**
	 * @return array|false
	 */
	protected function generateStubFile()
	{
		$fileName = md5(mt_rand());
		$fileName = \CTempFile::GetFileName($fileName);

		if(CheckDirPath($fileName))
		{
			if(\Bitrix\Main\IO\File::putFileContents($fileName, ' ') !== false)
			{
				return \CFile::MakeFileArray($fileName);
			}
		}

		return false;
	}
}

