<?php


namespace Bitrix\Crm\Order\Rest\Entity;


class OrderContactCompany extends \Bitrix\Sale\Rest\Entity\Base
{

	static protected function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
			],
			'ORDER_ID' => [
				'data_type' => 'integer',
			],
			'ENTITY_ID' => [
				'data_type' => 'string',
			],
			'ENTITY_TYPE_ID' => [
				'data_type' => 'integer',
			],
			'SORT' => [
				'data_type' => 'integer',
			],
			'ROLE_ID' => [
				'data_type' => 'integer',
			],
			'IS_PRIMARY' => [
				'data_type' => 'integer',
			]
		];
	}

	public function getFieldsRequired()
	{
		return [
				'ENTITY_ID',
				'ENTITY_TYPE_ID'
			];
	}

	public function getAvailableFields()
	{
		return [
			'ID',
			'ORDER_ID',
			'ENTITY_ID',
			'ENTITY_TYPE_ID',
			'SORT',
			'ROLE_ID',
			'IS_PRIMARY'
		];
	}

	public function getDateTimeFields()
	{
		return [];
	}
}