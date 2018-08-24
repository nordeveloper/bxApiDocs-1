<?php

namespace Bitrix\Crm\Ads\Form;

use Bitrix\Crm\WebForm\Form;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Ads\Internals\AdsFormLinkTable;
use Bitrix\Seo\LeadAds\Service as LeadAdsService;

Loc::loadMessages(__FILE__);

/**
 * Class WebHookFormFillHandler.
 * @package Bitrix\Crm\Ads\Form
 */
class WebHookFormFillHandler
{
	protected $data = array();

	/**
	 * Handle form fill.
	 *
	 * @param Event $event Web hook event.
	 */
	public static function handleEvent(Event $event)
	{
		$type = $event->getParameter('TYPE');
		$type = explode('.', $type);
		$type = $type[1];

		$adsFormId = $event->getParameter('EXTERNAL_ID');
		$payload = $event->getParameter('PAYLOAD');

		// check payload
		if (!self::checkPayload($payload))
		{
			return;
		}

		foreach ($payload['BATCH'] as $adsResult)
		{
			$adsResultId = self::getAdsResultValue($adsResult, 'leadgen_id');
			$adsFormId = self::getAdsResultValue($adsResult, 'form_id');
			$adsAccountId = self::getAdsResultValue($adsResult, 'page_id');
			if (!$adsResultId || !$adsFormId || !$adsAccountId)
			{
				continue;
			}

			self::processAdsResult($type, $adsResultId, $adsFormId, $adsAccountId);
		}
	}

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function get($key)
	{
		if (!isset($this->data[$key]) || !$this->data[$key])
		{
			return null;
		}

		return $this->data[$key];
	}

	protected static function checkPayload($payload)
	{
		if (!is_array($payload) || !isset($payload['BATCH']))
		{
			return false;
		}

		if (!is_array($payload['BATCH']) || count($payload['BATCH']) == 0)
		{
			return false;
		}

		return true;
	}

	protected static function getLinkedCrmForms($type, $adsFormId)
	{
		$linkDb = AdsFormLinkTable::getList(array(
			'select' => array('WEBFORM_ID'),
			'filter' => array(
				'=ADS_FORM_ID' => $adsFormId,
				'=ADS_TYPE' => $type
			),
			'limit' => 5,
			'order' => array('DATE_INSERT' => 'DESC'),
		));

		$crmForms = array();
		while ($link = $linkDb->fetch())
		{
			$crmForms[] = $link['WEBFORM_ID'];
		}

		return $crmForms;
	}

	protected static function getAdsResultValue($adsResult, $key)
	{
		if (!isset($adsResult[$key]) || !$adsResult[$key])
		{
			return null;
		}

		return $adsResult[$key];
	}

	protected static function processAdsResult($type, $adsResultId, $adsFormId, $adsAccountId)
	{
		// retrieve linked crm-forms
		$crmForms = self::getLinkedCrmForms($type, $adsFormId);
		if (count($crmForms) <= 0)
		{
			return;
		}

		$adsForm = LeadAdsService::getForm($type);
		$adsResult = $adsForm->getResult($adsResultId);
		if (!$adsResult->isSuccess())
		{
			return;
		}

		$incomeFields = array();
		while ($item = $adsResult->fetch())
		{
			$incomeFields[$item['NAME']] = $item['VALUES'];
		}

		$addResultParameters = array(
			'ORIGIN_ID' => $type . '/' . $adsResultId
		);
		foreach ($crmForms as $crmFormId)
		{
			// add result
			static::addResult($crmFormId, $incomeFields, $addResultParameters);
		}
	}

	protected static function addResult($formId, array $incomeFields, array $addResultParameters)
	{
		// check existing form
		$form = new Form();
		if (!$form->load($formId))
		{
			return false;
		}

		// check existing result
		if ($form->hasResult($addResultParameters['ORIGIN_ID']))
		{
			return false;
		}

		// prepare fields
		$fields = $form->getFieldsMap();
		foreach ($fields as $fieldKey => $field)
		{
			$values = array();
			if (isset($incomeFields[$field['name']]))
			{
				$values = $incomeFields[$field['name']];
				if(!is_array($values))
				{
					$values = array($values);
				}
			}

			$field['values'] = $values;
			$fields[$fieldKey] = $field;
		}

		// add result
		$result = $form->addResult($fields, $addResultParameters);
		$id = $result->getId();
		return ($id && $id > 0);
	}
}