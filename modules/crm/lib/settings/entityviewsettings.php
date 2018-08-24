<?php
namespace Bitrix\Crm\Settings;

class EntityViewSettings
{
	const UNDEFINED          = BX_CRM_VIEW_UNDEFINED;

	const LIST_VIEW          = BX_CRM_VIEW_LIST;
	const WIDGET_VIEW        = BX_CRM_VIEW_WIDGET;
	const KANBAN_VIEW        = BX_CRM_VIEW_KANBAN;
	const CALENDAR_VIEW      = BX_CRM_VIEW_CALENDAR;

	const LIST_VIEW_NAME     = 'LIST';
	const WIDGET_VIEW_NAME   = 'WIDGET';
	const KANBAN_VIEW_NAME   = 'KANBAN';
	const CALENDAR_VIEW_NAME = 'CALENDAR';

	/**
	 * Resolve view ID by name.
	 * @param string $name View Name.
	 * @return int
	 */
	public static function resolveID($name)
	{
		if($name === self::LIST_VIEW_NAME)
		{
			return self::LIST_VIEW;
		}
		elseif($name === self::WIDGET_VIEW_NAME)
		{
			return self::WIDGET_VIEW;
		}
		elseif($name === self::KANBAN_VIEW_NAME)
		{
			return self::KANBAN_VIEW;
		}
		elseif($name === self::CALENDAR_VIEW_NAME)
		{
			return self::CALENDAR_VIEW;
		}

		return self::UNDEFINED;
	}

	/**
	 * Resolve view name by ID.
	 * @param int $ID View ID.
	 * @return string
	 */
	public static function resolveName($ID)
	{
		if($ID === self::LIST_VIEW)
		{
			return self::LIST_VIEW_NAME;
		}
		elseif($ID === self::WIDGET_VIEW)
		{
			return self::WIDGET_VIEW_NAME;
		}
		elseif($ID === self::KANBAN_VIEW)
		{
			return self::KANBAN_VIEW_NAME;
		}
		elseif($ID === self::CALENDAR_VIEW)
		{
			return self::CALENDAR_VIEW_NAME;
		}

		return '';
	}
	/**
	 * Get raw configuration
	 * @return array|null
	 */
	protected function getConfig()
	{
		$result = \CUserOptions::GetOption('crm.navigation', 'index', null);
		return is_array($result) ? $result : array();
	}

	/**
	 * Get current view ID by entity type ID.
	 * @param int $entityTypeID Entity Type ID.
	 * @return int
	 */
	public function getViewID($entityTypeID)
	{
		$entityTypeName = strtolower(\CCrmOwnerType::ResolveName($entityTypeID));
		if($entityTypeName === '')
		{
			return self::UNDEFINED;
		}

		$index = $this->getConfig();
		if(!isset($index[$entityTypeName]))
		{
			return self::UNDEFINED;
		}

		$value = $index[$entityTypeName];
		$parts = explode(':', $value);
		return self::resolveID(
			strtoupper(is_array($parts) && count($parts) >= 2 ? $parts[0] : $value)
		);
	}
}