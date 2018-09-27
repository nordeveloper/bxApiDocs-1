<?php

namespace Bitrix\Crm\Ads\Form;

use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\WebForm\Form;
use Bitrix\Crm\WebForm\Internals\FieldTable;
use Bitrix\Seo\LeadAds;

Loc::loadMessages(__FILE__);

/**
 * Class FieldMapper.
 * @package Bitrix\Crm\Ads\Form
 */
class FieldMapper
{
	/**
	 * To Ads form.
	 *
	 * @param Form $form CRM-form.
	 * @return array|null
	 */
	public static function toAdsForm(Form $form)
	{
		$fields = $form->getFieldsMap();

		$result = [];
		foreach ($fields as $field)
		{
			$item = self::getMapTypeItem($field['type']);
			$type = $item ? $item['SEO_TYPE'] : LeadAds\Field::TYPE_INPUT;
			$name = !empty($item['CRM_NAME']) ? $item['CRM_NAME'] : $field['entity_field_name'];

			$adsField = new LeadAds\Field($type, $name, $field['caption'], $field['name']);
			if (isset($field['items']) && is_array($field['items']))
			{
				foreach ($field['items'] as $fieldItem)
				{
					$adsField->addOption($fieldItem['value'], $fieldItem['title']);
				}
			}

			$result[] = $adsField;
		}

		return $result;
	}

	protected static function getMapTypeItem($crmType = null, $seoType = null)
	{
		if (empty($crmType) && empty($seoType))
		{
			return null;
		}

		$map = [
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_PHONE,
				'SEO_TYPE' => LeadAds\Field::TYPE_INPUT,
				'CRM_NAME' => 'PHONE',
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_EMAIL,
				'SEO_TYPE' => LeadAds\Field::TYPE_INPUT,
				'CRM_NAME' => 'EMAIL',
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_STRING,
				'SEO_TYPE' => LeadAds\Field::TYPE_INPUT,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_TYPED_STRING,
				'SEO_TYPE' => LeadAds\Field::TYPE_INPUT,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_LIST,
				'SEO_TYPE' => LeadAds\Field::TYPE_SELECT,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_LIST,
				'SEO_TYPE' => LeadAds\Field::TYPE_SELECT,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_CHECKBOX,
				'SEO_TYPE' => LeadAds\Field::TYPE_CHECKBOX,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_RADIO,
				'SEO_TYPE' => LeadAds\Field::TYPE_RADIO,
				'CRM_NAME' => null,
			],
			[
				'CRM_TYPE' => FieldTable::TYPE_ENUM_TEXT,
				'SEO_TYPE' => LeadAds\Field::TYPE_TEXT_AREA,
				'CRM_NAME' => null,
			],
		];

		foreach ($map as $item)
		{
			if ($crmType && $item['CRM_TYPE'] === $crmType)
			{
				return $item;
			}

			if ($seoType && $item['SEO_TYPE'] === $seoType)
			{
				return $item;
			}
		}

		return null;
	}

	/*
	public static function toAdsForm(Form $form)
	{
		$fields = $form->getFieldsMap();

		$result = array();
		foreach ($fields as $field)
		{
			$mapItem = self::getMapItemByField($field);
			if ($mapItem)
			{
				$adsField = array(
					'type' => $mapItem['ADS_NAME'],
					'key' => $field['name'],
				);
			}
			else
			{
				$adsField = array(
					'type' => 'CUSTOM',
					'label' => $field['caption'],
					'key' => $field['name']
				);

				$listItems = array();
				if (isset($field['items']) && is_array($field['items']))
				{
					foreach ($field['items'] as $fieldItem)
					{
						$listItems[] = array(
							'value' => $fieldItem['title'],
							'key' => $fieldItem['value'],
						);

					}
				}

				if(!empty($listItems))
				{
					$adsField['options'] = $listItems;
				}
			}

			$result[] = $adsField;
		}

		return $result;
	}
	*/

	/*
	protected static function getMapItemByField($field)
	{
		foreach (self::getMap() as $item)
		{
			if ($item['NAME'] == $field['entity_field_name'])
			{
				return $item;
			}

			if ($item['TYPE'] && $item['TYPE'] == $field['type'])
			{
				return $item;
			}
		}

		return null;
	}
	*/

	/*
	protected static function getMap()
	{
		$map = array(
			array(
				'ADS_NAME' => 'COMPANY_NAME',
				'TYPE' => '',
				'NAME' => 'COMPANY_NAME',
			),
			array(
				'ADS_NAME' => 'EMAIL',
				'TYPE' => FieldTable::TYPE_ENUM_EMAIL,
				'NAME' => 'EMAIL',
			),
			array(
				'ADS_NAME' => 'PHONE',
				'TYPE' => FieldTable::TYPE_ENUM_PHONE,
				'NAME' => 'PHONE',
			),
			array(
				'ADS_NAME' => 'LAST_NAME',
				'TYPE' => '',
				'NAME' => 'LAST_NAME',
			),
			array(
				'ADS_NAME' => 'FIRST_NAME',
				'TYPE' => '',
				'NAME' => 'NAME',
			),
		);

		return $map;
	}
	*/
}