<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\EntityBankDetail;
use Bitrix\Crm\EntityRequisite;
use Bitrix\Crm\Format\PersonNameFormatter;
use Bitrix\Crm\Integration\DocumentGenerator\Value\Money;
use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\Crm\Timeline\DocumentController;
use Bitrix\Crm\Timeline\DocumentEntry;
use Bitrix\DocumentGenerator\DataProvider;
use Bitrix\DocumentGenerator\DataProvider\EntityDataProvider;
use Bitrix\DocumentGenerator\DataProvider\User;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Document;
use Bitrix\DocumentGenerator\Integration\Numerator\DocumentNumerable;
use Bitrix\DocumentGenerator\Template;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Hashable;
use Bitrix\Crm\Requisite\EntityLink;
use Bitrix\Main\Type\DateTime;

abstract class CrmEntityDataProvider extends EntityDataProvider implements Hashable, DocumentNumerable
{
	protected $multiFields;
	protected $linkData;
	protected $requisiteIds;
	protected $bankDetailIds;
	protected $crmUserTypeManager;
	protected $userFieldDescriptions = [];

	abstract public function getCrmOwnerType();

	/**
	 * @return mixed
	 */
	abstract protected function getUserFieldEntityID();

	public function onDocumentCreate(Document $document)
	{
		Loc::loadLanguageFile(__FILE__);
		$text = Loc::getMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_COMMENT', ['#TITLE#' => htmlspecialcharsbx($document->getTitle())]);
		$entryID = DocumentEntry::create([
			'TEXT' => $text,
			'AUTHOR_ID' => \CCrmSecurityHelper::GetCurrentUserID(),
			'BINDINGS' => [['ENTITY_TYPE_ID' => $this->getCrmOwnerType(), 'ENTITY_ID' => $this->source]],
		], $document->ID);
		if($entryID > 0)
		{
			$saveData = array(
				'COMMENT' => $text,
				'ENTITY_TYPE_ID' => $this->getCrmOwnerType(),
				'ENTITY_ID' => $this->source,
				'USER_ID' => \CCrmSecurityHelper::GetCurrentUserID(),
				'DOCUMENT_ID' => $document->ID,
			);
			DocumentController::getInstance()->onCreate($entryID, $saveData);
		}
	}

	/**
	 * @param Document $document
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function onDocumentDelete(Document $document)
	{
		$entries = DocumentEntry::getListByDocumentId($document->ID);
		foreach($entries as $entry)
		{
			DocumentController::getInstance()->onDelete($entry['ID'], $entry);
			DocumentEntry::delete($entry['ID']);
		}
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		if($this->fields === null)
		{
			$fields = parent::getFields();

			if(!$this->isLightMode())
			{
				$fields['MY_COMPANY'] = [
					'PROVIDER' => Company::class,
					'VALUE' => [$this, 'getMyCompanyId'],
					'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_MY_COMPANY_TITLE'),
					'OPTIONS' => [
						'MY_COMPANY' => 'Y',
						'VALUES' => [
							'REQUISITE' => $this->getMyCompanyRequisiteId(),
							'BANK_DETAIL' => $this->getMyCompanyBankDetailId(),
						]
					],
				];
			}

			$fields['REQUISITE'] = [
				'PROVIDER' => Requisite::class,
				'VALUE' => [$this, 'getRequisiteId'],
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CUSTOMER_REQUISITE_TITLE'),
			];
			$fields['BANK_DETAIL'] = [
				'PROVIDER' => BankDetail::class,
				'VALUE' => [$this, 'getBankDetailId'],
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_BANK_DETAIL_TITLE'),
			];

			$fields['COMPANY'] = [
				'PROVIDER' => Company::class,
				'VALUE' => [$this, 'getCompanyId'],
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_COMPANY_TITLE'),
				'OPTIONS' => [
					'DISABLE_MY_COMPANY' => true,
					'VALUES' => [
						'REQUISITE' => $this->getRequisiteId(),
						'BANK_DETAIL' => $this->getBankDetailId(),
					],
				]
			];
			$fields['CONTACT'] = [
				'PROVIDER' => Contact::class,
				'VALUE' => [$this, 'getContactId'],
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CONTACT_TITLE'),
				'OPTIONS' => [
					'DISABLE_MY_COMPANY' => true,
				],
			];

			$fields['ASSIGNED'] = [
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_ASSIGNED_TITLE'),
				'VALUE' => [$this, 'getAssignedId'],
				'PROVIDER' => User::class,
				'OPTIONS' => [
					'FORMATTED_NAME_FORMAT' => [
						'format' => $this->getNameFormat(),
					]
				]
			];

			$fields['CLIENT_PHONE'] = [
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CLIENT_PHONE_TITLE'),
				'VALUE' => [$this, 'getClientPhone'],
			];
			$fields['CLIENT_EMAIL'] = [
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CLIENT_EMAIL_TITLE'),
				'VALUE' => [$this, 'getClientEmail'],
			];
			$fields['CLIENT_WEB'] = [
				'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CLIENT_WEB_TITLE'),
				'VALUE' => [$this, 'getClientWeb'],
			];

			$this->fields = $fields;
			$fields = $this->getUserFields();
			$this->fields = array_merge($this->fields, $fields);
			foreach($this->fields as $placeholder => $field)
			{
				if(substr($placeholder, 0, 3) == 'UF_')
				{
					if(substr($placeholder, -7) == '_SINGLE')
					{
						unset($this->fields[$placeholder]);
					}
					else
					{
						$this->userFieldDescriptions[$placeholder] = $this->fields[$placeholder]['DESCRIPTION'];
						unset($this->fields[$placeholder]['DESCRIPTION']);
					}
				}
			}
		}

		return $this->fields;
	}

	/**
	 * @return array
	 */
	public function getUserFields()
	{
		$result = [];

		if($this->isLightMode())
		{
			return $result;
		}

		$manager = $this->getCrmUserTypeManager();
		if(!$manager)
		{
			return $result;
		}

		$crmOwnerTypeProvidersMap = DocumentGeneratorManager::getInstance()->getCrmOwnerTypeProvidersMap();
		$enumerationFields = [];
		$fields = $manager->GetEntityFields($this->getSource());
		foreach($fields as $code => $field)
		{
			if(!isset($this->getAvailableUserFieldTypes()[$field['USER_TYPE_ID']]))
			{
				if(isset($this->fields[$code]))
				{
					unset($this->fields[$code]);
				}
				continue;
			}
			$result[$code] = [
				'TITLE' => $field['EDIT_FORM_LABEL'],
				'VALUE' => [$this, 'getUserFieldValue'],
				'DESCRIPTION' => $field,
			];
			if($field['USER_TYPE_ID'] == 'file')
			{
				$result[$code]['TYPE'] = DataProvider::FIELD_TYPE_IMAGE;
			}
			elseif($field['USER_TYPE_ID'] == 'enumeration')
			{
				$enumerationFields[] = $field;
			}
			elseif($field['USER_TYPE_ID'] == 'employee')
			{
				$result[$code]['PROVIDER'] = User::class;
			}
			elseif($field['USER_TYPE_ID'] == 'date')
			{
				$result[$code]['TYPE'] = static::FIELD_TYPE_DATE;
			}
			elseif($field['USER_TYPE_ID'] == 'datetime')
			{
				$result[$code]['TYPE'] = static::FIELD_TYPE_DATE;
				$result[$code]['FORMAT'] = ['format' => DateTime::getFormat(DataProviderManager::getInstance()->getCulture())];
			}
			elseif($field['USER_TYPE_ID'] == 'crm')
			{
				$provider = null;
				$entityTypes = [];
				if($field['SETTINGS']['LEAD'] == 'Y')
				{
					$entityTypes[] = \CCrmOwnerType::Lead;
				}
				if($field['SETTINGS']['CONTACT'] == 'Y')
				{
					$entityTypes[] = \CCrmOwnerType::Contact;
				}
				if($field['SETTINGS']['COMPANY'] == 'Y')
				{
					$entityTypes[] = \CCrmOwnerType::Company;
				}
				if($field['SETTINGS']['DEAL'] == 'Y')
				{
					$entityTypes[] = \CCrmOwnerType::Deal;
				}
				$isCrmPrefix = (count($entityTypes) > 1);
				if($isCrmPrefix || (!is_numeric($field['VALUE'])) && $field['VALUE'] !== false)
				{
					$parts = explode('_', $field['VALUE']);
					$field['VALUE'] = $parts[1];
					$ownerTypeId = \CCrmOwnerType::ResolveID($parts[0]);
				}
				else
				{
					$ownerTypeId = $entityTypes[0];
				}
				if($ownerTypeId > 0)
				{
					if(isset($crmOwnerTypeProvidersMap[$ownerTypeId]))
					{
						$provider = $crmOwnerTypeProvidersMap[$ownerTypeId];
					}
				}
				if($provider)
				{
					$result[$code]['PROVIDER'] = $provider;
					$result[$code]['OPTIONS']['isLightMode'] = true;
					$result[$code]['DESCRIPTION'] = $field;
				}
			}
			elseif($field['USER_TYPE_ID'] == 'money')
			{
				$result[$code]['TYPE'] = Money::class;
				$parts = explode('|', $field['VALUE']);
				$currency = $parts[1];
				$result[$code]['FORMAT'] = ['CURRENCY_ID' => $currency];
			}
		}

		$enumInfos = \CCrmUserType::PrepareEnumerationInfos($enumerationFields);
		foreach($enumInfos as $placeholder => $data)
		{
			foreach($data as $enum)
			{
				$result[$placeholder]['DESCRIPTION']['DATA'][$enum['ID']] = $enum['VALUE'];
			}
		}

		return $result;
	}

	/**
	 * @param string $placeholder
	 * @return null
	 */
	public function getUserFieldValue($placeholder = null)
	{
		$value = null;
		if(!$placeholder || !isset($this->fields[$placeholder]))
		{
			return $value;
		}
		$field = $this->userFieldDescriptions[$placeholder];

		$value = $field['VALUE'];
		if(!$value)
		{
			return $value;
		}
		if($field['USER_TYPE_ID'] == 'file')
		{
			if(is_array($value))
			{
				$value = null;
			}
			else
			{
				$value = \CFile::GetPath($value);
			}
		}
		elseif($field['USER_TYPE_ID'] == 'enumeration')
		{
			if(!isset($field['DATA']))
			{
				$value = null;
			}
			elseif(is_array($value))
			{
				$items = [];
				foreach($value as $item)
				{
					$items[] = $field['DATA'][$item];
				}
				$value = implode(', ', $items);
			}
			else
			{
				$value = $field['DATA'][$value];
			}
		}
		elseif($field['USER_TYPE_ID'] == 'money')
		{
			$parts = explode('|', $field['VALUE']);
			$value = $parts[0];
			$currency = $parts[1];
			$value = new Money($value, ['CURRENCY_ID' => $currency]);
		}
		elseif(is_array($value))
		{
			$value = implode(', ', $value);
		}

		return $value;
	}

	/**
	 * @return \CCrmUserType
	 */
	protected function getCrmUserTypeManager()
	{
		if($this->crmUserTypeManager === null)
		{
			global $USER_FIELD_MANAGER;
			$this->crmUserTypeManager = new \CCrmUserType($USER_FIELD_MANAGER, $this->getUserFieldEntityID());
		}

		return $this->crmUserTypeManager;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	protected function getLinkData()
	{
		if($this->linkData === null)
		{
			$this->linkData = EntityLink::getByEntity($this->getCrmOwnerType(), $this->getSource());
		}

		return $this->linkData;
	}

	/**
	 * @return array
	 */
	protected function getAvailableUserFieldTypes()
	{
		return [
			'string' => 'string',
			'integer' => 'integer',
			'enumeration' => 'enumeration',
			'file' => 'file',
			'url' => 'url',
			'date' => 'date',
			'datetime' => 'datetime',
			'money' => 'money',
			//'boolean' => 'boolean',
			'double' => 'double',
			'crm' => 'crm',
			'employee' => 'employee',
			'address' => 'address',
		];
	}

	/**
	 * @return int|string
	 */
	public function getSelfCompanyId()
	{
		$myCompany = $this->getValue('MY_COMPANY');
		if($myCompany instanceof DataProvider)
		{
			return $myCompany->getSource();
		}

		return '';
	}

	/**
	 * @return int
	 */
	public function getSelfId()
	{
		return $this->getSource();
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return 'COMPANY_ID_' . $this->getSelfCompanyId();
	}

	/**
	 * @return int|string
	 */
	public function getClientId()
	{
		$id = '';

		$company = $this->getValue('COMPANY');
		if($company instanceof DataProvider)
		{
			$id = $company->getSource();
		}
		if(!$id)
		{
			$contact = $this->getValue('CONTACT');
			if($contact instanceof DataProvider)
			{
				$id = $contact->getSource();
			}
		}

		return $id;
	}

	/**
	 * @return array
	 */
	public function getEmailCommunication()
	{
		$result = [];
		$company = $this->getValue('COMPANY');
		if($company instanceof Company)
		{
			$email = $company->getValue('EMAIL_WORK');
			if(!$email)
			{
				$email = $company->getValue('EMAIL_HOME');
			}
			$result[] = [
				'entityType' => 'COMPANY',
				'entityId' => $company->getSource(),
				'entityTitle' => $company->getValue('TITLE'),
				'type' => 'EMAIL',
				'value' => $email,
			];
		}
		$contact = $this->getValue('CONTACT');
		if($contact instanceof Contact)
		{
			if(!$email)
			{
				$email = $contact->getValue('EMAIL_WORK');
			}
			if(!$email)
			{
				$email = $contact->getValue('EMAIL_HOME');
			}
			$result[] = [
				'entityType' => 'CONTACT',
				'entityId' => $contact->getSource(),
				'entityTitle' => $contact->getValue('FORMATTED_NAME'),
				'type' => 'EMAIL',
				'value' => $email,
			];
		}

		return $result;
	}

	/**
	 * @return int
	 */
	protected function getMyCompanyRequisiteId()
	{
		static $requisiteId = '';
		if($requisiteId > 0)
		{
			return $requisiteId;
		}
		if($this->isLoaded())
		{
			if(!empty($this->getOptions()['VALUES']['MY_COMPANY.REQUISITE']))
			{
				$requisiteId = $this->getOptions()['VALUES']['MY_COMPANY.REQUISITE'];
			}
			else
			{
				$linkData = $this->getLinkData();
				if($linkData['MC_REQUISITE_ID'] > 0)
				{
					$requisiteId = $linkData['MC_REQUISITE_ID'];
				}
				else
				{
					$requisiteLink = EntityLink::getDefaultMyCompanyRequisiteLink();
					if(isset($requisiteLink['MC_REQUISITE_ID']) && $requisiteLink['MC_REQUISITE_ID'] > 0)
					{
						$requisiteId = $requisiteLink['MC_REQUISITE_ID'];
					}
				}
			}
		}

		return $requisiteId;
	}

	/**
	 * @return int
	 */
	protected function getMyCompanyBankDetailId()
	{
		static $bankDetailId = '';
		if($bankDetailId > 0)
		{
			return $bankDetailId;
		}

		if($this->isLoaded())
		{
			if(!empty($this->getOptions()['VALUES']['MY_COMPANY.BANK_DETAIL']))
			{
				$bankDetailId = $this->getOptions()['VALUES']['MY_COMPANY.BANK_DETAIL'];
			}
			else
			{
				$linkData = $this->getLinkData();
				$bankDetailId = $linkData['MC_BANK_DETAIL_ID'];
			}
		}
		return $bankDetailId;
	}

	/**
	 * @return int|array
	 */
	public function getRequisiteId()
	{
		if($this->requisiteIds === null)
		{
			$this->requisiteIds = '';
			if($this->isLoaded())
			{
				$requisiteId = false;
				if(isset($this->data['REQUISITE']) && $this->data['REQUISITE'] instanceof DataProvider)
				{
					$requisite = $this->data['REQUISITE'];
					/** @var DataProvider $requisite */
					$requisiteId = $requisite->getSource();
				}
				elseif(!empty($this->getOptions()['VALUES']['REQUISITE']))
				{
					$requisiteId = $this->getOptions()['VALUES']['REQUISITE'];
				}
				else
				{
					$linkData = $this->getLinkData();
					if($linkData['REQUISITE_ID'] > 0)
					{
						$requisiteId = $linkData['REQUISITE_ID'];
					}
				}

				$entityTypeId = \CCrmOwnerType::Company;
				$entityId = $this->getCompanyId();
				if(!$entityId)
				{
					$entityId = $this->getContactId();
					$entityTypeId = \CCrmOwnerType::Contact;
				}

				if($entityId > 0)
				{
					$requisites = EntityRequisite::getSingleInstance()->getList([
						'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
						'filter' => [
							'=ENTITY_TYPE_ID' => $entityTypeId,
							'=ENTITY_ID' => $entityId,
						],
						'select' => ['ID', 'NAME'],
					])->fetchAll();
					if($requisites)
					{
						if(count($requisites) == 1)
						{
							$this->requisiteIds = (int)$requisites[0]['ID'];
						}
						else
						{
							$this->requisiteIds = [];
							foreach($requisites as $requisite)
							{
								$this->requisiteIds[$requisite['ID']] = [
									'VALUE' => $requisite['ID'],
									'TITLE' => $requisite['NAME'],
									'SELECTED' => false,
								];
								if($requisiteId && $requisiteId == $requisite['ID'])
								{
									$this->requisiteIds[$requisite['ID']]['SELECTED'] = true;
								}
							}
						}
					}
				}
			}
		}

		return $this->requisiteIds;
	}

	/**
	 * @return int
	 */
	public function getBankDetailId()
	{
		if($this->bankDetailIds === null)
		{
			if($this->isLoaded())
			{
				if(isset($this->data['BANK_DETAIL']) && $this->data['BANK_DETAIL'] instanceof DataProvider)
				{
					$bankDetail = $this->data['BANK_DETAIL'];
					/** @var DataProvider $bankDetail */
					$bankDetailId = $bankDetail->getSource();
				}
				elseif(!empty($this->getOptions()['VALUES']['BANK_DETAIL']))
				{
					$bankDetailId = $this->getOptions()['VALUES']['BANK_DETAIL'];
				}
				else
				{
					$linkData = $this->getLinkData();
					$bankDetailId = $linkData['BANK_DETAIL_ID'];
				}

				$requisiteId = DataProviderManager::getInstance()->getValueFromList($this->getRequisiteId(), true);
				if(!is_array($requisiteId) && $requisiteId > 0)
				{
					$bankDetails = EntityBankDetail::getSingleInstance()->getList([
						'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
						'filter' => [
							'=ENTITY_TYPE_ID' => \CCrmOwnerType::Requisite,
							'=ENTITY_ID' => $requisiteId
						],
						'select' => ['ID', 'NAME'],
					])->fetchAll();
					if($bankDetails)
					{
						if(count($bankDetails) == 1)
						{
							$this->bankDetailIds = (int)$bankDetails[0]['ID'];
						}
						else
						{
							$this->bankDetailIds = [];
							foreach($bankDetails as $bankDetail)
							{
								$this->bankDetailIds[$bankDetail['ID']] = [
									'VALUE' => $bankDetail['ID'],
									'TITLE' => $bankDetail['NAME'],
									'SELECTED' => false,
								];
								if($bankDetailId && $bankDetailId == $bankDetail['ID'])
								{
									$this->bankDetailIds[$bankDetail['ID']]['SELECTED'] = true;
								}
							}
						}
					}
				}
			}
		}

		return $this->bankDetailIds;
	}

	/**
	 * @return int|array
	 */
	public function getMyCompanyId()
	{
		$defaultMyCompanyId = $this->getLinkData()['MYCOMPANY_ID'];
		if(!$defaultMyCompanyId)
		{
			$defaultMyCompanyId = EntityLink::getDefaultMyCompanyId();
		}

		$companies = [];
		$res = \CCrmCompany::GetListEx(
			['ID' => 'ASC'],
			['IS_MY_COMPANY' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
			false,
			false,
			['ID', 'TITLE']
		);
		while($company = $res->Fetch())
		{
			$selected = false;
			if($defaultMyCompanyId > 0 && $defaultMyCompanyId == $company['ID'])
			{
				$selected = true;
			}
			$companies[] = [
				'VALUE' => $company['ID'],
				'TITLE' => $company['TITLE'],
				'SELECTED' => $selected,
			];
		}
		if(count($companies) === 0)
		{
			return null;
		}
		elseif(count($companies) === 1)
		{
			return $companies[0]['VALUE'];
		}

		return $companies;
	}

	/**
	 * @return int|null
	 */
	public function getCompanyId()
	{
		if(isset($this->data['COMPANY_ID']) && $this->data['COMPANY_ID'] > 0)
		{
			return $this->data['COMPANY_ID'];
		}

		return null;
	}

	/**
	 * @return int|null
	 */
	public function getContactId()
	{
		if(isset($this->data['CONTACT_ID']) && $this->data['CONTACT_ID'] > 0)
		{
			return $this->data['CONTACT_ID'];
		}

		return null;
	}

	protected function getMultiFields()
	{
		if($this->isLoaded())
		{
			if($this->multiFields === null)
			{
				$this->multiFields = [];

				$entityId = \CCrmOwnerType::CompanyName;
				$elementId = $this->getCompanyId();
				if(!$elementId)
				{
					$elementId = $this->getContactId();
					$entityId = \CCrmOwnerType::ContactName;
				}

				if($elementId > 0)
				{
					$multiFieldDbResult = \CCrmFieldMulti::GetList(
						['ID' => 'asc'],
						[
							'ENTITY_ID' => $entityId,
							'ELEMENT_ID' => $elementId,
						]
					);
					while($multiField = $multiFieldDbResult->Fetch())
					{
						$this->multiFields[$multiField['TYPE_ID']][] = $multiField;
					}
				}
			}
		}
		else
		{
			return [];
		}

		return $this->multiFields;
	}

	/**
	 * @return string|null
	 */
	public function getClientPhone()
	{
		return $this->getMultiFields()['PHONE'][0]['VALUE'];
	}

	/**
	 * @return string|null
	 */
	public function getClientEmail()
	{
		return $this->getMultiFields()['EMAIL'][0]['VALUE'];
	}

	/**
	 * @return string|null
	 */
	public function getClientWeb()
	{
		return $this->getMultiFields()['WEB'][0]['VALUE'];
	}

	/**
	 * @return int|string
	 */
	public function getAssignedId()
	{
		return $this->data['ASSIGNED_BY_ID'];
	}

	/**
	 * @param Document $document
	 * @return array
	 */
	public function getAdditionalDocumentInfo(Document $document)
	{
		$data = parent::getAdditionalDocumentInfo($document);

		$stampPlaceholders = [];
		$data['changeStampsEnabled'] = false;
		$template = $document->getTemplate();
		if($template)
		{
			$stampPlaceholders = $this->getTemplateStampsFields($template);
		}
		if(!empty($stampPlaceholders))
		{
			$documentFields = $document->getFields($stampPlaceholders);
			foreach($stampPlaceholders as $placeholder)
			{
				if(isset($documentFields[$placeholder]['VALUE']) && !empty($documentFields[$placeholder]['VALUE']) && $documentFields[$placeholder]['VALUE'] != false)
				{
					$data['changeStampsEnabled'] = true;
					break;
				}
			}
			if(!$data['changeStampsEnabled'])
			{
				$data['changeStampsDisabledReason'] = GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_STAMPS_DISABLED_EMPTY_FIELDS');
				$data['myCompanyEditUrl'] = $this->getMyCompanyEditUrl();
				if($data['myCompanyEditUrl'])
				{
					$data['changeStampsDisabledReason'] .= '<br />'.GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_EDIT_MY_COMPANY', ['#URL#' => $data['myCompanyEditUrl']]);
				}
			}
		}
		else
		{
			$data['changeStampsDisabledReason'] = GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_STAMPS_DISABLED_NO_TEMPLATE');
		}

		return $data;
	}

	/**
	 * @param Template $template
	 * @return array
	 */
	protected function getTemplateStampsFields(Template $template)
	{
		$placeholders = [];

		$fields = $template->getFields();
		foreach($fields as $placeholder => $field)
		{
			if(isset($field['TYPE']) && $field['TYPE'] === DataProvider::FIELD_TYPE_STAMP)
			{
				$placeholders[] = $placeholder;
			}
		}

		return $placeholders;
	}

	/**
	 * @param bool $singleOnly
	 * @return bool|string
	 */
	public function getMyCompanyEditUrl($singleOnly = true)
	{
		$siteDir = rtrim(SITE_DIR, '/');
		if($singleOnly)
		{
			$myCompanyId = DataProviderManager::getInstance()->getValueFromList($this->getMyCompanyId());
			if($myCompanyId > 0)
			{
				return $siteDir.'/crm/configs/mycompany/edit/'.$myCompanyId.'/';
			}
			else
			{
				return false;
			}
		}
		else
		{
			$myCompanyId = $this->getMyCompanyId();
			if(is_array($myCompanyId))
			{
				return $siteDir.'/crm/configs/mycompany/';
			}
			elseif($myCompanyId > 0)
			{
				return $siteDir.'/crm/configs/mycompany/edit/'.$myCompanyId.'/';
			}
			else
			{
				return $siteDir.'/crm/company/details/0/?mycompany=y';
			}
		}
	}

	/**
	 * @return string
	 */
	public function getPrimaryAddress()
	{
		return $this->getAddressFromRequisite($this->fields['REQUISITE'], 'PRIMARY_ADDRESS');
	}

	/**
	 * @return string
	 */
	public function getRegisteredAddress()
	{
		return $this->getAddressFromRequisite($this->fields['REQUISITE'], 'REGISTERED_ADDRESS');
	}

	/**
	 * @internal
	 * @param array $requisiteFieldDescription
	 * @param string $placeholder
	 * @return string
	 */
	protected function getAddressFromRequisite(array $requisiteFieldDescription, $placeholder)
	{
		$address = '';
		$requisites = $this->getValue('REQUISITE');
		if(!$requisites instanceof Requisite)
		{
			$requisites = DataProviderManager::getInstance()->getValueFromList($requisites);
			$requisites = DataProviderManager::getInstance()->createDataProvider($requisiteFieldDescription, $requisites, $this->getParentProvider(), 'REQUISITE');
		}
		if($requisites instanceof Requisite)
		{
			$data = DataProviderManager::getInstance()->getArray($requisites);
			if(isset($data[$placeholder]) && is_array($data[$placeholder]) && isset($data[$placeholder]['TEXT']) && !empty($data[$placeholder]['TEXT']))
			{
				$address = $data[$placeholder]['TEXT'];
			}
		}

		return $address;
	}

	/**
	 * @return array
	 */
	protected function getHiddenFields()
	{
		return array_merge(parent::getHiddenFields(), [
			'UTS_OBJECT',
		]);
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function getNameFormat()
	{
		$formatId = PersonNameFormatter::getFormatID();
		if($formatId == PersonNameFormatter::Dflt)
		{
			return DataProviderManager::getInstance()->getCulture()->getNameFormat();
		}
		else
		{
			return PersonNameFormatter::getFormatByID($formatId);
		}
	}

	/**
	 * @return string|null
	 */
	public function getAnotherPhone()
	{
		$phones = $this->getMultiFields()['PHONE'];
		if(is_array($phones))
		{
			foreach($phones as $phone)
			{
				if($phone['VALUE_TYPE'] === 'OTHER')
				{
					return $phone['VALUE'];
				}
			}
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	public function getAnotherEmail()
	{
		$emails = $this->getMultiFields()['EMAIL'];
		if(is_array($emails))
		{
			foreach($emails as $email)
			{
				if($email['VALUE_TYPE'] === 'OTHER')
				{
					return $email['VALUE'];
				}
			}
		}

		return null;
	}
}