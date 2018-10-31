<?php

namespace Bitrix\DocumentGenerator\Controller;

use Bitrix\DocumentGenerator\Body\Docx;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Driver;
use Bitrix\DocumentGenerator\Model\FileTable;
use Bitrix\DocumentGenerator\Model\TemplateProviderTable;
use Bitrix\DocumentGenerator\Model\TemplateTable;
use Bitrix\DocumentGenerator\Model\TemplateUserTable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;

class Template extends Base
{
	const DEFAULT_DATA_PATH = '/bitrix/modules/documentgenerator/data/';

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
	 * Deletes template by id.
	 *
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @throws \Exception
	 */
	public function deleteAction(\Bitrix\DocumentGenerator\Template $template)
	{
		$deleteResult = TemplateTable::delete($template->ID);
		if(!$deleteResult->isSuccess())
		{
			$this->errorCollection = $deleteResult->getErrorCollection();
		}
	}

	/**
	 * Let user download template file.
	 *
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return array|bool
	 */
	public function downloadAction(\Bitrix\DocumentGenerator\Template $template)
	{
		Loc::loadLanguageFile(__FILE__);
		if(FileTable::download($template->FILE_ID, $template->getFileName(Loc::getMessage('DOCGEN_CONTROLLER_TEMPLATE_FILE_PREFIX'))) === false)
		{
			$this->errorCollection->add([new Error(Loc::getMessage('DOCGEN_CONTROLLER_TEMPLATE_DOWNLOAD_ERROR'))]);
		}

		return false;
	}

	/**
	 * Add new template.
	 *
	 * @param string $name
	 * @param int $fileId
	 * @param $numeratorId
	 * @param $region
	 * @param array $providers
	 * @param array $users
	 * @param int $id
	 * @param string $moduleId
	 * @param string $siteId
	 * @param string $active
	 * @param string $stamps
	 * @return array|bool
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function addAction($name, $fileId, $numeratorId, $region, array $providers, array $users = [], $id = null, $moduleId = Driver::MODULE_ID, $siteId = '', $active = 'Y', $stamps = 'N')
	{
		if(!$this->includeModule($moduleId))
		{
			return false;
		}
		$templateData = [
			'ACTIVE' => $active,
			'FILE_ID' => $fileId,
			'MODULE_ID' => $moduleId,
			'SITE_ID' => $siteId,
			'NAME' => $name,
			'BODY_TYPE' => Docx::class,
			'CREATED_BY' => Driver::getInstance()->getUserId(),
			'ID' => $id,
			'NUMERATOR_ID' => $numeratorId,
			'REGION' => $region,
			'WITH_STAMPS' => $stamps,
		];
		$result = $this->add($templateData, $providers, $users);
		if($result->isSuccess())
		{
			return $result->getData();
		}
		else
		{
			$this->errorCollection = $result->getErrorCollection();
			return false;
		}
	}

	/**
	 * Install default template with code $code. If template with the same code is installed - it will be overwritten.
	 *
	 * @param string $code
	 * @return array|bool
	 */
	public function installDefaultAction($code)
	{
		$filter = ['CODE' => $code];
		$result = static::getDefaultTemplateList($filter);
		if($result->isSuccess())
		{
			$templates = $result->getData();
			if(!isset($templates[$code]))
			{
				$this->errorCollection->add([new Error(Loc::getMessage('DOCGEN_TEMPLATES_DEFAULT_TEMPLATE_NOT_FOUND'))]);
				return false;
			}
			$template = $templates[$code];
			$result = $this->installDefaultTemplate($template);
		}
		if(!$result->isSuccess())
		{
			$this->errorCollection = $result->getErrorCollection();
		}

		return $result->getData();
	}

	/**
	 * Install default template.
	 *
	 * @param array $template
	 * @return Result
	 */
	public function installDefaultTemplate(array $template)
	{
		$result = new Result();
		/** @var \Bitrix\DocumentGenerator\Body $body */
		$body = new $template['BODY_TYPE']('');
		$bodyFile = new \Bitrix\Main\IO\File(Path::combine(Application::getDocumentRoot(), $template['FILE']));
		if($bodyFile->isExists())
		{
			$fileArray = \CFile::MakeFileArray($bodyFile->getPath(), $body->getFileMimeType());
			$saveResult = FileTable::saveFile($fileArray);
			if($saveResult->isSuccess())
			{
				$template['FILE_ID'] = $saveResult->getId();
			}
			else
			{
				$result->addErrors($saveResult->getErrors());
			}
		}
		else
		{
			$result->addError(new Error('File '.$bodyFile->getPath().' is not exist'));
		}
		if($result->isSuccess())
		{
			if(isset($template['MODULE_ID']) && !$this->includeModule($template['MODULE_ID']))
			{
				$result->addErrors($this->getErrors());
			}
		}
		if($result->isSuccess())
		{
			$template['IS_DELETED'] = 'N';
			$providers = $template['PROVIDERS'];
			unset($template['PROVIDER_NAMES']);
			unset($template['PROVIDERS']);
			unset($template['FILE']);
			$result = $this->add($template, $providers, [TemplateUserTable::ALL_USERS]);
		}

		return $result;
	}

	/**
	 * Returns list of default templates.
	 *
	 * @param array $filter
	 * @return Result
	 */
	public static function getDefaultTemplateList(array $filter = [])
	{
		$result = new Result();
		$dataPath = Application::getDocumentRoot().self::DEFAULT_DATA_PATH;

		if(!Directory::isDirectoryExists($dataPath))
		{
			return $result->addError(new Error('Default data directory not found'));
		}
		$templatesFile = new \Bitrix\Main\IO\File(Path::combine($dataPath, 'templates.php'));
		if(!$templatesFile->isExists())
		{
			return $result->addError(new Error('File with default templates not found'));
		}
		$templates = include $templatesFile->getPath();
		if(!is_array($templates))
		{
			return $result->addError(new Error('No data in templates file'));
		}

		foreach($templates as $key => $template)
		{
			if(!$template['FILE'])
			{
				$result->addError(new Error('Empty FILE for template'));
				unset($templates[$key]);
				continue;
			}
			if(isset($filter['CODE']) && $template['CODE'] != $filter['CODE'])
			{
				unset($templates[$key]);
				continue;
			}
			if(isset($filter['MODULE_ID']) && $template['MODULE_ID'] != $filter['MODULE_ID'])
			{
				unset($templates[$key]);
				continue;
			}
			if(isset($filter['REGION']))
			{
				if(is_array($filter['REGION']))
				{
					if(!in_array($template['REGION'], $filter['REGION']))
					{
						unset($templates[$key]);
						continue;
					}
				}
				else
				{
					if($filter['REGION'] != $template['REGION'])
					{
						unset($templates[$key]);
						continue;
					}
				}
			}
			if(isset($filter['NAME']) && strpos($template['NAME'], $filter['NAME']) === false)
			{
				unset($templates[$key]);
				continue;
			}
		}

		$templates = array_values($templates);

		$providers = DataProviderManager::getInstance()->getList();
		$extendedProviders = [];
		foreach($providers as $provider)
		{
			if(isset($provider['ORIGINAL']))
			{
				$extendedProviders[$provider['ORIGINAL']][] = $provider;
			}
		}
		$buffer = $names = $codes = [];
		foreach($templates as $template)
		{
			$names[] = $template['NAME'];
			$codes[] = $template['CODE'];
			foreach($template['PROVIDERS'] as $key => $provider)
			{
				$provider = strtolower($provider);
				if(isset($extendedProviders[$provider]))
				{
					unset($template['PROVIDERS'][$key]);
					foreach($extendedProviders[$provider] as $extendedProvider)
					{
						$template['PROVIDER_NAMES'][] = $extendedProvider['NAME'];
						$template['PROVIDERS'][] = $extendedProvider['CLASS'];
					}
				}
				else
				{
					$template['PROVIDER_NAMES'][] = $providers[strtolower($provider)]['NAME'];
				}
			}
			$buffer[$template['CODE']] = $template;
		}
		$templates = $buffer;
		unset($buffer);
		$oldTemplates = TemplateTable::getList([
			'select' => [
				'ID',
				'FILE_ID',
				'NAME',
				'CODE',
				'IS_DELETED',
			],
			'order' => [
				'ID' => 'asc'
			],
			'filter' => [
				'@CODE' => $codes,
				'@NAME' => $names
			],
		])->fetchAll();
		$unFoundTemplates = $templates;
		foreach($oldTemplates as $oldTemplate)
		{
			foreach($unFoundTemplates as $code => $unFoundTemplate)
			{
				if($oldTemplate['CODE'] == $unFoundTemplate['CODE'] && $oldTemplate['NAME'] == $unFoundTemplate['NAME'])
				{
					$templates[$code]['IS_DELETED'] = $oldTemplate['IS_DELETED'];
					$templates[$code]['ID'] = $oldTemplate['ID'];
					$templates[$code]['FILE_ID'] = $oldTemplate['FILE_ID'];
					unset($unFoundTemplates[$code]);
					break;
				}
			}
		}
		$result->setData($templates);

		return $result;
	}

	/**
	 * @param array $templateData
	 * @param array $providers
	 * @param array $users
	 * @return Result
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Exception
	 */
	protected function add(array $templateData, array $providers, array $users = [])
	{
		$id = intval($templateData['ID']);
		if($id > 0)
		{
			$template = \Bitrix\DocumentGenerator\Template::loadById($id);
			if($template)
			{
				unset($templateData['CREATED_BY']);
				$templateData['UPDATE_TIME'] = new DateTime();
				$templateData['UPDATED_BY'] = Driver::getInstance()->getUserId();
				$result = TemplateTable::update($id, $templateData);
			}
			else
			{
				$result = new Result();
				$result->addError(new Error(Loc::getMessage('DOCGEN_CONTROLLER_TEMPLATE_NOT_FOUND')));
			}
		}
		else
		{
			if(empty($templateData['NUMERATOR_ID']))
			{
				$templateData['NUMERATOR_ID'] = $this->createNumerator($templateData['NAME']);
			}
			$result = TemplateTable::add($templateData);
		}
		if(!$result->isSuccess())
		{
			return $result;
		}
		$templateId = $result->getId();
		TemplateProviderTable::deleteByTemplateId($templateId);
		foreach($providers as $provider)
		{
			$result = TemplateProviderTable::add([
				'TEMPLATE_ID' => $templateId,
				'PROVIDER' => $provider,
			]);
			if(!$result->isSuccess())
			{
				TemplateTable::delete($templateId, true);
				return $result;
			}
		}
		TemplateUserTable::delete($templateId);
		foreach($users as $code)
		{
			$result = TemplateUserTable::add([
				'TEMPLATE_ID' => $templateId,
				'ACCESS_CODE' => $code,
			]);
			if(!$result->isSuccess())
			{
				TemplateTable::delete($templateId, true);
				return $result;
			}
		}
		$template = \Bitrix\DocumentGenerator\Template::loadById($templateId);
		$result->setData($this->getAction($template));

		return $result;
	}

	/**
	 * @param string $name
	 * @return int|null
	 */
	protected function createNumerator($name)
	{
		$numeratorId = null;

		$numerator = Numerator::create();
		$numerator->setConfig([
			Numerator::getType() => [
				'name'     => $name,
				'template' => '{NUMBER}',
				'type'     => Driver::NUMERATOR_TYPE,
			],
		]);
		$saveResult = $numerator->save();
		if($saveResult->isSuccess())
		{
			$numeratorId = $saveResult->getId();
		}

		return $numeratorId;
	}

	protected function includeModule($moduleId)
	{
		if(!empty($moduleId) && !(ModuleManager::isModuleInstalled($moduleId) && Loader::includeModule($moduleId)))
		{
			$this->errorCollection->add([new Error(Loc::getMessage('DOCGEN_CONTROLLER_MODULE_INVALID', ['#MODULE#' => $moduleId]))]);
			return false;
		}

		return true;
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @param $providerClassName
	 * @param $value
	 * @param array $values
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function getFieldsAction(\Bitrix\DocumentGenerator\Template $template, $providerClassName, $value, array $values = [])
	{
		$template->setSourceType($providerClassName);
		$document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $value);
		if(!$document->hasAccess(Driver::getInstance()->getUserId()))
		{
			$this->errorCollection[] = new Error('Access denied', Document::ERROR_ACCESS_DENIED);
			return false;
		}
		$fields = $document->setValues($values)->getFields([], true, true);
		foreach($fields as &$field)
		{
			$field = $this->convertArrayKeysToCamel($field, 3);
		}
		return ['templateFields' => $fields];
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
		$withProviders = $withUsers = false;
		if(($key = array_search('providers', $select)) !== false)
		{
			$withProviders = true;
			unset($select[$key]);
		}
		if(($key = array_search('users', $select)) !== false)
		{
			$withUsers = true;
			unset($select[$key]);
		}

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

		if($withUsers || $withProviders)
		{
			if(!in_array('ID', $select))
			{
				$select[] = 'ID';
			}
		}

		$templates = TemplateTable::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
		])->fetchAll();

		if($withUsers || $withProviders)
		{
			$buffer = [];
			foreach($templates as $template)
			{
				$buffer[$template['ID']] = $template;
			}
			$templates = $buffer;
			unset($buffer);
		}

		if($withProviders)
		{
			$providers = TemplateProviderTable::getList(['filter' => ['TEMPLATE_ID' => array_keys($templates)]]);
			while($provider = $providers->fetch())
			{
				$templates[$provider['TEMPLATE_ID']]['PROVIDERS'][] = $provider['PROVIDER'];
			}
		}
		if($withUsers)
		{
			$users = TemplateUserTable::getList(['filter' => ['TEMPLATE_ID' => array_keys($templates)]]);
			while($user = $users->fetch())
			{
				$templates[$user['TEMPLATE_ID']]['USERS'][] = $user['ACCESS_CODE'];
			}
		}

		return new Page('templates', $this->convertArrayKeysToCamel($templates, 1), function() use ($filter)
		{
			return TemplateTable::getCount($filter);
		});
	}

	/**
	 * @param \Bitrix\DocumentGenerator\Template $template
	 * @return array
	 */
	public function getAction(\Bitrix\DocumentGenerator\Template $template)
	{
		return [
			'template' => [
				'id' => $template->ID,
				'name' => $template->NAME,
				'region' => $template->REGION,
				'code' => $template->CODE,
				'links' => [
					'download' => $template->getDownloadUrl(true),
				],
				'active' => $template->ACTIVE,
				'moduleId' => $template->MODULE_ID,
				'numeratorId' => $template->NUMERATOR_ID,
				'withStamps' => $template->WITH_STAMPS,
				'providers' => $template->getDataProviders(),
				'users' => $template->getUsers(),
				'fileId' => $template->FILE_ID,
			],
		];
	}
}