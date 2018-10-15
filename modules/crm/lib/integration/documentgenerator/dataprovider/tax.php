<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Integration\DocumentGenerator\Value\Money;
use Bitrix\DocumentGenerator\DataProvider\HashDataProvider;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\Main\IO\Path;

class Tax extends HashDataProvider
{
	/**
	 * @return array
	 */
	public function getFields()
	{
		$fields = [
			'NAME' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_TITLE_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'TITLE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_TITLE_NEW_TITLE'),
				'VALUE' => function()
				{
					$name = $this->data['NAME'];
					if(ToUpper(SITE_CHARSET) !== 'UTF-8')
					{
						global $APPLICATION;
						$name = $APPLICATION->ConvertCharsetArray($name, SITE_CHARSET, 'UTF-8');
					}

					if($this->data['TAX_INCLUDED'] == 'Y')
					{
						$phrase = 'TAX_INCLUDED';
					}
					else
					{
						$phrase = 'TAX_NOT_INCLUDED';
					}
					if(\CCrmTax::isTaxMode())
					{
						$phrase .= '_NOT_VAT';
					}
					$title = DataProviderManager::getInstance()->getLangPhraseValue($this, $phrase);

					return str_replace('#NAME#', $name, $title);
				},
				'HIDE_ROW' => 'Y',
			],
			'RATE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_RATE_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'NETTO' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_NETTO_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'VALUE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_VALUE_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'BRUTTO' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_BRUTTO_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'TAX_INCLUDED' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_TAX_INCLUDED_TITLE'),
			]
		];

		return $fields;
	}

	/**
	 * @return string
	 */
	public function getLangPhrasesPath()
	{
		return Path::getDirectory(__FILE__).'/../phrases';
	}
}