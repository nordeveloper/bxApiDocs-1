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

/*ZDUyZmZYTBiN2M5ZDc4MWUyNmY2YWYzMDNhYTA3YWU0MDU5MDA=*/$GLOBALS['_____547179995']= array(base64_decode('R2V0TW9kdWxlRXZlbnRz'),base64_decode('RX'.'hlY3'.'V0ZU1vZHVsZUV2ZW50RX'.'g='));$GLOBALS['____1038174309']= array(base64_decode('ZGVmaW5'.'l'),base64_decode('c'.'3Ry'.'bG'.'Vu'),base64_decode('YmFzZ'.'TY0X2R'.'lY29k'.'ZQ='.'='),base64_decode('dW5zZXJpYWx'.'pemU='),base64_decode(''.'a'.'XNfYX'.'Jy'.'YXk='),base64_decode('Y2'.'91bn'.'Q'.'='),base64_decode('aW'.'5fYX'.'JyYX'.'k='),base64_decode('c2V'.'yaWFs'.'aX'.'pl'),base64_decode('YmFz'.'ZTY0X2V'.'uY29'.'kZQ='.'='),base64_decode('c3RybGVu'),base64_decode('YXJyYXlfa2V5X2V4aX'.'N0c'.'w='.'='),base64_decode('YXJyYX'.'lfa2'.'V5X2V4aX'.'N0c'.'w=='),base64_decode('bWt'.'0'.'aW1l'),base64_decode(''.'ZGF0ZQ=='),base64_decode('Z'.'G'.'F'.'0ZQ=='),base64_decode('Y'.'X'.'JyYXlfa'.'2V5X2V4a'.'X'.'N'.'0cw=='),base64_decode('c'.'3RybGVu'),base64_decode('YXJyYXlfa'.'2V5X2V4aXN0'.'cw='.'='),base64_decode('c'.'3'.'R'.'yb'.'G'.'Vu'),base64_decode('YXJy'.'YXlfa'.'2'.'V5X2V'.'4aXN0cw=='),base64_decode('YXJy'.'YXlf'.'a'.'2V5'.'X2V4aX'.'N0'.'cw=='),base64_decode('bWt0aW1l'),base64_decode('ZG'.'F'.'0'.'ZQ'.'=='),base64_decode(''.'ZGF0ZQ=='),base64_decode('bWV0aG9kX2V4aX'.'N'.'0cw='.'='),base64_decode(''.'Y2FsbF91c2Vy'.'X'.'2'.'Z1bmN'.'f'.'YXJ'.'y'.'YXk='),base64_decode('c3'.'Ryb'.'GVu'),base64_decode('YXJyY'.'Xlfa'.'2V5X2'.'V'.'4a'.'XN0cw=='),base64_decode('YXJyYXlfa2V5X2V4aXN0cw'.'=='),base64_decode('c2VyaWF'.'saXp'.'l'),base64_decode('YmFzZTY'.'0'.'X2'.'VuY29kZQ=='),base64_decode('c'.'3R'.'yb'.'GV'.'u'),base64_decode(''.'YXJyYXlf'.'a2V'.'5X2V'.'4aX'.'N0'.'c'.'w=='),base64_decode(''.'YXJy'.'YXlf'.'a2V5X2'.'V4aXN0cw=='),base64_decode('YXJyY'.'Xlfa'.'2V5'.'X'.'2V4aXN0cw=='),base64_decode(''.'a'.'XNfYXJy'.'YXk='),base64_decode('YX'.'JyYXlfa2V'.'5X'.'2V4aXN0cw=='),base64_decode('c2VyaWFsa'.'Xpl'),base64_decode('YmF'.'zZTY0X2VuY29'.'kZQ=='),base64_decode('YXJ'.'yYXl'.'fa2'.'V5'.'X2V4aXN'.'0cw=='),base64_decode('YXJyYXl'.'fa2V5X2'.'V4'.'aX'.'N0cw=='),base64_decode(''.'c'.'2V'.'y'.'aWFs'.'a'.'X'.'pl'),base64_decode(''.'YmFzZ'.'TY'.'0X'.'2Vu'.'Y2'.'9kZ'.'Q='.'='),base64_decode('aXNfYXJyYX'.'k='),base64_decode('aX'.'Nf'.'YX'.'JyYXk='),base64_decode('aW5fY'.'XJ'.'yYX'.'k='),base64_decode('YX'.'J'.'yYXlf'.'a'.'2V5X2V'.'4aXN'.'0cw='.'='),base64_decode('aW5fYXJyYXk'.'='),base64_decode('b'.'Wt0aW1'.'l'),base64_decode('ZGF0'.'ZQ=='),base64_decode('ZGF0Z'.'Q'.'=='),base64_decode('Z'.'GF0'.'ZQ=='),base64_decode('bWt0a'.'W1l'),base64_decode('Z'.'G'.'F'.'0ZQ=='),base64_decode('ZG'.'F0'.'ZQ='.'='),base64_decode('aW5fY'.'XJyYXk='),base64_decode('YXJyYXlfa2V'.'5X2V'.'4aXN0cw=='),base64_decode('YXJyYXl'.'fa2'.'V5X'.'2'.'V4aXN0cw=='),base64_decode('c2VyaWFsaXp'.'l'),base64_decode('YmFzZTY0X'.'2VuY29kZQ='.'='),base64_decode('YXJyY'.'Xlfa2V5X2V'.'4aX'.'N0cw=='),base64_decode('aW'.'50d'.'mFs'),base64_decode('dGl'.'tZ'.'Q=='),base64_decode('YXJyYXl'.'fa2V'.'5X2V'.'4'.'a'.'XN0'.'cw=='),base64_decode(''.'Zm'.'ls'.'ZV9'.'leGl'.'zdHM='),base64_decode('c3RyX3'.'Jl'.'cGx'.'hY2'.'U'.'='),base64_decode('Y2x'.'hc3N'.'fZXhpc3Rz'),base64_decode('ZGVmaW5'.'l'));if(!function_exists(__NAMESPACE__.'\\___903390100')){function ___903390100($_6405298){static $_197562817= false; if($_197562817 == false) $_197562817=array('S'.'U5UUkFORVRf'.'RU'.'RJVElPTg==','WQ==','bWFpbg==',''.'fmNwZ'.'l9tYXBfdmFsdWU'.'=','','ZQ==','Z'.'g'.'==','Z'.'Q==','Rg'.'==',''.'WA'.'==','Zg==','bWF'.'pbg==','fmNwZ'.'l9tYXBfdmFsdWU=',''.'UG9'.'ydGFs','Rg==','ZQ==','ZQ==','WA'.'==','R'.'g==',''.'RA==','RA==','bQ==','ZA==','WQ'.'==','Zg==','Zg==','Zg'.'==','Z'.'g==','UG9ydGFs','Rg==','ZQ==',''.'ZQ==','WA='.'=',''.'Rg'.'='.'=','RA==','R'.'A==','bQ='.'=','ZA='.'=','WQ'.'==','b'.'WFpbg==','T24=','U2V0d'.'GluZ'.'3N'.'DaGF'.'uZ2U=',''.'Zg==','Zg==','Zg'.'==','Zg==','bWFpbg==','fmN'.'wZl'.'9tYXB'.'fd'.'m'.'Fs'.'dWU=','ZQ==','ZQ='.'=','Z'.'Q==','RA==','ZQ==','Z'.'Q==','Z'.'g='.'=','Zg='.'=',''.'Zg'.'==','ZQ='.'=','b'.'WF'.'pbg'.'==','fmNwZl9tY'.'XB'.'fdm'.'F'.'sdWU=','ZQ==','Zg='.'=',''.'Zg==','Zg==','Z'.'g==',''.'bW'.'F'.'p'.'bg'.'==','fmNwZl9tY'.'XBfdm'.'F'.'sdWU'.'=','ZQ==','Zg==','UG9ydGFs','U'.'G9ydGFs',''.'ZQ='.'=','ZQ==','UG9yd'.'GFs',''.'Rg==','WA==',''.'Rg'.'='.'=','RA==','ZQ==','Z'.'Q'.'==','RA==',''.'bQ==','Z'.'A='.'=','WQ'.'==',''.'ZQ==',''.'WA==','ZQ==','Rg==',''.'Z'.'Q==',''.'RA==','Zg==','ZQ'.'==','RA==','ZQ==',''.'b'.'Q==','ZA==','WQ==',''.'Zg==','Zg==','Zg==','Zg'.'==','Zg'.'==','Z'.'g==','Z'.'g'.'==',''.'Zg==','bWF'.'pbg==','fm'.'NwZl9tYXBfdmF'.'sdWU=','ZQ'.'==','Z'.'Q==','UG9ydGFs','Rg==','WA==',''.'VFlQR'.'Q'.'==','REFURQ==',''.'RkVBV'.'FVSRVM=','RVhQSVJFRA==','VFlQRQ==',''.'RA==','VFJ'.'ZX0RBWVNfQ09'.'V'.'TlQ=','REFU'.'RQ==','V'.'FJZX'.'0RB'.'WV'.'NfQ09'.'VTlQ=','R'.'VhQSVJFRA==',''.'RkVB'.'VFVSRVM=','Zg==',''.'Zg==','RE9'.'DV'.'U1FTlR'.'fUk9PVA'.'==','L2JpdHJ'.'pe'.'C9t'.'b2R1bGVzLw==','L2lu'.'c'.'3Rhb'.'GwvaW5kZXgucGhw','Lg'.'==',''.'X'.'w==','c2VhcmNo','T'.'g==','','','QUNUS'.'VZF',''.'WQ==',''.'c29jaWFsbmV0d29yaw'.'='.'=','YWxsb3dfZ'.'nJpZWxkcw==','WQ==','SUQ'.'=','c'.'29jaW'.'FsbmV'.'0d'.'29ya'.'w==','YW'.'xsb'.'3df'.'Z'.'n'.'JpZWx'.'kcw'.'==',''.'SUQ=','c29j'.'aWFs'.'b'.'m'.'V0'.'d2'.'9yaw==','YWxsb3dfZnJpZ'.'Wxk'.'cw==','Tg==','','','QUNU'.'S'.'VZF','WQ==','c29'.'jaWFsbmV'.'0d'.'29y'.'aw==','YWxsb3dfbW'.'l'.'jc'.'m9'.'ibG9'.'nX3VzZ'.'XI=',''.'WQ='.'=','S'.'UQ=','c2'.'9jaWFsbmV0d2'.'9y'.'aw==',''.'Y'.'Wxsb3dfbWlj'.'cm'.'9ib'.'G9n'.'X3VzZXI'.'=','SU'.'Q=','c29j'.'aWFsbmV'.'0d29'.'ya'.'w==','YWx'.'sb3dfbW'.'ljcm9i'.'bG9nX3VzZX'.'I=','c2'.'9'.'jaWFsbmV'.'0d2'.'9ya'.'w==',''.'YWxsb3'.'d'.'fbWljc'.'m9ibG'.'9nX'.'2dyb'.'3V'.'w','WQ='.'=','SU'.'Q'.'=','c29jaWFsbm'.'V0d2'.'9ya'.'w==','Y'.'Wxsb3dfbWl'.'jcm9ibG9'.'nX2'.'dy'.'b3Vw','SUQ'.'=',''.'c29jaWFsb'.'mV0'.'d2'.'9yaw==','YWxs'.'b3dfbWl'.'jcm9ibG9'.'nX'.'2d'.'yb3'.'Vw','Tg'.'==','','','QUNU'.'SV'.'ZF','WQ==',''.'c29'.'jaW'.'Fs'.'bmV0d29'.'ya'.'w='.'=','Y'.'W'.'xsb3dfZm'.'l'.'sZX'.'N'.'f'.'dXNlc'.'g==','WQ'.'='.'=','S'.'UQ=','c29jaWFsbm'.'V0d29ya'.'w'.'==','YWxsb3'.'dfZmlsZXNfd'.'XNlcg==',''.'SUQ'.'=','c29jaW'.'F'.'sbmV0'.'d29yaw==',''.'Y'.'Wxsb3'.'df'.'Z'.'m'.'ls'.'ZXNfdXNlcg='.'=','Tg==','','','QU'.'NUSV'.'ZF','WQ==',''.'c29jaW'.'FsbmV'.'0'.'d29ya'.'w'.'==','Y'.'W'.'xsb3dfYmxvZ1'.'91c2Vy','WQ'.'==','SUQ=','c'.'29'.'jaW'.'Fsb'.'mV0d29yaw==','Y'.'W'.'xsb3dfY'.'m'.'xvZ'.'191c2'.'Vy','SUQ'.'=',''.'c'.'29ja'.'WFsb'.'mV'.'0d'.'29yaw==','YWxsb'.'3df'.'Ym'.'xvZ191c2Vy','T'.'g==','','','QUNUSV'.'ZF','WQ==','c'.'29jaWF'.'sbmV0d29'.'yaw==','YWxsb3dfcGhvd'.'G9'.'fdXNlcg==','W'.'Q'.'==',''.'S'.'UQ=','c29'.'j'.'aW'.'F'.'sbmV0d'.'2'.'9ya'.'w='.'=','YWxsb3dfcGh'.'vdG9fdXNlcg==','S'.'UQ=',''.'c2'.'9jaWFsb'.'mV0d29'.'yaw==','YWxsb3df'.'c'.'GhvdG9fdXNlcg==','Tg==','','','QUNU'.'SVZF','W'.'Q==','c29jaW'.'Fsbm'.'V0d29'.'yaw==','Y'.'W'.'x'.'sb3dfZm9ydW1fdXNl'.'cg==','WQ='.'=','SUQ'.'=','c29'.'jaW'.'Fs'.'bmV0d29ya'.'w==','YW'.'xsb3dfZm9ydW1fd'.'XNlcg==','S'.'UQ=','c29jaWF'.'s'.'bmV0d29ya'.'w==','YWxs'.'b3'.'d'.'f'.'Zm'.'9'.'ydW1fdXNlcg='.'=','Tg==','','','QU'.'NUSV'.'ZF',''.'WQ==','c29jaWF'.'sbmV0'.'d29'.'ya'.'w==','Y'.'W'.'xsb3dfdGFza'.'3NfdXNlcg='.'=','WQ==',''.'SU'.'Q=',''.'c29j'.'aWFsbm'.'V0d29y'.'aw'.'==','Y'.'Wxsb'.'3dfdGFz'.'a3NfdXNlcg==','SUQ=','c2'.'9ja'.'WFsbmV'.'0d'.'2'.'9y'.'aw==','YWxsb3d'.'fdGFz'.'a3NfdXN'.'lcg==','c29jaWFsbmV0d2'.'9yaw==','YWx'.'s'.'b3dfdGFza3NfZ3Jvd'.'XA'.'=',''.'W'.'Q'.'==','SU'.'Q'.'=','c29jaWFs'.'bmV0d'.'2'.'9y'.'a'.'w'.'==','YWxsb3dfdGFza3NfZ'.'3JvdX'.'A=','SU'.'Q=','c29j'.'aWFsbmV0d29yaw==',''.'YW'.'xsb3dfdG'.'Fza3Nf'.'Z3JvdXA=',''.'dGFza'.'3M=','Tg==','','','QUNUSV'.'Z'.'F',''.'WQ'.'==','c29'.'ja'.'WF'.'s'.'b'.'mV0'.'d'.'29yaw'.'==',''.'YWxs'.'b3'.'dfY'.'2'.'FsZ'.'W'.'5'.'kYXJfdXN'.'l'.'cg==',''.'WQ='.'=','SUQ=','c29jaWFsb'.'mV0'.'d2'.'9yaw'.'='.'=',''.'YWxsb3d'.'f'.'Y2'.'FsZW5kYX'.'J'.'fdXNlcg'.'==',''.'SUQ=',''.'c2'.'9jaWFsbmV0d29ya'.'w'.'='.'=','YWxsb3'.'dfY'.'2FsZ'.'W5'.'kYX'.'JfdXN'.'lc'.'g==','c2'.'9jaWFsbmV0'.'d29yaw==','YWxs'.'b3d'.'f'.'Y2FsZW5kYXJ'.'fZ3'.'JvdXA=','WQ==','SU'.'Q=','c'.'29'.'jaWFsb'.'mV0d29yaw==','YWx'.'s'.'b3dfY2FsZW5'.'kY'.'XJfZ3JvdXA=','SUQ=',''.'c'.'29j'.'aWFsbm'.'V'.'0d29y'.'aw==',''.'YWxsb3dfY2FsZW'.'5k'.'Y'.'XJf'.'Z3J'.'vdXA=','QU'.'NUSVZF','WQ='.'=','Tg==',''.'ZXh0c'.'mFuZX'.'Q'.'=','aW'.'Js'.'b2'.'N'.'r','T2'.'5BZ'.'nRlcklCbG9ja0VsZW1lbnRV'.'cGRhdG'.'U=','aW5'.'0'.'c'.'m'.'FuZX'.'Q=','Q0ludHJhbmV0RX'.'ZlbnRIYW'.'5kbGV'.'ycw'.'==','U1BSZWd'.'pc3Rlcl'.'VwZGF0Z'.'WRJd'.'GVt','Q0'.'ludHJhbmV0U2hh'.'cmVwb2l'.'u'.'d'.'D'.'o6QWdlbnRMaXN0cygpOw==','aW5'.'0cmF'.'uZXQ=','T'.'g='.'=','Q'.'0l'.'udHJhb'.'m'.'V'.'0'.'U2hh'.'cmVw'.'b'.'2'.'l'.'udDo6QW'.'d'.'lbnR'.'RdWV'.'1ZSgpOw==','aW50cmF'.'uZXQ=',''.'Tg='.'=','Q0l'.'udHJhbm'.'V0'.'U2h'.'hcmVw'.'b2lu'.'dDo6QWdl'.'bnRVcG'.'Rh'.'dGUoKTs=','a'.'W5'.'0cmFuZXQ=','Tg==','aW'.'Jsb2'.'Nr','T2'.'5B'.'Z'.'nRlckl'.'CbG9ja0V'.'sZW1'.'lbnRBZGQ=','aW5'.'0cmFu'.'ZXQ=','Q'.'0l'.'u'.'dH'.'J'.'hbmV'.'0RX'.'ZlbnR'.'IYW5k'.'bGVyc'.'w==','U1'.'BSZWdpc3R'.'l'.'cl'.'VwZGF0ZWR'.'J'.'dGV'.'t','aW'.'Jsb'.'2Nr','T25'.'BZnRlcklCbG9ja'.'0VsZ'.'W'.'1lb'.'nRVcGRhdGU'.'=','aW'.'50cmFuZXQ'.'=','Q0ludHJhbmV0RXZ'.'lbnRIYW5'.'kbGVycw='.'=','U1BSZ'.'W'.'d'.'pc3RlclVw'.'ZGF0ZWRJ'.'dGVt','Q0'.'ludH'.'JhbmV0U2'.'hhc'.'mVwb'.'2ludDo'.'6QWdl'.'bn'.'RMa'.'X'.'N0'.'cygpOw='.'=','aW50c'.'m'.'FuZXQ=','Q0ludHJh'.'bm'.'V0U'.'2hhc'.'mVwb2'.'ludDo6QWdlbnRRdW'.'V1ZSgpOw==',''.'aW50cm'.'FuZX'.'Q=',''.'Q0lud'.'HJh'.'bmV0'.'U2hhcmVwb2lud'.'Do6'.'QWdlbnRVcG'.'RhdGU'.'o'.'K'.'Ts'.'=','aW'.'50c'.'mF'.'uZ'.'XQ=','Y3Jt','bWFpbg'.'='.'=','T'.'2'.'5'.'CZ'.'W'.'Zvc'.'m'.'VQcm9sb2c=','bWFp'.'bg==','Q1dpemF'.'yZFNvb'.'FBhb'.'mV'.'sS'.'W'.'50cm'.'F'.'u'.'ZXQ=','U2hvd1Bhb'.'mVs','L21vZHVsZX'.'Mva'.'W50'.'c'.'mF'.'uZXQ'.'v'.'c'.'GFuZWxfYnV0dG'.'9'.'uL'.'nBocA'.'==','RU5'.'DT'.'0RF','WQ'.'==');return base64_decode($_197562817[$_6405298]);}};$GLOBALS['____1038174309'][0](___903390100(0), ___903390100(1));class CBXFeatures{ private static $_666426985= 30; private static $_1009929619= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_741856539= false; private static $_189162218= false; private static function __397530092(){ if(self::$_741856539 == false){ self::$_741856539= array(); foreach(self::$_1009929619 as $_2128099832 => $_488547284){ foreach($_488547284 as $_289995077) self::$_741856539[$_289995077]= $_2128099832;}} if(self::$_189162218 == false){ self::$_189162218= array(); $_694529417= COption::GetOptionString(___903390100(2), ___903390100(3), ___903390100(4)); if($GLOBALS['____1038174309'][1]($_694529417)>(139*2-278)){ $_694529417= $GLOBALS['____1038174309'][2]($_694529417); self::$_189162218= $GLOBALS['____1038174309'][3]($_694529417); if(!$GLOBALS['____1038174309'][4](self::$_189162218)) self::$_189162218= array();} if($GLOBALS['____1038174309'][5](self::$_189162218) <=(195*2-390)) self::$_189162218= array(___903390100(5) => array(), ___903390100(6) => array());}} public static function InitiateEditionsSettings($_1238087715){ self::__397530092(); $_176431714= array(); foreach(self::$_1009929619 as $_2128099832 => $_488547284){ $_1937680291= $GLOBALS['____1038174309'][6]($_2128099832, $_1238087715); self::$_189162218[___903390100(7)][$_2128099832]=($_1937680291? array(___903390100(8)): array(___903390100(9))); foreach($_488547284 as $_289995077){ self::$_189162218[___903390100(10)][$_289995077]= $_1937680291; if(!$_1937680291) $_176431714[]= array($_289995077, false);}} $_622132586= $GLOBALS['____1038174309'][7](self::$_189162218); $_622132586= $GLOBALS['____1038174309'][8]($_622132586); COption::SetOptionString(___903390100(11), ___903390100(12), $_622132586); foreach($_176431714 as $_2110206248) self::__1940859627($_2110206248[min(28,0,9.3333333333333)], $_2110206248[round(0+1)]);} public static function IsFeatureEnabled($_289995077){ if($GLOBALS['____1038174309'][9]($_289995077) <= 0) return true; self::__397530092(); if(!$GLOBALS['____1038174309'][10]($_289995077, self::$_741856539)) return true; if(self::$_741856539[$_289995077] == ___903390100(13)) $_76591175= array(___903390100(14)); elseif($GLOBALS['____1038174309'][11](self::$_741856539[$_289995077], self::$_189162218[___903390100(15)])) $_76591175= self::$_189162218[___903390100(16)][self::$_741856539[$_289995077]]; else $_76591175= array(___903390100(17)); if($_76591175[min(26,0,8.6666666666667)] != ___903390100(18) && $_76591175[(190*2-380)] != ___903390100(19)){ return false;} elseif($_76591175[(792-2*396)] == ___903390100(20)){ if($_76591175[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____1038174309'][12](min(118,0,39.333333333333),(934-2*467),(1412/2-706), Date(___903390100(21)), $GLOBALS['____1038174309'][13](___903390100(22))- self::$_666426985, $GLOBALS['____1038174309'][14](___903390100(23)))){ if(!isset($_76591175[round(0+1+1)]) ||!$_76591175[round(0+2)]) self::__889592819(self::$_741856539[$_289995077]); return false;}} return!$GLOBALS['____1038174309'][15]($_289995077, self::$_189162218[___903390100(24)]) || self::$_189162218[___903390100(25)][$_289995077];} public static function IsFeatureInstalled($_289995077){ if($GLOBALS['____1038174309'][16]($_289995077) <= 0) return true; self::__397530092(); return($GLOBALS['____1038174309'][17]($_289995077, self::$_189162218[___903390100(26)]) && self::$_189162218[___903390100(27)][$_289995077]);} public static function IsFeatureEditable($_289995077){ if($GLOBALS['____1038174309'][18]($_289995077) <= 0) return true; self::__397530092(); if(!$GLOBALS['____1038174309'][19]($_289995077, self::$_741856539)) return true; if(self::$_741856539[$_289995077] == ___903390100(28)) $_76591175= array(___903390100(29)); elseif($GLOBALS['____1038174309'][20](self::$_741856539[$_289995077], self::$_189162218[___903390100(30)])) $_76591175= self::$_189162218[___903390100(31)][self::$_741856539[$_289995077]]; else $_76591175= array(___903390100(32)); if($_76591175[min(130,0,43.333333333333)] != ___903390100(33) && $_76591175[min(170,0,56.666666666667)] != ___903390100(34)){ return false;} elseif($_76591175[(202*2-404)] == ___903390100(35)){ if($_76591175[round(0+1)]< $GLOBALS['____1038174309'][21](min(188,0,62.666666666667), min(30,0,10),(786-2*393), Date(___903390100(36)), $GLOBALS['____1038174309'][22](___903390100(37))- self::$_666426985, $GLOBALS['____1038174309'][23](___903390100(38)))){ if(!isset($_76591175[round(0+0.4+0.4+0.4+0.4+0.4)]) ||!$_76591175[round(0+0.4+0.4+0.4+0.4+0.4)]) self::__889592819(self::$_741856539[$_289995077]); return false;}} return true;} private static function __1940859627($_289995077, $_283160836){ if($GLOBALS['____1038174309'][24]("CBXFeatures", "On".$_289995077."SettingsChange")) $GLOBALS['____1038174309'][25](array("CBXFeatures", "On".$_289995077."SettingsChange"), array($_289995077, $_283160836)); $_454230340= $GLOBALS['_____547179995'][0](___903390100(39), ___903390100(40).$_289995077.___903390100(41)); while($_890661250= $_454230340->Fetch()) $GLOBALS['_____547179995'][1]($_890661250, array($_289995077, $_283160836));} public static function SetFeatureEnabled($_289995077, $_283160836= true, $_746938901= true){ if($GLOBALS['____1038174309'][26]($_289995077) <= 0) return; if(!self::IsFeatureEditable($_289995077)) $_283160836= false; $_283160836=($_283160836? true: false); self::__397530092(); $_2139280796=(!$GLOBALS['____1038174309'][27]($_289995077, self::$_189162218[___903390100(42)]) && $_283160836 || $GLOBALS['____1038174309'][28]($_289995077, self::$_189162218[___903390100(43)]) && $_283160836 != self::$_189162218[___903390100(44)][$_289995077]); self::$_189162218[___903390100(45)][$_289995077]= $_283160836; $_622132586= $GLOBALS['____1038174309'][29](self::$_189162218); $_622132586= $GLOBALS['____1038174309'][30]($_622132586); COption::SetOptionString(___903390100(46), ___903390100(47), $_622132586); if($_2139280796 && $_746938901) self::__1940859627($_289995077, $_283160836);} private static function __889592819($_2128099832){ if($GLOBALS['____1038174309'][31]($_2128099832) <= 0 || $_2128099832 == "Portal") return; self::__397530092(); if(!$GLOBALS['____1038174309'][32]($_2128099832, self::$_189162218[___903390100(48)]) || $GLOBALS['____1038174309'][33]($_2128099832, self::$_189162218[___903390100(49)]) && self::$_189162218[___903390100(50)][$_2128099832][(936-2*468)] != ___903390100(51)) return; if(isset(self::$_189162218[___903390100(52)][$_2128099832][round(0+1+1)]) && self::$_189162218[___903390100(53)][$_2128099832][round(0+0.4+0.4+0.4+0.4+0.4)]) return; $_176431714= array(); if($GLOBALS['____1038174309'][34]($_2128099832, self::$_1009929619) && $GLOBALS['____1038174309'][35](self::$_1009929619[$_2128099832])){ foreach(self::$_1009929619[$_2128099832] as $_289995077){ if($GLOBALS['____1038174309'][36]($_289995077, self::$_189162218[___903390100(54)]) && self::$_189162218[___903390100(55)][$_289995077]){ self::$_189162218[___903390100(56)][$_289995077]= false; $_176431714[]= array($_289995077, false);}} self::$_189162218[___903390100(57)][$_2128099832][round(0+0.4+0.4+0.4+0.4+0.4)]= true;} $_622132586= $GLOBALS['____1038174309'][37](self::$_189162218); $_622132586= $GLOBALS['____1038174309'][38]($_622132586); COption::SetOptionString(___903390100(58), ___903390100(59), $_622132586); foreach($_176431714 as $_2110206248) self::__1940859627($_2110206248[(158*2-316)], $_2110206248[round(0+1)]);} public static function ModifyFeaturesSettings($_1238087715, $_488547284){ self::__397530092(); foreach($_1238087715 as $_2128099832 => $_129035444) self::$_189162218[___903390100(60)][$_2128099832]= $_129035444; $_176431714= array(); foreach($_488547284 as $_289995077 => $_283160836){ if(!$GLOBALS['____1038174309'][39]($_289995077, self::$_189162218[___903390100(61)]) && $_283160836 || $GLOBALS['____1038174309'][40]($_289995077, self::$_189162218[___903390100(62)]) && $_283160836 != self::$_189162218[___903390100(63)][$_289995077]) $_176431714[]= array($_289995077, $_283160836); self::$_189162218[___903390100(64)][$_289995077]= $_283160836;} $_622132586= $GLOBALS['____1038174309'][41](self::$_189162218); $_622132586= $GLOBALS['____1038174309'][42]($_622132586); COption::SetOptionString(___903390100(65), ___903390100(66), $_622132586); self::$_189162218= false; foreach($_176431714 as $_2110206248) self::__1940859627($_2110206248[(996-2*498)], $_2110206248[round(0+0.25+0.25+0.25+0.25)]);} public static function SaveFeaturesSettings($_455287410, $_1347936240){ self::__397530092(); $_1640532198= array(___903390100(67) => array(), ___903390100(68) => array()); if(!$GLOBALS['____1038174309'][43]($_455287410)) $_455287410= array(); if(!$GLOBALS['____1038174309'][44]($_1347936240)) $_1347936240= array(); if(!$GLOBALS['____1038174309'][45](___903390100(69), $_455287410)) $_455287410[]= ___903390100(70); foreach(self::$_1009929619 as $_2128099832 => $_488547284){ if($GLOBALS['____1038174309'][46]($_2128099832, self::$_189162218[___903390100(71)])) $_1651973209= self::$_189162218[___903390100(72)][$_2128099832]; else $_1651973209=($_2128099832 == ___903390100(73))? array(___903390100(74)): array(___903390100(75)); if($_1651973209[(874-2*437)] == ___903390100(76) || $_1651973209[(808-2*404)] == ___903390100(77)){ $_1640532198[___903390100(78)][$_2128099832]= $_1651973209;} else{ if($GLOBALS['____1038174309'][47]($_2128099832, $_455287410)) $_1640532198[___903390100(79)][$_2128099832]= array(___903390100(80), $GLOBALS['____1038174309'][48](min(244,0,81.333333333333),(766-2*383),(1456/2-728), $GLOBALS['____1038174309'][49](___903390100(81)), $GLOBALS['____1038174309'][50](___903390100(82)), $GLOBALS['____1038174309'][51](___903390100(83)))); else $_1640532198[___903390100(84)][$_2128099832]= array(___903390100(85));}} $_176431714= array(); foreach(self::$_741856539 as $_289995077 => $_2128099832){ if($_1640532198[___903390100(86)][$_2128099832][(772-2*386)] != ___903390100(87) && $_1640532198[___903390100(88)][$_2128099832][(992-2*496)] != ___903390100(89)){ $_1640532198[___903390100(90)][$_289995077]= false;} else{ if($_1640532198[___903390100(91)][$_2128099832][(792-2*396)] == ___903390100(92) && $_1640532198[___903390100(93)][$_2128099832][round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____1038174309'][52]((1048/2-524),(1276/2-638), min(106,0,35.333333333333), Date(___903390100(94)), $GLOBALS['____1038174309'][53](___903390100(95))- self::$_666426985, $GLOBALS['____1038174309'][54](___903390100(96)))) $_1640532198[___903390100(97)][$_289995077]= false; else $_1640532198[___903390100(98)][$_289995077]= $GLOBALS['____1038174309'][55]($_289995077, $_1347936240); if(!$GLOBALS['____1038174309'][56]($_289995077, self::$_189162218[___903390100(99)]) && $_1640532198[___903390100(100)][$_289995077] || $GLOBALS['____1038174309'][57]($_289995077, self::$_189162218[___903390100(101)]) && $_1640532198[___903390100(102)][$_289995077] != self::$_189162218[___903390100(103)][$_289995077]) $_176431714[]= array($_289995077, $_1640532198[___903390100(104)][$_289995077]);}} $_622132586= $GLOBALS['____1038174309'][58]($_1640532198); $_622132586= $GLOBALS['____1038174309'][59]($_622132586); COption::SetOptionString(___903390100(105), ___903390100(106), $_622132586); self::$_189162218= false; foreach($_176431714 as $_2110206248) self::__1940859627($_2110206248[(205*2-410)], $_2110206248[round(0+0.25+0.25+0.25+0.25)]);} public static function GetFeaturesList(){ self::__397530092(); $_1049920851= array(); foreach(self::$_1009929619 as $_2128099832 => $_488547284){ if($GLOBALS['____1038174309'][60]($_2128099832, self::$_189162218[___903390100(107)])) $_1651973209= self::$_189162218[___903390100(108)][$_2128099832]; else $_1651973209=($_2128099832 == ___903390100(109))? array(___903390100(110)): array(___903390100(111)); $_1049920851[$_2128099832]= array( ___903390100(112) => $_1651973209[(1280/2-640)], ___903390100(113) => $_1651973209[round(0+0.33333333333333+0.33333333333333+0.33333333333333)], ___903390100(114) => array(),); $_1049920851[$_2128099832][___903390100(115)]= false; if($_1049920851[$_2128099832][___903390100(116)] == ___903390100(117)){ $_1049920851[$_2128099832][___903390100(118)]= $GLOBALS['____1038174309'][61](($GLOBALS['____1038174309'][62]()- $_1049920851[$_2128099832][___903390100(119)])/ round(0+21600+21600+21600+21600)); if($_1049920851[$_2128099832][___903390100(120)]> self::$_666426985) $_1049920851[$_2128099832][___903390100(121)]= true;} foreach($_488547284 as $_289995077) $_1049920851[$_2128099832][___903390100(122)][$_289995077]=(!$GLOBALS['____1038174309'][63]($_289995077, self::$_189162218[___903390100(123)]) || self::$_189162218[___903390100(124)][$_289995077]);} return $_1049920851;} private static function __2022501611($_1038528895, $_1543384463){ if(IsModuleInstalled($_1038528895) == $_1543384463) return true; $_1091595345= $_SERVER[___903390100(125)].___903390100(126).$_1038528895.___903390100(127); if(!$GLOBALS['____1038174309'][64]($_1091595345)) return false; include_once($_1091595345); $_381790123= $GLOBALS['____1038174309'][65](___903390100(128), ___903390100(129), $_1038528895); if(!$GLOBALS['____1038174309'][66]($_381790123)) return false; $_1394219843= new $_381790123; if($_1543384463){ if(!$_1394219843->InstallDB()) return false; $_1394219843->InstallEvents(); if(!$_1394219843->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___903390100(130))) CSearch::DeleteIndex($_1038528895); UnRegisterModule($_1038528895);} return true;} protected static function OnRequestsSettingsChange($_289995077, $_283160836){ self::__2022501611("form", $_283160836);} protected static function OnLearningSettingsChange($_289995077, $_283160836){ self::__2022501611("learning", $_283160836);} protected static function OnJabberSettingsChange($_289995077, $_283160836){ self::__2022501611("xmpp", $_283160836);} protected static function OnVideoConferenceSettingsChange($_289995077, $_283160836){ self::__2022501611("video", $_283160836);} protected static function OnBizProcSettingsChange($_289995077, $_283160836){ self::__2022501611("bizprocdesigner", $_283160836);} protected static function OnListsSettingsChange($_289995077, $_283160836){ self::__2022501611("lists", $_283160836);} protected static function OnWikiSettingsChange($_289995077, $_283160836){ self::__2022501611("wiki", $_283160836);} protected static function OnSupportSettingsChange($_289995077, $_283160836){ self::__2022501611("support", $_283160836);} protected static function OnControllerSettingsChange($_289995077, $_283160836){ self::__2022501611("controller", $_283160836);} protected static function OnAnalyticsSettingsChange($_289995077, $_283160836){ self::__2022501611("statistic", $_283160836);} protected static function OnVoteSettingsChange($_289995077, $_283160836){ self::__2022501611("vote", $_283160836);} protected static function OnFriendsSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(131); $_887556811= CSite::GetList(($_1937680291= ___903390100(132)),($_275537993= ___903390100(133)), array(___903390100(134) => ___903390100(135))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(136), ___903390100(137), ___903390100(138), $_1728385399[___903390100(139)]) != $_717230798){ COption::SetOptionString(___903390100(140), ___903390100(141), $_717230798, false, $_1728385399[___903390100(142)]); COption::SetOptionString(___903390100(143), ___903390100(144), $_717230798);}}} protected static function OnMicroBlogSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(145); $_887556811= CSite::GetList(($_1937680291= ___903390100(146)),($_275537993= ___903390100(147)), array(___903390100(148) => ___903390100(149))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(150), ___903390100(151), ___903390100(152), $_1728385399[___903390100(153)]) != $_717230798){ COption::SetOptionString(___903390100(154), ___903390100(155), $_717230798, false, $_1728385399[___903390100(156)]); COption::SetOptionString(___903390100(157), ___903390100(158), $_717230798);} if(COption::GetOptionString(___903390100(159), ___903390100(160), ___903390100(161), $_1728385399[___903390100(162)]) != $_717230798){ COption::SetOptionString(___903390100(163), ___903390100(164), $_717230798, false, $_1728385399[___903390100(165)]); COption::SetOptionString(___903390100(166), ___903390100(167), $_717230798);}}} protected static function OnPersonalFilesSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(168); $_887556811= CSite::GetList(($_1937680291= ___903390100(169)),($_275537993= ___903390100(170)), array(___903390100(171) => ___903390100(172))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(173), ___903390100(174), ___903390100(175), $_1728385399[___903390100(176)]) != $_717230798){ COption::SetOptionString(___903390100(177), ___903390100(178), $_717230798, false, $_1728385399[___903390100(179)]); COption::SetOptionString(___903390100(180), ___903390100(181), $_717230798);}}} protected static function OnPersonalBlogSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(182); $_887556811= CSite::GetList(($_1937680291= ___903390100(183)),($_275537993= ___903390100(184)), array(___903390100(185) => ___903390100(186))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(187), ___903390100(188), ___903390100(189), $_1728385399[___903390100(190)]) != $_717230798){ COption::SetOptionString(___903390100(191), ___903390100(192), $_717230798, false, $_1728385399[___903390100(193)]); COption::SetOptionString(___903390100(194), ___903390100(195), $_717230798);}}} protected static function OnPersonalPhotoSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(196); $_887556811= CSite::GetList(($_1937680291= ___903390100(197)),($_275537993= ___903390100(198)), array(___903390100(199) => ___903390100(200))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(201), ___903390100(202), ___903390100(203), $_1728385399[___903390100(204)]) != $_717230798){ COption::SetOptionString(___903390100(205), ___903390100(206), $_717230798, false, $_1728385399[___903390100(207)]); COption::SetOptionString(___903390100(208), ___903390100(209), $_717230798);}}} protected static function OnPersonalForumSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(210); $_887556811= CSite::GetList(($_1937680291= ___903390100(211)),($_275537993= ___903390100(212)), array(___903390100(213) => ___903390100(214))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(215), ___903390100(216), ___903390100(217), $_1728385399[___903390100(218)]) != $_717230798){ COption::SetOptionString(___903390100(219), ___903390100(220), $_717230798, false, $_1728385399[___903390100(221)]); COption::SetOptionString(___903390100(222), ___903390100(223), $_717230798);}}} protected static function OnTasksSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(224); $_887556811= CSite::GetList(($_1937680291= ___903390100(225)),($_275537993= ___903390100(226)), array(___903390100(227) => ___903390100(228))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(229), ___903390100(230), ___903390100(231), $_1728385399[___903390100(232)]) != $_717230798){ COption::SetOptionString(___903390100(233), ___903390100(234), $_717230798, false, $_1728385399[___903390100(235)]); COption::SetOptionString(___903390100(236), ___903390100(237), $_717230798);} if(COption::GetOptionString(___903390100(238), ___903390100(239), ___903390100(240), $_1728385399[___903390100(241)]) != $_717230798){ COption::SetOptionString(___903390100(242), ___903390100(243), $_717230798, false, $_1728385399[___903390100(244)]); COption::SetOptionString(___903390100(245), ___903390100(246), $_717230798);}} self::__2022501611(___903390100(247), $_283160836);} protected static function OnCalendarSettingsChange($_289995077, $_283160836){ if($_283160836) $_717230798= "Y"; else $_717230798= ___903390100(248); $_887556811= CSite::GetList(($_1937680291= ___903390100(249)),($_275537993= ___903390100(250)), array(___903390100(251) => ___903390100(252))); while($_1728385399= $_887556811->Fetch()){ if(COption::GetOptionString(___903390100(253), ___903390100(254), ___903390100(255), $_1728385399[___903390100(256)]) != $_717230798){ COption::SetOptionString(___903390100(257), ___903390100(258), $_717230798, false, $_1728385399[___903390100(259)]); COption::SetOptionString(___903390100(260), ___903390100(261), $_717230798);} if(COption::GetOptionString(___903390100(262), ___903390100(263), ___903390100(264), $_1728385399[___903390100(265)]) != $_717230798){ COption::SetOptionString(___903390100(266), ___903390100(267), $_717230798, false, $_1728385399[___903390100(268)]); COption::SetOptionString(___903390100(269), ___903390100(270), $_717230798);}}} protected static function OnSMTPSettingsChange($_289995077, $_283160836){ self::__2022501611("mail", $_283160836);} protected static function OnExtranetSettingsChange($_289995077, $_283160836){ $_1349559139= COption::GetOptionString("extranet", "extranet_site", ""); if($_1349559139){ $_1845404784= new CSite; $_1845404784->Update($_1349559139, array(___903390100(271) =>($_283160836? ___903390100(272): ___903390100(273))));} self::__2022501611(___903390100(274), $_283160836);} protected static function OnDAVSettingsChange($_289995077, $_283160836){ self::__2022501611("dav", $_283160836);} protected static function OntimemanSettingsChange($_289995077, $_283160836){ self::__2022501611("timeman", $_283160836);} protected static function Onintranet_sharepointSettingsChange($_289995077, $_283160836){ if($_283160836){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___903390100(275), ___903390100(276), ___903390100(277), ___903390100(278), ___903390100(279)); CAgent::AddAgent(___903390100(280), ___903390100(281), ___903390100(282), round(0+100+100+100+100+100)); CAgent::AddAgent(___903390100(283), ___903390100(284), ___903390100(285), round(0+300)); CAgent::AddAgent(___903390100(286), ___903390100(287), ___903390100(288), round(0+3600));} else{ UnRegisterModuleDependences(___903390100(289), ___903390100(290), ___903390100(291), ___903390100(292), ___903390100(293)); UnRegisterModuleDependences(___903390100(294), ___903390100(295), ___903390100(296), ___903390100(297), ___903390100(298)); CAgent::RemoveAgent(___903390100(299), ___903390100(300)); CAgent::RemoveAgent(___903390100(301), ___903390100(302)); CAgent::RemoveAgent(___903390100(303), ___903390100(304));}} protected static function OncrmSettingsChange($_289995077, $_283160836){ if($_283160836) COption::SetOptionString("crm", "form_features", "Y"); self::__2022501611(___903390100(305), $_283160836);} protected static function OnClusterSettingsChange($_289995077, $_283160836){ self::__2022501611("cluster", $_283160836);} protected static function OnMultiSitesSettingsChange($_289995077, $_283160836){ if($_283160836) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___903390100(306), ___903390100(307), ___903390100(308), ___903390100(309), ___903390100(310), ___903390100(311));} protected static function OnIdeaSettingsChange($_289995077, $_283160836){ self::__2022501611("idea", $_283160836);} protected static function OnMeetingSettingsChange($_289995077, $_283160836){ self::__2022501611("meeting", $_283160836);} protected static function OnXDImportSettingsChange($_289995077, $_283160836){ self::__2022501611("xdimport", $_283160836);}} $GLOBALS['____1038174309'][67](___903390100(312), ___903390100(313));/**/			//Do not remove this

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

/*ZDUyZmZZDlkNDYyMjE2ZWZmNTM0MTlkNmVkZjY4NjUxZjQ3N2I=*/$GLOBALS['____1889997688']= array(base64_decode(''.'b'.'XRfcmFuZA=='),base64_decode('ZXh'.'w'.'bG'.'9kZQ=='),base64_decode('cGFja'.'w='.'='),base64_decode('bW'.'Q1'),base64_decode('Y'.'29uc3'.'Rh'.'bnQ'.'='),base64_decode('aGFzaF9ob'.'WF'.'j'),base64_decode('c3RyY'.'2'.'1w'),base64_decode(''.'a'.'XN'.'f'.'b2JqZWN0'),base64_decode('Y2FsbF91c2'.'VyX2Z1bmM='),base64_decode('Y'.'2F'.'sbF9'.'1c2VyX2Z1b'.'mM='),base64_decode('Y2FsbF'.'91c2VyX2Z'.'1bmM='),base64_decode('Y2Fs'.'bF91c2'.'VyX2Z'.'1'.'bmM='),base64_decode('Y2Fsb'.'F91'.'c2Vy'.'X2Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___331615128')){function ___331615128($_1037832854){static $_1367941027= false; if($_1367941027 == false) $_1367941027=array('R'.'EI=','U'.'0VMRUNU'.'IFZBTF'.'VFIE'.'ZST0'.'0gY'.'l'.'9v'.'cHRpb'.'2'.'4gV'.'0hFUkUgTkFNR'.'T0'.'n'.'flBBUkFNX'.'01BWF9V'.'U'.'0V'.'SUycgQU5EIE'.'1PRF'.'VMRV'.'9JR'.'D0nb'.'WFpbi'.'c'.'gQU'.'5EIFNJVEVfSUQ'.'gSVM'.'gTlVMTA='.'=',''.'VkFMVUU=','L'.'g==','SCo=','Y'.'ml0cml4','TElD'.'RU5TR'.'V9LR'.'V'.'k=','c2hh'.'Mj'.'U'.'2','VVNFU'.'g==','VVNFUg==','VV'.'NFUg==','SXNBdXRo'.'b3'.'JpemVk','VVNFUg==','SXN'.'BZG1'.'pbg==','QVBQT'.'El'.'DQV'.'R'.'J'.'T04=','UmV'.'zdGFydEJ1ZmZlc'.'g==','TG9jYWxSZWRp'.'cmVj'.'dA'.'==',''.'L2xpY2Vuc2VfcmVzdHJp'.'Y3'.'Rpb24ucG'.'hw','X'.'EJ'.'pdHJ'.'peFxNYWluXE'.'NvbmZpZ1x'.'Pc'.'H'.'R'.'pb246'.'OnNl'.'d'.'A==','bWFpbg==','UEFS'.'QU1f'.'TUFY'.'X'.'1V'.'TRVJT');return base64_decode($_1367941027[$_1037832854]);}};if($GLOBALS['____1889997688'][0](round(0+0.33333333333333+0.33333333333333+0.33333333333333), round(0+20)) == round(0+2.3333333333333+2.3333333333333+2.3333333333333)){ $_1891787137= $GLOBALS[___331615128(0)]->Query(___331615128(1), true); if($_168483522= $_1891787137->Fetch()){ $_1748203670= $_168483522[___331615128(2)]; list($_517327569, $_1254154225)= $GLOBALS['____1889997688'][1](___331615128(3), $_1748203670); $_706001486= $GLOBALS['____1889997688'][2](___331615128(4), $_517327569); $_1650837565= ___331615128(5).$GLOBALS['____1889997688'][3]($GLOBALS['____1889997688'][4](___331615128(6))); $_1171548385= $GLOBALS['____1889997688'][5](___331615128(7), $_1254154225, $_1650837565, true); if($GLOBALS['____1889997688'][6]($_1171548385, $_706001486) !==(782-2*391)){ if(isset($GLOBALS[___331615128(8)]) && $GLOBALS['____1889997688'][7]($GLOBALS[___331615128(9)]) && $GLOBALS['____1889997688'][8](array($GLOBALS[___331615128(10)], ___331615128(11))) &&!$GLOBALS['____1889997688'][9](array($GLOBALS[___331615128(12)], ___331615128(13)))){ $GLOBALS['____1889997688'][10](array($GLOBALS[___331615128(14)], ___331615128(15))); $GLOBALS['____1889997688'][11](___331615128(16), ___331615128(17), true);}}} else{ $GLOBALS['____1889997688'][12](___331615128(18), ___331615128(19), ___331615128(20), round(0+3+3+3+3));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
