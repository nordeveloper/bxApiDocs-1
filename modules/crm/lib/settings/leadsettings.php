<?php
namespace Bitrix\Crm\Settings;
use Bitrix\Main;
use Bitrix\Crm\Activity;

class LeadSettings
{
	const VIEW_LIST = EntityViewSettings::LIST_VIEW;
	const VIEW_WIDGET = EntityViewSettings::WIDGET_VIEW;
	const VIEW_KANBAN = EntityViewSettings::KANBAN_VIEW;
	const VIEW_CALENDAR = EntityViewSettings::CALENDAR_VIEW;

	/** @var LeadSettings  */
	private static $current = null;
	/** @var bool */
	private static $messagesLoaded = false;
	/** @var array */
	private static $descriptions = null;
	/** @var BooleanSetting  */
	private $isOpened = null;
	/** @var IntegerSetting */
	private $defaultListView = null;
	/** @var EntityViewSettings */
	private $entityListView = null;
	/** @var BooleanSetting */
	private $enableProductRowExport = null;
	/** @var BooleanSetting */
	private $enableAutoGenRc = null;
	/** @var BooleanSetting */
	private $enableAutoUsingFinishedLead = null;
	/** @var ArraySetting */
	private $activityCompletionConfig = null;

	function __construct()
	{
		$this->defaultListView = new IntegerSetting('lead_default_list_view', self::VIEW_KANBAN);
		$this->isOpened = new BooleanSetting('lead_opened_flag', true);
		$this->enableProductRowExport = new BooleanSetting('enable_lead_prod_row_export', true);
		$this->enableAutoGenRc = new BooleanSetting('enable_auto_gen_rc', true);
		$this->enableAutoUsingFinishedLead = new BooleanSetting('enable_auto_using_finished_lead', false);

		$completionConfig = array();
		foreach(Activity\Provider\ProviderManager::getCompletableProviderList() as $providerInfo)
		{
			$completionConfig[$providerInfo['ID']] = true;
		}
		$this->activityCompletionConfig = new ArraySetting('lead_act_completion_cfg', $completionConfig);
	}
	/**
	 * Get current instance
	 * @return LeadSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new LeadSettings();
		}
		return self::$current;
	}
	/**
	 * Get value of flag 'OPENED'
	 * @return bool
	 */
	public function getOpenedFlag()
	{
		return $this->isOpened->get();
	}
	/**
	 * Set value of flag 'OPENED'
	 * @param bool $opened Opened Flag.
	 * @return void
	 */
	public function setOpenedFlag($opened)
	{
		$this->isOpened->set($opened);
	}
	/**
	 * Check if export of the product rows is enabled
	 * @return bool
	 */
	public function isProductRowExportEnabled()
	{
		return $this->enableProductRowExport->get();
	}
	/**
	 * Enable export of the product rows
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableProductRowExport($enabled)
	{
		$this->enableProductRowExport->set($enabled);
	}
	/**
	 * Return true if auto generate of RC(return customer) is enabled.
	 * @return bool
	 */
	public function isAutoGenRcEnabled()
	{
		return $this->enableAutoGenRc->get();
	}
	/**
	 * Enable auto generate of RC(return customer).
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableAutoGenRc($enabled)
	{
		$this->enableAutoGenRc->set($enabled);
	}
	/**
	 * Return true if using finished lead for auto generating is enabled.
	 * @return bool
	 */
	public function isAutoUsingFinishedLeadEnabled()
	{
		return $this->enableAutoUsingFinishedLead->get();
	}
	/**
	 * Enable using finished lead for auto generating.
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableAutoUsingFinishedLead($enabled)
	{
		$this->enableAutoUsingFinishedLead->set($enabled);
	}
	public function getActivityCompletionConfig()
	{
		return $this->activityCompletionConfig->get();
	}
	public function setActivityCompletionConfig(array $config)
	{
		$this->activityCompletionConfig->set($config);
	}
	public function resetActivityCompletionConfig()
	{
		$this->activityCompletionConfig->remove();
	}
	/**
	 * Get current list view ID
	 * @return int
	 */
	public function getCurrentListViewID()
	{
		if($this->entityListView === null)
		{
			$this->entityListView = new EntityViewSettings();
		}

		$viewID = $this->entityListView->getViewID(\CCrmOwnerType::Lead);
		if($viewID === EntityViewSettings::UNDEFINED)
		{
			$viewID = $this->getDefaultListViewID();
		}
		return $viewID;
	}
	/**
	 * Get default list view ID
	 * @return int
	 */
	public function getDefaultListViewID()
	{
		return $this->defaultListView->get();
	}
	/**
	 * Set default list view ID
	 * @param int $viewID View ID.
	 * @return void
	 */
	public function setDefaultListViewID($viewID)
	{
		$this->defaultListView->set($viewID);
	}
	/**
	 * Get descriptions of views supported in current context
	 * @return array
	 */
	public static function getViewDescriptions()
	{
		if(!self::$descriptions)
		{
			self::includeModuleFile();

			self::$descriptions= array(
				self::VIEW_LIST => GetMessage('CRM_LEAD_SETTINGS_VIEW_LIST'),
				self::VIEW_WIDGET => GetMessage('CRM_LEAD_SETTINGS_VIEW_WIDGET'),
				self::VIEW_KANBAN => GetMessage('CRM_LEAD_SETTINGS_VIEW_KANBAN')
			);
		}
		return self::$descriptions;
	}
	/**
	 * Prepare list items for view selector
	 * @return array
	 */
	public static function prepareViewListItems()
	{
		return \CCrmEnumeration::PrepareListItems(self::getViewDescriptions());
	}
	/**
	 * Enable leads
	 * @param bool $enabled Enabled Flag.
	 * @return bool
	 */
	public static function enableLead($enabled)
	{
		$enabled = (bool)$enabled;
		if ($enabled)
		{
			$result = \Bitrix\Crm\Automation\Demo\Wizard::unInstallSimpleCRM();
		}
		else
		{
			$result = \Bitrix\Crm\Automation\Demo\Wizard::installSimpleCRM();
		}
		if ($result)
		{
			if ($enabled)
			{
				ConversionSettings::getCurrent()->enableAutocreation(true);
			}

			\Bitrix\Main\Config\Option::set('crm', 'crm_lead_enabled', $enabled ? "Y" : "N");
		}

		return $result;
	}
	/**
	 * Check if leads are enabled
	 * @return bool
	 */
	public static function isEnabled()
	{
		$isEnabled = \Bitrix\Main\Config\Option::get('crm', 'crm_lead_enabled', "Y");
		return $isEnabled == "Y";
	}

	public static function showCrmTypePopup()
	{
		\CJSCore::Init(array('popup'));

		$isCrmAdmin = "N";
		$CrmPerms = \CCrmPerms::GetCurrentUserPermissions();
		if ($CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
		{
			$isCrmAdmin = "Y";
		}

		$needRobotsNotify = \Bitrix\Crm\Automation\Factory::hasRobotsForStatus(\CCrmOwnerType::Lead, 'NEW');

		$arParams = array(
			"ajaxPath" => "/bitrix/tools/crm_lead_mode.php",
			"dealPath" => SITE_DIR."crm/deal/list/",
			"leadPath" => SITE_DIR."crm/lead/list/",
			"isAdmin" => $isCrmAdmin,
			"isLeadEnabled" => self::isEnabled() ? "Y" : "N",
			"needRobotsNotify" => $needRobotsNotify ? "Y" : "N",
			"messages" => array(
				"CRM_TYPE_TITLE" => GetMessage("CRM_TYPE_TITLE"),
				"CRM_TYPE_SAVE" => GetMessage("CRM_TYPE_SAVE"),
				"CRM_TYPE_CANCEL" => GetMessage("CRM_TYPE_CANCEL"),
				"CRM_TYPE_TURN_ON" => GetMessage("CRM_TYPE_TURN_ON"),
				"CRM_ROBOTS_TITLE" => GetMessage("CRM_ROBOTS_TITLE"),
				"CRM_ROBOTS_TEXT" => GetMessage("CRM_ROBOTS_TEXT")
			)
		);

		return "BX.CrmLeadMode.init(".\CUtil::PhpToJSObject($arParams)."); BX.CrmLeadMode.showPopup();";
	}
	/**
	 * Include language file
	 * @return void
	 */
	protected static function includeModuleFile()
	{
		if(self::$messagesLoaded)
		{
			return;
		}

		Main\Localization\Loc::loadMessages(__FILE__);
		self::$messagesLoaded = true;
	}
}