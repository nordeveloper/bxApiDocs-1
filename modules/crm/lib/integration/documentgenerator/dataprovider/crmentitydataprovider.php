<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\EntityBankDetail;
use Bitrix\Crm\EntityRequisite;
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

abstract class CrmEntityDataProvider extends EntityDataProvider implements Hashable, DocumentNumerable
{
	protected $multiFields;
	protected $linkData;
	protected $requisiteIds;
	protected $bankDetailIds;

	abstract public function getCrmOwnerType();

	/**
	 * @return mixed
	 */
	abstract protected function getUserFieldEntityID();

	public function onDocumentCreate(Document $document)
	{
		Loc::loadLanguageFile(__FILE__);
		$text = Loc::getMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_COMMENT', ['#TITLE#' => $document->getTitle()]);
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
		$fields = parent::getFields();

		$fields['MY_COMPANY'] = [
			'PROVIDER' => Company::class,
			'VALUE' => function()
			{
				return $this->getMyCompanyId();
			},
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_MY_COMPANY_TITLE'),
			'OPTIONS' => [
				'MY_COMPANY' => 'Y',
				'VALUES' => [
					'REQUISITE' => $this->getMyCompanyRequisiteId(),
					'BANK_DETAIL' => $this->getMyCompanyBankDetailId(),
				]
			],
		];

		$fields['REQUISITE'] = [
			'PROVIDER' => Requisite::class,
			'VALUE' => function()
			{
				return $this->getRequisiteId();
			},
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CUSTOMER_REQUISITE_TITLE'),
		];
		$fields['BANK_DETAIL'] = [
			'PROVIDER' => BankDetail::class,
			'VALUE' => function()
			{
				return $this->getBankDetailId();
			},
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_BANK_DETAIL_TITLE'),
		];

		$fields['COMPANY'] = [
			'PROVIDER' => Company::class,
			'VALUE' => function()
			{
				return $this->getCompanyId();
			},
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
			'VALUE' => function()
			{
				return $this->getContactId();
			},
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CONTACT_TITLE'),
			'OPTIONS' => [
				'DISABLE_MY_COMPANY' => true,
			],
		];

		$fields['ASSIGNED'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_ASSIGNED_TITLE'),
			'VALUE' => function()
			{
				return $this->data['ASSIGNED_BY_ID'];
			},
			'PROVIDER' => User::class,
		];

		$fields['CLIENT_PHONE'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CLIENT_PHONE_TITLE'),
			'VALUE' => function()
			{
				return $this->getClientPhone();
			}
		];
		$fields['CLIENT_EMAIL'] = [
			'TITLE' => GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_CLIENT_EMAIL_TITLE'),
			'VALUE' => function()
			{
				return $this->getClientEmail();
			}
		];

		$fields = array_merge($fields, $this->getUserFields());

		return $fields;
	}

	/**
	 * @return array
	 */
	public function getUserFields()
	{
		$result = [];

		$manager = $this->getCrmUserTypeManager();
		if(!$manager)
		{
			return $result;
		}

		$fields = $manager->GetEntityFields($this->getSource());
		foreach($fields as $code => $field)
		{
			$result[$code] = [
				'TITLE' => $field['EDIT_FORM_LABEL'],
				'VALUE' => $field['VALUE'],
			];
		}

		return $result;
	}

	/**
	 * @return \CCrmUserType
	 */
	protected function getCrmUserTypeManager()
	{
		global $USER_FIELD_MANAGER;
		return new \CCrmUserType($USER_FIELD_MANAGER, $this->getUserFieldEntityID());
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
	protected function getRequisiteId()
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
	protected function getBankDetailId()
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
	protected function getMyCompanyId()
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
	protected function getCompanyId()
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
	protected function getContactId()
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

		return $this->multiFields;
	}

	/**
	 * @return string|null
	 */
	protected function getClientPhone()
	{
		return $this->getMultiFields()['PHONE'][0]['VALUE'];
	}

	/**
	 * @return string|null
	 */
	protected function getClientEmail()
	{
		return $this->getMultiFields()['EMAIL'][0]['VALUE'];
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
			$data['changeStampsDisabledReason'] = GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_STAMPS_DISABLED_EMPTY_FIELDS');
			$data['myCompanyEditUrl'] = $this->getMyCompanyEditUrl();
			if($data['myCompanyEditUrl'])
			{
				$data['changeStampsDisabledReason'] .= '<br />'.GetMessage('CRM_DOCGEN_CRMENTITYDATAPROVIDER_EDIT_MY_COMPANY', ['#URL#' => $data['myCompanyEditUrl']]);
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
	 * @return bool|string
	 */
	protected function getMyCompanyEditUrl()
	{
		$myCompanyId = DataProviderManager::getInstance()->getValueFromList($this->getMyCompanyId());
		if($myCompanyId > 0)
		{
			return '/crm/configs/mycompany/edit/'.$myCompanyId.'/';
		}

		return false;
	}
}