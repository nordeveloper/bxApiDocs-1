<?php
namespace Bitrix\Crm\Entity;

//use Bitrix\Main;

class EntityValidator
{
	protected $entityID = 0;
	protected $entityFields = null;

	public function __construct($entityID, array $entityFields)
	{
		$this->entityID = $entityID;
		$this->entityFields = $entityFields;
	}

	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Undefined;
	}

	public function getEntityID()
	{
		return $this->entityID;
	}

	public function getFieldInfos()
	{
		return array();
	}

	public function getFieldInfo($fieldName)
	{
		$fieldInfos = $this->getFieldInfos();
		return $fieldInfos[$fieldName] ? $fieldInfos[$fieldName] : null;
	}

	protected function checkAllFieldPresence(array $fieldNames)
	{
		foreach($fieldNames as $fieldName)
		{
			if(!$this->innerCheckFieldPresence($fieldName))
			{
				return false;
			}
		}
		return true;
	}

	protected function checkAnyFieldPresence(array $fieldNames)
	{
		foreach($fieldNames as $fieldName)
		{
			if($this->innerCheckFieldPresence($fieldName))
			{
				return true;
			}
		}
		return false;
	}

	public function checkFieldPresence($fieldName)
	{
		return $this->innerCheckFieldPresence($fieldName);
	}

	protected function innerCheckFieldPresence($fieldName)
	{
		if($this->entityID > 0 && !array_key_exists($fieldName, $this->entityFields))
		{
			return true;
		}

		$fieldInfo = $this->getFieldInfo($fieldName);
		$typeName = is_array($fieldInfo) && isset($fieldInfo['TYPE']) ? $fieldInfo['TYPE'] : '';
		if($typeName === 'boolean')
		{
			return true;
		}

		$value = isset($this->entityFields[$fieldName]) ? $this->entityFields[$fieldName] : null;
		if(is_array($value))
		{
			return !empty($value);
		}
		return strlen($this->entityFields[$fieldName]) > 0;
	}

	protected function checkMultifieldPresence($fieldName)
	{
		if($this->entityID > 0 && !array_key_exists('FM', $this->entityFields))
		{
			return true;
		}

		if(isset($this->entityFields['FM'])
			&& is_array($this->entityFields['FM'])
			&& isset($this->entityFields['FM'][$fieldName])
			&& is_array($this->entityFields['FM'][$fieldName])
		)
		{
			foreach($this->entityFields['FM'][$fieldName] as $value)
			{
				if(isset($value['VALUE']) && $value['VALUE'] !== '')
				{
					return true;
				}
			}
		}
		return false;
	}
}