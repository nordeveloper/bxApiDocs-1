<?php

namespace Bitrix\Crm\Integration\DocumentGenerator\DataProvider;

use Bitrix\Crm\EntityBankDetail;
use Bitrix\Crm\EntityPreset;
use Bitrix\DocumentGenerator\DataProvider;

class BankDetail extends DataProvider
{
	/**
	 * Returns list of value names for this Provider.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = [];

		$fieldNames = $this->getFieldNames();
		foreach($fieldNames as $placeholder)
		{
			$fields[$placeholder] = [
				'TITLE' => $this->getFieldsTitles()[$placeholder],
			];
		}

		return $fields;
	}

	/**
	 * Returns value by its name.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getValue($name)
	{
		$this->fetchData();
		return parent::getValue($name);
	}

	/**
	 * Loads data from the database.
	 *
	 * @return array|false
	 */
	protected function fetchData()
	{
		if(!$this->isLoaded())
		{
			if($this->source > 0)
			{
				$this->data = EntityBankDetail::getSingleInstance()->getList(['filter' => ['ID' => $this->source]])->fetch();
			}
		}

		return $this->data;
	}

	/**
	 * @return int
	 */
	protected function getCountryId()
	{
		return EntityPreset::getCurrentCountryId();
	}

	/**
	 * @return array
	 */
	protected function getFieldsTitles()
	{
		static $titles = false;
		if(!$titles)
		{
			$titles = EntityBankDetail::getSingleInstance()->getFieldsTitles($this->getCountryId());
		}

		return $titles;
	}

	/**
	 * @return array
	 */
	protected function getFieldNames()
	{
		$countryFields = EntityBankDetail::getSingleInstance()->getRqFieldByCountry();
		if(isset($countryFields[$this->getCountryId()]))
		{
			$fields = $countryFields[$this->getCountryId()];
		}
		else
		{
			$fields = EntityBankDetail::getSingleInstance()->getRqFields();
		}

		return $fields;
	}
}