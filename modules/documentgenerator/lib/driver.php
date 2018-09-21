<?php

namespace Bitrix\DocumentGenerator;

use Bitrix\DocumentGenerator\DataProvider\Filterable;
use Bitrix\DocumentGenerator\Integration\Bitrix24Manager;
use Bitrix\DocumentGenerator\Model\TemplateProviderTable;
use Bitrix\DocumentGenerator\Model\TemplateTable;
use Bitrix\DocumentGenerator\Storage\BFile;
use Bitrix\DocumentGenerator\Storage\Disk;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Numerator\Numerator;

final class Driver
{
	const MODULE_ID = 'documentgenerator';
	const DEFAULT_DATA_PATH = '/bitrix/modules/documentgenerator/data/';

	const NUMERATOR_TYPE = 'DOCUMENT';

	/** @var  Driver */
	private static $instance;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	/**
	 * Returns Singleton of Driver
	 * @return Driver
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Driver;
		}

		return self::$instance;
	}

	/**
	 * @return Storage
	 */
	public function getDefaultStorage()
	{
		$storageType = Option::get('documentgenerator', 'default_storage_type');
		if($storageType && is_a($storageType, Storage::class, true))
		{
			return new $storageType();
		}

		if(ModuleManager::isModuleInstalled('disk'))
		{
			return new Disk();
		}

		return new BFile();
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		global $USER;
		if(is_object($USER))
		{
			return CurrentUser::get()->getId();
		}

		return 0;
	}

	/**
	 * @return string
	 */
	protected function getDefaultNumeratorId()
	{
		return Option::get(self::MODULE_ID, 'default_numerator', 0);
	}

	/**
	 * @param $id
	 * @return $this
	 */
	protected function setDefaultNumeratorId($id)
	{
		Option::set(self::MODULE_ID, 'default_numerator', $id);

		return $this;
	}

	/**
	 * @param null $source
	 * @return false|Numerator
	 */
	public function getDefaultNumerator($source = null)
	{
		$numeratorId = $this->getDefaultNumeratorId();
		$numerator = Numerator::load($numeratorId, $source);
		if(!$numerator)
		{
			Loc::loadLanguageFile(__FILE__);
			$numerator = Numerator::create();
			$numerator->setConfig([
				Numerator::getType() => [
					'name'     => Loc::getMessage('DOCUMENTGENERATOR_DEFAULT_NUMERATOR_TITLE'),
					'template' => '{NUMBER}',
					'type'     => static::NUMERATOR_TYPE,
				],
			]);
			$saveResult = $numerator->save();
			if($saveResult->isSuccess())
			{
				$numeratorId = $saveResult->getId();
				$this->setDefaultNumeratorId($numeratorId);
			}
			else
			{
				$numerator = false;
			}
		}

		return $numerator;
	}

	/**
	 * @return array
	 */
	public function getRegionsList()
	{
		Loc::loadLanguageFile(__FILE__);
		return [
			'ru' => [
				'CODE' => 'ru',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_RU'),
				'LANGUAGE_ID' => 'ru',
			],
			'by' => [
				'CODE' => 'by',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_BY'),
				'LANGUAGE_ID' => 'ru',
			],
			'kz' => [
				'CODE' => 'kz',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_KZ'),
				'LANGUAGE_ID' => 'kz',
			],
			'ua' => [
				'CODE' => 'ua',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_UA'),
				'LANGUAGE_ID' => 'ua',
			],
			'de' => [
				'CODE' => 'de',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_DE'),
				'LANGUAGE_ID' => 'de',
			],
//			'en' => [
//				'CODE' => 'en',
//				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_EN'),
//				'LANGUAGE_ID' => 'en',
//			],
			'uk' => [
				'CODE' => 'uk',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_UK'),
				'LANGUAGE_ID' => 'en',
			],
			'br' => [
				'CODE' => 'br',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_BR'),
				'LANGUAGE_ID' => 'br',
			],
			'mx' => [
				'CODE' => 'mx',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_MX'),
				'LANGUAGE_ID' => 'la',
			],
			'pl' => [
				'CODE' => 'pl',
				'TITLE' => Loc::getMessage('DOCUMENTGENERATOR_REGIONS_PL'),
				'LANGUAGE_ID' => 'pl',
			],
		];
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getCurrentRegion()
	{
		$region = [];

		if(Bitrix24Manager::isEnabled())
		{
			$region = substr((string)Option::get('main', '~controller_group_name'), 0, 2);
			if(empty($region))
			{
				$region = $this->getRegionByLanguageId(Bitrix24Manager::getDefaultLanguage());
			}
			else
			{
				$region = $this->getRegionByLanguageId($region);
			}
		}
		if(empty($region))
		{
			$region = $this->getRegionByLanguageId(LANGUAGE_ID);
		}
		if(empty($region))
		{
			$region = $this->getRegionByLanguageId('en');
		}

		return $region;
	}

	/**
	 * @param $languageId
	 * @return array
	 */
	protected function getRegionByLanguageId($languageId)
	{
		foreach($this->getRegionsList() as $region => $description)
		{
			if($description['LANGUAGE_ID'] == $languageId)
			{
				return $description;
			}
		}

		return [];
	}

	/**
	 * @param bool $rewrite
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function installDefaultTemplatesForCurrentRegion($rewrite = false)
	{
		$controller = new Controller\Template();

		$result = $controller::getDefaultTemplateList(['REGION' => Driver::getInstance()->getCurrentRegion()['CODE']]);
		if($result->isSuccess())
		{
			foreach($result->getData() as $template)
			{
				if(!$rewrite && $template['ID'] > 0)
				{
					continue;
				}
				$controller->installDefaultTemplate($template);
			}
		}

		return '';
	}

	public static function extendTemplateProviders($moduleId, $provider)
	{
		if(!Loader::includeModule($moduleId))
		{
			return '';
		}
		if(!is_a($provider, Filterable::class, true))
		{
			return '';
		}
		$provider = trim(strtolower($provider), '\\');
		/** @var Filterable $provider */
		$extendedList = $provider::getExtendedList();
		$templateProviders = TemplateProviderTable::getList(['filter' => ['=PROVIDER' => strtolower($provider)]])->fetchAll();
		foreach($templateProviders as $templateProvider)
		{
			TemplateProviderTable::delete($templateProvider);
			foreach($extendedList as $item)
			{
				TemplateProviderTable::add([
					'TEMPLATE_ID' => $templateProvider['TEMPLATE_ID'],
					'PROVIDER' => $item['PROVIDER'],
				]);
			}
		}

		return '';
	}

	public static function normalizeCurrentTemplates()
	{
		$templates = TemplateTable::getList(['select' => ['ID'], 'filter' => ['IS_DELETED' => 'N']]);
		while($template = $templates->fetch())
		{
			TemplateTable::normalizeBody($template['ID']);
		}
	}
}