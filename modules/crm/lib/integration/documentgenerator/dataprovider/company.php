<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\CompanyTable;
use Bitrix\DocumentGenerator\Nameable;
use Bitrix\Main\IO\Path;

class Company extends CrmEntityDataProvider implements Nameable
{
	protected $bankDetailIds;
	protected $multiFields;

	public function getFields()
	{
		$fields = parent::getFields();
		$fields['TYPE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_COMPANY_TYPE_TITLE'),
		];
		$fields['INDUSTRY_TYPE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_INDUSTRY_TYPE_TITLE'),
		];
		$fields['EMPLOYEES_NUM'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_EMPLOYEES_NUM_TITLE'),
		];
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
		$fields['WEB'] = [
			'VALUE' => function()
			{
				return $this->getWeb();
			},
			'TITLE' => GetMessage('CRM_DOCGEN_DATAPROVIDER_COMPANY_WEB_TITLE'),
		];

		if($this->isMyCompany())
		{
			foreach($fields as $placeholder => $field)
			{
				if(isset($this->getMyCompanyFields()[$placeholder]))
				{
					$fields[$placeholder] = array_merge($fields[$placeholder], $this->getMyCompanyFields()[$placeholder]);
				}
			}
			$fields['REQUISITE']['TITLE'] = GetMessage('CRM_DOCGEN_DATAPROVIDER_MY_COMPANY_REQUISITE_TITLE');
			if(!is_array($fields['REQUISITE']['OPTIONS']))
			{
				$fields['REQUISITE']['OPTIONS'] = [];
			}
			$fields['REQUISITE']['OPTIONS'] = array_merge_recursive($fields['REQUISITE']['OPTIONS'], ['IS_MY_COMPANY' => 'Y']);
			$fields['BANK_DETAIL']['TITLE'] = GetMessage('CRM_DOCGEN_DATAPROVIDER_MY_COMPANY_BANK_DETAIL_TITLE');
			if(!is_array($fields['BANK_DETAIL']['OPTIONS']))
			{
				$fields['BANK_DETAIL']['OPTIONS'] = [];
			}
			$fields['BANK_DETAIL']['OPTIONS'] = array_merge_recursive($fields['BANK_DETAIL']['OPTIONS'], ['IS_MY_COMPANY' => 'Y']);
		}

		if($this->isMyCompany() || isset($this->getOptions()['DISABLE_MY_COMPANY']))
		{
			unset($fields['MY_COMPANY']);
		}

		unset($fields['COMPANY']);
		unset($fields['CONTACT']);

		return $fields;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getValue($name)
	{
		$value = parent::getValue($name);

		if($this->isMyCompany())
		{
			if(isset($this->getMyCompanyFields()[$name]) && $value > 0)
			{
				$value = \CFile::GetPath($value);
			}
		}

		return $value;
	}

	/**
	 * @return string
	 */
	public static function getLangName()
	{
		return GetMessage('CRM_DOCGEN_DATAPROVIDER_COMPANY_TITLE');
	}

	/**
	 * @return array
	 */
	protected function getMultiFields()
	{
		if($this->isLoaded())
		{
			if($this->multiFields === null)
			{
				$this->multiFields = [];

				$multiFieldDbResult = \CCrmFieldMulti::GetList(
					['ID' => 'asc'],
					[
						'ENTITY_ID' => \CCrmOwnerType::CompanyName,
						'ELEMENT_ID' => $this->source,
					]
				);
				while($multiField = $multiFieldDbResult->Fetch())
				{
					$this->multiFields[$multiField['TYPE_ID']][] = $multiField;
				}
			}
		}

		return $this->multiFields;
	}

	/**
	 * @return string
	 */
	protected function getWeb()
	{
		return $this->getMultiFields()['WEB'][0]['VALUE'];
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
			return \CCrmCompany::CheckReadPermission($this->source, $userPermissions);
		}

		return false;
	}

	protected function getModuleId()
	{
		return 'crm';
	}

	protected function getTableClass()
	{
		return CompanyTable::class;
	}

	/**
	 * @return array
	 */
	protected function getHiddenFields()
	{
		$fields = array_merge(parent::getHiddenFields(), [
			'COMPANY_TYPE',
			'COMPANY_TYPE_BY',
			'INDUSTRY',
			'INDUSTRY_BY',
			'EMPLOYEES',
			'EMPLOYEES_BY',
			'ASSIGNED_BY_ID',
			'ASSIGNED_BY',
			'CREATED_BY_ID',
			'CREATED_BY',
			'MODIFY_BY_ID',
			'MODIFY_BY',
			'EVENT_RELATION',
			'LEAD_ID',
			'IS_MY_COMPANY',
			'SEARCH_CONTENT',
			'HAS_EMAIL',
			'HAS_PHONE',
			'HAS_IMOL',
			'EMAIL_HOME',
			'EMAIL_WORK',
			'PHONE_MOBILE',
			'PHONE_WORK',
		]);

		if(!$this->isMyCompany())
		{
			$fields = array_merge($fields, array_keys($this->getMyCompanyFields()));
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	protected function getGetListParameters()
	{
		return array_merge_recursive(parent::getGetListParameters(), [
			'select' => [
				'TYPE' => 'COMPANY_TYPE_BY.NAME',
				'INDUSTRY_TYPE' => 'INDUSTRY_BY.NAME',
				'EMPLOYEES_NUM' => 'EMPLOYEES_BY.NAME',
				'EMAIL_HOME',
				'EMAIL_WORK',
				'PHONE_MOBILE',
				'PHONE_WORK',
				'IMOL',
				'EMAIL',
				'PHONE',
			],
		]);
	}

	/**
	 * @return bool
	 */
	protected function isMyCompany()
	{
		return (isset($this->options['MY_COMPANY']) && $this->options['MY_COMPANY'] === 'Y');
	}

	/**
	 * @return array
	 */
	protected function getMyCompanyFields()
	{
		$result = [];
		$fields = \CCrmCompany::getMyCompanyAdditionalUserFields();
		foreach($fields as $name => $field)
		{
			if($name == 'UF_LOGO')
			{
				$type = static::FIELD_TYPE_IMAGE;
			}
			else
			{
				$type = static::FIELD_TYPE_STAMP;
			}
			$result[$name] = [
				'TITLE' => $field['EDIT_FORM_LABEL'][LANGUAGE_ID],
				'TYPE' => $type,
			];
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function getCrmOwnerType()
	{
		return \CCrmOwnerType::Company;
	}

	/**
	 * @return int|null
	 */
	protected function getCompanyId()
	{
		return $this->source;
	}

	/**
	 * @return int|null
	 */
	protected function getContactId()
	{
		return null;
	}

	/**
	 * @return string
	 */
	protected function getUserFieldEntityID()
	{
		return \CCrmCompany::GetUserFieldEntityID();
	}

	protected function getCrmUserTypeManager()
	{
		global $USER_FIELD_MANAGER;
		return new \CCrmUserType($USER_FIELD_MANAGER, $this->getUserFieldEntityID(), ['isMyCompany' => $this->isMyCompany()]);
	}
}