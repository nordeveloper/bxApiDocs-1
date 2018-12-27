<?php


namespace Bitrix\Crm\Order\Rest\Entity;


class OrderRequisiteLink extends \Bitrix\Sale\Rest\Entity\Base
{

	static protected function getMap()
	{
		return [
			'REQUISITE_ID' => [
				'data_type' => 'integer',
			],
			'BANK_DETAIL_ID' => [
				'data_type' => 'integer',
			],
			'MC_REQUISITE_ID' => [
				'data_type' => 'integer',
			],
			'MC_BANK_DETAIL_ID' => [
				'data_type' => 'integer',
			]
		];
	}

	public function getFieldsRequired()
	{
		return [];
	}

	public function getAvailableFields()
	{
		return [
			'REQUISITE_ID',
			'BANK_DETAIL_ID',
			'MC_REQUISITE_ID',
			'MC_BANK_DETAIL_ID'
		];
	}

	public function getSettableFields()
	{
		return $this->getAvailableFields();
	}

	public function getDateTimeFields()
	{
		return [];
	}
}