<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

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
			'TITLE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_TITLE_TITLE'),
				'VALUE' => function()
				{
					if($this->data['TAX_INCLUDED'] == 'Y')
					{
						return DataProviderManager::getInstance()->getLangPhraseValue($this, 'TAX_INCLUDED');
					}
					else
					{
						return DataProviderManager::getInstance()->getLangPhraseValue($this, 'TAX_NOT_INCLUDED');
					}
				},
				'HIDE_ROW' => 'Y',
			],
			'RATE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_RATE_TITLE'),
				'HIDE_ROW' => 'Y',
			],
			'VALUE' => [
				'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_TAX_VALUE_TITLE'),
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