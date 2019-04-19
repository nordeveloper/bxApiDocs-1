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

/*ZDUyZmZODhiYWFlYTBhZmE0MWFhZTA3ZDk5ZTM1MTQ1N2FkZjY=*/$GLOBALS['_____2016020051']= array(base64_decode('R2V0T'.'W9kdWxlRX'.'Z'.'l'.'bnRz'),base64_decode('RXh'.'lY'.'3V0ZU1'.'vZHVsZUV2Z'.'W50RXg='));$GLOBALS['____864073948']= array(base64_decode('ZGVmaW'.'5l'),base64_decode('c'.'3RybGV'.'u'),base64_decode('YmFzZ'.'TY'.'0'.'X2RlY29'.'k'.'ZQ=='),base64_decode('d'.'W'.'5zZX'.'JpYWxpe'.'mU'.'='),base64_decode('aXNf'.'YXJyYXk'.'='),base64_decode('Y29'.'1bnQ='),base64_decode('aW5f'.'Y'.'X'.'JyYXk'.'='),base64_decode('c2'.'VyaWFsaXpl'),base64_decode('YmF'.'zZ'.'TY0X'.'2VuY29k'.'ZQ'.'=='),base64_decode('c3Ryb'.'GVu'),base64_decode('YXJy'.'YXlfa2V5'.'X'.'2V4aX'.'N0'.'cw=='),base64_decode('Y'.'X'.'J'.'yYXlfa2V5X'.'2V4aXN0'.'cw=='),base64_decode('bWt0aW1l'),base64_decode('Z'.'GF0ZQ=='),base64_decode('Z'.'GF0ZQ=='),base64_decode('YXJ'.'yYXlfa2V5X2V4aXN0cw'.'=='),base64_decode(''.'c'.'3'.'Ryb'.'G'.'Vu'),base64_decode(''.'Y'.'XJyYX'.'lfa'.'2V5X2V4aXN0cw=='),base64_decode(''.'c'.'3RybGVu'),base64_decode(''.'YXJyY'.'X'.'lfa2V'.'5'.'X2V4aXN0cw=='),base64_decode('YX'.'J'.'y'.'YX'.'lfa'.'2V5X'.'2V'.'4aXN0cw='.'='),base64_decode('bWt0aW1l'),base64_decode('ZGF0ZQ='.'='),base64_decode('ZG'.'F0ZQ='.'='),base64_decode('bWV'.'0aG9kX2'.'V'.'4aXN'.'0cw=='),base64_decode('Y2FsbF91c2VyX2Z1bmNfY'.'XJyYXk='),base64_decode('c3Ryb'.'GV'.'u'),base64_decode('YXJyYXlfa2'.'V5X2V'.'4aXN0'.'c'.'w'.'='.'='),base64_decode(''.'YXJ'.'yYX'.'lf'.'a2V'.'5X2V4aXN0cw='.'='),base64_decode('c'.'2V'.'y'.'a'.'WFs'.'aX'.'pl'),base64_decode('Y'.'mFzZTY0X'.'2VuY2'.'9'.'kZ'.'Q=='),base64_decode('c3RybGVu'),base64_decode('Y'.'XJyYXl'.'f'.'a2V5X'.'2V4aXN0cw'.'=='),base64_decode(''.'YXJyYXl'.'fa2'.'V5X2V'.'4aXN0c'.'w'.'=='),base64_decode('YX'.'JyY'.'Xlfa'.'2V5X'.'2'.'V'.'4'.'aXN0c'.'w=='),base64_decode('aXNfYXJyYX'.'k'.'='),base64_decode('YX'.'J'.'y'.'Y'.'Xlf'.'a2'.'V5X2'.'V4aXN0'.'c'.'w'.'=='),base64_decode('c2Vy'.'aWFs'.'a'.'Xpl'),base64_decode('Ym'.'FzZTY0X'.'2Vu'.'Y29'.'kZQ='.'='),base64_decode('YXJyYX'.'lf'.'a'.'2'.'V5X2V4aXN0cw=='),base64_decode('YXJyYXlfa2V'.'5X2'.'V4aXN'.'0cw=='),base64_decode(''.'c2VyaWFs'.'aXpl'),base64_decode(''.'Ym'.'FzZTY'.'0X2VuY'.'29k'.'Z'.'Q='.'='),base64_decode('aXNfY'.'XJ'.'yYXk'.'='),base64_decode('a'.'XNfY'.'XJ'.'yY'.'Xk'.'='),base64_decode(''.'aW'.'5'.'fYXJyY'.'X'.'k='),base64_decode('YX'.'JyYXlf'.'a2V5'.'X2V4'.'aXN0cw=='),base64_decode('aW5fYXJ'.'yY'.'X'.'k='),base64_decode('bWt0a'.'W1l'),base64_decode('ZG'.'F0ZQ'.'=='),base64_decode('ZGF0ZQ=='),base64_decode('ZG'.'F0Z'.'Q=='),base64_decode('bWt0aW'.'1l'),base64_decode('ZGF0'.'Z'.'Q='.'='),base64_decode('ZGF0ZQ=='),base64_decode('aW5fYXJ'.'yYXk='),base64_decode('YXJy'.'YXlfa2V5X'.'2V'.'4aX'.'N0cw=='),base64_decode(''.'Y'.'XJyYXlfa'.'2'.'V5X2V'.'4aXN0cw'.'=='),base64_decode(''.'c2VyaWF'.'saXpl'),base64_decode('YmFzZTY0X2VuY29'.'kZQ=='),base64_decode('YX'.'J'.'y'.'Y'.'Xlfa2V'.'5'.'X2V4aX'.'N'.'0c'.'w=='),base64_decode('aW50dmFs'),base64_decode('dGltZQ=='),base64_decode(''.'YXJyYX'.'lfa2V5X'.'2V'.'4'.'aXN0cw=='),base64_decode('Z'.'mlsZ'.'V9leGlzdHM='),base64_decode('c3RyX3Jlc'.'G'.'x'.'hY2U='),base64_decode(''.'Y2xhc3NfZXhp'.'c3R'.'z'),base64_decode('ZG'.'VmaW'.'5l'));if(!function_exists(__NAMESPACE__.'\\___247804393')){function ___247804393($_1791859924){static $_143384680= false; if($_143384680 == false) $_143384680=array('SU5UUkF'.'OR'.'VRfRUR'.'JVElPTg==','WQ==',''.'bW'.'Fpbg==','fmNwZl9tYXB'.'fdmF'.'sdWU'.'=','',''.'ZQ==','Zg==',''.'ZQ==','Rg'.'==','W'.'A='.'=',''.'Zg==','b'.'WFpbg'.'==','f'.'m'.'NwZl9tYX'.'Bfd'.'mFs'.'dWU=','UG9ydGFs','R'.'g==','ZQ==',''.'Z'.'Q='.'=','WA='.'=','Rg==','R'.'A==','RA==','bQ==','ZA='.'=','W'.'Q==','Zg==',''.'Zg==','Zg==','Zg==','UG9ydGFs','Rg==','ZQ==','ZQ==',''.'WA='.'=','Rg='.'=','RA==','RA'.'==','bQ==','ZA'.'==','WQ==','bW'.'Fp'.'bg==',''.'T24=','U2V0dGluZ3NDaGFu'.'Z2U=','Z'.'g==','Zg'.'==','Zg==','Zg='.'=',''.'bWFpbg==','f'.'mNwZl9tYXBfdmF'.'s'.'dWU=','Z'.'Q==',''.'Z'.'Q==','ZQ='.'=','RA==','ZQ==','ZQ==',''.'Zg'.'='.'=','Z'.'g==','Zg==','ZQ==',''.'bW'.'Fpbg==',''.'fmNwZl9t'.'YXBfdmFsdWU=','ZQ'.'==','Zg'.'==','Zg==','Z'.'g==','Z'.'g==','bWFpbg==','f'.'m'.'NwZl9'.'tY'.'X'.'Bfdm'.'FsdWU'.'=',''.'ZQ==','Z'.'g='.'=','UG9'.'yd'.'G'.'Fs',''.'UG9ydGFs','ZQ='.'=','ZQ==','UG9y'.'dGF'.'s','R'.'g'.'==','WA'.'='.'=',''.'Rg='.'=','R'.'A==','ZQ==','ZQ==','RA==','bQ==','Z'.'A'.'==',''.'WQ='.'=','ZQ==','WA='.'=','Z'.'Q'.'==','Rg='.'=','ZQ==','RA==','Zg==','ZQ'.'==','RA==','ZQ==','bQ==','Z'.'A==','WQ='.'=',''.'Zg'.'='.'=','Z'.'g==','Zg==',''.'Z'.'g==','Zg'.'==',''.'Zg==','Zg==','Zg==',''.'bWFp'.'bg='.'=','fmNwZl9'.'tYXBfdmFsdWU=','ZQ'.'==','ZQ==',''.'UG9y'.'dGFs','R'.'g'.'==',''.'WA'.'==',''.'VFlQ'.'RQ==','REFURQ==',''.'RkVBV'.'F'.'VSRV'.'M=','RVhQS'.'VJFR'.'A==','V'.'FlQRQ='.'=','RA==','V'.'FJZX'.'0RBWVNfQ09VTlQ'.'=',''.'R'.'EFURQ==','VFJZX0RBWVNf'.'Q09VTlQ'.'=','R'.'Vh'.'Q'.'SVJ'.'FRA==',''.'RkVBVF'.'VSRVM'.'=','Zg'.'==','Zg==','RE'.'9DVU1FTlRfUk9PV'.'A==',''.'L2JpdH'.'J'.'peC9tb2R'.'1b'.'GV'.'zLw==',''.'L2'.'luc3RhbGwvaW'.'5k'.'ZXg'.'ucGhw','Lg==',''.'X'.'w'.'==','c2V'.'hcmNo','Tg==','','','Q'.'UN'.'USVZF',''.'WQ==','c2'.'9'.'jaWFsb'.'m'.'V0d29yaw'.'==',''.'YWxs'.'b'.'3d'.'fZn'.'JpZ'.'Wxkcw==','W'.'Q='.'=','SUQ=','c29ja'.'WFsbm'.'V0d29ya'.'w==','YW'.'xsb3d'.'fZ'.'nJpZWxk'.'cw==',''.'S'.'UQ=',''.'c29jaWFsbm'.'V0d29yaw='.'=',''.'Y'.'W'.'xsb'.'3dfZ'.'n'.'JpZW'.'x'.'kcw==','Tg'.'==','','','Q'.'UNUS'.'VZF','W'.'Q='.'=','c29jaWFsbmV0d29y'.'aw='.'=','YWxsb3'.'dfbWl'.'jcm9ibG9nX3'.'Vz'.'ZXI'.'=','WQ==','SUQ=','c29jaWFsbmV0d2'.'9'.'ya'.'w==','YWxsb3d'.'fbWljcm9ib'.'G9n'.'X3V'.'zZXI=','S'.'U'.'Q=','c29jaWF'.'s'.'bmV0d29yaw='.'=',''.'YWx'.'sb3dfbWljcm9i'.'bG9nX'.'3'.'V'.'z'.'ZXI=','c29'.'ja'.'WFs'.'bmV0d29ya'.'w==',''.'YW'.'xsb3dfbWljcm9ibG9'.'n'.'X2'.'dyb3Vw','WQ'.'==','SUQ=','c29jaWFs'.'bm'.'V0d'.'2'.'9yaw==','YWxsb3dfbWljcm9'.'ib'.'G9nX'.'2'.'d'.'yb3V'.'w','SUQ'.'=','c'.'29j'.'aW'.'FsbmV0d29yaw='.'=','Y'.'Wxsb'.'3dfbWljcm'.'9ibG9'.'nX'.'2'.'dyb3Vw',''.'Tg'.'==','','','QU'.'NUSVZF','WQ==','c2'.'9'.'jaWFsbmV0d2'.'9yaw==',''.'YW'.'x'.'s'.'b3df'.'ZmlsZXNf'.'dXN'.'l'.'cg'.'==','WQ==','SU'.'Q=','c'.'29jaWFs'.'bmV'.'0d'.'29y'.'aw==',''.'YWxsb3df'.'Zm'.'lsZXNfdXNlc'.'g==','SUQ=','c29'.'jaWFsbmV0'.'d29yaw==',''.'YWxsb3dfZmlsZXNf'.'dXNlcg==','T'.'g==','','','QU'.'NUSV'.'ZF','WQ==','c29'.'ja'.'WFsbmV0d29'.'ya'.'w'.'==','YWx'.'sb3'.'d'.'fYm'.'xv'.'Z191'.'c2V'.'y','WQ==','SUQ=','c2'.'9ja'.'WFsbmV0d'.'29yaw==','YWxs'.'b3d'.'fYmxv'.'Z1'.'91c'.'2Vy',''.'SUQ=',''.'c29jaW'.'FsbmV'.'0d29y'.'aw'.'==','YW'.'xsb'.'3dfYmx'.'vZ191c'.'2'.'Vy','Tg==','','',''.'QUNUSVZF',''.'WQ==','c29j'.'aWFsbmV'.'0d29yaw==','YWxsb'.'3'.'dfc'.'G'.'hv'.'dG9fdXNl'.'c'.'g==','W'.'Q'.'==','S'.'UQ=',''.'c29jaWFsbmV0d29'.'yaw='.'=','Y'.'Wxsb3dfcGh'.'vdG9fdX'.'Nlcg='.'=',''.'SUQ=','c29jaWFsbm'.'V0'.'d2'.'9y'.'aw==','YWxsb3dfcGhvdG'.'9fdXN'.'l'.'cg='.'=','Tg'.'==','','','QUNUSVZF','WQ'.'==','c29'.'jaWF'.'sbmV'.'0d'.'29yaw==',''.'YWxsb'.'3dfZ'.'m9'.'y'.'dW1fdXNlcg==','WQ'.'==','SUQ'.'=','c2'.'9'.'jaWFsb'.'mV0d29yaw==','YWxsb'.'3dfZm9yd'.'W1'.'fd'.'XNlcg==','SU'.'Q'.'=',''.'c29ja'.'W'.'FsbmV0d'.'29yaw==','YWxsb'.'3d'.'fZm9ydW'.'1fdX'.'Nl'.'cg='.'=','T'.'g==','','','QU'.'NU'.'S'.'VZF','WQ==','c2'.'9jaWFsbm'.'V0d'.'29y'.'a'.'w==','YWxsb3dfdG'.'Fz'.'a3NfdXNl'.'cg'.'==','WQ='.'=','SUQ=',''.'c29jaWFsbmV0d29yaw==','YW'.'x'.'sb3'.'dfdGFza3NfdXN'.'lcg==','SUQ=',''.'c'.'29ja'.'WFsbmV0d29'.'y'.'aw==','YWx'.'sb3dfdGFza3NfdXN'.'lcg'.'==','c29'.'jaWFsbmV0d29ya'.'w'.'==','YWxsb'.'3dfdGFz'.'a3NfZ3J'.'vdXA=',''.'W'.'Q==',''.'S'.'U'.'Q'.'=',''.'c29ja'.'WFs'.'bmV0d29y'.'aw'.'='.'=','YWx'.'sb'.'3dfdGFza'.'3NfZ3J'.'vdXA=',''.'SUQ=','c'.'29jaWF'.'sb'.'m'.'V'.'0'.'d'.'2'.'9'.'ya'.'w==','Y'.'W'.'x'.'sb3'.'dfdGFza3'.'Nf'.'Z3J'.'vdXA=','dGFza3M=','Tg'.'==','','',''.'QUNUS'.'VZ'.'F','WQ==','c29j'.'aWFs'.'bmV0'.'d'.'29'.'yaw==',''.'YWxs'.'b3dfY2F'.'s'.'ZW'.'5kYXJfdXNl'.'cg'.'==','WQ='.'=','SU'.'Q=','c29jaW'.'FsbmV0d29'.'yaw==','YWx'.'sb3dfY2'.'FsZW5kYXJfdXNlc'.'g==','SUQ'.'=','c'.'2'.'9jaWF'.'sbmV0d2'.'9yaw==',''.'YWxsb3d'.'fY'.'2FsZW5kYX'.'J'.'f'.'dXNlcg==','c29jaW'.'F'.'sbmV0d29yaw==',''.'YWxsb3dfY'.'2FsZ'.'W5'.'kY'.'XJfZ'.'3JvdXA=','WQ==',''.'S'.'UQ=','c'.'29j'.'aWFsbmV0'.'d'.'29yaw='.'=',''.'YWxsb3dfY2'.'F'.'sZW5'.'kYXJf'.'Z'.'3JvdXA'.'=',''.'SUQ=','c2'.'9jaWFsbmV0d29ya'.'w'.'==','YWx'.'sb3dfY2FsZW5'.'kY'.'XJfZ'.'3J'.'vdXA=','QUNUSVZF','WQ'.'==','Tg='.'=',''.'ZXh0'.'cmF'.'uZXQ=','aWJsb'.'2Nr','T'.'25BZnR'.'lcklCbG'.'9j'.'a'.'0VsZ'.'W1l'.'bnRVcGR'.'h'.'dGU=',''.'aW50c'.'mF'.'uZX'.'Q'.'=','Q0'.'ludHJh'.'b'.'mV'.'0RXZlbn'.'RIY'.'W5k'.'bGVy'.'cw==','U1B'.'S'.'ZWdpc'.'3'.'R'.'lclVwZGF'.'0ZWRJdG'.'Vt','Q0ludHJh'.'b'.'mV0'.'U2'.'hhcmVw'.'b2'.'lud'.'Do6QWdlbnRM'.'aXN'.'0'.'cygpO'.'w'.'==',''.'aW5'.'0cmFuZXQ=','Tg==','Q0lud'.'HJhb'.'mV0U2hhcmVw'.'b2ludDo'.'6QW'.'d'.'lb'.'nRR'.'dWV1Z'.'SgpOw==','aW50cmFu'.'ZXQ=','Tg==','Q0ludHJhbmV0U2hhcmVwb2'.'l'.'udDo6QWdlbnRVcGRhdG'.'UoKTs=',''.'aW5'.'0'.'cmFuZ'.'XQ=','Tg==','aWJs'.'b2Nr','T25BZnRlcklCbG9'.'ja0VsZW1l'.'b'.'n'.'RBZ'.'G'.'Q=','aW50cm'.'FuZ'.'XQ=','Q0lud'.'HJhbmV0RXZlbnRIYW'.'5'.'kbGVy'.'cw==','U'.'1'.'BS'.'ZWdpc3'.'RlclV'.'wZGF'.'0ZW'.'R'.'JdG'.'Vt','aWJsb2'.'Nr','T2'.'5B'.'ZnRlcklCbG9ja0VsZW1lbnRVcGRhdG'.'U=',''.'aW50c'.'m'.'Fu'.'ZXQ=','Q0'.'ludHJhb'.'mV0'.'RXZlb'.'n'.'R'.'IYW5kbGVycw==','U1BSZWdpc3RlclVwZGF0ZWRJdGV'.'t','Q'.'0lud'.'HJhbm'.'V0'.'U2hhc'.'mVw'.'b2lud'.'Do6'.'QWdlbnRMa'.'XN0cygp'.'Ow==','a'.'W50cmFu'.'ZXQ=','Q0ludHJhbmV0U2hhcmVwb2ludDo6'.'QWdl'.'bnRRdWV1'.'Z'.'SgpOw==','aW50c'.'m'.'FuZXQ=','Q0ludHJhbmV'.'0U2'.'hhcm'.'Vwb'.'2ludDo6QWdl'.'b'.'nRV'.'cGR'.'hd'.'GUoKT'.'s=','aW50'.'cmFuZXQ'.'=','Y3Jt','bWFpbg='.'=','T25CZWZvcmVQcm9'.'sb2c'.'=',''.'bWF'.'pb'.'g==',''.'Q1dpemFy'.'Z'.'FNvbF'.'B'.'hbmVsS'.'W50c'.'mF'.'uZ'.'XQ'.'=','U2h'.'v'.'d1Bhbm'.'V'.'s','L21vZH'.'VsZXM'.'vaW50cmFuZXQvcGFuZW'.'xfY'.'nV'.'0dG9u'.'LnB'.'o'.'cA==','RU5DT0RF','WQ'.'='.'=');return base64_decode($_143384680[$_1791859924]);}};$GLOBALS['____864073948'][0](___247804393(0), ___247804393(1));class CBXFeatures{ private static $_586003061= 30; private static $_135114885= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_1014823581= false; private static $_705622022= false; private static function __715636600(){ if(self::$_1014823581 == false){ self::$_1014823581= array(); foreach(self::$_135114885 as $_9820013 => $_1167213488){ foreach($_1167213488 as $_1441631974) self::$_1014823581[$_1441631974]= $_9820013;}} if(self::$_705622022 == false){ self::$_705622022= array(); $_392770028= COption::GetOptionString(___247804393(2), ___247804393(3), ___247804393(4)); if($GLOBALS['____864073948'][1]($_392770028)>(1388/2-694)){ $_392770028= $GLOBALS['____864073948'][2]($_392770028); self::$_705622022= $GLOBALS['____864073948'][3]($_392770028); if(!$GLOBALS['____864073948'][4](self::$_705622022)) self::$_705622022= array();} if($GLOBALS['____864073948'][5](self::$_705622022) <=(854-2*427)) self::$_705622022= array(___247804393(5) => array(), ___247804393(6) => array());}} public static function InitiateEditionsSettings($_559774457){ self::__715636600(); $_2125695439= array(); foreach(self::$_135114885 as $_9820013 => $_1167213488){ $_1484322243= $GLOBALS['____864073948'][6]($_9820013, $_559774457); self::$_705622022[___247804393(7)][$_9820013]=($_1484322243? array(___247804393(8)): array(___247804393(9))); foreach($_1167213488 as $_1441631974){ self::$_705622022[___247804393(10)][$_1441631974]= $_1484322243; if(!$_1484322243) $_2125695439[]= array($_1441631974, false);}} $_34889616= $GLOBALS['____864073948'][7](self::$_705622022); $_34889616= $GLOBALS['____864073948'][8]($_34889616); COption::SetOptionString(___247804393(11), ___247804393(12), $_34889616); foreach($_2125695439 as $_1085367635) self::__1128592846($_1085367635[(187*2-374)], $_1085367635[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function IsFeatureEnabled($_1441631974){ if($GLOBALS['____864073948'][9]($_1441631974) <= 0) return true; self::__715636600(); if(!$GLOBALS['____864073948'][10]($_1441631974, self::$_1014823581)) return true; if(self::$_1014823581[$_1441631974] == ___247804393(13)) $_1738395225= array(___247804393(14)); elseif($GLOBALS['____864073948'][11](self::$_1014823581[$_1441631974], self::$_705622022[___247804393(15)])) $_1738395225= self::$_705622022[___247804393(16)][self::$_1014823581[$_1441631974]]; else $_1738395225= array(___247804393(17)); if($_1738395225[(904-2*452)] != ___247804393(18) && $_1738395225[(982-2*491)] != ___247804393(19)){ return false;} elseif($_1738395225[(1080/2-540)] == ___247804393(20)){ if($_1738395225[round(0+1)]< $GLOBALS['____864073948'][12]((178*2-356), min(26,0,8.6666666666667),(1400/2-700), Date(___247804393(21)), $GLOBALS['____864073948'][13](___247804393(22))- self::$_586003061, $GLOBALS['____864073948'][14](___247804393(23)))){ if(!isset($_1738395225[round(0+0.5+0.5+0.5+0.5)]) ||!$_1738395225[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) self::__1045443752(self::$_1014823581[$_1441631974]); return false;}} return!$GLOBALS['____864073948'][15]($_1441631974, self::$_705622022[___247804393(24)]) || self::$_705622022[___247804393(25)][$_1441631974];} public static function IsFeatureInstalled($_1441631974){ if($GLOBALS['____864073948'][16]($_1441631974) <= 0) return true; self::__715636600(); return($GLOBALS['____864073948'][17]($_1441631974, self::$_705622022[___247804393(26)]) && self::$_705622022[___247804393(27)][$_1441631974]);} public static function IsFeatureEditable($_1441631974){ if($GLOBALS['____864073948'][18]($_1441631974) <= 0) return true; self::__715636600(); if(!$GLOBALS['____864073948'][19]($_1441631974, self::$_1014823581)) return true; if(self::$_1014823581[$_1441631974] == ___247804393(28)) $_1738395225= array(___247804393(29)); elseif($GLOBALS['____864073948'][20](self::$_1014823581[$_1441631974], self::$_705622022[___247804393(30)])) $_1738395225= self::$_705622022[___247804393(31)][self::$_1014823581[$_1441631974]]; else $_1738395225= array(___247804393(32)); if($_1738395225[min(202,0,67.333333333333)] != ___247804393(33) && $_1738395225[(178*2-356)] != ___247804393(34)){ return false;} elseif($_1738395225[(1296/2-648)] == ___247804393(35)){ if($_1738395225[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____864073948'][21]((1352/2-676),(898-2*449),(982-2*491), Date(___247804393(36)), $GLOBALS['____864073948'][22](___247804393(37))- self::$_586003061, $GLOBALS['____864073948'][23](___247804393(38)))){ if(!isset($_1738395225[round(0+0.5+0.5+0.5+0.5)]) ||!$_1738395225[round(0+1+1)]) self::__1045443752(self::$_1014823581[$_1441631974]); return false;}} return true;} private static function __1128592846($_1441631974, $_1975687230){ if($GLOBALS['____864073948'][24]("CBXFeatures", "On".$_1441631974."SettingsChange")) $GLOBALS['____864073948'][25](array("CBXFeatures", "On".$_1441631974."SettingsChange"), array($_1441631974, $_1975687230)); $_2134692131= $GLOBALS['_____2016020051'][0](___247804393(39), ___247804393(40).$_1441631974.___247804393(41)); while($_274884825= $_2134692131->Fetch()) $GLOBALS['_____2016020051'][1]($_274884825, array($_1441631974, $_1975687230));} public static function SetFeatureEnabled($_1441631974, $_1975687230= true, $_1127425600= true){ if($GLOBALS['____864073948'][26]($_1441631974) <= 0) return; if(!self::IsFeatureEditable($_1441631974)) $_1975687230= false; $_1975687230=($_1975687230? true: false); self::__715636600(); $_866453849=(!$GLOBALS['____864073948'][27]($_1441631974, self::$_705622022[___247804393(42)]) && $_1975687230 || $GLOBALS['____864073948'][28]($_1441631974, self::$_705622022[___247804393(43)]) && $_1975687230 != self::$_705622022[___247804393(44)][$_1441631974]); self::$_705622022[___247804393(45)][$_1441631974]= $_1975687230; $_34889616= $GLOBALS['____864073948'][29](self::$_705622022); $_34889616= $GLOBALS['____864073948'][30]($_34889616); COption::SetOptionString(___247804393(46), ___247804393(47), $_34889616); if($_866453849 && $_1127425600) self::__1128592846($_1441631974, $_1975687230);} private static function __1045443752($_9820013){ if($GLOBALS['____864073948'][31]($_9820013) <= 0 || $_9820013 == "Portal") return; self::__715636600(); if(!$GLOBALS['____864073948'][32]($_9820013, self::$_705622022[___247804393(48)]) || $GLOBALS['____864073948'][33]($_9820013, self::$_705622022[___247804393(49)]) && self::$_705622022[___247804393(50)][$_9820013][min(210,0,70)] != ___247804393(51)) return; if(isset(self::$_705622022[___247804393(52)][$_9820013][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) && self::$_705622022[___247804393(53)][$_9820013][round(0+1+1)]) return; $_2125695439= array(); if($GLOBALS['____864073948'][34]($_9820013, self::$_135114885) && $GLOBALS['____864073948'][35](self::$_135114885[$_9820013])){ foreach(self::$_135114885[$_9820013] as $_1441631974){ if($GLOBALS['____864073948'][36]($_1441631974, self::$_705622022[___247804393(54)]) && self::$_705622022[___247804393(55)][$_1441631974]){ self::$_705622022[___247804393(56)][$_1441631974]= false; $_2125695439[]= array($_1441631974, false);}} self::$_705622022[___247804393(57)][$_9820013][round(0+0.4+0.4+0.4+0.4+0.4)]= true;} $_34889616= $GLOBALS['____864073948'][37](self::$_705622022); $_34889616= $GLOBALS['____864073948'][38]($_34889616); COption::SetOptionString(___247804393(58), ___247804393(59), $_34889616); foreach($_2125695439 as $_1085367635) self::__1128592846($_1085367635[(1008/2-504)], $_1085367635[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function ModifyFeaturesSettings($_559774457, $_1167213488){ self::__715636600(); foreach($_559774457 as $_9820013 => $_1460647689) self::$_705622022[___247804393(60)][$_9820013]= $_1460647689; $_2125695439= array(); foreach($_1167213488 as $_1441631974 => $_1975687230){ if(!$GLOBALS['____864073948'][39]($_1441631974, self::$_705622022[___247804393(61)]) && $_1975687230 || $GLOBALS['____864073948'][40]($_1441631974, self::$_705622022[___247804393(62)]) && $_1975687230 != self::$_705622022[___247804393(63)][$_1441631974]) $_2125695439[]= array($_1441631974, $_1975687230); self::$_705622022[___247804393(64)][$_1441631974]= $_1975687230;} $_34889616= $GLOBALS['____864073948'][41](self::$_705622022); $_34889616= $GLOBALS['____864073948'][42]($_34889616); COption::SetOptionString(___247804393(65), ___247804393(66), $_34889616); self::$_705622022= false; foreach($_2125695439 as $_1085367635) self::__1128592846($_1085367635[(930-2*465)], $_1085367635[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function SaveFeaturesSettings($_1017865207, $_1834169268){ self::__715636600(); $_413664586= array(___247804393(67) => array(), ___247804393(68) => array()); if(!$GLOBALS['____864073948'][43]($_1017865207)) $_1017865207= array(); if(!$GLOBALS['____864073948'][44]($_1834169268)) $_1834169268= array(); if(!$GLOBALS['____864073948'][45](___247804393(69), $_1017865207)) $_1017865207[]= ___247804393(70); foreach(self::$_135114885 as $_9820013 => $_1167213488){ if($GLOBALS['____864073948'][46]($_9820013, self::$_705622022[___247804393(71)])) $_2043786906= self::$_705622022[___247804393(72)][$_9820013]; else $_2043786906=($_9820013 == ___247804393(73))? array(___247804393(74)): array(___247804393(75)); if($_2043786906[(1132/2-566)] == ___247804393(76) || $_2043786906[min(104,0,34.666666666667)] == ___247804393(77)){ $_413664586[___247804393(78)][$_9820013]= $_2043786906;} else{ if($GLOBALS['____864073948'][47]($_9820013, $_1017865207)) $_413664586[___247804393(79)][$_9820013]= array(___247804393(80), $GLOBALS['____864073948'][48]((1460/2-730), min(72,0,24),(758-2*379), $GLOBALS['____864073948'][49](___247804393(81)), $GLOBALS['____864073948'][50](___247804393(82)), $GLOBALS['____864073948'][51](___247804393(83)))); else $_413664586[___247804393(84)][$_9820013]= array(___247804393(85));}} $_2125695439= array(); foreach(self::$_1014823581 as $_1441631974 => $_9820013){ if($_413664586[___247804393(86)][$_9820013][min(38,0,12.666666666667)] != ___247804393(87) && $_413664586[___247804393(88)][$_9820013][(130*2-260)] != ___247804393(89)){ $_413664586[___247804393(90)][$_1441631974]= false;} else{ if($_413664586[___247804393(91)][$_9820013][min(190,0,63.333333333333)] == ___247804393(92) && $_413664586[___247804393(93)][$_9820013][round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____864073948'][52]((203*2-406), min(50,0,16.666666666667), min(248,0,82.666666666667), Date(___247804393(94)), $GLOBALS['____864073948'][53](___247804393(95))- self::$_586003061, $GLOBALS['____864073948'][54](___247804393(96)))) $_413664586[___247804393(97)][$_1441631974]= false; else $_413664586[___247804393(98)][$_1441631974]= $GLOBALS['____864073948'][55]($_1441631974, $_1834169268); if(!$GLOBALS['____864073948'][56]($_1441631974, self::$_705622022[___247804393(99)]) && $_413664586[___247804393(100)][$_1441631974] || $GLOBALS['____864073948'][57]($_1441631974, self::$_705622022[___247804393(101)]) && $_413664586[___247804393(102)][$_1441631974] != self::$_705622022[___247804393(103)][$_1441631974]) $_2125695439[]= array($_1441631974, $_413664586[___247804393(104)][$_1441631974]);}} $_34889616= $GLOBALS['____864073948'][58]($_413664586); $_34889616= $GLOBALS['____864073948'][59]($_34889616); COption::SetOptionString(___247804393(105), ___247804393(106), $_34889616); self::$_705622022= false; foreach($_2125695439 as $_1085367635) self::__1128592846($_1085367635[(866-2*433)], $_1085367635[round(0+0.5+0.5)]);} public static function GetFeaturesList(){ self::__715636600(); $_652743690= array(); foreach(self::$_135114885 as $_9820013 => $_1167213488){ if($GLOBALS['____864073948'][60]($_9820013, self::$_705622022[___247804393(107)])) $_2043786906= self::$_705622022[___247804393(108)][$_9820013]; else $_2043786906=($_9820013 == ___247804393(109))? array(___247804393(110)): array(___247804393(111)); $_652743690[$_9820013]= array( ___247804393(112) => $_2043786906[(976-2*488)], ___247804393(113) => $_2043786906[round(0+0.2+0.2+0.2+0.2+0.2)], ___247804393(114) => array(),); $_652743690[$_9820013][___247804393(115)]= false; if($_652743690[$_9820013][___247804393(116)] == ___247804393(117)){ $_652743690[$_9820013][___247804393(118)]= $GLOBALS['____864073948'][61](($GLOBALS['____864073948'][62]()- $_652743690[$_9820013][___247804393(119)])/ round(0+28800+28800+28800)); if($_652743690[$_9820013][___247804393(120)]> self::$_586003061) $_652743690[$_9820013][___247804393(121)]= true;} foreach($_1167213488 as $_1441631974) $_652743690[$_9820013][___247804393(122)][$_1441631974]=(!$GLOBALS['____864073948'][63]($_1441631974, self::$_705622022[___247804393(123)]) || self::$_705622022[___247804393(124)][$_1441631974]);} return $_652743690;} private static function __2069306795($_654256885, $_1978206232){ if(IsModuleInstalled($_654256885) == $_1978206232) return true; $_728702152= $_SERVER[___247804393(125)].___247804393(126).$_654256885.___247804393(127); if(!$GLOBALS['____864073948'][64]($_728702152)) return false; include_once($_728702152); $_293371415= $GLOBALS['____864073948'][65](___247804393(128), ___247804393(129), $_654256885); if(!$GLOBALS['____864073948'][66]($_293371415)) return false; $_543035861= new $_293371415; if($_1978206232){ if(!$_543035861->InstallDB()) return false; $_543035861->InstallEvents(); if(!$_543035861->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___247804393(130))) CSearch::DeleteIndex($_654256885); UnRegisterModule($_654256885);} return true;} protected static function OnRequestsSettingsChange($_1441631974, $_1975687230){ self::__2069306795("form", $_1975687230);} protected static function OnLearningSettingsChange($_1441631974, $_1975687230){ self::__2069306795("learning", $_1975687230);} protected static function OnJabberSettingsChange($_1441631974, $_1975687230){ self::__2069306795("xmpp", $_1975687230);} protected static function OnVideoConferenceSettingsChange($_1441631974, $_1975687230){ self::__2069306795("video", $_1975687230);} protected static function OnBizProcSettingsChange($_1441631974, $_1975687230){ self::__2069306795("bizprocdesigner", $_1975687230);} protected static function OnListsSettingsChange($_1441631974, $_1975687230){ self::__2069306795("lists", $_1975687230);} protected static function OnWikiSettingsChange($_1441631974, $_1975687230){ self::__2069306795("wiki", $_1975687230);} protected static function OnSupportSettingsChange($_1441631974, $_1975687230){ self::__2069306795("support", $_1975687230);} protected static function OnControllerSettingsChange($_1441631974, $_1975687230){ self::__2069306795("controller", $_1975687230);} protected static function OnAnalyticsSettingsChange($_1441631974, $_1975687230){ self::__2069306795("statistic", $_1975687230);} protected static function OnVoteSettingsChange($_1441631974, $_1975687230){ self::__2069306795("vote", $_1975687230);} protected static function OnFriendsSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(131); $_1324232925= CSite::GetList(($_1484322243= ___247804393(132)),($_642811045= ___247804393(133)), array(___247804393(134) => ___247804393(135))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(136), ___247804393(137), ___247804393(138), $_252348680[___247804393(139)]) != $_1288308364){ COption::SetOptionString(___247804393(140), ___247804393(141), $_1288308364, false, $_252348680[___247804393(142)]); COption::SetOptionString(___247804393(143), ___247804393(144), $_1288308364);}}} protected static function OnMicroBlogSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(145); $_1324232925= CSite::GetList(($_1484322243= ___247804393(146)),($_642811045= ___247804393(147)), array(___247804393(148) => ___247804393(149))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(150), ___247804393(151), ___247804393(152), $_252348680[___247804393(153)]) != $_1288308364){ COption::SetOptionString(___247804393(154), ___247804393(155), $_1288308364, false, $_252348680[___247804393(156)]); COption::SetOptionString(___247804393(157), ___247804393(158), $_1288308364);} if(COption::GetOptionString(___247804393(159), ___247804393(160), ___247804393(161), $_252348680[___247804393(162)]) != $_1288308364){ COption::SetOptionString(___247804393(163), ___247804393(164), $_1288308364, false, $_252348680[___247804393(165)]); COption::SetOptionString(___247804393(166), ___247804393(167), $_1288308364);}}} protected static function OnPersonalFilesSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(168); $_1324232925= CSite::GetList(($_1484322243= ___247804393(169)),($_642811045= ___247804393(170)), array(___247804393(171) => ___247804393(172))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(173), ___247804393(174), ___247804393(175), $_252348680[___247804393(176)]) != $_1288308364){ COption::SetOptionString(___247804393(177), ___247804393(178), $_1288308364, false, $_252348680[___247804393(179)]); COption::SetOptionString(___247804393(180), ___247804393(181), $_1288308364);}}} protected static function OnPersonalBlogSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(182); $_1324232925= CSite::GetList(($_1484322243= ___247804393(183)),($_642811045= ___247804393(184)), array(___247804393(185) => ___247804393(186))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(187), ___247804393(188), ___247804393(189), $_252348680[___247804393(190)]) != $_1288308364){ COption::SetOptionString(___247804393(191), ___247804393(192), $_1288308364, false, $_252348680[___247804393(193)]); COption::SetOptionString(___247804393(194), ___247804393(195), $_1288308364);}}} protected static function OnPersonalPhotoSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(196); $_1324232925= CSite::GetList(($_1484322243= ___247804393(197)),($_642811045= ___247804393(198)), array(___247804393(199) => ___247804393(200))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(201), ___247804393(202), ___247804393(203), $_252348680[___247804393(204)]) != $_1288308364){ COption::SetOptionString(___247804393(205), ___247804393(206), $_1288308364, false, $_252348680[___247804393(207)]); COption::SetOptionString(___247804393(208), ___247804393(209), $_1288308364);}}} protected static function OnPersonalForumSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(210); $_1324232925= CSite::GetList(($_1484322243= ___247804393(211)),($_642811045= ___247804393(212)), array(___247804393(213) => ___247804393(214))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(215), ___247804393(216), ___247804393(217), $_252348680[___247804393(218)]) != $_1288308364){ COption::SetOptionString(___247804393(219), ___247804393(220), $_1288308364, false, $_252348680[___247804393(221)]); COption::SetOptionString(___247804393(222), ___247804393(223), $_1288308364);}}} protected static function OnTasksSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(224); $_1324232925= CSite::GetList(($_1484322243= ___247804393(225)),($_642811045= ___247804393(226)), array(___247804393(227) => ___247804393(228))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(229), ___247804393(230), ___247804393(231), $_252348680[___247804393(232)]) != $_1288308364){ COption::SetOptionString(___247804393(233), ___247804393(234), $_1288308364, false, $_252348680[___247804393(235)]); COption::SetOptionString(___247804393(236), ___247804393(237), $_1288308364);} if(COption::GetOptionString(___247804393(238), ___247804393(239), ___247804393(240), $_252348680[___247804393(241)]) != $_1288308364){ COption::SetOptionString(___247804393(242), ___247804393(243), $_1288308364, false, $_252348680[___247804393(244)]); COption::SetOptionString(___247804393(245), ___247804393(246), $_1288308364);}} self::__2069306795(___247804393(247), $_1975687230);} protected static function OnCalendarSettingsChange($_1441631974, $_1975687230){ if($_1975687230) $_1288308364= "Y"; else $_1288308364= ___247804393(248); $_1324232925= CSite::GetList(($_1484322243= ___247804393(249)),($_642811045= ___247804393(250)), array(___247804393(251) => ___247804393(252))); while($_252348680= $_1324232925->Fetch()){ if(COption::GetOptionString(___247804393(253), ___247804393(254), ___247804393(255), $_252348680[___247804393(256)]) != $_1288308364){ COption::SetOptionString(___247804393(257), ___247804393(258), $_1288308364, false, $_252348680[___247804393(259)]); COption::SetOptionString(___247804393(260), ___247804393(261), $_1288308364);} if(COption::GetOptionString(___247804393(262), ___247804393(263), ___247804393(264), $_252348680[___247804393(265)]) != $_1288308364){ COption::SetOptionString(___247804393(266), ___247804393(267), $_1288308364, false, $_252348680[___247804393(268)]); COption::SetOptionString(___247804393(269), ___247804393(270), $_1288308364);}}} protected static function OnSMTPSettingsChange($_1441631974, $_1975687230){ self::__2069306795("mail", $_1975687230);} protected static function OnExtranetSettingsChange($_1441631974, $_1975687230){ $_1244819504= COption::GetOptionString("extranet", "extranet_site", ""); if($_1244819504){ $_1402322213= new CSite; $_1402322213->Update($_1244819504, array(___247804393(271) =>($_1975687230? ___247804393(272): ___247804393(273))));} self::__2069306795(___247804393(274), $_1975687230);} protected static function OnDAVSettingsChange($_1441631974, $_1975687230){ self::__2069306795("dav", $_1975687230);} protected static function OntimemanSettingsChange($_1441631974, $_1975687230){ self::__2069306795("timeman", $_1975687230);} protected static function Onintranet_sharepointSettingsChange($_1441631974, $_1975687230){ if($_1975687230){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___247804393(275), ___247804393(276), ___247804393(277), ___247804393(278), ___247804393(279)); CAgent::AddAgent(___247804393(280), ___247804393(281), ___247804393(282), round(0+250+250)); CAgent::AddAgent(___247804393(283), ___247804393(284), ___247804393(285), round(0+300)); CAgent::AddAgent(___247804393(286), ___247804393(287), ___247804393(288), round(0+1800+1800));} else{ UnRegisterModuleDependences(___247804393(289), ___247804393(290), ___247804393(291), ___247804393(292), ___247804393(293)); UnRegisterModuleDependences(___247804393(294), ___247804393(295), ___247804393(296), ___247804393(297), ___247804393(298)); CAgent::RemoveAgent(___247804393(299), ___247804393(300)); CAgent::RemoveAgent(___247804393(301), ___247804393(302)); CAgent::RemoveAgent(___247804393(303), ___247804393(304));}} protected static function OncrmSettingsChange($_1441631974, $_1975687230){ if($_1975687230) COption::SetOptionString("crm", "form_features", "Y"); self::__2069306795(___247804393(305), $_1975687230);} protected static function OnClusterSettingsChange($_1441631974, $_1975687230){ self::__2069306795("cluster", $_1975687230);} protected static function OnMultiSitesSettingsChange($_1441631974, $_1975687230){ if($_1975687230) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___247804393(306), ___247804393(307), ___247804393(308), ___247804393(309), ___247804393(310), ___247804393(311));} protected static function OnIdeaSettingsChange($_1441631974, $_1975687230){ self::__2069306795("idea", $_1975687230);} protected static function OnMeetingSettingsChange($_1441631974, $_1975687230){ self::__2069306795("meeting", $_1975687230);} protected static function OnXDImportSettingsChange($_1441631974, $_1975687230){ self::__2069306795("xdimport", $_1975687230);}} $GLOBALS['____864073948'][67](___247804393(312), ___247804393(313));/**/			//Do not remove this

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
				$USER_LID = SITE_ID;
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
				$arAuthResult = CUser::SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], $_REQUEST["USER_PHONE_NUMBER"]);
			}
			elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "CHANGE_PWD")
			{
				$arAuthResult = $GLOBALS["USER"]->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], true, $_REQUEST["USER_PHONE_NUMBER"]);
			}
			elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y" && $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION!==true))
			{
				$arAuthResult = $GLOBALS["USER"]->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], false, $_REQUEST["USER_PHONE_NUMBER"]);
			}

			if($_REQUEST["TYPE"] == "AUTH" || $_REQUEST["TYPE"] == "OTP")
			{
				//special login form in the control panel
				if($arAuthResult === true && defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					//store cookies for next hit (see CMain::GetSpreadCookieHTML())
					$GLOBALS["APPLICATION"]->StoreCookies();
					$_SESSION['BX_ADMIN_LOAD_AUTH'] = true;

					//logout or re-authorize the user if something importand has changed
					$GLOBALS["USER"]->CheckAuthActions();

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

/*ZDUyZmZOGQxYjFhY2U5NWM3ZDk5YTZiZjgwNTI2MmY5Y2U0YTM=*/$GLOBALS['____1085506061']= array(base64_decode(''.'bXRfcmFuZ'.'A=='),base64_decode('Z'.'Xhw'.'bG9kZQ=='),base64_decode(''.'cG'.'Fjaw='.'='),base64_decode('b'.'WQ1'),base64_decode('Y29u'.'c3'.'R'.'hbnQ='),base64_decode('aGFz'.'aF9'.'obWFj'),base64_decode('c'.'3'.'Ry'.'Y'.'21w'),base64_decode('a'.'XNfb2JqZ'.'WN0'),base64_decode('Y2FsbF91c2VyX2Z1bmM'.'='),base64_decode(''.'Y2FsbF'.'91c2VyX2Z1b'.'mM='),base64_decode('Y'.'2F'.'sb'.'F91c'.'2'.'VyX2Z1bmM='),base64_decode('Y'.'2F'.'s'.'bF'.'91c2VyX'.'2Z1b'.'mM'.'='),base64_decode(''.'Y2FsbF'.'91'.'c2VyX2'.'Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___1993498747')){function ___1993498747($_1384682401){static $_576214006= false; if($_576214006 == false) $_576214006=array('RE'.'I'.'=','U0V'.'MRUNUIFZ'.'BT'.'FVF'.'IEZST'.'00gY'.'l9vc'.'HRpb24gV'.'0'.'hF'.'U'.'kUgTkFNRT'.'0nf'.'lBBUkFNX0'.'1B'.'W'.'F9V'.'U0VSUycgQ'.'U5E'.'IE1'.'PRF'.'V'.'MRV9JRD0nbWFpb'.'icg'.'QU5EIFNJV'.'EVfSUQgSVMgTl'.'VMTA'.'='.'=','Vk'.'FMVUU=','L'.'g==','SCo=',''.'Yml0cml4','TElDRU5TRV9LRV'.'k=',''.'c2hhMjU2','VVNFU'.'g==','VVN'.'F'.'Ug==','V'.'VNFUg'.'==',''.'SXNBd'.'X'.'Rob3JpemVk','V'.'VNFU'.'g==','S'.'XNBZ'.'G1pbg='.'=','QVBQTE'.'lDQVRJT04=','UmVz'.'dGFydEJ1ZmZlcg==',''.'TG9j'.'YWxSZWR'.'pcm'.'V'.'jdA==','L2xpY2Vuc2V'.'fcmVzdHJp'.'Y3'.'Rpb24ucG'.'h'.'w','XE'.'JpdH'.'JpeF'.'xNYW'.'luXEN'.'v'.'bmZpZ1xPcH'.'Rpb24'.'6OnNldA==',''.'bWFp'.'bg'.'==',''.'UEFSQ'.'U1f'.'TUFY'.'X'.'1VTRVJT');return base64_decode($_576214006[$_1384682401]);}};if($GLOBALS['____1085506061'][0](round(0+0.2+0.2+0.2+0.2+0.2), round(0+5+5+5+5)) == round(0+1.4+1.4+1.4+1.4+1.4)){ $_1808950182= $GLOBALS[___1993498747(0)]->Query(___1993498747(1), true); if($_663403129= $_1808950182->Fetch()){ $_1345317142= $_663403129[___1993498747(2)]; list($_2123403801, $_1719463794)= $GLOBALS['____1085506061'][1](___1993498747(3), $_1345317142); $_460619127= $GLOBALS['____1085506061'][2](___1993498747(4), $_2123403801); $_2021367280= ___1993498747(5).$GLOBALS['____1085506061'][3]($GLOBALS['____1085506061'][4](___1993498747(6))); $_1576600350= $GLOBALS['____1085506061'][5](___1993498747(7), $_1719463794, $_2021367280, true); if($GLOBALS['____1085506061'][6]($_1576600350, $_460619127) !== min(20,0,6.6666666666667)){ if(isset($GLOBALS[___1993498747(8)]) && $GLOBALS['____1085506061'][7]($GLOBALS[___1993498747(9)]) && $GLOBALS['____1085506061'][8](array($GLOBALS[___1993498747(10)], ___1993498747(11))) &&!$GLOBALS['____1085506061'][9](array($GLOBALS[___1993498747(12)], ___1993498747(13)))){ $GLOBALS['____1085506061'][10](array($GLOBALS[___1993498747(14)], ___1993498747(15))); $GLOBALS['____1085506061'][11](___1993498747(16), ___1993498747(17), true);}}} else{ $GLOBALS['____1085506061'][12](___1993498747(18), ___1993498747(19), ___1993498747(20), round(0+6+6));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
