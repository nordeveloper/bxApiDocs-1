<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\QuoteTable;
use Bitrix\DocumentGenerator\Nameable;

class Quote extends ProductsDataProvider implements Nameable
{
	/**
	 * Returns list of value names for this Provider.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = parent::getFields();
		$fields['ID'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_ID_TITLE'),];
		$fields['TITLE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_TITLE_TITLE'),];
		$fields['OPPORTUNITY'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_OPPORTUNITY_TITLE'),];
		$fields['TAX_VALUE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_TAX_VALUE_TITLE'),];
		$fields['CURRENCY_ID'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_CURRENCY_ID_TITLE'),];
		$fields['COMMENTS'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_COMMENTS_TITLE'),];
		$fields['BEGINDATE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_BEGINDATE_TITLE'),];
		$fields['CLOSEDATE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_CLOSEDATE_TITLE'),];
		$fields['DATE_CREATE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_DATE_CREATE_TITLE'),];
		$fields['DATE_MODIFY'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_DATE_MODIFY_TITLE'),];
		$fields['CONTENT'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_CONTENT_TITLE'),];
		$fields['TERMS'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_TERMS_TITLE'),];
//		$fields['LEAD'] = [
//			'PROVIDER' => Lead::class,
//			'VALUE' => 'LEAD_ID',
//			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_LEAD_TITLE'),
//		];
		$fields['DEAL'] = [
			'PROVIDER' => Deal::class,
			'VALUE' => 'DEAL_ID',
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_DEAL_TITLE'),
		];
		//files

		return $fields;
	}

	/**
	 * Fill $this->data.
	 */
	protected function fetchData()
	{
		if($this->data === null)
		{
			$this->data = [];
			$data = \CCrmQuote::GetByID($this->source);
			if($data)
			{
				$this->data = $data;
			}
		}
		$this->loadProducts();
		$this->calculateTotalFields();
	}

	/**
	 * @param int $userId
	 * @return boolean
	 */
	public function hasAccess($userId)
	{
		if($this->isLoaded())
		{
			$userPermissions = new \CCrmPerms($userId);

			return \CCrmQuote::CheckReadPermission(
				$this->source,
				$userPermissions
			);
		}

		return false;
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_QUOTE_TITLE');
	}

	/**
	 * @return string
	 */
	protected function getTableClass()
	{
		return QuoteTable::class;
	}

	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Quote;
	}

	protected function getCrmProductOwnerType()
	{
		return \CCrmQuote::OWNER_TYPE;
	}

	/**
	 * @return string
	 */
	protected function getUserFieldEntityID()
	{
		return \CCrmQuote::GetUserFieldEntityID();
	}
}