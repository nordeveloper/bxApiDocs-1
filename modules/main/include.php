<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/include.php"))."/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_file.php");


$application = \Bitrix\Main\Application::getInstance();
$application->initializeExtendedKernel(array(
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
));

//define global application object
$GLOBALS["APPLICATION"] = new CMain;

if(defined("SITE_ID"))
	define("LANG", SITE_ID);

if(defined("LANG"))
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
		$db_lang = CLangAdmin::GetByID(LANG);
	else
		$db_lang = CLang::GetByID(LANG);

	$arLang = $db_lang->Fetch();

	if(!$arLang)
	{
		throw new \Bitrix\Main\SystemException("Incorrect site: ".LANG.".");
	}
}
else
{
	$arLang = $GLOBALS["APPLICATION"]->GetLang();
	define("LANG", $arLang["LID"]);
}

$lang = $arLang["LID"];
if (!defined("SITE_ID"))
	define("SITE_ID", $arLang["LID"]);
define("SITE_DIR", $arLang["DIR"]);
define("SITE_SERVER_NAME", $arLang["SERVER_NAME"]);
define("SITE_CHARSET", $arLang["CHARSET"]);
define("FORMAT_DATE", $arLang["FORMAT_DATE"]);
define("FORMAT_DATETIME", $arLang["FORMAT_DATETIME"]);
define("LANG_DIR", $arLang["DIR"]);
define("LANG_CHARSET", $arLang["CHARSET"]);
define("LANG_ADMIN_LID", $arLang["LANGUAGE_ID"]);
define("LANGUAGE_ID", $arLang["LANGUAGE_ID"]);

$context = $application->getContext();
$context->setLanguage(LANGUAGE_ID);
$context->setCulture(new \Bitrix\Main\Context\Culture($arLang));

$request = $context->getRequest();
if (!$request->isAdminSection())
{
	$context->setSite(SITE_ID);
}

$application->start();

$GLOBALS["APPLICATION"]->reinitPath();

if (!defined("POST_FORM_ACTION_URI"))
{
	define("POST_FORM_ACTION_URI", htmlspecialcharsbx(GetRequestUri()));
}

$GLOBALS["MESS"] = array();
$GLOBALS["ALL_LANG_FILES"] = array();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/database.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");
IncludeModuleLangFile(__FILE__);

error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) & ~E_STRICT & ~E_DEPRECATED);

if(!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N")
{
	define("BX_COMP_MANAGED_CACHE", true);
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/ajax_tools.php");

/*ZDUyZmZMTkwYTQ0ZTljYzhhMWI3YThhMGIwZGQ4NDFlMWQxNjA=*/$GLOBALS['_____1742921085']= array(base64_decode('R2V0TW9'.'kdWxlRXZlbnR'.'z'),base64_decode('RXhlY3V0ZU1vZ'.'H'.'VsZ'.'UV2'.'Z'.'W50'.'RXg='));$GLOBALS['____1924754177']= array(base64_decode('ZGVma'.'W'.'5l'),base64_decode(''.'c'.'3Ryb'.'GV'.'u'),base64_decode('Y'.'m'.'FzZ'.'TY0X2'.'RlY29kZQ=='),base64_decode(''.'dW5zZXJ'.'pYWxpemU='),base64_decode('aX'.'NfY'.'X'.'J'.'yY'.'Xk='),base64_decode('Y291bnQ'.'='),base64_decode('a'.'W'.'5'.'fYXJ'.'yYX'.'k'.'='),base64_decode('c2V'.'yaW'.'Fs'.'a'.'X'.'pl'),base64_decode('Ym'.'FzZTY0X2VuY'.'29kZQ='.'='),base64_decode('c3Ry'.'b'.'GV'.'u'),base64_decode('Y'.'X'.'JyYXlf'.'a2V5X'.'2V4aX'.'N'.'0cw=='),base64_decode(''.'Y'.'XJy'.'Y'.'Xlfa'.'2V5X2'.'V'.'4a'.'XN0'.'cw='.'='),base64_decode('bWt0aW1l'),base64_decode('ZGF0ZQ'.'=='),base64_decode('Z'.'GF0ZQ='.'='),base64_decode('YXJyYXl'.'fa2V5X2V4a'.'X'.'N'.'0cw=='),base64_decode('c3'.'RybGVu'),base64_decode('Y'.'XJyY'.'Xlf'.'a2V'.'5'.'X2'.'V4aXN0cw=='),base64_decode('c3RybG'.'Vu'),base64_decode('YXJyYX'.'lfa'.'2'.'V5X2V4'.'aXN'.'0cw=='),base64_decode('YX'.'J'.'yY'.'Xl'.'fa2V5X'.'2V4aXN0cw=='),base64_decode('b'.'Wt'.'0aW'.'1l'),base64_decode(''.'ZGF0ZQ=='),base64_decode('ZG'.'F0Z'.'Q=='),base64_decode('bW'.'V0'.'aG'.'9kX2'.'V4'.'aXN0cw'.'=='),base64_decode('Y2F'.'sbF91c2'.'VyX2Z1bmNf'.'Y'.'XJyYXk='),base64_decode('c3R'.'yb'.'G'.'Vu'),base64_decode('YXJyYX'.'lfa'.'2'.'V5'.'X2V4aXN0cw=='),base64_decode('Y'.'XJyYXlfa'.'2V5X2'.'V4aXN0cw=='),base64_decode('c'.'2VyaWFsaXpl'),base64_decode('Ym'.'FzZTY0X2Vu'.'Y29kZQ=='),base64_decode('c3RybGVu'),base64_decode(''.'YXJyYX'.'lfa2'.'V5'.'X2V4aXN0cw=='),base64_decode('YXJyYX'.'lfa2'.'V5'.'X'.'2'.'V'.'4aXN0cw=='),base64_decode(''.'YXJy'.'YXl'.'fa2V5'.'X2V'.'4aX'.'N'.'0cw'.'=='),base64_decode('aXN'.'f'.'YXJ'.'yYXk='),base64_decode('YXJy'.'Y'.'Xlfa2V5X2V4aXN0cw=='),base64_decode('c2VyaWFsa'.'Xpl'),base64_decode(''.'Ym'.'F'.'zZ'.'T'.'Y'.'0X'.'2VuY29'.'kZQ'.'=='),base64_decode('YX'.'J'.'yYX'.'lfa2V5X2V4aXN0c'.'w='.'='),base64_decode('YXJy'.'Y'.'Xl'.'fa2'.'V5X'.'2'.'V4'.'a'.'XN0'.'cw'.'=='),base64_decode('c'.'2VyaWFsaXpl'),base64_decode('Ym'.'Fz'.'ZTY0'.'X2VuY29kZQ=='),base64_decode('a'.'XNf'.'Y'.'X'.'JyYXk='),base64_decode('aXNfYXJy'.'YXk='),base64_decode(''.'a'.'W5fY'.'XJyYXk='),base64_decode('YXJ'.'yYX'.'lfa2V'.'5X2V4aXN0cw=='),base64_decode('aW5fY'.'XJyYXk='),base64_decode('bWt'.'0aW'.'1l'),base64_decode('Z'.'GF0'.'ZQ='.'='),base64_decode('ZG'.'F0Z'.'Q='.'='),base64_decode('ZGF0ZQ=='),base64_decode('bWt0a'.'W1l'),base64_decode('Z'.'G'.'F0ZQ'.'=='),base64_decode('Z'.'G'.'F'.'0Z'.'Q=='),base64_decode('aW5fYXJ'.'yYXk='),base64_decode('YX'.'J'.'yYX'.'l'.'fa2'.'V5X'.'2V4'.'aXN0cw=='),base64_decode('YXJy'.'YXlfa2V5X2V4aXN'.'0c'.'w'.'=='),base64_decode('c2Vy'.'aWFsaX'.'pl'),base64_decode('YmF'.'zZ'.'TY0X2V'.'uY'.'29kZQ='.'='),base64_decode('YXJyYXlfa'.'2V5X'.'2V4'.'a'.'XN0cw'.'=='),base64_decode('a'.'W50d'.'m'.'Fs'),base64_decode('dGltZQ=='),base64_decode(''.'Y'.'XJyY'.'Xlfa2V5X2V4'.'aXN0cw=='),base64_decode('ZmlsZV'.'9leGlz'.'d'.'HM='),base64_decode('c3Ry'.'X3'.'Jlc'.'Gx'.'h'.'Y2'.'U'.'='),base64_decode('Y2xhc3NfZXh'.'pc'.'3Rz'),base64_decode(''.'ZGVm'.'aW5l'));if(!function_exists(__NAMESPACE__.'\\___1108680438')){function ___1108680438($_953941012){static $_2034641802= false; if($_2034641802 == false) $_2034641802=array(''.'SU5UUkFO'.'RVRf'.'RURJ'.'VElP'.'Tg'.'==','WQ==','b'.'WFpb'.'g='.'=',''.'fmNwZl9tYXBfdmFsdWU=','','ZQ==','Zg'.'==',''.'ZQ='.'=','Rg==','W'.'A==',''.'Zg==','bWFpbg'.'==','fmN'.'w'.'Zl9t'.'YXBfdmFs'.'d'.'WU=','U'.'G9yd'.'G'.'Fs','Rg'.'==','ZQ==',''.'ZQ==',''.'WA==','Rg='.'=',''.'RA==',''.'RA='.'=','bQ==',''.'ZA'.'==','W'.'Q'.'==',''.'Zg==','Zg==','Zg==','Zg==','UG9'.'ydG'.'Fs','Rg==','ZQ==','ZQ='.'=','WA==','Rg==','RA'.'==','RA==','bQ='.'=',''.'ZA==','WQ'.'==',''.'bWFp'.'bg==','T24=',''.'U2'.'V0dGluZ3NDaGFuZ'.'2'.'U=','Zg==',''.'Zg'.'='.'=','Zg==','Zg==',''.'b'.'W'.'F'.'pbg==',''.'fmNwZ'.'l'.'9'.'t'.'YX'.'BfdmF'.'s'.'d'.'WU=','ZQ'.'==',''.'ZQ==','ZQ='.'=','RA==','ZQ==',''.'ZQ==','Z'.'g==','Zg==',''.'Z'.'g='.'=','ZQ==','bWFpbg='.'=','fm'.'NwZl'.'9'.'tYXBfdmFsdWU=','ZQ==',''.'Zg='.'=','Zg==','Zg'.'==','Zg'.'==','b'.'W'.'Fpb'.'g='.'=','fm'.'NwZl9t'.'YXB'.'fdmFs'.'dWU=','ZQ==','Zg='.'=','UG9'.'y'.'dGFs','UG9ydGF'.'s','ZQ==',''.'Z'.'Q==','UG9ydGFs','Rg==','WA'.'==','Rg==',''.'RA'.'==',''.'ZQ==','Z'.'Q'.'==',''.'R'.'A='.'=','bQ='.'=',''.'ZA==','WQ==','ZQ==','WA==','ZQ==','Rg==','Z'.'Q==','RA==','Zg='.'=',''.'ZQ==','RA='.'=','ZQ==','bQ==','ZA'.'='.'=','W'.'Q==','Zg==','Zg==','Zg==','Zg==','Zg'.'='.'=',''.'Zg'.'='.'=',''.'Z'.'g==','Zg='.'=',''.'bW'.'Fpbg==','fmNwZl9tYX'.'B'.'fdmFsd'.'W'.'U=',''.'ZQ'.'==',''.'ZQ='.'=','UG9'.'ydGFs','R'.'g='.'=','W'.'A='.'=','VFlQR'.'Q'.'==','REF'.'URQ='.'=','Rk'.'VBVFVSRVM=','RVhQS'.'VJ'.'FRA='.'=','VFl'.'QRQ==','RA==',''.'VFJZX0RBWVNfQ09VTlQ=','RE'.'FUR'.'Q'.'='.'=','VFJ'.'ZX0RBWVNfQ09VTl'.'Q=','RVhQSVJFRA==','RkVB'.'VFV'.'SRVM=','Zg==','Z'.'g==',''.'RE'.'9'.'D'.'VU1FTlRf'.'Uk9PVA='.'=','L2JpdHJp'.'e'.'C9tb2R1'.'bGVzLw='.'=','L'.'2'.'luc3R'.'hbGwvaW5kZX'.'gucGhw','Lg==','X'.'w'.'='.'=',''.'c2Vhcm'.'No','Tg==','','','QUNUSVZ'.'F','WQ'.'='.'=','c29j'.'aW'.'F'.'sbmV0d'.'29yaw==','YWxsb3'.'dfZ'.'nJpZWxkcw==',''.'WQ==','SUQ=','c29j'.'a'.'WFs'.'bmV0d29yaw==','YWxsb3dfZ'.'nJp'.'ZW'.'xkcw==','SUQ=','c29jaWFsbmV0d29yaw==','YWx'.'sb3dfZn'.'JpZWx'.'kcw==','Tg==','','','Q'.'UNUSVZF','WQ==','c29j'.'aWFsbmV0d29yaw==','Y'.'W'.'xs'.'b3dfbWljcm9ibG9nX3VzZXI=','WQ==',''.'S'.'UQ=','c29jaWFsbmV0d2'.'9yaw==','YWxs'.'b3dfbWljcm'.'9ibG9nX'.'3VzZXI=','SU'.'Q'.'=','c29jaWF'.'sbmV0'.'d'.'2'.'9'.'yaw==','YWxsb3dfbWljcm9ibG9nX'.'3'.'VzZXI'.'=',''.'c29j'.'aWFsb'.'mV0'.'d29yaw='.'=','YWxsb3df'.'bW'.'ljcm9ibG9nX2d'.'yb3Vw','WQ==','SUQ=','c'.'2'.'9jaW'.'F'.'s'.'bmV0'.'d2'.'9yaw='.'=','YWx'.'sb3d'.'fb'.'Wl'.'jcm9ibG'.'9'.'nX2dyb'.'3Vw','SUQ'.'=','c'.'29jaWF'.'sb'.'mV0d29yaw==','YWxsb3dfbWlj'.'cm9ibG9'.'nX2'.'d'.'yb3Vw',''.'Tg==','','','QUNU'.'S'.'VZF',''.'W'.'Q==',''.'c'.'29jaW'.'FsbmV0d29ya'.'w==','YWxsb3dfZmlsZXN'.'fdXNl'.'cg==','WQ==','S'.'UQ'.'=',''.'c29jaWF'.'sbmV0d29yaw'.'==','YW'.'xs'.'b3df'.'ZmlsZXN'.'fdXNlcg==',''.'SU'.'Q=',''.'c29j'.'aWFsbmV'.'0d'.'2'.'9y'.'aw==','YWx'.'sb3df'.'Zml'.'sZXNf'.'dXNlcg==','T'.'g'.'==','','','QUNU'.'SVZF',''.'W'.'Q='.'=','c2'.'9'.'j'.'a'.'WFsbmV0d'.'2'.'9'.'yaw'.'==','YWxsb3'.'df'.'YmxvZ19'.'1c2'.'Vy','W'.'Q==','SUQ=','c29jaW'.'FsbmV0d29y'.'aw==','YWxs'.'b3'.'dfYmxvZ1'.'9'.'1c2V'.'y','SUQ=',''.'c29ja'.'W'.'FsbmV'.'0d'.'2'.'9'.'yaw'.'='.'=','YWxsb3dfYmxvZ191c'.'2Vy',''.'Tg'.'='.'=','','',''.'Q'.'UN'.'USVZ'.'F','WQ==',''.'c29jaWFsbmV0'.'d'.'29'.'yaw'.'==','Y'.'Wxsb3'.'dfcGhvdG9fd'.'X'.'Nl'.'cg==',''.'WQ==','S'.'UQ=','c29jaWFsbmV0'.'d29'.'yaw==','YWx'.'sb3dfcGhv'.'dG9'.'f'.'dX'.'N'.'lcg'.'==',''.'SUQ=','c29jaW'.'Fsb'.'mV0d29'.'yaw==',''.'YWxsb3'.'d'.'fcGhvdG9fdXNlcg==','Tg==','','',''.'Q'.'U'.'NUS'.'VZF','WQ'.'==','c'.'29ja'.'W'.'FsbmV0d2'.'9'.'yaw==',''.'YWxs'.'b3'.'dfZm'.'9ydW1'.'fdXNl'.'cg==','WQ==',''.'SUQ=','c29ja'.'WFsbmV0d'.'29'.'ya'.'w'.'='.'=',''.'YWxsb3'.'dfZ'.'m9'.'ydW'.'1fdXNlc'.'g==','SUQ'.'=','c29jaWF'.'sbmV0d29ya'.'w==',''.'YW'.'xsb'.'3dfZm9ydW1f'.'dXNlcg==',''.'T'.'g='.'=','','','QUN'.'U'.'S'.'V'.'ZF','WQ'.'==','c29'.'jaWFsbm'.'V0d29y'.'aw==',''.'YWxsb3dfdG'.'Fza3NfdXNlcg='.'=','WQ==',''.'S'.'UQ=','c29ja'.'W'.'Fs'.'bm'.'V0d'.'29yaw==',''.'YWx'.'s'.'b3dfdG'.'Fza'.'3'.'N'.'fdX'.'N'.'lcg='.'=',''.'SU'.'Q'.'=','c2'.'9jaWF'.'sbmV0d'.'29'.'yaw==','Y'.'Wxsb3'.'dfd'.'GFza3NfdXNlcg='.'=','c29'.'jaWFsbmV0'.'d29yaw==',''.'YW'.'xsb3dfdGFza'.'3'.'NfZ3JvdXA=','W'.'Q==',''.'S'.'UQ'.'=','c29'.'jaWFs'.'b'.'m'.'V0d29yaw==','YWxs'.'b3'.'dfdG'.'F'.'za3NfZ3JvdX'.'A'.'=','SUQ=',''.'c29jaWFs'.'bm'.'V0d29'.'yaw==','YW'.'x'.'s'.'b3df'.'dGFz'.'a'.'3NfZ3'.'JvdX'.'A=','dGFza3M=','Tg==','','','QU'.'NUSVZF','WQ'.'==','c29jaWFsbm'.'V0d'.'2'.'9yaw==','YWx'.'sb3dfY2FsZ'.'W5kYXJf'.'dXNlcg==','WQ'.'==','SUQ=','c2'.'9'.'jaWFsbmV0'.'d2'.'9'.'yaw==','YWxs'.'b'.'3d'.'fY'.'2'.'F'.'sZW5kY'.'XJfdX'.'Nl'.'cg='.'=',''.'S'.'U'.'Q=','c29jaWFs'.'bmV0d29yaw'.'==','YWx'.'s'.'b3'.'df'.'Y2Fs'.'ZW5kYXJfdXNlcg'.'='.'=','c29jaWFsbmV0d'.'29'.'yaw'.'==','YWx'.'sb'.'3dfY2FsZW5k'.'YX'.'JfZ3J'.'v'.'dXA=','WQ'.'==','SU'.'Q=','c29'.'ja'.'WFsb'.'mV0d29'.'y'.'aw='.'=','Y'.'W'.'x'.'sb3dfY2FsZW'.'5kY'.'XJf'.'Z3J'.'vdXA=','S'.'UQ=','c29j'.'aWFsbm'.'V0d29yaw==','YWxs'.'b3dfY'.'2FsZ'.'W5kYXJfZ3Jv'.'dXA'.'=',''.'QU'.'NU'.'S'.'V'.'Z'.'F',''.'WQ==','Tg==','ZXh0cmFuZXQ=','aWJs'.'b'.'2'.'Nr','T25'.'BZnRlck'.'lCb'.'G'.'9ja0V'.'sZW1lbnRVc'.'GRh'.'dGU=','aW50cmFuZXQ=','Q0lud'.'HJhbmV0RXZlbnRIYW5kbGVycw==','U'.'1BS'.'ZWdpc3RlclVwZGF0Z'.'W'.'R'.'JdG'.'V'.'t','Q0l'.'udHJhbmV'.'0U'.'2hhcmV'.'wb2lu'.'dDo6QWd'.'lbnRMaX'.'N0c'.'yg'.'pO'.'w==','aW5'.'0cmFuZX'.'Q=','Tg==','Q0'.'ludHJhbmV0U2hhcmVwb2lu'.'d'.'Do6QWdlbnRRdWV1ZSgpOw==','aW50c'.'mFuZXQ=',''.'Tg==','Q0ludHJhbm'.'V0U2hhc'.'mVwb2ludDo6QWdlb'.'nR'.'VcGRhdGUoK'.'Ts=',''.'aW5'.'0c'.'mFuZ'.'X'.'Q=',''.'T'.'g='.'=','aW'.'Jsb2Nr','T25BZnRlcklC'.'bG9ja0VsZW'.'1lbn'.'RBZGQ=','aW'.'50cm'.'F'.'uZXQ=',''.'Q0ludHJh'.'bm'.'V0RXZ'.'lbn'.'RIYW5kbGVycw'.'==','U1BSZW'.'dpc3RlclVw'.'Z'.'GF'.'0'.'ZWR'.'JdGVt','aWJsb2N'.'r','T25BZnRlc'.'klCb'.'G9ja0VsZ'.'W'.'1'.'lb'.'nRVcGRhdGU=','aW50c'.'mFuZXQ=',''.'Q'.'0ludHJ'.'hbmV0R'.'XZl'.'bnRIY'.'W5kbGVycw==','U'.'1B'.'SZWd'.'pc3Rlc'.'lVwZGF'.'0Z'.'WRJdG'.'Vt','Q0lud'.'HJ'.'h'.'bmV0U'.'2hhcmVwb'.'2ludDo6QWd'.'lb'.'nRMa'.'XN0cy'.'gpOw='.'=',''.'aW5'.'0cmF'.'uZXQ=','Q0'.'ludHJhbmV0U'.'2h'.'hc'.'mVwb2l'.'udD'.'o'.'6QW'.'dlbnRR'.'dWV1Z'.'SgpOw==','aW50cmFuZX'.'Q'.'=','Q0ludHJhbm'.'V'.'0U2h'.'hcmVwb2'.'ludDo6QWd'.'lb'.'nRVcGRhdG'.'U'.'o'.'KTs'.'=','aW50cmF'.'uZXQ=','Y'.'3'.'Jt',''.'bWFp'.'b'.'g==','T25CZWZ'.'vcmVQcm9sb2c=','b'.'WFpbg'.'==','Q'.'1d'.'pe'.'mF'.'yZFN'.'vbFBh'.'bmVsSW50c'.'mF'.'uZXQ=','U2h'.'vd'.'1Bhb'.'m'.'Vs','L'.'21vZHVsZXM'.'v'.'aW50c'.'m'.'FuZXQvcG'.'FuZWxfYnV0'.'d'.'G9uLnBocA==','RU5DT0RF','W'.'Q==');return base64_decode($_2034641802[$_953941012]);}};$GLOBALS['____1924754177'][0](___1108680438(0), ___1108680438(1));class CBXFeatures{ private static $_1123323726= 30; private static $_334009854= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_1896060455= false; private static $_1784127894= false; private static function __682756365(){ if(self::$_1896060455 == false){ self::$_1896060455= array(); foreach(self::$_334009854 as $_1992914037 => $_1421375361){ foreach($_1421375361 as $_2059676670) self::$_1896060455[$_2059676670]= $_1992914037;}} if(self::$_1784127894 == false){ self::$_1784127894= array(); $_661062096= COption::GetOptionString(___1108680438(2), ___1108680438(3), ___1108680438(4)); if($GLOBALS['____1924754177'][1]($_661062096)>(224*2-448)){ $_661062096= $GLOBALS['____1924754177'][2]($_661062096); self::$_1784127894= $GLOBALS['____1924754177'][3]($_661062096); if(!$GLOBALS['____1924754177'][4](self::$_1784127894)) self::$_1784127894= array();} if($GLOBALS['____1924754177'][5](self::$_1784127894) <=(221*2-442)) self::$_1784127894= array(___1108680438(5) => array(), ___1108680438(6) => array());}} public static function InitiateEditionsSettings($_1340333504){ self::__682756365(); $_1794543824= array(); foreach(self::$_334009854 as $_1992914037 => $_1421375361){ $_459618738= $GLOBALS['____1924754177'][6]($_1992914037, $_1340333504); self::$_1784127894[___1108680438(7)][$_1992914037]=($_459618738? array(___1108680438(8)): array(___1108680438(9))); foreach($_1421375361 as $_2059676670){ self::$_1784127894[___1108680438(10)][$_2059676670]= $_459618738; if(!$_459618738) $_1794543824[]= array($_2059676670, false);}} $_1725981273= $GLOBALS['____1924754177'][7](self::$_1784127894); $_1725981273= $GLOBALS['____1924754177'][8]($_1725981273); COption::SetOptionString(___1108680438(11), ___1108680438(12), $_1725981273); foreach($_1794543824 as $_1126152526) self::__54304065($_1126152526[(1120/2-560)], $_1126152526[round(0+0.25+0.25+0.25+0.25)]);} public static function IsFeatureEnabled($_2059676670){ if($GLOBALS['____1924754177'][9]($_2059676670) <= 0) return true; self::__682756365(); if(!$GLOBALS['____1924754177'][10]($_2059676670, self::$_1896060455)) return true; if(self::$_1896060455[$_2059676670] == ___1108680438(13)) $_1843393943= array(___1108680438(14)); elseif($GLOBALS['____1924754177'][11](self::$_1896060455[$_2059676670], self::$_1784127894[___1108680438(15)])) $_1843393943= self::$_1784127894[___1108680438(16)][self::$_1896060455[$_2059676670]]; else $_1843393943= array(___1108680438(17)); if($_1843393943[(1460/2-730)] != ___1108680438(18) && $_1843393943[(1252/2-626)] != ___1108680438(19)){ return false;} elseif($_1843393943[(1044/2-522)] == ___1108680438(20)){ if($_1843393943[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____1924754177'][12]((146*2-292),(928-2*464),(1272/2-636), Date(___1108680438(21)), $GLOBALS['____1924754177'][13](___1108680438(22))- self::$_1123323726, $GLOBALS['____1924754177'][14](___1108680438(23)))){ if(!isset($_1843393943[round(0+0.5+0.5+0.5+0.5)]) ||!$_1843393943[round(0+2)]) self::__2087598249(self::$_1896060455[$_2059676670]); return false;}} return!$GLOBALS['____1924754177'][15]($_2059676670, self::$_1784127894[___1108680438(24)]) || self::$_1784127894[___1108680438(25)][$_2059676670];} public static function IsFeatureInstalled($_2059676670){ if($GLOBALS['____1924754177'][16]($_2059676670) <= 0) return true; self::__682756365(); return($GLOBALS['____1924754177'][17]($_2059676670, self::$_1784127894[___1108680438(26)]) && self::$_1784127894[___1108680438(27)][$_2059676670]);} public static function IsFeatureEditable($_2059676670){ if($GLOBALS['____1924754177'][18]($_2059676670) <= 0) return true; self::__682756365(); if(!$GLOBALS['____1924754177'][19]($_2059676670, self::$_1896060455)) return true; if(self::$_1896060455[$_2059676670] == ___1108680438(28)) $_1843393943= array(___1108680438(29)); elseif($GLOBALS['____1924754177'][20](self::$_1896060455[$_2059676670], self::$_1784127894[___1108680438(30)])) $_1843393943= self::$_1784127894[___1108680438(31)][self::$_1896060455[$_2059676670]]; else $_1843393943= array(___1108680438(32)); if($_1843393943[(968-2*484)] != ___1108680438(33) && $_1843393943[min(222,0,74)] != ___1108680438(34)){ return false;} elseif($_1843393943[(1040/2-520)] == ___1108680438(35)){ if($_1843393943[round(0+0.2+0.2+0.2+0.2+0.2)]< $GLOBALS['____1924754177'][21]((776-2*388),(1432/2-716),(240*2-480), Date(___1108680438(36)), $GLOBALS['____1924754177'][22](___1108680438(37))- self::$_1123323726, $GLOBALS['____1924754177'][23](___1108680438(38)))){ if(!isset($_1843393943[round(0+2)]) ||!$_1843393943[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) self::__2087598249(self::$_1896060455[$_2059676670]); return false;}} return true;} private static function __54304065($_2059676670, $_499581885){ if($GLOBALS['____1924754177'][24]("CBXFeatures", "On".$_2059676670."SettingsChange")) $GLOBALS['____1924754177'][25](array("CBXFeatures", "On".$_2059676670."SettingsChange"), array($_2059676670, $_499581885)); $_1695566135= $GLOBALS['_____1742921085'][0](___1108680438(39), ___1108680438(40).$_2059676670.___1108680438(41)); while($_783732097= $_1695566135->Fetch()) $GLOBALS['_____1742921085'][1]($_783732097, array($_2059676670, $_499581885));} public static function SetFeatureEnabled($_2059676670, $_499581885= true, $_109812537= true){ if($GLOBALS['____1924754177'][26]($_2059676670) <= 0) return; if(!self::IsFeatureEditable($_2059676670)) $_499581885= false; $_499581885=($_499581885? true: false); self::__682756365(); $_1825821954=(!$GLOBALS['____1924754177'][27]($_2059676670, self::$_1784127894[___1108680438(42)]) && $_499581885 || $GLOBALS['____1924754177'][28]($_2059676670, self::$_1784127894[___1108680438(43)]) && $_499581885 != self::$_1784127894[___1108680438(44)][$_2059676670]); self::$_1784127894[___1108680438(45)][$_2059676670]= $_499581885; $_1725981273= $GLOBALS['____1924754177'][29](self::$_1784127894); $_1725981273= $GLOBALS['____1924754177'][30]($_1725981273); COption::SetOptionString(___1108680438(46), ___1108680438(47), $_1725981273); if($_1825821954 && $_109812537) self::__54304065($_2059676670, $_499581885);} private static function __2087598249($_1992914037){ if($GLOBALS['____1924754177'][31]($_1992914037) <= 0 || $_1992914037 == "Portal") return; self::__682756365(); if(!$GLOBALS['____1924754177'][32]($_1992914037, self::$_1784127894[___1108680438(48)]) || $GLOBALS['____1924754177'][33]($_1992914037, self::$_1784127894[___1108680438(49)]) && self::$_1784127894[___1108680438(50)][$_1992914037][(872-2*436)] != ___1108680438(51)) return; if(isset(self::$_1784127894[___1108680438(52)][$_1992914037][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) && self::$_1784127894[___1108680438(53)][$_1992914037][round(0+1+1)]) return; $_1794543824= array(); if($GLOBALS['____1924754177'][34]($_1992914037, self::$_334009854) && $GLOBALS['____1924754177'][35](self::$_334009854[$_1992914037])){ foreach(self::$_334009854[$_1992914037] as $_2059676670){ if($GLOBALS['____1924754177'][36]($_2059676670, self::$_1784127894[___1108680438(54)]) && self::$_1784127894[___1108680438(55)][$_2059676670]){ self::$_1784127894[___1108680438(56)][$_2059676670]= false; $_1794543824[]= array($_2059676670, false);}} self::$_1784127894[___1108680438(57)][$_1992914037][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]= true;} $_1725981273= $GLOBALS['____1924754177'][37](self::$_1784127894); $_1725981273= $GLOBALS['____1924754177'][38]($_1725981273); COption::SetOptionString(___1108680438(58), ___1108680438(59), $_1725981273); foreach($_1794543824 as $_1126152526) self::__54304065($_1126152526[(816-2*408)], $_1126152526[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function ModifyFeaturesSettings($_1340333504, $_1421375361){ self::__682756365(); foreach($_1340333504 as $_1992914037 => $_953448866) self::$_1784127894[___1108680438(60)][$_1992914037]= $_953448866; $_1794543824= array(); foreach($_1421375361 as $_2059676670 => $_499581885){ if(!$GLOBALS['____1924754177'][39]($_2059676670, self::$_1784127894[___1108680438(61)]) && $_499581885 || $GLOBALS['____1924754177'][40]($_2059676670, self::$_1784127894[___1108680438(62)]) && $_499581885 != self::$_1784127894[___1108680438(63)][$_2059676670]) $_1794543824[]= array($_2059676670, $_499581885); self::$_1784127894[___1108680438(64)][$_2059676670]= $_499581885;} $_1725981273= $GLOBALS['____1924754177'][41](self::$_1784127894); $_1725981273= $GLOBALS['____1924754177'][42]($_1725981273); COption::SetOptionString(___1108680438(65), ___1108680438(66), $_1725981273); self::$_1784127894= false; foreach($_1794543824 as $_1126152526) self::__54304065($_1126152526[min(224,0,74.666666666667)], $_1126152526[round(0+1)]);} public static function SaveFeaturesSettings($_1549379183, $_798392944){ self::__682756365(); $_2072641995= array(___1108680438(67) => array(), ___1108680438(68) => array()); if(!$GLOBALS['____1924754177'][43]($_1549379183)) $_1549379183= array(); if(!$GLOBALS['____1924754177'][44]($_798392944)) $_798392944= array(); if(!$GLOBALS['____1924754177'][45](___1108680438(69), $_1549379183)) $_1549379183[]= ___1108680438(70); foreach(self::$_334009854 as $_1992914037 => $_1421375361){ if($GLOBALS['____1924754177'][46]($_1992914037, self::$_1784127894[___1108680438(71)])) $_850742533= self::$_1784127894[___1108680438(72)][$_1992914037]; else $_850742533=($_1992914037 == ___1108680438(73))? array(___1108680438(74)): array(___1108680438(75)); if($_850742533[(1340/2-670)] == ___1108680438(76) || $_850742533[(186*2-372)] == ___1108680438(77)){ $_2072641995[___1108680438(78)][$_1992914037]= $_850742533;} else{ if($GLOBALS['____1924754177'][47]($_1992914037, $_1549379183)) $_2072641995[___1108680438(79)][$_1992914037]= array(___1108680438(80), $GLOBALS['____1924754177'][48]((138*2-276), min(126,0,42),(1216/2-608), $GLOBALS['____1924754177'][49](___1108680438(81)), $GLOBALS['____1924754177'][50](___1108680438(82)), $GLOBALS['____1924754177'][51](___1108680438(83)))); else $_2072641995[___1108680438(84)][$_1992914037]= array(___1108680438(85));}} $_1794543824= array(); foreach(self::$_1896060455 as $_2059676670 => $_1992914037){ if($_2072641995[___1108680438(86)][$_1992914037][(958-2*479)] != ___1108680438(87) && $_2072641995[___1108680438(88)][$_1992914037][(161*2-322)] != ___1108680438(89)){ $_2072641995[___1108680438(90)][$_2059676670]= false;} else{ if($_2072641995[___1108680438(91)][$_1992914037][(782-2*391)] == ___1108680438(92) && $_2072641995[___1108680438(93)][$_1992914037][round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____1924754177'][52](min(44,0,14.666666666667),(204*2-408),(1312/2-656), Date(___1108680438(94)), $GLOBALS['____1924754177'][53](___1108680438(95))- self::$_1123323726, $GLOBALS['____1924754177'][54](___1108680438(96)))) $_2072641995[___1108680438(97)][$_2059676670]= false; else $_2072641995[___1108680438(98)][$_2059676670]= $GLOBALS['____1924754177'][55]($_2059676670, $_798392944); if(!$GLOBALS['____1924754177'][56]($_2059676670, self::$_1784127894[___1108680438(99)]) && $_2072641995[___1108680438(100)][$_2059676670] || $GLOBALS['____1924754177'][57]($_2059676670, self::$_1784127894[___1108680438(101)]) && $_2072641995[___1108680438(102)][$_2059676670] != self::$_1784127894[___1108680438(103)][$_2059676670]) $_1794543824[]= array($_2059676670, $_2072641995[___1108680438(104)][$_2059676670]);}} $_1725981273= $GLOBALS['____1924754177'][58]($_2072641995); $_1725981273= $GLOBALS['____1924754177'][59]($_1725981273); COption::SetOptionString(___1108680438(105), ___1108680438(106), $_1725981273); self::$_1784127894= false; foreach($_1794543824 as $_1126152526) self::__54304065($_1126152526[(1072/2-536)], $_1126152526[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function GetFeaturesList(){ self::__682756365(); $_1885059471= array(); foreach(self::$_334009854 as $_1992914037 => $_1421375361){ if($GLOBALS['____1924754177'][60]($_1992914037, self::$_1784127894[___1108680438(107)])) $_850742533= self::$_1784127894[___1108680438(108)][$_1992914037]; else $_850742533=($_1992914037 == ___1108680438(109))? array(___1108680438(110)): array(___1108680438(111)); $_1885059471[$_1992914037]= array( ___1108680438(112) => $_850742533[(183*2-366)], ___1108680438(113) => $_850742533[round(0+0.33333333333333+0.33333333333333+0.33333333333333)], ___1108680438(114) => array(),); $_1885059471[$_1992914037][___1108680438(115)]= false; if($_1885059471[$_1992914037][___1108680438(116)] == ___1108680438(117)){ $_1885059471[$_1992914037][___1108680438(118)]= $GLOBALS['____1924754177'][61](($GLOBALS['____1924754177'][62]()- $_1885059471[$_1992914037][___1108680438(119)])/ round(0+28800+28800+28800)); if($_1885059471[$_1992914037][___1108680438(120)]> self::$_1123323726) $_1885059471[$_1992914037][___1108680438(121)]= true;} foreach($_1421375361 as $_2059676670) $_1885059471[$_1992914037][___1108680438(122)][$_2059676670]=(!$GLOBALS['____1924754177'][63]($_2059676670, self::$_1784127894[___1108680438(123)]) || self::$_1784127894[___1108680438(124)][$_2059676670]);} return $_1885059471;} private static function __157029727($_120778214, $_947810987){ if(IsModuleInstalled($_120778214) == $_947810987) return true; $_1249825112= $_SERVER[___1108680438(125)].___1108680438(126).$_120778214.___1108680438(127); if(!$GLOBALS['____1924754177'][64]($_1249825112)) return false; include_once($_1249825112); $_74300247= $GLOBALS['____1924754177'][65](___1108680438(128), ___1108680438(129), $_120778214); if(!$GLOBALS['____1924754177'][66]($_74300247)) return false; $_181950748= new $_74300247; if($_947810987){ if(!$_181950748->InstallDB()) return false; $_181950748->InstallEvents(); if(!$_181950748->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___1108680438(130))) CSearch::DeleteIndex($_120778214); UnRegisterModule($_120778214);} return true;} protected static function OnRequestsSettingsChange($_2059676670, $_499581885){ self::__157029727("form", $_499581885);} protected static function OnLearningSettingsChange($_2059676670, $_499581885){ self::__157029727("learning", $_499581885);} protected static function OnJabberSettingsChange($_2059676670, $_499581885){ self::__157029727("xmpp", $_499581885);} protected static function OnVideoConferenceSettingsChange($_2059676670, $_499581885){ self::__157029727("video", $_499581885);} protected static function OnBizProcSettingsChange($_2059676670, $_499581885){ self::__157029727("bizprocdesigner", $_499581885);} protected static function OnListsSettingsChange($_2059676670, $_499581885){ self::__157029727("lists", $_499581885);} protected static function OnWikiSettingsChange($_2059676670, $_499581885){ self::__157029727("wiki", $_499581885);} protected static function OnSupportSettingsChange($_2059676670, $_499581885){ self::__157029727("support", $_499581885);} protected static function OnControllerSettingsChange($_2059676670, $_499581885){ self::__157029727("controller", $_499581885);} protected static function OnAnalyticsSettingsChange($_2059676670, $_499581885){ self::__157029727("statistic", $_499581885);} protected static function OnVoteSettingsChange($_2059676670, $_499581885){ self::__157029727("vote", $_499581885);} protected static function OnFriendsSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(131); $_1587474907= CSite::GetList(($_459618738= ___1108680438(132)),($_303050256= ___1108680438(133)), array(___1108680438(134) => ___1108680438(135))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(136), ___1108680438(137), ___1108680438(138), $_76127984[___1108680438(139)]) != $_1654524945){ COption::SetOptionString(___1108680438(140), ___1108680438(141), $_1654524945, false, $_76127984[___1108680438(142)]); COption::SetOptionString(___1108680438(143), ___1108680438(144), $_1654524945);}}} protected static function OnMicroBlogSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(145); $_1587474907= CSite::GetList(($_459618738= ___1108680438(146)),($_303050256= ___1108680438(147)), array(___1108680438(148) => ___1108680438(149))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(150), ___1108680438(151), ___1108680438(152), $_76127984[___1108680438(153)]) != $_1654524945){ COption::SetOptionString(___1108680438(154), ___1108680438(155), $_1654524945, false, $_76127984[___1108680438(156)]); COption::SetOptionString(___1108680438(157), ___1108680438(158), $_1654524945);} if(COption::GetOptionString(___1108680438(159), ___1108680438(160), ___1108680438(161), $_76127984[___1108680438(162)]) != $_1654524945){ COption::SetOptionString(___1108680438(163), ___1108680438(164), $_1654524945, false, $_76127984[___1108680438(165)]); COption::SetOptionString(___1108680438(166), ___1108680438(167), $_1654524945);}}} protected static function OnPersonalFilesSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(168); $_1587474907= CSite::GetList(($_459618738= ___1108680438(169)),($_303050256= ___1108680438(170)), array(___1108680438(171) => ___1108680438(172))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(173), ___1108680438(174), ___1108680438(175), $_76127984[___1108680438(176)]) != $_1654524945){ COption::SetOptionString(___1108680438(177), ___1108680438(178), $_1654524945, false, $_76127984[___1108680438(179)]); COption::SetOptionString(___1108680438(180), ___1108680438(181), $_1654524945);}}} protected static function OnPersonalBlogSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(182); $_1587474907= CSite::GetList(($_459618738= ___1108680438(183)),($_303050256= ___1108680438(184)), array(___1108680438(185) => ___1108680438(186))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(187), ___1108680438(188), ___1108680438(189), $_76127984[___1108680438(190)]) != $_1654524945){ COption::SetOptionString(___1108680438(191), ___1108680438(192), $_1654524945, false, $_76127984[___1108680438(193)]); COption::SetOptionString(___1108680438(194), ___1108680438(195), $_1654524945);}}} protected static function OnPersonalPhotoSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(196); $_1587474907= CSite::GetList(($_459618738= ___1108680438(197)),($_303050256= ___1108680438(198)), array(___1108680438(199) => ___1108680438(200))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(201), ___1108680438(202), ___1108680438(203), $_76127984[___1108680438(204)]) != $_1654524945){ COption::SetOptionString(___1108680438(205), ___1108680438(206), $_1654524945, false, $_76127984[___1108680438(207)]); COption::SetOptionString(___1108680438(208), ___1108680438(209), $_1654524945);}}} protected static function OnPersonalForumSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(210); $_1587474907= CSite::GetList(($_459618738= ___1108680438(211)),($_303050256= ___1108680438(212)), array(___1108680438(213) => ___1108680438(214))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(215), ___1108680438(216), ___1108680438(217), $_76127984[___1108680438(218)]) != $_1654524945){ COption::SetOptionString(___1108680438(219), ___1108680438(220), $_1654524945, false, $_76127984[___1108680438(221)]); COption::SetOptionString(___1108680438(222), ___1108680438(223), $_1654524945);}}} protected static function OnTasksSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(224); $_1587474907= CSite::GetList(($_459618738= ___1108680438(225)),($_303050256= ___1108680438(226)), array(___1108680438(227) => ___1108680438(228))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(229), ___1108680438(230), ___1108680438(231), $_76127984[___1108680438(232)]) != $_1654524945){ COption::SetOptionString(___1108680438(233), ___1108680438(234), $_1654524945, false, $_76127984[___1108680438(235)]); COption::SetOptionString(___1108680438(236), ___1108680438(237), $_1654524945);} if(COption::GetOptionString(___1108680438(238), ___1108680438(239), ___1108680438(240), $_76127984[___1108680438(241)]) != $_1654524945){ COption::SetOptionString(___1108680438(242), ___1108680438(243), $_1654524945, false, $_76127984[___1108680438(244)]); COption::SetOptionString(___1108680438(245), ___1108680438(246), $_1654524945);}} self::__157029727(___1108680438(247), $_499581885);} protected static function OnCalendarSettingsChange($_2059676670, $_499581885){ if($_499581885) $_1654524945= "Y"; else $_1654524945= ___1108680438(248); $_1587474907= CSite::GetList(($_459618738= ___1108680438(249)),($_303050256= ___1108680438(250)), array(___1108680438(251) => ___1108680438(252))); while($_76127984= $_1587474907->Fetch()){ if(COption::GetOptionString(___1108680438(253), ___1108680438(254), ___1108680438(255), $_76127984[___1108680438(256)]) != $_1654524945){ COption::SetOptionString(___1108680438(257), ___1108680438(258), $_1654524945, false, $_76127984[___1108680438(259)]); COption::SetOptionString(___1108680438(260), ___1108680438(261), $_1654524945);} if(COption::GetOptionString(___1108680438(262), ___1108680438(263), ___1108680438(264), $_76127984[___1108680438(265)]) != $_1654524945){ COption::SetOptionString(___1108680438(266), ___1108680438(267), $_1654524945, false, $_76127984[___1108680438(268)]); COption::SetOptionString(___1108680438(269), ___1108680438(270), $_1654524945);}}} protected static function OnSMTPSettingsChange($_2059676670, $_499581885){ self::__157029727("mail", $_499581885);} protected static function OnExtranetSettingsChange($_2059676670, $_499581885){ $_1076345203= COption::GetOptionString("extranet", "extranet_site", ""); if($_1076345203){ $_839670259= new CSite; $_839670259->Update($_1076345203, array(___1108680438(271) =>($_499581885? ___1108680438(272): ___1108680438(273))));} self::__157029727(___1108680438(274), $_499581885);} protected static function OnDAVSettingsChange($_2059676670, $_499581885){ self::__157029727("dav", $_499581885);} protected static function OntimemanSettingsChange($_2059676670, $_499581885){ self::__157029727("timeman", $_499581885);} protected static function Onintranet_sharepointSettingsChange($_2059676670, $_499581885){ if($_499581885){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___1108680438(275), ___1108680438(276), ___1108680438(277), ___1108680438(278), ___1108680438(279)); CAgent::AddAgent(___1108680438(280), ___1108680438(281), ___1108680438(282), round(0+100+100+100+100+100)); CAgent::AddAgent(___1108680438(283), ___1108680438(284), ___1108680438(285), round(0+60+60+60+60+60)); CAgent::AddAgent(___1108680438(286), ___1108680438(287), ___1108680438(288), round(0+1800+1800));} else{ UnRegisterModuleDependences(___1108680438(289), ___1108680438(290), ___1108680438(291), ___1108680438(292), ___1108680438(293)); UnRegisterModuleDependences(___1108680438(294), ___1108680438(295), ___1108680438(296), ___1108680438(297), ___1108680438(298)); CAgent::RemoveAgent(___1108680438(299), ___1108680438(300)); CAgent::RemoveAgent(___1108680438(301), ___1108680438(302)); CAgent::RemoveAgent(___1108680438(303), ___1108680438(304));}} protected static function OncrmSettingsChange($_2059676670, $_499581885){ if($_499581885) COption::SetOptionString("crm", "form_features", "Y"); self::__157029727(___1108680438(305), $_499581885);} protected static function OnClusterSettingsChange($_2059676670, $_499581885){ self::__157029727("cluster", $_499581885);} protected static function OnMultiSitesSettingsChange($_2059676670, $_499581885){ if($_499581885) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___1108680438(306), ___1108680438(307), ___1108680438(308), ___1108680438(309), ___1108680438(310), ___1108680438(311));} protected static function OnIdeaSettingsChange($_2059676670, $_499581885){ self::__157029727("idea", $_499581885);} protected static function OnMeetingSettingsChange($_2059676670, $_499581885){ self::__157029727("meeting", $_499581885);} protected static function OnXDImportSettingsChange($_2059676670, $_499581885){ self::__157029727("xdimport", $_499581885);}} $GLOBALS['____1924754177'][67](___1108680438(312), ___1108680438(313));/**/			//Do not remove this

//component 2.0 template engines
$GLOBALS["arCustomTemplateEngines"] = array();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/urlrewriter.php");

/**
 * Defined in dbconn.php
 * @param string $DBType
 */

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"main",
	array(
		"CSiteTemplate" => "classes/general/site_template.php",
		"CBitrixComponent" => "classes/general/component.php",
		"CComponentEngine" => "classes/general/component_engine.php",
		"CComponentAjax" => "classes/general/component_ajax.php",
		"CBitrixComponentTemplate" => "classes/general/component_template.php",
		"CComponentUtil" => "classes/general/component_util.php",
		"CControllerClient" => "classes/general/controller_member.php",
		"PHPParser" => "classes/general/php_parser.php",
		"CDiskQuota" => "classes/".$DBType."/quota.php",
		"CEventLog" => "classes/general/event_log.php",
		"CEventMain" => "classes/general/event_log.php",
		"CAdminFileDialog" => "classes/general/file_dialog.php",
		"WLL_User" => "classes/general/liveid.php",
		"WLL_ConsentToken" => "classes/general/liveid.php",
		"WindowsLiveLogin" => "classes/general/liveid.php",
		"CAllFile" => "classes/general/file.php",
		"CFile" => "classes/".$DBType."/file.php",
		"CTempFile" => "classes/general/file_temp.php",
		"CFavorites" => "classes/".$DBType."/favorites.php",
		"CUserOptions" => "classes/general/user_options.php",
		"CGridOptions" => "classes/general/grids.php",
		"CUndo" => "/classes/general/undo.php",
		"CAutoSave" => "/classes/general/undo.php",
		"CRatings" => "classes/".$DBType."/ratings.php",
		"CRatingsComponentsMain" => "classes/".$DBType."/ratings_components.php",
		"CRatingRule" => "classes/general/rating_rule.php",
		"CRatingRulesMain" => "classes/".$DBType."/rating_rules.php",
		"CTopPanel" => "public/top_panel.php",
		"CEditArea" => "public/edit_area.php",
		"CComponentPanel" => "public/edit_area.php",
		"CTextParser" => "classes/general/textparser.php",
		"CPHPCacheFiles" => "classes/general/cache_files.php",
		"CDataXML" => "classes/general/xml.php",
		"CXMLFileStream" => "classes/general/xml.php",
		"CRsaProvider" => "classes/general/rsasecurity.php",
		"CRsaSecurity" => "classes/general/rsasecurity.php",
		"CRsaBcmathProvider" => "classes/general/rsabcmath.php",
		"CRsaOpensslProvider" => "classes/general/rsaopenssl.php",
		"CASNReader" => "classes/general/asn.php",
		"CBXShortUri" => "classes/".$DBType."/short_uri.php",
		"CFinder" => "classes/general/finder.php",
		"CAccess" => "classes/general/access.php",
		"CAuthProvider" => "classes/general/authproviders.php",
		"IProviderInterface" => "classes/general/authproviders.php",
		"CGroupAuthProvider" => "classes/general/authproviders.php",
		"CUserAuthProvider" => "classes/general/authproviders.php",
		"CTableSchema" => "classes/general/table_schema.php",
		"CCSVData" => "classes/general/csv_data.php",
		"CSmile" => "classes/general/smile.php",
		"CSmileGallery" => "classes/general/smile.php",
		"CSmileSet" => "classes/general/smile.php",
		"CGlobalCounter" => "classes/general/global_counter.php",
		"CUserCounter" => "classes/".$DBType."/user_counter.php",
		"CUserCounterPage" => "classes/".$DBType."/user_counter.php",
		"CHotKeys" => "classes/general/hot_keys.php",
		"CHotKeysCode" => "classes/general/hot_keys.php",
		"CBXSanitizer" => "classes/general/sanitizer.php",
		"CBXArchive" => "classes/general/archive.php",
		"CAdminNotify" => "classes/general/admin_notify.php",
		"CBXFavAdmMenu" => "classes/general/favorites.php",
		"CAdminInformer" => "classes/general/admin_informer.php",
		"CSiteCheckerTest" => "classes/general/site_checker.php",
		"CSqlUtil" => "classes/general/sql_util.php",
		"CFileUploader" => "classes/general/uploader.php",
		"LPA" => "classes/general/lpa.php",
		"CAdminFilter" => "interface/admin_filter.php",
		"CAdminList" => "interface/admin_list.php",
		"CAdminUiList" => "interface/admin_ui_list.php",
		"CAdminUiResult" => "interface/admin_ui_list.php",
		"CAdminUiContextMenu" => "interface/admin_ui_list.php",
		"CAdminListRow" => "interface/admin_list.php",
		"CAdminTabControl" => "interface/admin_tabcontrol.php",
		"CAdminForm" => "interface/admin_form.php",
		"CAdminFormSettings" => "interface/admin_form.php",
		"CAdminTabControlDrag" => "interface/admin_tabcontrol_drag.php",
		"CAdminDraggableBlockEngine" => "interface/admin_tabcontrol_drag.php",
		"CJSPopup" => "interface/jspopup.php",
		"CJSPopupOnPage" => "interface/jspopup.php",
		"CAdminCalendar" => "interface/admin_calendar.php",
		"CAdminViewTabControl" => "interface/admin_viewtabcontrol.php",
		"CAdminTabEngine" => "interface/admin_tabengine.php",
		"CCaptcha" => "classes/general/captcha.php",
		"CMpNotifications" => "classes/general/mp_notifications.php",

		//deprecated
		"CHTMLPagesCache" => "lib/composite/helper.php",
		"StaticHtmlMemcachedResponse" => "lib/composite/responder.php",
		"StaticHtmlFileResponse" => "lib/composite/responder.php",
		"Bitrix\\Main\\Page\\Frame" => "lib/composite/engine.php",
		"Bitrix\\Main\\Page\\FrameStatic" => "lib/composite/staticarea.php",
		"Bitrix\\Main\\Page\\FrameBuffered" => "lib/composite/bufferarea.php",
		"Bitrix\\Main\\Page\\FrameHelper" => "lib/composite/bufferarea.php",
		"Bitrix\\Main\\Data\\StaticHtmlCache" => "lib/composite/page.php",
		"Bitrix\\Main\\Data\\StaticHtmlStorage" => "lib/composite/data/abstractstorage.php",
		"Bitrix\\Main\\Data\\StaticHtmlFileStorage" => "lib/composite/data/filestorage.php",
		"Bitrix\\Main\\Data\\StaticHtmlMemcachedStorage" => "lib/composite/data/memcachedstorage.php",
		"Bitrix\\Main\\Data\\StaticCacheProvider" => "lib/composite/data/cacheprovider.php",
		"Bitrix\\Main\\Data\\AppCacheManifest" => "lib/composite/appcache.php",
	)
);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/agent.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/user.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/event.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/menu.php");
AddEventHandler("main", "OnAfterEpilog", array("\\Bitrix\\Main\\Data\\ManagedCache", "finalize"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/usertype.php");

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php")))
{
	$US_HOST_PROCESS_MAIN = False;
	include($_fname);
}

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php")))
	include_once($_fname);

if(($_fname = getLocalPath("php_interface/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(($_fname = getLocalPath("php_interface/".SITE_ID."/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0644);
if(!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0755);

//global var, is used somewhere
$GLOBALS["sDocPath"] = $GLOBALS["APPLICATION"]->GetCurPage();

if((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && substr($GLOBALS["APPLICATION"]->GetCurPage(), 0, strlen(BX_ROOT."/admin/"))!=BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y")=="Y" && strlen(LANG_CHARSET)>0)
	header("Content-Type: text/html; charset=".LANG_CHARSET);

if(COption::GetOptionString("main", "set_p3p_header", "Y")=="Y")
	header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");

header("X-Powered-CMS: Bitrix Site Manager (".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE")).")");
if (COption::GetOptionString("main", "update_devsrv", "") == "Y")
	header("X-DevSrv-CMS: Bitrix");

define("BX_CRONTAB_SUPPORT", defined("BX_CRONTAB"));

if(COption::GetOptionString("main", "check_agents", "Y")=="Y")
{
	define("START_EXEC_AGENTS_1", microtime());
	$GLOBALS["BX_STATE"] = "AG";
	$GLOBALS["DB"]->StartUsingMasterOnly();
	CAgent::CheckAgents();
	$GLOBALS["DB"]->StopUsingMasterOnly();
	define("START_EXEC_AGENTS_2", microtime());
	$GLOBALS["BX_STATE"] = "PB";
}

//session initialization
ini_set("session.cookie_httponly", "1");

if(($domain = \Bitrix\Main\Web\Cookie::getCookieDomain()) <> '')
{
	ini_set("session.cookie_domain", $domain);
}

if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
	CSecuritySession::Init();

session_start();

foreach (GetModuleEvents("main", "OnPageStart", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

//define global user object
$GLOBALS["USER"] = new CUser;

//session control from group policy
$arPolicy = $GLOBALS["USER"]->GetSecurityPolicy();
$currTime = time();
if(
	(
		//IP address changed
		$_SESSION['SESS_IP']
		&& strlen($arPolicy["SESSION_IP_MASK"])>0
		&& (
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SESSION['SESS_IP']))
			!=
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER['REMOTE_ADDR']))
		)
	)
	||
	(
		//session timeout
		$arPolicy["SESSION_TIMEOUT"]>0
		&& $_SESSION['SESS_TIME']>0
		&& $currTime-$arPolicy["SESSION_TIMEOUT"]*60 > $_SESSION['SESS_TIME']
	)
	||
	(
		//session expander control
		isset($_SESSION["BX_SESSION_TERMINATE_TIME"])
		&& $_SESSION["BX_SESSION_TERMINATE_TIME"] > 0
		&& $currTime > $_SESSION["BX_SESSION_TERMINATE_TIME"]
	)
	||
	(
		//signed session
		isset($_SESSION["BX_SESSION_SIGN"])
		&& $_SESSION["BX_SESSION_SIGN"] <> bitrix_sess_sign()
	)
	||
	(
		//session manually expired, e.g. in $User->LoginHitByHash
		isSessionExpired()
	)
)
{
	$_SESSION = array();
	@session_destroy();

	//session_destroy cleans user sesssion handles in some PHP versions
	//see http://bugs.php.net/bug.php?id=32330 discussion
	if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
		CSecuritySession::Init();

	session_id(md5(uniqid(rand(), true)));
	session_start();
	$GLOBALS["USER"] = new CUser;
}
$_SESSION['SESS_IP'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['SESS_TIME'] = time();
if(!isset($_SESSION["BX_SESSION_SIGN"]))
	$_SESSION["BX_SESSION_SIGN"] = bitrix_sess_sign();

//session control from security module
if(
	(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
	&& !defined("BX_SESSION_ID_CHANGE")
)
{
	if(!array_key_exists('SESS_ID_TIME', $_SESSION))
	{
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
	elseif(($_SESSION['SESS_ID_TIME'] + COption::GetOptionInt("main", "session_id_ttl")) < $_SESSION['SESS_TIME'])
	{
		if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
		{
			CSecuritySession::UpdateSessID();
		}
		else
		{
			session_regenerate_id();
		}
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
}

define("BX_STARTED", true);

if (isset($_SESSION['BX_ADMIN_LOAD_AUTH']))
{
	define('ADMIN_SECTION_LOAD_AUTH', 1);
	unset($_SESSION['BX_ADMIN_LOAD_AUTH']);
}

if(!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true)
{
	$bLogout = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == "yes");

	if($bLogout && $GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->Logout();
		LocalRedirect($GLOBALS["APPLICATION"]->GetCurPageParam('', array('logout')));
	}

	// authorize by cookies
	if(!$GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->LoginByCookies();
	}

	$arAuthResult = false;

	//http basic and digest authorization
	if(($httpAuth = $GLOBALS["USER"]->LoginByHttpAuth()) !== null)
	{
		$arAuthResult = $httpAuth;
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}

	//Authorize user from authorization html form
	if(isset($_REQUEST["AUTH_FORM"]) && $_REQUEST["AUTH_FORM"] <> '')
	{
		$bRsaError = false;
		if(COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
		{
			//possible encrypted user password
			$sec = new CRsaSecurity();
			if(($arKeys = $sec->LoadKeys()))
			{
				$sec->SetKeys($arKeys);
				$errno = $sec->AcceptFromForm(array('USER_PASSWORD', 'USER_CONFIRM_PASSWORD'));
				if($errno == CRsaSecurity::ERROR_SESS_CHECK)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_sess"), "TYPE"=>"ERROR");
				elseif($errno < 0)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_err", array("#ERRCODE#"=>$errno)), "TYPE"=>"ERROR");

				if($errno < 0)
					$bRsaError = true;
			}
		}

		if($bRsaError == false)
		{
			if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
				$USER_LID = LANG;
			else
				$USER_LID = false;

			if($_REQUEST["TYPE"] == "AUTH")
			{
				$arAuthResult = $GLOBALS["USER"]->Login($_REQUEST["USER_LOGIN"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_REMEMBER"]);
			}
			elseif($_REQUEST["TYPE"] == "OTP")
			{
				$arAuthResult = $GLOBALS["USER"]->LoginByOtp($_REQUEST["USER_OTP"], $_REQUEST["OTP_REMEMBER"], $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif($_REQUEST["TYPE"] == "SEND_PWD")
			{
				$arAuthResult = CUser::SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "CHANGE_PWD")
			{
				$arAuthResult = $GLOBALS["USER"]->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y" && $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION!==true))
			{
				$arAuthResult = $GLOBALS["USER"]->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}

			if($_REQUEST["TYPE"] == "AUTH" || $_REQUEST["TYPE"] == "OTP")
			{
				//special login form in the control panel
				if($arAuthResult === true && defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					//store cookies for next hit (see CMain::GetSpreadCookieHTML())
					$GLOBALS["APPLICATION"]->StoreCookies();
					$_SESSION['BX_ADMIN_LOAD_AUTH'] = true;
					CMain::FinalActions('<script type="text/javascript">window.onload=function(){top.BX.AUTHAGENT.setAuthResult(false);};</script>');
					die();
				}
			}
		}
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}
	elseif(!$GLOBALS["USER"]->IsAuthorized())
	{
		//Authorize by unique URL
		$GLOBALS["USER"]->LoginHitByHash();
	}
}

//logout or re-authorize the user if something importand has changed
$GLOBALS["USER"]->CheckAuthActions();

//magic short URI
if(defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri())
{
	//local redirect inside
	die();
}

//application password scope control
if(($applicationID = $GLOBALS["USER"]->GetParam("APPLICATION_ID")) !== null)
{
	$appManager = \Bitrix\Main\Authentication\ApplicationManager::getInstance();
	if($appManager->checkScope($applicationID) !== true)
	{
		$event = new \Bitrix\Main\Event("main", "onApplicationScopeError", Array('APPLICATION_ID' => $applicationID));
		$event->send();

		CHTTP::SetStatus("403 Forbidden");
		die();
	}
}

//define the site template
if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
{
	$siteTemplate = "";
	if(is_string($_REQUEST["bitrix_preview_site_template"]) && $_REQUEST["bitrix_preview_site_template"] <> "" && $GLOBALS["USER"]->CanDoOperation('view_other_settings'))
	{
		//preview of site template
		$signer = new Bitrix\Main\Security\Sign\Signer();
		try
		{
			//protected by a sign
			$requestTemplate = $signer->unsign($_REQUEST["bitrix_preview_site_template"], "template_preview".bitrix_sessid());

			$aTemplates = CSiteTemplate::GetByID($requestTemplate);
			if($template = $aTemplates->Fetch())
			{
				$siteTemplate = $template["ID"];

				//preview of unsaved template
				if(isset($_GET['bx_template_preview_mode']) && $_GET['bx_template_preview_mode'] == 'Y' && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
				{
					define("SITE_TEMPLATE_PREVIEW_MODE", true);
				}
			}
		}
		catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
		}
	}
	if($siteTemplate == "")
	{
		$siteTemplate = CSite::GetCurTemplate();
	}
	define("SITE_TEMPLATE_ID", $siteTemplate);
	define("SITE_TEMPLATE_PATH", getLocalPath('templates/'.SITE_TEMPLATE_ID, BX_PERSONAL_ROOT));
}

//magic parameters: show page creation time
if(isset($_GET["show_page_exec_time"]))
{
	if($_GET["show_page_exec_time"]=="Y" || $_GET["show_page_exec_time"]=="N")
		$_SESSION["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];
}

//magic parameters: show included file processing time
if(isset($_GET["show_include_exec_time"]))
{
	if($_GET["show_include_exec_time"]=="Y" || $_GET["show_include_exec_time"]=="N")
		$_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];
}

//magic parameters: show include areas
if(isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
	$GLOBALS["APPLICATION"]->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");

//magic sound
if($GLOBALS["USER"]->IsAuthorized())
{
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	if(!isset($_COOKIE[$cookie_prefix.'_SOUND_LOGIN_PLAYED']))
		$GLOBALS["APPLICATION"]->set_cookie('SOUND_LOGIN_PLAYED', 'Y', 0);
}

//magic cache
\Bitrix\Main\Composite\Engine::shouldBeEnabled();

foreach(GetModuleEvents("main", "OnBeforeProlog", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

if((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS!==true))
{
	$real_path = $request->getScriptFile();

	if(!$GLOBALS["USER"]->CanDoFileOperation('fm_view_file', array(SITE_ID, $real_path)) || (defined("NEED_AUTH") && NEED_AUTH && !$GLOBALS["USER"]->IsAuthorized()))
	{
		/** @noinspection PhpUndefinedVariableInspection */
		if($GLOBALS["USER"]->IsAuthorized() && $arAuthResult["MESSAGE"] == '')
			$arAuthResult = array("MESSAGE"=>GetMessage("ACCESS_DENIED").' '.GetMessage("ACCESS_DENIED_FILE", array("#FILE#"=>$real_path)), "TYPE"=>"ERROR");

		if(defined("ADMIN_SECTION") && ADMIN_SECTION==true)
		{
			if ($_REQUEST["mode"]=="list" || $_REQUEST["mode"]=="settings")
			{
				echo "<script>top.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';</script>";
				die();
			}
			elseif ($_REQUEST["mode"]=="frame")
			{
				echo "<script type=\"text/javascript\">
					var w = (opener? opener.window:parent.window);
					w.location.href='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';
				</script>";
				die();
			}
			elseif(defined("MOBILE_APP_ADMIN") && MOBILE_APP_ADMIN==true)
			{
				echo json_encode(Array("status"=>"failed"));
				die();
			}
		}

		/** @noinspection PhpUndefinedVariableInspection */
		$GLOBALS["APPLICATION"]->AuthForm($arAuthResult);
	}
}

/*ZDUyZmZNjAwYTU4MGViOGYxYzc5ZjQwMjhmMTMwY2ZmZmZhZDU=*/$GLOBALS['____1743369033']= array(base64_decode('bX'.'RfcmFuZA=='),base64_decode('Z'.'Xh'.'wbG9'.'kZQ=='),base64_decode('cGFjaw=='),base64_decode(''.'bWQ1'),base64_decode('Y29'.'uc3RhbnQ='),base64_decode('aGFzaF'.'9'.'obW'.'Fj'),base64_decode('c3RyY2'.'1w'),base64_decode('aXN'.'fb2J'.'qZ'.'WN0'),base64_decode('Y'.'2F'.'sbF91c'.'2VyX2'.'Z1'.'bmM='),base64_decode(''.'Y2Fsb'.'F91'.'c2'.'VyX2Z1bmM='),base64_decode('Y2FsbF91'.'c2'.'Vy'.'X2Z'.'1bm'.'M'.'='),base64_decode('Y2FsbF91c'.'2V'.'yX2Z1b'.'mM'.'='),base64_decode('Y2F'.'s'.'bF91'.'c2VyX2Z'.'1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___169208984')){function ___169208984($_1803028301){static $_156890429= false; if($_156890429 == false) $_156890429=array('RE'.'I'.'=','U0'.'VMRUNUIFZBTFVFI'.'EZST00gYl9v'.'cH'.'Rpb24gV'.'0hFUkUgTkFN'.'RT0nfl'.'BB'.'UkFN'.'X01B'.'WF9VU0'.'VSUy'.'c'.'gQU'.'5'.'EI'.'E1PRF'.'VMR'.'V9JRD0n'.'bW'.'FpbicgQU5EI'.'F'.'N'.'JVEVfSU'.'QgSVMgTlVMTA==','VkFMVUU=','Lg==','SCo'.'=',''.'Yml0c'.'m'.'l4',''.'TElDRU5'.'TRV'.'9LRVk=',''.'c'.'2hhM'.'jU'.'2','VVN'.'FUg==','VVNF'.'Ug==','VV'.'NF'.'Ug==','SXNBd'.'XRob3Jpe'.'mV'.'k','V'.'VNF'.'Ug'.'==','S'.'X'.'N'.'BZG1pb'.'g'.'==','QVBQTElDQ'.'VRJT04=','UmVzdG'.'FydEJ1Zm'.'Z'.'lcg==',''.'TG9'.'jYWxSZWRpc'.'mVjd'.'A==','L2xp'.'Y2'.'Vuc2Vfcm'.'Vz'.'dH'.'JpY'.'3Rpb'.'24ucGhw',''.'XEJpdHJp'.'eFxNYW'.'luXENv'.'bmZpZ1'.'xPcHR'.'pb246O'.'n'.'NldA==','bW'.'Fpbg==','UEF'.'SQU'.'1f'.'TU'.'FYX1'.'V'.'TR'.'VJT');return base64_decode($_156890429[$_1803028301]);}};if($GLOBALS['____1743369033'][0](round(0+0.5+0.5), round(0+5+5+5+5)) == round(0+7)){ $_477539641= $GLOBALS[___169208984(0)]->Query(___169208984(1), true); if($_1418626076= $_477539641->Fetch()){ $_241623668= $_1418626076[___169208984(2)]; list($_235303661, $_403170988)= $GLOBALS['____1743369033'][1](___169208984(3), $_241623668); $_1486625916= $GLOBALS['____1743369033'][2](___169208984(4), $_235303661); $_894014701= ___169208984(5).$GLOBALS['____1743369033'][3]($GLOBALS['____1743369033'][4](___169208984(6))); $_2141726671= $GLOBALS['____1743369033'][5](___169208984(7), $_403170988, $_894014701, true); if($GLOBALS['____1743369033'][6]($_2141726671, $_1486625916) !==(880-2*440)){ if(isset($GLOBALS[___169208984(8)]) && $GLOBALS['____1743369033'][7]($GLOBALS[___169208984(9)]) && $GLOBALS['____1743369033'][8](array($GLOBALS[___169208984(10)], ___169208984(11))) &&!$GLOBALS['____1743369033'][9](array($GLOBALS[___169208984(12)], ___169208984(13)))){ $GLOBALS['____1743369033'][10](array($GLOBALS[___169208984(14)], ___169208984(15))); $GLOBALS['____1743369033'][11](___169208984(16), ___169208984(17), true);}}} else{ $GLOBALS['____1743369033'][12](___169208984(18), ___169208984(19), ___169208984(20), round(0+3+3+3+3));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
