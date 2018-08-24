<?php
namespace Bitrix\Crm\Filter;

class Filter
{
	/** @var string */
	protected $ID = '';
	/** @var DataProvider|null */
	protected $entityDataProvider = null;
	/** @var DataProvider[]|null */
	protected $extraProviders = null;

	/** @var array|null  */
	protected $params = null;

	/** @var Field[]|null */
	protected $fields = null;

	function __construct($ID, DataProvider $entityDataProvider, array $extraDataProviders = null, array $params = null)
	{
		$this->ID = $ID;
		$this->entityDataProvider = $entityDataProvider;

		$this->extraProviders = array();
		if(is_array($extraDataProviders))
		{
			foreach($extraDataProviders as $dataProvider)
			{
				if($dataProvider instanceof DataProvider)
				{
					$this->extraProviders[] = $dataProvider;
				}
			}
		}

		$this->params = is_array($params) ? $params : array();
	}

	/**
	 * Get Filter ID.
	 * @return string
	 */
	function getID()
	{
		return $this->ID;
	}

	/**
	 * Get Default Field IDs.
	 * @return array
	 */
	public function getDefaultFieldIDs()
	{
		$results = array();
		foreach($this->getFields() as $fieldID => $field)
		{
			if($field->isDefault())
			{
				$results[] = $fieldID;
			}
		}
		return $results;
	}

	/**
	 * Get Field list.
	 * @return Field[]
	 */
	public function getFields()
	{
		if($this->fields === null)
		{
			$this->fields = $this->entityDataProvider->prepareFields();
			foreach($this->extraProviders as $dataProvider)
			{
				$fields = $dataProvider->prepareFields();
				if(!empty($fields))
				{
					$this->fields += $fields;
				}
			}
		}
		return $this->fields;
	}

	/**
	 * Get Field by ID.
	 * @param string $fieldID Field ID.
	 * @return Field|null
	 */
	public function getField($fieldID)
	{
		$fields = $this->getFields();
		return isset($fields[$fieldID]) ? $fields[$fieldID] : null;
	}
}