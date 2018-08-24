<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\DealTable;
use Bitrix\DocumentGenerator\DataProvider\Filterable;
use Bitrix\DocumentGenerator\Nameable;

class Deal extends ProductsDataProvider implements Nameable, Filterable
{
	protected $linkData;

	public function getFields()
	{
		$fields = parent::getFields();

		$fields['STAGE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_DEAL_STAGE_TITLE'),
		];
		$fields['TYPE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_DEAL_TYPE_TITLE'),
		];
		$fields['EVENT'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_DEAL_EVENT_TITLE'),
		];
//		$fields['LEAD'] = [
//			'PROVIDER' => Lead::class,
//			'VALUE' => 'LEAD_ID',
//			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_DEAL_LEAD_TITLE'),
//		];

		return $fields;
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_DEAL_TITLE');
	}

	/**
	 * @param int $userId
	 * @return bool
	 */
	public function hasAccess($userId)
	{
		if($this->isLoaded())
		{
			$userPermissions = new \CCrmPerms($userId);

			return \CCrmDeal::CheckReadPermission(
				$this->source,
				$userPermissions
			);
		}

		return false;
	}

	protected function getTableClass()
	{
		return DealTable::class;
	}

	/**
	 * @return array
	 */
	protected function getHiddenFields()
	{
		return array_merge(parent::getHiddenFields(), [
			'EXCH_RATE',
			'STAGE_ID',
			'STAGE_BY',
			'CLOSED',
			'IS_RECURRING',
			'TYPE_ID',
			'TYPE_BY',
			'EVENT_ID',
			'EVENT_BY',
			'BEGINDATE_SHORT',
			'DATE_CREATE_SHORT',
			'CLOSEDATE_SHORT',
			'DATE_MODIFY_SHORT',
			'EVENT_DATE_SHORT',
			'ASSIGNED_BY_ID',
			'ASSIGNED_BY',
			'CREATED_BY_ID',
			'CREATED_BY',
			'MODIFY_BY_ID',
			'MODIFY_BY',
			'EVENT_RELATION',
			'LEAD_ID',
			'LEAD_BY',
			'CONTACT_ID',
			'CONTACT_BY',
			'COMPANY_ID',
			'COMPANY_BY',
			'IS_WORK',
			'IS_WON',
			'IS_LOSE',
			'HAS_PRODUCTS',
			'SEARCH_CONTENT',
			'ORIGIN_ID',
			'ORIGINATOR_ID',
			'ORIGINATOR_BY',
			'STAGE_SEMANTIC_ID',
		]);
	}

	/**
	 * @return array
	 */
	protected function getGetListParameters()
	{
		return array_merge_recursive(parent::getGetListParameters(), [
			'select' => [
				'STAGE' => 'STAGE_BY.NAME',
				'TYPE' => 'TYPE_BY.NAME',
				'EVENT' => 'EVENT_BY.NAME',
			],
		]);
	}

	/**
	 * @return int
	 */
	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Deal;
	}

	protected function getCrmProductOwnerType()
	{
		return 'D';
	}

	/**
	 * @return string
	 */
	protected function getUserFieldEntityID()
	{
		return \CCrmDeal::GetUserFieldEntityID();
	}

	/**
	 * @return array
	 */
	public static function getExtendedList()
	{
		static $list = false;
		if($list === false)
		{
			$list = [];

			$categories = DealCategory::getAll(true);
			foreach($categories as $category)
			{
				$list[] = [
					'NAME' => static::getLangName().' ('.$category['NAME'].')',
					'PROVIDER' => strtolower(static::class).'_category_'.$category['ID'],
				];
			}
		}

		return $list;
	}

	/**
	 * @return string
	 */
	public function getFilterString()
	{
		$categoryId = 0;

		if($this->isLoaded())
		{
			$categoryId = $this->getValue('CATEGORY_ID');
		}

		return strtolower(static::class).'_category_'.$categoryId;
	}
}