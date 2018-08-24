<?
IncludeModuleLangFile(__FILE__);

class CMobileEvent
{
	public static function PullOnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "mobile",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}

class MobileApplication extends Bitrix\Main\Authentication\Application
{
	protected $validUrls = array(
		"/mobile/",
		"/bitrix/tools/check_appcache.php",
		"/bitrix/tools/disk/uf.php",
		"/bitrix/services/disk/index.php",
		"/bitrix/groupdav.php",
		"/bitrix/tools/composite_data.php",
		"/bitrix/tools/crm_show_file.php",
		"/bitrix/tools/dav_profile.php",
		"/bitrix/components/bitrix/disk.folder.list/ajax.php",
		"/bitrix/services/mobile/jscomponent.php",
		"/bitrix/services/mobile/webcomponent.php",
		"/bitrix/services/rest/index.php",
		"/bitrix/services/main/ajax.php",
		"/rest/"
	);

	public function __construct()
	{
		$diskEnabled = \Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false) && CModule::includeModule('disk');

		if(!$diskEnabled)
		{
			$this->validUrls = array_merge(
				$this->validUrls,
				array(
					"/company/personal.php",
					"/docs/index.php",
					"/docs/shared/index.php",
					"/workgroups/index.php"
				));
		}

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('extranet'))
		{
			$extranetSiteId = \Bitrix\Main\Config\Option::get('extranet', 'extranet_site', false);
			if ($extranetSiteId)
			{
				$res = \Bitrix\Main\SiteTable::getList(array(
					'filter' => array('=LID' => $extranetSiteId),
					'select' => array('DIR')
				));
				if ($site = $res->fetch())
				{
					$this->validUrls = array_merge(
						$this->validUrls,
						array(
							$site['DIR']."mobile/",
							$site['DIR']."contacts/personal.php"
						));
				}
			}
		}
	}

	public static function OnApplicationsBuildList()
	{
		return array(
			"ID" => "mobile",
			"NAME" => GetMessage("MOBILE_APPLICATION_NAME"),
			"DESCRIPTION" => GetMessage("MOBILE_APPLICATION_DESC"),
			"SORT" => 90,
			"CLASS" => "MobileApplication",
		);
	}
}
