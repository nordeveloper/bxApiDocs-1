<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!\Bitrix\Main\Loader::includeModule("crm"))
{
	return;
}

IncludeModuleLangFile(__FILE__);

if(!function_exists('__CrmShowEndJsonResonse'))
{
	function __CrmShowEndJsonResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}
		CMain::FinalActions();
		die();
	}
}

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"]) > 0 && check_bitrix_sessid())
{
	$CrmPerms = CCrmPerms::GetCurrentUserPermissions();
	if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
	{
		__CrmShowEndJsonResonse(array('error' => GetMessage("CRM_TYPE_RIGHTS_ERROR")));
	}

	\Bitrix\Main\Config\Option::set('crm', 'crm_lead_enabled_show', "N");

	if ($_POST["action"] == "popupClose")
	{
		__CrmShowEndJsonResonse(array('success' => "Y"));
	}

	if (strlen($_POST["crmType"]) > 0 && \Bitrix\Main\Loader::includeModule("crm"))
	{
		if ($_POST["crmType"] == "simple")
		{
			$res = \Bitrix\Crm\Settings\LeadSettings::enableLead(false);
			if ($res)
			{
				__CrmShowEndJsonResonse(array('success' => "Y"));
			}
			else
			{
				__CrmShowEndJsonResonse(array('error' => GetMessage("CRM_TYPE_CONVERT_ERROR")));
			}
		}
		elseif ($_POST["crmType"] == "classic")
		{
			$res = \Bitrix\Crm\Settings\LeadSettings::enableLead(true);
			if ($res)
			{
				__CrmShowEndJsonResonse(array('success' => "Y"));
			}
			else
			{
				__CrmShowEndJsonResonse(array('error' => GetMessage("CRM_TYPE_CONVERT_ERROR")));
			}
		}
	}
}

$APPLICATION->IncludeComponent(
	"bitrix:crm.lead.mode",
	"",
	array()
);

?>