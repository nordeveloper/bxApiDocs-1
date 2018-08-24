<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\ContactTable;
use Bitrix\DocumentGenerator\Nameable;

class Contact extends CrmEntityDataProvider implements Nameable
{
	protected $bankDetailIds;

	/**
	 * @return array
	 */
	public function getFields()
	{
		$fields = parent::getFields();
		$fields['FORMATTED_NAME'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_FORMATTED_NAME_TITLE'),
			'VALUE' => function()
			{
				return \CCrmContact::PrepareFormattedName([
					'HONORIFIC' => $this->getValue('HONORIFIC'),
					'NAME' => $this->getValue('NAME'),
					'SECOND_NAME' => $this->getValue('SECOND_NAME'),
					'LAST_NAME' => $this->getValue('LAST_NAME'),
				]);
			}
		];
		$fields['TYPE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_CONTACT_TYPE_TITLE'),];
		$fields['SOURCE'] = ['TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_CONTACT_SOURCE_TITLE'),];
		$fields['EMAIL_HOME'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_EMAIL_HOME_TITLE'),
		];
		$fields['EMAIL_WORK'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_EMAIL_WORK_TITLE'),
		];
		$fields['PHONE_MOBILE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PHONE_MOBILE_TITLE'),
		];
		$fields['PHONE_WORK'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_PHONE_WORK_TITLE'),
		];
		$fields['IMOL'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_IMOL_TITLE'),
		];

		if(isset($this->getOptions()['DISABLE_MY_COMPANY']))
		{
			unset($fields['MY_COMPANY']);
		}

		unset($fields['COMPANY']);
		unset($fields['CONTACT']);

		return $fields;
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_CONTACT_TITLE');
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
			return \CCrmContact::CheckReadPermission($this->source, $userPermissions);
		}

		return false;
	}

	/**
	 * @return string
	 */
	protected function getTableClass()
	{
		return ContactTable::class;
	}

	/**
	 * @return array
	 */
	protected function getHiddenFields()
	{
		return array_merge(parent::getHiddenFields(), [
			'LOGIN',
			'TYPE_ID',
			'TYPE_BY',
			'SOURCE_ID',
			'SOURCE_BY',
			'ASSIGNED_BY_ID',
			'ASSIGNED_BY',
			'CREATED_BY_ID',
			'CREATED_BY',
			'MODIFY_BY_ID',
			'MODIFY_BY',
			'EVENT_RELATION',
			'FACE_ID',
			'HAS_EMAIL',
			'HAS_PHONE',
			'HAS_IMOL',
			'SEARCH_CONTENT',
//			'HONORIFIC',
//			'FULL_NAME',
		]);
	}

	/**
	 * @return array
	 */
	protected function getGetListParameters()
	{
		return array_merge_recursive(parent::getGetListParameters(), [
			'select' => [
				'TYPE' => 'TYPE_BY.NAME',
				'SOURCE' => 'SOURCE_BY.NAME',
			],
		]);
	}

	/**
	 * @return int
	 */
	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Contact;
	}

	/**
	 * @return int|null
	 */
	protected function getCompanyId()
	{
		return null;
	}

	/**
	 * @return int|null
	 */
	protected function getContactId()
	{
		return $this->source;
	}

	/**
	 * @return string
	 */
	protected function getUserFieldEntityID()
	{
		return \CCrmContact::GetUserFieldEntityID();
	}
}