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

/*ZDUyZmZNWM3NzZlODY4YmQzOWZkNzljZjY5NjE0MTQzMmZlMGU=*/$GLOBALS['_____547914682']= array(base64_decode(''.'R'.'2V0TW'.'9kdWx'.'l'.'RXZ'.'l'.'bnRz'),base64_decode('R'.'XhlY3V'.'0ZU1vZHVsZUV2ZW50'.'RX'.'g='));$GLOBALS['____1626405312']= array(base64_decode('ZGVmaW5l'),base64_decode(''.'c3RybGV'.'u'),base64_decode('YmFz'.'ZTY0X'.'2'.'R'.'lY29'.'kZQ'.'=='),base64_decode('dW5zZ'.'X'.'JpY'.'Wxpem'.'U='),base64_decode('aX'.'N'.'fYXJyYX'.'k='),base64_decode('Y2'.'91'.'bnQ='),base64_decode('aW'.'5fYX'.'Jy'.'YXk='),base64_decode(''.'c2Vya'.'W'.'F'.'sa'.'Xpl'),base64_decode('YmFzZT'.'Y0'.'X2VuY29kZ'.'Q'.'=='),base64_decode('c'.'3R'.'ybGV'.'u'),base64_decode(''.'YXJyYXlfa2V5X'.'2'.'V4aXN0cw=='),base64_decode('YXJyYXlfa2'.'V5X2V4'.'aXN0cw=='),base64_decode(''.'b'.'Wt0aW1l'),base64_decode('ZGF0Z'.'Q=='),base64_decode('ZG'.'F0ZQ='.'='),base64_decode(''.'YXJyYXlfa2V5X2V4aXN0cw='.'='),base64_decode('c3RybGVu'),base64_decode('Y'.'XJyYXlfa2V'.'5'.'X'.'2V4aXN0cw=='),base64_decode(''.'c3'.'RybGVu'),base64_decode('Y'.'XJy'.'YX'.'lfa'.'2V'.'5'.'X2V4'.'aXN0cw=='),base64_decode(''.'YXJyY'.'X'.'lfa'.'2V5X2V4aXN0c'.'w='.'='),base64_decode('b'.'Wt0aW1l'),base64_decode(''.'Z'.'GF0ZQ='.'='),base64_decode('ZGF'.'0'.'Z'.'Q'.'=='),base64_decode('bWV0aG'.'9k'.'X2V'.'4aXN0cw='.'='),base64_decode('Y2F'.'sbF'.'9'.'1c2VyX2Z1'.'bmNfY'.'XJyYXk='),base64_decode('c3R'.'yb'.'G'.'Vu'),base64_decode(''.'YXJyYXlfa2'.'V5X2V'.'4aX'.'N0cw'.'=='),base64_decode(''.'Y'.'XJ'.'yYX'.'lfa2V5X'.'2'.'V4aXN0'.'cw=='),base64_decode('c2Vy'.'aW'.'FsaXpl'),base64_decode('YmFzZTY0X'.'2VuY29'.'k'.'ZQ='.'='),base64_decode('c3RybG'.'Vu'),base64_decode('YXJyYXl'.'f'.'a2V5X'.'2V'.'4aX'.'N0cw'.'=='),base64_decode('Y'.'X'.'JyY'.'Xlf'.'a2V5'.'X2V4aXN0cw=='),base64_decode('YXJ'.'yYXlfa2V5'.'X2V4aXN0cw=='),base64_decode(''.'aXNfYXJ'.'yYXk='),base64_decode('YXJy'.'Y'.'Xlfa2V5X'.'2'.'V4aXN0cw=='),base64_decode('c2VyaWFsaXp'.'l'),base64_decode('YmF'.'zZTY0X2'.'VuY29kZQ=='),base64_decode(''.'YXJyYXlfa2'.'V5X2V'.'4aXN0cw=='),base64_decode('YXJy'.'Y'.'X'.'l'.'fa2'.'V5X2'.'V4aXN0'.'cw=='),base64_decode('c2'.'VyaW'.'F'.'sa'.'Xpl'),base64_decode('YmF'.'zZTY0'.'X2V'.'uY29kZQ=='),base64_decode('a'.'X'.'NfY'.'XJyYX'.'k='),base64_decode('aXN'.'fYXJyYXk='),base64_decode('aW5fYXJyYXk='),base64_decode('Y'.'XJyYXl'.'fa2'.'V'.'5X'.'2V4a'.'XN0'.'c'.'w'.'=='),base64_decode('aW5f'.'YX'.'JyY'.'Xk='),base64_decode(''.'bW'.'t0aW1'.'l'),base64_decode(''.'ZG'.'F0ZQ'.'=='),base64_decode('ZG'.'F'.'0'.'ZQ=='),base64_decode(''.'Z'.'GF0ZQ=='),base64_decode(''.'bWt0aW1l'),base64_decode('ZG'.'F0'.'ZQ'.'=='),base64_decode('ZGF0ZQ='.'='),base64_decode('aW'.'5f'.'YXJ'.'yYXk'.'='),base64_decode(''.'Y'.'XJyYXlfa2V5X2'.'V4aXN0cw=='),base64_decode('YXJyYXlf'.'a2V'.'5'.'X2V'.'4aXN0cw=='),base64_decode('c2'.'VyaWFsaX'.'pl'),base64_decode('YmFzZTY0'.'X2'.'VuY29k'.'Z'.'Q=='),base64_decode('Y'.'X'.'J'.'y'.'Y'.'Xlfa2'.'V5X'.'2V4aXN0cw'.'=='),base64_decode('aW50dmFs'),base64_decode('dGl'.'tZ'.'Q'.'=='),base64_decode('YXJ'.'yYXl'.'fa2V'.'5X2V'.'4'.'aX'.'N0c'.'w=='),base64_decode('ZmlsZV9'.'leGl'.'z'.'d'.'H'.'M='),base64_decode('c'.'3RyX3JlcG'.'x'.'hY'.'2U='),base64_decode('Y'.'2'.'xhc3NfZXh'.'pc3Rz'),base64_decode(''.'ZGVm'.'aW5l'));if(!function_exists(__NAMESPACE__.'\\___267756245')){function ___267756245($_1114521621){static $_73545813= false; if($_73545813 == false) $_73545813=array('SU5'.'U'.'UkFOR'.'VRfRURJ'.'V'.'El'.'PTg='.'=',''.'WQ==','bWFpb'.'g==','fmNwZl9tYXBfdmFsdW'.'U=','','ZQ==','Zg='.'=',''.'ZQ==','Rg==',''.'WA==','Zg==','bW'.'Fpb'.'g==',''.'f'.'m'.'Nw'.'Zl9tY'.'XBf'.'dmFsdWU'.'=','U'.'G9ydG'.'Fs',''.'Rg==','ZQ==','ZQ==','WA==',''.'Rg='.'=','RA='.'=','RA='.'=','bQ==','ZA==','WQ='.'=','Zg==','Zg==','Z'.'g==','Zg'.'==','UG9ydGFs','Rg==',''.'ZQ='.'=','ZQ==','WA==','Rg==','RA==',''.'RA==','bQ==','ZA==','WQ==','bWFp'.'bg==','T'.'24'.'=','U2V'.'0dGl'.'uZ'.'3N'.'D'.'aGF'.'uZ2U'.'=','Zg'.'==',''.'Zg==','Zg==','Z'.'g'.'==','b'.'W'.'Fpb'.'g==','fmNwZl'.'9tYX'.'BfdmFsdWU=','ZQ='.'=','ZQ==','ZQ==',''.'RA==','ZQ==','ZQ==','Zg==',''.'Zg==','Zg==','ZQ==','bWFp'.'bg==','fmNwZl9tYXB'.'fd'.'mFs'.'dWU=','ZQ==','Zg==',''.'Zg==','Zg==','Zg==','b'.'WFpbg='.'=',''.'fmNw'.'Zl9tYXB'.'f'.'d'.'mF'.'sd'.'WU'.'=','ZQ==','Z'.'g==','UG'.'9y'.'dGFs','UG9y'.'dG'.'F'.'s',''.'ZQ==',''.'ZQ==','UG9y'.'dGF'.'s',''.'Rg'.'==','WA==','R'.'g==','RA==','ZQ='.'=','ZQ==','RA='.'=','bQ==','ZA==',''.'WQ='.'=','ZQ='.'=','WA='.'=','Z'.'Q==','Rg==','Z'.'Q'.'==','RA==',''.'Z'.'g==','ZQ==',''.'R'.'A==',''.'ZQ==','bQ==','Z'.'A==','W'.'Q==','Zg==',''.'Zg==','Zg='.'=','Zg'.'='.'=','Zg='.'=','Zg==','Zg==','Zg==','bWF'.'pbg='.'=','fm'.'NwZ'.'l9'.'tYXB'.'fdm'.'FsdWU=','ZQ==','Z'.'Q'.'==','UG9y'.'dGFs','R'.'g==','WA='.'=','VFlQR'.'Q==','REFURQ==','Rk'.'VBVFVS'.'RVM'.'=',''.'RVhQSVJFRA==','VFlQRQ='.'=','RA==','VFJ'.'ZX0'.'RBWVNfQ09V'.'TlQ=',''.'RE'.'FUR'.'Q==','V'.'FJZX0'.'RBWVNfQ'.'09VT'.'lQ=',''.'RVhQSVJFRA==','R'.'kVB'.'V'.'F'.'V'.'SRVM=','Zg==',''.'Zg==','R'.'E9DVU1FT'.'lRfU'.'k9P'.'VA==',''.'L2JpdHJpeC9tb2R1'.'bGV'.'zLw'.'==','L2luc'.'3Rh'.'bGwva'.'W5k'.'ZXgucG'.'hw','Lg==','Xw==','c2'.'VhcmNo',''.'Tg='.'=','','','QU'.'NUSV'.'ZF','W'.'Q==','c'.'29'.'jaWFsbmV0d29yaw'.'==','YW'.'xs'.'b3dfZnJpZWxkcw==','W'.'Q'.'==','SUQ'.'=','c29j'.'aW'.'FsbmV0d29ya'.'w='.'=','YWxs'.'b3dfZnJpZWxkcw==','SUQ'.'=','c2'.'9j'.'aWFsbmV0d29yaw='.'=','YWxsb'.'3dfZn'.'Jp'.'Z'.'Wxkcw='.'=',''.'Tg==','','','Q'.'U'.'NUSV'.'ZF','WQ==','c29jaWFsb'.'m'.'V0'.'d29yaw==','YWx'.'sb3dfbWl'.'jcm'.'9ibG9nX3VzZXI=',''.'WQ==','S'.'U'.'Q=',''.'c'.'29j'.'aWFsbmV0d2'.'9ya'.'w='.'=','YW'.'xs'.'b3df'.'b'.'Wl'.'j'.'cm9ibG9nX'.'3VzZXI=','SUQ=','c29jaWFsbm'.'V0d2'.'9'.'yaw==','Y'.'Wxsb3'.'d'.'fb'.'Wljcm9ibG'.'9n'.'X'.'3Vz'.'ZXI'.'=','c2'.'9ja'.'WFsbmV0d2'.'9'.'yaw==','Y'.'W'.'xsb3dfbWljcm9ib'.'G9'.'nX2dyb3'.'V'.'w','W'.'Q==','SU'.'Q'.'=','c29jaWFs'.'b'.'mV0d2'.'9yaw==','Y'.'Wxsb3df'.'b'.'Wljcm9ibG9'.'nX2dy'.'b3Vw','SUQ=','c29jaWFs'.'bmV0d29ya'.'w==','YW'.'x'.'sb'.'3dfb'.'Wljcm'.'9i'.'b'.'G9nX2d'.'yb3Vw','Tg='.'=','','',''.'QUN'.'US'.'VZF',''.'WQ==','c29jaWFsbm'.'V0d'.'2'.'9y'.'aw==','Y'.'Wxsb3df'.'ZmlsZ'.'XN'.'f'.'dXNlcg==','W'.'Q==',''.'SUQ'.'=','c29jaWF'.'s'.'b'.'mV0d'.'2'.'9y'.'aw='.'=','YWxsb3dfZmlsZXNfdXN'.'lcg'.'==','SUQ=',''.'c29j'.'a'.'WFs'.'bmV0d29'.'y'.'a'.'w'.'==','YW'.'xs'.'b'.'3dfZ'.'m'.'lsZXNfd'.'XN'.'lcg='.'=','T'.'g==','','','Q'.'UNU'.'SVZF','W'.'Q'.'==','c29jaWFsbmV'.'0'.'d29ya'.'w'.'==','YWxsb3d'.'fYmxvZ19'.'1c2Vy','WQ==','SUQ=','c2'.'9jaW'.'F'.'sbmV0d'.'29yaw='.'=',''.'Y'.'Wx'.'sb3dfYmxvZ19'.'1c'.'2Vy',''.'SUQ'.'=','c29j'.'aWFsbmV0'.'d2'.'9ya'.'w==','YWxsb3dfY'.'mx'.'vZ191c2Vy','Tg'.'==','','','Q'.'UNUSVZF','W'.'Q'.'==',''.'c29'.'jaW'.'FsbmV0d'.'29y'.'aw==','YWx'.'s'.'b3dfc'.'GhvdG9'.'fdXN'.'lcg==',''.'WQ='.'=','SU'.'Q=','c2'.'9jaWFsbmV'.'0d2'.'9'.'yaw==','YWxsb3'.'dfcGhvd'.'G9'.'fd'.'XNlcg==','SUQ=','c'.'29ja'.'WF'.'s'.'bmV0d29yaw==','YWx'.'sb3dfcGhvd'.'G9fd'.'XNlc'.'g==',''.'Tg='.'=','','','QUNUSVZF',''.'WQ==','c29j'.'aWFs'.'b'.'mV0d'.'29y'.'aw'.'==','Y'.'Wxsb'.'3dfZm9ydW1fdXNlcg==','WQ==','SUQ=',''.'c29jaW'.'FsbmV0d'.'29'.'yaw==','YWx'.'sb3d'.'f'.'Zm'.'9y'.'dW1'.'fdXNl'.'cg'.'==','SUQ=',''.'c2'.'9jaWFsbmV0d29yaw==','YW'.'xs'.'b3d'.'f'.'Zm'.'9ydW1'.'fdXNlcg==','Tg'.'='.'=','','','Q'.'UNUS'.'V'.'ZF',''.'WQ==','c29jaWFsbm'.'V'.'0d29'.'yaw==','YW'.'xsb3df'.'d'.'GFza3NfdX'.'Nlcg==',''.'WQ==',''.'S'.'UQ'.'=','c29jaWFsbmV0d'.'29yaw==','YWxsb3dfdG'.'Fza3NfdXNlcg==','SUQ=','c29'.'jaWFs'.'bm'.'V0'.'d29ya'.'w==','YWxs'.'b3'.'dfdGFza3NfdXNl'.'cg='.'=','c2'.'9jaW'.'F'.'sbmV0'.'d'.'2'.'9'.'yaw==','YWxs'.'b3d'.'fdGFza3NfZ3JvdXA=','W'.'Q'.'==',''.'SU'.'Q=','c2'.'9ja'.'W'.'FsbmV0d'.'29ya'.'w='.'=',''.'YWxsb3dfdGFza3NfZ'.'3'.'J'.'vdXA'.'=',''.'SUQ=','c29jaWF'.'sbmV0d2'.'9'.'ya'.'w==','YWxsb3dfdGF'.'z'.'a3'.'Nf'.'Z3'.'Jvd'.'XA'.'=','d'.'G'.'Fza3M'.'=','Tg==','','',''.'QU'.'NUSV'.'ZF','WQ==','c29jaW'.'Fs'.'bmV'.'0d29yaw==',''.'Y'.'Wxsb3d'.'f'.'Y2'.'FsZW5kYX'.'JfdX'.'Nl'.'cg==','WQ==',''.'S'.'UQ=','c2'.'9jaWFs'.'bmV0d29ya'.'w==','YWxsb'.'3dfY2FsZW5kYXJfd'.'XNlcg==','SUQ=',''.'c29jaWFsbmV0d'.'29y'.'aw==','Y'.'Wxsb3'.'dfY2FsZW'.'5kYX'.'JfdXNlc'.'g==','c29jaWFsbmV0'.'d29y'.'aw='.'=','YWxsb3dfY2FsZ'.'W5'.'kYXJ'.'fZ3JvdXA'.'=',''.'W'.'Q==','SUQ=','c'.'2'.'9ja'.'WFsbm'.'V0d29ya'.'w==','Y'.'Wxsb3dfY2FsZW5kYXJ'.'fZ'.'3'.'JvdXA=',''.'SUQ=','c29j'.'aW'.'Fsbm'.'V0'.'d'.'29yaw==',''.'YWxsb3dfY2FsZW5kYXJfZ3JvdXA=',''.'QUNUSV'.'ZF','WQ==','Tg'.'==','ZXh0cmF'.'uZXQ'.'=','a'.'WJ'.'sb'.'2N'.'r','T25BZnRlck'.'lCbG9ja0VsZ'.'W1lb'.'nRVcGRh'.'dGU=','aW5'.'0cmFu'.'ZXQ=','Q0lu'.'dHJh'.'bmV0R'.'XZlbnRIYW5kbGVycw==','U1BSZWdpc3Rlcl'.'VwZG'.'F0ZWR'.'Jd'.'GVt','Q0lu'.'d'.'H'.'Jh'.'bmV0'.'U'.'2hhcmVwb2'.'l'.'ud'.'Do'.'6QWdlbnR'.'MaX'.'N'.'0'.'c'.'ygpOw==','aW50cmFuZXQ=','Tg'.'==','Q0ludHJhb'.'mV'.'0U2hh'.'cmVw'.'b2lu'.'dDo'.'6QWdl'.'bnR'.'R'.'dWV1ZSgp'.'Ow==','aW5'.'0cmFuZXQ'.'=','Tg==','Q0'.'ludHJhbm'.'V0U2hhcmV'.'wb2ludDo6QWd'.'lbn'.'RVcGRhdGUoKTs=','aW50'.'cmFuZXQ=','Tg==','a'.'WJsb2'.'Nr','T25B'.'Z'.'nR'.'lc'.'klCbG9j'.'a'.'0VsZW1lbnRBZGQ=','aW50c'.'mFu'.'ZXQ=','Q0lud'.'H'.'JhbmV0'.'R'.'XZlbnR'.'IY'.'W5kbG'.'V'.'ycw='.'=','U'.'1B'.'SZWdpc3'.'Rl'.'clVwZG'.'F0ZWR'.'JdGV'.'t','aWJsb'.'2N'.'r','T'.'25'.'B'.'ZnRlcklCbG'.'9ja0VsZ'.'W'.'1'.'l'.'bnRVcG'.'RhdGU=','aW50cmFuZXQ=','Q'.'0ludHJ'.'hbmV0R'.'XZlbnRIY'.'W5kbGVyc'.'w==',''.'U1B'.'SZWd'.'pc3R'.'l'.'cl'.'VwZ'.'GF0ZW'.'RJ'.'dG'.'Vt','Q0ludHJ'.'hbm'.'V0U2hhcmVw'.'b'.'2'.'ludDo'.'6'.'Q'.'Wd'.'lbnRMaX'.'N0cygpOw==','aW50c'.'m'.'FuZXQ=',''.'Q0ludHJ'.'hbmV0U'.'2h'.'hc'.'mVwb2ludDo'.'6QWdlbnRRdWV1ZSgpOw==','aW50cmFuZ'.'XQ=',''.'Q0lu'.'d'.'HJhb'.'mV0U2hhcmVwb2'.'lu'.'dDo6Q'.'Wdlb'.'n'.'RVcGRhd'.'GUoKTs=','aW50cmFuZXQ=','Y3Jt','bWFpbg==','T'.'25'.'CZWZvcmVQcm9s'.'b2c=','bWFp'.'bg'.'==','Q1dp'.'emF'.'yZFN'.'vb'.'FBhbmVsSW'.'50cmFuZXQ=','U2hvd'.'1'.'BhbmVs','L21vZHV'.'sZ'.'XMvaW50cmF'.'u'.'Z'.'XQ'.'vcG'.'FuZ'.'Wxf'.'YnV0'.'dG9'.'uLnB'.'ocA==','RU5DT0'.'RF','WQ'.'==');return base64_decode($_73545813[$_1114521621]);}};$GLOBALS['____1626405312'][0](___267756245(0), ___267756245(1));class CBXFeatures{ private static $_639768302= 30; private static $_162300837= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_833205792= false; private static $_271820885= false; private static function __1780282462(){ if(self::$_833205792 == false){ self::$_833205792= array(); foreach(self::$_162300837 as $_2101519942 => $_1304075273){ foreach($_1304075273 as $_1080570707) self::$_833205792[$_1080570707]= $_2101519942;}} if(self::$_271820885 == false){ self::$_271820885= array(); $_254164622= COption::GetOptionString(___267756245(2), ___267756245(3), ___267756245(4)); if($GLOBALS['____1626405312'][1]($_254164622)>(810-2*405)){ $_254164622= $GLOBALS['____1626405312'][2]($_254164622); self::$_271820885= $GLOBALS['____1626405312'][3]($_254164622); if(!$GLOBALS['____1626405312'][4](self::$_271820885)) self::$_271820885= array();} if($GLOBALS['____1626405312'][5](self::$_271820885) <=(900-2*450)) self::$_271820885= array(___267756245(5) => array(), ___267756245(6) => array());}} public static function InitiateEditionsSettings($_1618263261){ self::__1780282462(); $_1753139624= array(); foreach(self::$_162300837 as $_2101519942 => $_1304075273){ $_1378416546= $GLOBALS['____1626405312'][6]($_2101519942, $_1618263261); self::$_271820885[___267756245(7)][$_2101519942]=($_1378416546? array(___267756245(8)): array(___267756245(9))); foreach($_1304075273 as $_1080570707){ self::$_271820885[___267756245(10)][$_1080570707]= $_1378416546; if(!$_1378416546) $_1753139624[]= array($_1080570707, false);}} $_1601131268= $GLOBALS['____1626405312'][7](self::$_271820885); $_1601131268= $GLOBALS['____1626405312'][8]($_1601131268); COption::SetOptionString(___267756245(11), ___267756245(12), $_1601131268); foreach($_1753139624 as $_1392581768) self::__2034484057($_1392581768[(1096/2-548)], $_1392581768[round(0+1)]);} public static function IsFeatureEnabled($_1080570707){ if($GLOBALS['____1626405312'][9]($_1080570707) <= 0) return true; self::__1780282462(); if(!$GLOBALS['____1626405312'][10]($_1080570707, self::$_833205792)) return true; if(self::$_833205792[$_1080570707] == ___267756245(13)) $_155218163= array(___267756245(14)); elseif($GLOBALS['____1626405312'][11](self::$_833205792[$_1080570707], self::$_271820885[___267756245(15)])) $_155218163= self::$_271820885[___267756245(16)][self::$_833205792[$_1080570707]]; else $_155218163= array(___267756245(17)); if($_155218163[min(6,0,2)] != ___267756245(18) && $_155218163[min(24,0,8)] != ___267756245(19)){ return false;} elseif($_155218163[min(18,0,6)] == ___267756245(20)){ if($_155218163[round(0+0.5+0.5)]< $GLOBALS['____1626405312'][12]((210*2-420),(129*2-258),(1220/2-610), Date(___267756245(21)), $GLOBALS['____1626405312'][13](___267756245(22))- self::$_639768302, $GLOBALS['____1626405312'][14](___267756245(23)))){ if(!isset($_155218163[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) ||!$_155218163[round(0+0.5+0.5+0.5+0.5)]) self::__1868845070(self::$_833205792[$_1080570707]); return false;}} return!$GLOBALS['____1626405312'][15]($_1080570707, self::$_271820885[___267756245(24)]) || self::$_271820885[___267756245(25)][$_1080570707];} public static function IsFeatureInstalled($_1080570707){ if($GLOBALS['____1626405312'][16]($_1080570707) <= 0) return true; self::__1780282462(); return($GLOBALS['____1626405312'][17]($_1080570707, self::$_271820885[___267756245(26)]) && self::$_271820885[___267756245(27)][$_1080570707]);} public static function IsFeatureEditable($_1080570707){ if($GLOBALS['____1626405312'][18]($_1080570707) <= 0) return true; self::__1780282462(); if(!$GLOBALS['____1626405312'][19]($_1080570707, self::$_833205792)) return true; if(self::$_833205792[$_1080570707] == ___267756245(28)) $_155218163= array(___267756245(29)); elseif($GLOBALS['____1626405312'][20](self::$_833205792[$_1080570707], self::$_271820885[___267756245(30)])) $_155218163= self::$_271820885[___267756245(31)][self::$_833205792[$_1080570707]]; else $_155218163= array(___267756245(32)); if($_155218163[min(216,0,72)] != ___267756245(33) && $_155218163[(245*2-490)] != ___267756245(34)){ return false;} elseif($_155218163[(169*2-338)] == ___267756245(35)){ if($_155218163[round(0+0.5+0.5)]< $GLOBALS['____1626405312'][21]((774-2*387),(882-2*441), min(196,0,65.333333333333), Date(___267756245(36)), $GLOBALS['____1626405312'][22](___267756245(37))- self::$_639768302, $GLOBALS['____1626405312'][23](___267756245(38)))){ if(!isset($_155218163[round(0+1+1)]) ||!$_155218163[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) self::__1868845070(self::$_833205792[$_1080570707]); return false;}} return true;} private static function __2034484057($_1080570707, $_734079028){ if($GLOBALS['____1626405312'][24]("CBXFeatures", "On".$_1080570707."SettingsChange")) $GLOBALS['____1626405312'][25](array("CBXFeatures", "On".$_1080570707."SettingsChange"), array($_1080570707, $_734079028)); $_397451636= $GLOBALS['_____547914682'][0](___267756245(39), ___267756245(40).$_1080570707.___267756245(41)); while($_2090047681= $_397451636->Fetch()) $GLOBALS['_____547914682'][1]($_2090047681, array($_1080570707, $_734079028));} public static function SetFeatureEnabled($_1080570707, $_734079028= true, $_994353113= true){ if($GLOBALS['____1626405312'][26]($_1080570707) <= 0) return; if(!self::IsFeatureEditable($_1080570707)) $_734079028= false; $_734079028=($_734079028? true: false); self::__1780282462(); $_430672946=(!$GLOBALS['____1626405312'][27]($_1080570707, self::$_271820885[___267756245(42)]) && $_734079028 || $GLOBALS['____1626405312'][28]($_1080570707, self::$_271820885[___267756245(43)]) && $_734079028 != self::$_271820885[___267756245(44)][$_1080570707]); self::$_271820885[___267756245(45)][$_1080570707]= $_734079028; $_1601131268= $GLOBALS['____1626405312'][29](self::$_271820885); $_1601131268= $GLOBALS['____1626405312'][30]($_1601131268); COption::SetOptionString(___267756245(46), ___267756245(47), $_1601131268); if($_430672946 && $_994353113) self::__2034484057($_1080570707, $_734079028);} private static function __1868845070($_2101519942){ if($GLOBALS['____1626405312'][31]($_2101519942) <= 0 || $_2101519942 == "Portal") return; self::__1780282462(); if(!$GLOBALS['____1626405312'][32]($_2101519942, self::$_271820885[___267756245(48)]) || $GLOBALS['____1626405312'][33]($_2101519942, self::$_271820885[___267756245(49)]) && self::$_271820885[___267756245(50)][$_2101519942][(934-2*467)] != ___267756245(51)) return; if(isset(self::$_271820885[___267756245(52)][$_2101519942][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) && self::$_271820885[___267756245(53)][$_2101519942][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) return; $_1753139624= array(); if($GLOBALS['____1626405312'][34]($_2101519942, self::$_162300837) && $GLOBALS['____1626405312'][35](self::$_162300837[$_2101519942])){ foreach(self::$_162300837[$_2101519942] as $_1080570707){ if($GLOBALS['____1626405312'][36]($_1080570707, self::$_271820885[___267756245(54)]) && self::$_271820885[___267756245(55)][$_1080570707]){ self::$_271820885[___267756245(56)][$_1080570707]= false; $_1753139624[]= array($_1080570707, false);}} self::$_271820885[___267756245(57)][$_2101519942][round(0+2)]= true;} $_1601131268= $GLOBALS['____1626405312'][37](self::$_271820885); $_1601131268= $GLOBALS['____1626405312'][38]($_1601131268); COption::SetOptionString(___267756245(58), ___267756245(59), $_1601131268); foreach($_1753139624 as $_1392581768) self::__2034484057($_1392581768[(1232/2-616)], $_1392581768[round(0+0.25+0.25+0.25+0.25)]);} public static function ModifyFeaturesSettings($_1618263261, $_1304075273){ self::__1780282462(); foreach($_1618263261 as $_2101519942 => $_1184881789) self::$_271820885[___267756245(60)][$_2101519942]= $_1184881789; $_1753139624= array(); foreach($_1304075273 as $_1080570707 => $_734079028){ if(!$GLOBALS['____1626405312'][39]($_1080570707, self::$_271820885[___267756245(61)]) && $_734079028 || $GLOBALS['____1626405312'][40]($_1080570707, self::$_271820885[___267756245(62)]) && $_734079028 != self::$_271820885[___267756245(63)][$_1080570707]) $_1753139624[]= array($_1080570707, $_734079028); self::$_271820885[___267756245(64)][$_1080570707]= $_734079028;} $_1601131268= $GLOBALS['____1626405312'][41](self::$_271820885); $_1601131268= $GLOBALS['____1626405312'][42]($_1601131268); COption::SetOptionString(___267756245(65), ___267756245(66), $_1601131268); self::$_271820885= false; foreach($_1753139624 as $_1392581768) self::__2034484057($_1392581768[(784-2*392)], $_1392581768[round(0+0.5+0.5)]);} public static function SaveFeaturesSettings($_1665406632, $_1743131762){ self::__1780282462(); $_943654240= array(___267756245(67) => array(), ___267756245(68) => array()); if(!$GLOBALS['____1626405312'][43]($_1665406632)) $_1665406632= array(); if(!$GLOBALS['____1626405312'][44]($_1743131762)) $_1743131762= array(); if(!$GLOBALS['____1626405312'][45](___267756245(69), $_1665406632)) $_1665406632[]= ___267756245(70); foreach(self::$_162300837 as $_2101519942 => $_1304075273){ if($GLOBALS['____1626405312'][46]($_2101519942, self::$_271820885[___267756245(71)])) $_1378418010= self::$_271820885[___267756245(72)][$_2101519942]; else $_1378418010=($_2101519942 == ___267756245(73))? array(___267756245(74)): array(___267756245(75)); if($_1378418010[(127*2-254)] == ___267756245(76) || $_1378418010[(235*2-470)] == ___267756245(77)){ $_943654240[___267756245(78)][$_2101519942]= $_1378418010;} else{ if($GLOBALS['____1626405312'][47]($_2101519942, $_1665406632)) $_943654240[___267756245(79)][$_2101519942]= array(___267756245(80), $GLOBALS['____1626405312'][48]((1108/2-554), min(40,0,13.333333333333), min(168,0,56), $GLOBALS['____1626405312'][49](___267756245(81)), $GLOBALS['____1626405312'][50](___267756245(82)), $GLOBALS['____1626405312'][51](___267756245(83)))); else $_943654240[___267756245(84)][$_2101519942]= array(___267756245(85));}} $_1753139624= array(); foreach(self::$_833205792 as $_1080570707 => $_2101519942){ if($_943654240[___267756245(86)][$_2101519942][(224*2-448)] != ___267756245(87) && $_943654240[___267756245(88)][$_2101519942][(156*2-312)] != ___267756245(89)){ $_943654240[___267756245(90)][$_1080570707]= false;} else{ if($_943654240[___267756245(91)][$_2101519942][(1332/2-666)] == ___267756245(92) && $_943654240[___267756245(93)][$_2101519942][round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____1626405312'][52]((942-2*471),(1428/2-714), min(68,0,22.666666666667), Date(___267756245(94)), $GLOBALS['____1626405312'][53](___267756245(95))- self::$_639768302, $GLOBALS['____1626405312'][54](___267756245(96)))) $_943654240[___267756245(97)][$_1080570707]= false; else $_943654240[___267756245(98)][$_1080570707]= $GLOBALS['____1626405312'][55]($_1080570707, $_1743131762); if(!$GLOBALS['____1626405312'][56]($_1080570707, self::$_271820885[___267756245(99)]) && $_943654240[___267756245(100)][$_1080570707] || $GLOBALS['____1626405312'][57]($_1080570707, self::$_271820885[___267756245(101)]) && $_943654240[___267756245(102)][$_1080570707] != self::$_271820885[___267756245(103)][$_1080570707]) $_1753139624[]= array($_1080570707, $_943654240[___267756245(104)][$_1080570707]);}} $_1601131268= $GLOBALS['____1626405312'][58]($_943654240); $_1601131268= $GLOBALS['____1626405312'][59]($_1601131268); COption::SetOptionString(___267756245(105), ___267756245(106), $_1601131268); self::$_271820885= false; foreach($_1753139624 as $_1392581768) self::__2034484057($_1392581768[(149*2-298)], $_1392581768[round(0+0.5+0.5)]);} public static function GetFeaturesList(){ self::__1780282462(); $_31057768= array(); foreach(self::$_162300837 as $_2101519942 => $_1304075273){ if($GLOBALS['____1626405312'][60]($_2101519942, self::$_271820885[___267756245(107)])) $_1378418010= self::$_271820885[___267756245(108)][$_2101519942]; else $_1378418010=($_2101519942 == ___267756245(109))? array(___267756245(110)): array(___267756245(111)); $_31057768[$_2101519942]= array( ___267756245(112) => $_1378418010[(1368/2-684)], ___267756245(113) => $_1378418010[round(0+0.5+0.5)], ___267756245(114) => array(),); $_31057768[$_2101519942][___267756245(115)]= false; if($_31057768[$_2101519942][___267756245(116)] == ___267756245(117)){ $_31057768[$_2101519942][___267756245(118)]= $GLOBALS['____1626405312'][61](($GLOBALS['____1626405312'][62]()- $_31057768[$_2101519942][___267756245(119)])/ round(0+17280+17280+17280+17280+17280)); if($_31057768[$_2101519942][___267756245(120)]> self::$_639768302) $_31057768[$_2101519942][___267756245(121)]= true;} foreach($_1304075273 as $_1080570707) $_31057768[$_2101519942][___267756245(122)][$_1080570707]=(!$GLOBALS['____1626405312'][63]($_1080570707, self::$_271820885[___267756245(123)]) || self::$_271820885[___267756245(124)][$_1080570707]);} return $_31057768;} private static function __1912375221($_834114820, $_1710702069){ if(IsModuleInstalled($_834114820) == $_1710702069) return true; $_1753810392= $_SERVER[___267756245(125)].___267756245(126).$_834114820.___267756245(127); if(!$GLOBALS['____1626405312'][64]($_1753810392)) return false; include_once($_1753810392); $_1797380734= $GLOBALS['____1626405312'][65](___267756245(128), ___267756245(129), $_834114820); if(!$GLOBALS['____1626405312'][66]($_1797380734)) return false; $_1818773214= new $_1797380734; if($_1710702069){ if(!$_1818773214->InstallDB()) return false; $_1818773214->InstallEvents(); if(!$_1818773214->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___267756245(130))) CSearch::DeleteIndex($_834114820); UnRegisterModule($_834114820);} return true;} protected static function OnRequestsSettingsChange($_1080570707, $_734079028){ self::__1912375221("form", $_734079028);} protected static function OnLearningSettingsChange($_1080570707, $_734079028){ self::__1912375221("learning", $_734079028);} protected static function OnJabberSettingsChange($_1080570707, $_734079028){ self::__1912375221("xmpp", $_734079028);} protected static function OnVideoConferenceSettingsChange($_1080570707, $_734079028){ self::__1912375221("video", $_734079028);} protected static function OnBizProcSettingsChange($_1080570707, $_734079028){ self::__1912375221("bizprocdesigner", $_734079028);} protected static function OnListsSettingsChange($_1080570707, $_734079028){ self::__1912375221("lists", $_734079028);} protected static function OnWikiSettingsChange($_1080570707, $_734079028){ self::__1912375221("wiki", $_734079028);} protected static function OnSupportSettingsChange($_1080570707, $_734079028){ self::__1912375221("support", $_734079028);} protected static function OnControllerSettingsChange($_1080570707, $_734079028){ self::__1912375221("controller", $_734079028);} protected static function OnAnalyticsSettingsChange($_1080570707, $_734079028){ self::__1912375221("statistic", $_734079028);} protected static function OnVoteSettingsChange($_1080570707, $_734079028){ self::__1912375221("vote", $_734079028);} protected static function OnFriendsSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(131); $_1319994556= CSite::GetList(($_1378416546= ___267756245(132)),($_380200818= ___267756245(133)), array(___267756245(134) => ___267756245(135))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(136), ___267756245(137), ___267756245(138), $_765234292[___267756245(139)]) != $_539981973){ COption::SetOptionString(___267756245(140), ___267756245(141), $_539981973, false, $_765234292[___267756245(142)]); COption::SetOptionString(___267756245(143), ___267756245(144), $_539981973);}}} protected static function OnMicroBlogSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(145); $_1319994556= CSite::GetList(($_1378416546= ___267756245(146)),($_380200818= ___267756245(147)), array(___267756245(148) => ___267756245(149))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(150), ___267756245(151), ___267756245(152), $_765234292[___267756245(153)]) != $_539981973){ COption::SetOptionString(___267756245(154), ___267756245(155), $_539981973, false, $_765234292[___267756245(156)]); COption::SetOptionString(___267756245(157), ___267756245(158), $_539981973);} if(COption::GetOptionString(___267756245(159), ___267756245(160), ___267756245(161), $_765234292[___267756245(162)]) != $_539981973){ COption::SetOptionString(___267756245(163), ___267756245(164), $_539981973, false, $_765234292[___267756245(165)]); COption::SetOptionString(___267756245(166), ___267756245(167), $_539981973);}}} protected static function OnPersonalFilesSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(168); $_1319994556= CSite::GetList(($_1378416546= ___267756245(169)),($_380200818= ___267756245(170)), array(___267756245(171) => ___267756245(172))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(173), ___267756245(174), ___267756245(175), $_765234292[___267756245(176)]) != $_539981973){ COption::SetOptionString(___267756245(177), ___267756245(178), $_539981973, false, $_765234292[___267756245(179)]); COption::SetOptionString(___267756245(180), ___267756245(181), $_539981973);}}} protected static function OnPersonalBlogSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(182); $_1319994556= CSite::GetList(($_1378416546= ___267756245(183)),($_380200818= ___267756245(184)), array(___267756245(185) => ___267756245(186))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(187), ___267756245(188), ___267756245(189), $_765234292[___267756245(190)]) != $_539981973){ COption::SetOptionString(___267756245(191), ___267756245(192), $_539981973, false, $_765234292[___267756245(193)]); COption::SetOptionString(___267756245(194), ___267756245(195), $_539981973);}}} protected static function OnPersonalPhotoSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(196); $_1319994556= CSite::GetList(($_1378416546= ___267756245(197)),($_380200818= ___267756245(198)), array(___267756245(199) => ___267756245(200))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(201), ___267756245(202), ___267756245(203), $_765234292[___267756245(204)]) != $_539981973){ COption::SetOptionString(___267756245(205), ___267756245(206), $_539981973, false, $_765234292[___267756245(207)]); COption::SetOptionString(___267756245(208), ___267756245(209), $_539981973);}}} protected static function OnPersonalForumSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(210); $_1319994556= CSite::GetList(($_1378416546= ___267756245(211)),($_380200818= ___267756245(212)), array(___267756245(213) => ___267756245(214))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(215), ___267756245(216), ___267756245(217), $_765234292[___267756245(218)]) != $_539981973){ COption::SetOptionString(___267756245(219), ___267756245(220), $_539981973, false, $_765234292[___267756245(221)]); COption::SetOptionString(___267756245(222), ___267756245(223), $_539981973);}}} protected static function OnTasksSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(224); $_1319994556= CSite::GetList(($_1378416546= ___267756245(225)),($_380200818= ___267756245(226)), array(___267756245(227) => ___267756245(228))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(229), ___267756245(230), ___267756245(231), $_765234292[___267756245(232)]) != $_539981973){ COption::SetOptionString(___267756245(233), ___267756245(234), $_539981973, false, $_765234292[___267756245(235)]); COption::SetOptionString(___267756245(236), ___267756245(237), $_539981973);} if(COption::GetOptionString(___267756245(238), ___267756245(239), ___267756245(240), $_765234292[___267756245(241)]) != $_539981973){ COption::SetOptionString(___267756245(242), ___267756245(243), $_539981973, false, $_765234292[___267756245(244)]); COption::SetOptionString(___267756245(245), ___267756245(246), $_539981973);}} self::__1912375221(___267756245(247), $_734079028);} protected static function OnCalendarSettingsChange($_1080570707, $_734079028){ if($_734079028) $_539981973= "Y"; else $_539981973= ___267756245(248); $_1319994556= CSite::GetList(($_1378416546= ___267756245(249)),($_380200818= ___267756245(250)), array(___267756245(251) => ___267756245(252))); while($_765234292= $_1319994556->Fetch()){ if(COption::GetOptionString(___267756245(253), ___267756245(254), ___267756245(255), $_765234292[___267756245(256)]) != $_539981973){ COption::SetOptionString(___267756245(257), ___267756245(258), $_539981973, false, $_765234292[___267756245(259)]); COption::SetOptionString(___267756245(260), ___267756245(261), $_539981973);} if(COption::GetOptionString(___267756245(262), ___267756245(263), ___267756245(264), $_765234292[___267756245(265)]) != $_539981973){ COption::SetOptionString(___267756245(266), ___267756245(267), $_539981973, false, $_765234292[___267756245(268)]); COption::SetOptionString(___267756245(269), ___267756245(270), $_539981973);}}} protected static function OnSMTPSettingsChange($_1080570707, $_734079028){ self::__1912375221("mail", $_734079028);} protected static function OnExtranetSettingsChange($_1080570707, $_734079028){ $_1224727677= COption::GetOptionString("extranet", "extranet_site", ""); if($_1224727677){ $_261619384= new CSite; $_261619384->Update($_1224727677, array(___267756245(271) =>($_734079028? ___267756245(272): ___267756245(273))));} self::__1912375221(___267756245(274), $_734079028);} protected static function OnDAVSettingsChange($_1080570707, $_734079028){ self::__1912375221("dav", $_734079028);} protected static function OntimemanSettingsChange($_1080570707, $_734079028){ self::__1912375221("timeman", $_734079028);} protected static function Onintranet_sharepointSettingsChange($_1080570707, $_734079028){ if($_734079028){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___267756245(275), ___267756245(276), ___267756245(277), ___267756245(278), ___267756245(279)); CAgent::AddAgent(___267756245(280), ___267756245(281), ___267756245(282), round(0+250+250)); CAgent::AddAgent(___267756245(283), ___267756245(284), ___267756245(285), round(0+300)); CAgent::AddAgent(___267756245(286), ___267756245(287), ___267756245(288), round(0+900+900+900+900));} else{ UnRegisterModuleDependences(___267756245(289), ___267756245(290), ___267756245(291), ___267756245(292), ___267756245(293)); UnRegisterModuleDependences(___267756245(294), ___267756245(295), ___267756245(296), ___267756245(297), ___267756245(298)); CAgent::RemoveAgent(___267756245(299), ___267756245(300)); CAgent::RemoveAgent(___267756245(301), ___267756245(302)); CAgent::RemoveAgent(___267756245(303), ___267756245(304));}} protected static function OncrmSettingsChange($_1080570707, $_734079028){ if($_734079028) COption::SetOptionString("crm", "form_features", "Y"); self::__1912375221(___267756245(305), $_734079028);} protected static function OnClusterSettingsChange($_1080570707, $_734079028){ self::__1912375221("cluster", $_734079028);} protected static function OnMultiSitesSettingsChange($_1080570707, $_734079028){ if($_734079028) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___267756245(306), ___267756245(307), ___267756245(308), ___267756245(309), ___267756245(310), ___267756245(311));} protected static function OnIdeaSettingsChange($_1080570707, $_734079028){ self::__1912375221("idea", $_734079028);} protected static function OnMeetingSettingsChange($_1080570707, $_734079028){ self::__1912375221("meeting", $_734079028);} protected static function OnXDImportSettingsChange($_1080570707, $_734079028){ self::__1912375221("xdimport", $_734079028);}} $GLOBALS['____1626405312'][67](___267756245(312), ___267756245(313));/**/			//Do not remove this

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
					CMain::FinalActions();
					echo '<script type="text/javascript">window.onload=function(){top.BX.AUTHAGENT.setAuthResult(false);};</script>';
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

/*ZDUyZmZNDVmZmY2M2I3MWI4YzE0MmUwZWI0YjQxM2I1MDU5YmQ=*/$GLOBALS['____1405324153']= array(base64_decode('bXRf'.'cmFu'.'ZA=='),base64_decode('ZXhwbG9'.'kZ'.'Q'.'=='),base64_decode('cGFja'.'w=='),base64_decode('bWQ1'),base64_decode(''.'Y29uc'.'3Rh'.'b'.'nQ='),base64_decode('aGFzaF9obWFj'),base64_decode('c'.'3'.'RyY21w'),base64_decode('aX'.'N'.'fb'.'2JqZWN0'),base64_decode('Y2F'.'sbF9'.'1c'.'2'.'VyX'.'2Z1bm'.'M='),base64_decode(''.'Y2'.'Fs'.'bF'.'91'.'c2VyX'.'2Z1'.'bmM='),base64_decode(''.'Y2FsbF'.'91c2'.'VyX'.'2'.'Z'.'1b'.'mM='),base64_decode('Y2F'.'sbF91'.'c2VyX2'.'Z1'.'bmM'.'='),base64_decode('Y2F'.'sbF9'.'1c2V'.'yX'.'2Z'.'1b'.'mM='));if(!function_exists(__NAMESPACE__.'\\___1764052905')){function ___1764052905($_1099650707){static $_909838679= false; if($_909838679 == false) $_909838679=array(''.'REI=',''.'U0VMRU'.'NU'.'IFZBT'.'F'.'VFI'.'E'.'ZST0'.'0gYl9vcHR'.'pb24'.'gV0hFUk'.'UgTkFNRT0nflBB'.'Uk'.'F'.'N'.'X01BWF9VU0VSUycg'.'QU5EIE1PRFVMRV9JRD0'.'nbWF'.'pbicg'.'Q'.'U5EIFNJVE'.'VfS'.'U'.'QgS'.'VMgTl'.'VM'.'TA==','VkFMVUU=','Lg==','SCo=',''.'Yml0cml4','TElD'.'RU5TRV'.'9LR'.'Vk=','c2hhMjU2','VV'.'NFUg'.'==','V'.'V'.'NF'.'Ug==','VVNF'.'Ug==','S'.'XNBdXRo'.'b'.'3Jp'.'e'.'mVk','VVNFUg==','SXNBZG'.'1pbg==','QVBQTElDQVRJT0'.'4=','Um'.'VzdGFydEJ1'.'ZmZlcg==','TG9jYWx'.'SZWRpcmVjdA==','L2xpY2Vuc2VfcmV'.'zd'.'H'.'JpY3'.'Rpb24uc'.'Ghw','X'.'EJpdHJpe'.'F'.'xNYWluX'.'ENvbmZ'.'pZ1xPcH'.'Rp'.'b246On'.'N'.'ld'.'A==','bWFpbg==','UEFSQU1'.'fT'.'UFYX1VTR'.'VJT');return base64_decode($_909838679[$_1099650707]);}};if($GLOBALS['____1405324153'][0](round(0+1), round(0+6.6666666666667+6.6666666666667+6.6666666666667)) == round(0+7)){ $_1381666472= $GLOBALS[___1764052905(0)]->Query(___1764052905(1), true); if($_340882145= $_1381666472->Fetch()){ $_1075965047= $_340882145[___1764052905(2)]; list($_590409407, $_2042240975)= $GLOBALS['____1405324153'][1](___1764052905(3), $_1075965047); $_1867589207= $GLOBALS['____1405324153'][2](___1764052905(4), $_590409407); $_1120583807= ___1764052905(5).$GLOBALS['____1405324153'][3]($GLOBALS['____1405324153'][4](___1764052905(6))); $_694948661= $GLOBALS['____1405324153'][5](___1764052905(7), $_2042240975, $_1120583807, true); if($GLOBALS['____1405324153'][6]($_694948661, $_1867589207) !==(776-2*388)){ if(isset($GLOBALS[___1764052905(8)]) && $GLOBALS['____1405324153'][7]($GLOBALS[___1764052905(9)]) && $GLOBALS['____1405324153'][8](array($GLOBALS[___1764052905(10)], ___1764052905(11))) &&!$GLOBALS['____1405324153'][9](array($GLOBALS[___1764052905(12)], ___1764052905(13)))){ $GLOBALS['____1405324153'][10](array($GLOBALS[___1764052905(14)], ___1764052905(15))); $GLOBALS['____1405324153'][11](___1764052905(16), ___1764052905(17), true);}}} else{ $GLOBALS['____1405324153'][12](___1764052905(18), ___1764052905(19), ___1764052905(20), round(0+3+3+3+3));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
