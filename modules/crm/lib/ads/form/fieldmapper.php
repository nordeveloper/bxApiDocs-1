<?php

namespace Bitrix\Crm\Ads\Form;

use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\WebForm\Form;
use Bitrix\Crm\WebForm\Internals\FieldTable;

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
}