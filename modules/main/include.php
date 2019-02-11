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

/*ZDUyZmZZWRkOWVhMWI0M2YxMzY1OGI1MTBjNjYyMzUxOGExNzA=*/$GLOBALS['_____867960505']= array(base64_decode('R2V0TW9kd'.'W'.'xlRXZlbnRz'),base64_decode(''.'RXhlY3V0'.'ZU1vZH'.'VsZU'.'V2ZW50RXg'.'='));$GLOBALS['____86168232']= array(base64_decode('Z'.'GVmaW5l'),base64_decode('c'.'3R'.'ybGVu'),base64_decode('Y'.'mFzZ'.'TY0X2'.'RlY29kZQ=='),base64_decode('dW5zZ'.'XJpYW'.'xpem'.'U='),base64_decode(''.'aXNfYXJyYXk'.'='),base64_decode('Y2'.'91bn'.'Q='),base64_decode('a'.'W5f'.'YXJ'.'yYXk='),base64_decode('c2VyaWFsa'.'Xpl'),base64_decode(''.'YmFzZT'.'Y0'.'X2V'.'uY29kZQ=='),base64_decode('c3Ry'.'bGVu'),base64_decode('YXJyY'.'Xlf'.'a'.'2V5X2V4aXN0cw'.'='.'='),base64_decode('YXJ'.'yYXlfa2V5'.'X'.'2V'.'4aX'.'N0cw=='),base64_decode('bWt0aW1l'),base64_decode('ZGF0ZQ'.'=='),base64_decode('ZG'.'F'.'0ZQ='.'='),base64_decode('Y'.'XJyYXlfa2V5X2V4a'.'XN0c'.'w=='),base64_decode('c3R'.'y'.'bGVu'),base64_decode('YXJyYXlf'.'a2'.'V5X2V4aX'.'N0cw=='),base64_decode('c3RybGVu'),base64_decode('YXJyY'.'Xlfa2V5X2V4aXN0cw=='),base64_decode('Y'.'XJyYXlfa2V5X2V'.'4a'.'X'.'N0'.'cw'.'=='),base64_decode('bWt0aW1l'),base64_decode('ZGF0ZQ'.'=='),base64_decode('ZGF0ZQ'.'=='),base64_decode('bWV0a'.'G9k'.'X2V4aXN'.'0'.'cw='.'='),base64_decode(''.'Y2Fsb'.'F'.'91c2VyX'.'2Z1bmNf'.'YX'.'JyYX'.'k='),base64_decode('c3Ryb'.'GV'.'u'),base64_decode('Y'.'XJyYXlfa2'.'V5'.'X'.'2V4aXN0cw='.'='),base64_decode('Y'.'XJy'.'YXlfa2V'.'5X'.'2'.'V'.'4aXN'.'0cw'.'=='),base64_decode('c2'.'Vy'.'a'.'WFsaXpl'),base64_decode('Y'.'mFzZTY0X2'.'VuY29kZQ=='),base64_decode('c'.'3'.'RybGVu'),base64_decode('YXJyYXlfa2V5X'.'2V4aXN'.'0cw=='),base64_decode('YXJyYXlfa2V5'.'X2'.'V4aX'.'N0cw=='),base64_decode('Y'.'X'.'Jy'.'YXlfa'.'2V5X'.'2V4aXN0cw=='),base64_decode('aXN'.'fYX'.'JyYXk'.'='),base64_decode('YXJyY'.'Xlfa2V5X'.'2V'.'4a'.'XN'.'0cw='.'='),base64_decode('c2VyaWFsaXpl'),base64_decode('YmFzZT'.'Y'.'0X2'.'VuY'.'2'.'9'.'kZ'.'Q=='),base64_decode(''.'YX'.'JyYXlfa2V5X2'.'V4a'.'XN0cw=='),base64_decode('YXJyYXlfa'.'2V5X2V4'.'aXN0cw=='),base64_decode('c'.'2'.'V'.'y'.'aWFsaX'.'pl'),base64_decode('Y'.'mFzZTY0X2'.'Vu'.'Y29kZ'.'Q=='),base64_decode('aXNfYXJyYXk='),base64_decode(''.'aXNfY'.'XJyYXk='),base64_decode('aW5'.'f'.'YXJyYXk='),base64_decode('YXJy'.'Y'.'Xlfa2V5X2V4aXN0cw=='),base64_decode('aW'.'5fYXJyYX'.'k'.'='),base64_decode('bWt'.'0aW'.'1'.'l'),base64_decode(''.'ZGF0ZQ='.'='),base64_decode('ZGF0Z'.'Q=='),base64_decode('Z'.'GF0ZQ=='),base64_decode('bWt0aW1l'),base64_decode('Z'.'GF0ZQ=='),base64_decode('Z'.'GF0Z'.'Q'.'=='),base64_decode('aW'.'5fYXJy'.'YXk='),base64_decode('YXJyYXlfa2'.'V'.'5X'.'2V'.'4aXN0c'.'w=='),base64_decode(''.'YX'.'JyYX'.'lfa2'.'V5X2'.'V4a'.'X'.'N'.'0cw='.'='),base64_decode('c2Vy'.'a'.'WFs'.'aX'.'pl'),base64_decode('YmFzZT'.'Y0X'.'2VuY29k'.'ZQ=='),base64_decode('YX'.'JyYXlfa2'.'V5X2V4aXN0cw=='),base64_decode('aW50dm'.'Fs'),base64_decode('dGl'.'tZQ=='),base64_decode('YXJ'.'yYX'.'lfa'.'2V'.'5'.'X2V4a'.'XN0'.'cw=='),base64_decode('ZmlsZV9leGlz'.'dHM='),base64_decode('c3RyX3JlcGxhY'.'2U='),base64_decode('Y2xhc'.'3NfZXhpc'.'3Rz'),base64_decode('Z'.'GVma'.'W'.'5l'));if(!function_exists(__NAMESPACE__.'\\___1085525023')){function ___1085525023($_1882643737){static $_49855193= false; if($_49855193 == false) $_49855193=array('S'.'U5'.'UU'.'kF'.'O'.'RVRfRURJ'.'VElPTg==','WQ==','b'.'WFpbg==','fmNwZl9tYXB'.'f'.'d'.'mF'.'sdWU=','','ZQ==','Zg==','Z'.'Q='.'=',''.'Rg==',''.'WA='.'=','Zg='.'=','bWF'.'pbg='.'=','fmNwZl9tYX'.'BfdmFsd'.'W'.'U=','UG'.'9ydGFs','Rg==','ZQ==','ZQ='.'=','W'.'A==','Rg'.'==','RA==','RA==','bQ==','ZA==','W'.'Q==','Zg==','Z'.'g='.'=','Z'.'g==','Zg==','UG'.'9ydGFs','Rg==','ZQ'.'='.'=','Z'.'Q==',''.'WA='.'=','R'.'g==',''.'RA='.'=',''.'R'.'A='.'=','bQ==','ZA==','WQ==',''.'bWFpbg='.'=','T24=','U2V'.'0d'.'Glu'.'Z3'.'ND'.'aGFuZ2'.'U=','Zg==',''.'Zg='.'=','Z'.'g==',''.'Zg'.'==','b'.'W'.'Fpb'.'g==',''.'fmNw'.'Zl9tYXBfdmFsdWU=','ZQ==','ZQ'.'==','ZQ==','R'.'A'.'='.'=','ZQ'.'='.'=',''.'ZQ==','Zg'.'==',''.'Zg'.'==',''.'Zg'.'==','ZQ==',''.'bWFpbg'.'==',''.'fm'.'N'.'wZl'.'9t'.'Y'.'XBfdmFsdWU=','ZQ'.'='.'=','Zg==','Z'.'g==','Zg'.'==','Z'.'g==','bWFp'.'bg==','fmNwZl9tYX'.'Bf'.'dmFsd'.'WU=','Z'.'Q==','Zg==','UG9ydGF'.'s','UG9ydGFs','ZQ='.'=','ZQ'.'='.'=','UG9ydGFs',''.'Rg='.'=','W'.'A==','Rg==',''.'RA==','ZQ==','ZQ==','RA==','bQ='.'=',''.'ZA==','WQ==','ZQ='.'=','W'.'A'.'='.'=','ZQ==','Rg==',''.'Z'.'Q==','RA==','Zg==','ZQ==','R'.'A==',''.'Z'.'Q==',''.'bQ==',''.'ZA==','WQ'.'==','Zg='.'=',''.'Zg==','Zg==','Zg==','Zg==','Z'.'g==',''.'Z'.'g'.'==','Zg==',''.'bWF'.'p'.'bg'.'==',''.'fm'.'N'.'wZl9tYX'.'Bf'.'dmFsdWU=','Z'.'Q'.'==',''.'ZQ==','U'.'G9'.'y'.'dGFs',''.'Rg==','WA==',''.'VF'.'lQRQ==','R'.'EFURQ==','RkVBVFV'.'S'.'RVM=','RVh'.'QSVJFRA==','VFlQRQ='.'=','RA'.'==','VFJZ'.'X0RBWVNfQ09VTlQ'.'=','R'.'EFURQ==','VFJ'.'ZX'.'0RBWV'.'NfQ09VTlQ=','R'.'Vh'.'QSVJFR'.'A==','RkVBVFVSRVM=','Zg'.'==',''.'Z'.'g='.'=','RE9D'.'V'.'U1FT'.'lRfU'.'k9PVA==',''.'L2'.'JpdH'.'JpeC9'.'tb2R1bG'.'VzLw'.'='.'=','L'.'2lu'.'c3'.'RhbGw'.'vaW5kZXgu'.'cGhw','Lg==','Xw='.'=','c2'.'VhcmNo','Tg='.'=','','',''.'QUNU'.'SVZF','WQ'.'==','c29jaW'.'FsbmV0d29'.'y'.'aw'.'==','YWxsb3dfZnJpZ'.'Wx'.'k'.'cw==',''.'WQ==','SUQ=',''.'c29'.'jaWFsb'.'m'.'V0d'.'29yaw==','YWx'.'sb'.'3dfZ'.'nJpZWxkcw'.'==','S'.'UQ=','c29jaWFsbmV0d29yaw==','Y'.'W'.'xs'.'b3dfZnJpZWxk'.'c'.'w==','Tg==','','','QUNU'.'SVZF','WQ==',''.'c29'.'jaWF'.'s'.'bmV0d2'.'9y'.'a'.'w==','Y'.'Wxs'.'b3df'.'bWlj'.'cm9ib'.'G9nX3V'.'z'.'ZXI=','WQ==','SUQ=','c'.'29ja'.'WFsbmV0d'.'2'.'9ya'.'w==','YWxsb3'.'df'.'b'.'Wljcm9i'.'bG9n'.'X3VzZXI'.'=','S'.'U'.'Q=',''.'c29'.'jaW'.'FsbmV0'.'d29ya'.'w==','YWxsb3df'.'bWljcm9'.'ibG9nX3'.'VzZX'.'I=','c29jaWFsbmV'.'0'.'d2'.'9ya'.'w==','Y'.'Wxsb3'.'dfbWljcm'.'9ibG9nX2dyb'.'3'.'V'.'w','WQ==','SUQ=','c29ja'.'WFsbm'.'V0d29yaw='.'=','YW'.'x'.'sb3dfbW'.'ljcm9ib'.'G9nX2dyb'.'3Vw','SU'.'Q'.'=','c29j'.'aWF'.'sbm'.'V'.'0d29yaw==','Y'.'Wxs'.'b3d'.'fbWljcm9ibG9nX2dyb3Vw','T'.'g==','','',''.'QUNUSV'.'ZF',''.'WQ==','c29ja'.'W'.'FsbmV0d29y'.'a'.'w==','YW'.'xsb3'.'dfZmlsZ'.'X'.'NfdXNlcg'.'==','WQ='.'=','S'.'UQ'.'=',''.'c29'.'jaWFsbmV0'.'d29yaw='.'=','YWxsb3dfZmlsZXNfdXNlcg='.'=','S'.'UQ=','c29j'.'aWFs'.'bmV0d29yaw==','YW'.'xsb3dfZmlsZXNfdXNlcg='.'=','Tg'.'='.'=','','','QU'.'NUSVZ'.'F','WQ==','c29j'.'aWFs'.'bm'.'V0d2'.'9ya'.'w==','YWxsb3d'.'fYmxvZ'.'191c'.'2V'.'y',''.'WQ='.'=','SUQ=','c'.'29jaWFsbmV0d29y'.'aw==',''.'Y'.'W'.'x'.'sb3dfYm'.'xvZ191c2Vy','SUQ'.'=','c2'.'9ja'.'WF'.'s'.'b'.'mV0'.'d'.'29yaw==','YWxsb3dfYmxvZ'.'191c'.'2'.'Vy','Tg'.'='.'=','','','QUNU'.'SVZ'.'F','WQ='.'=',''.'c29j'.'aWFsbmV0d29y'.'a'.'w'.'='.'=','Y'.'W'.'xsb3d'.'f'.'cGhvdG9fdX'.'N'.'l'.'cg='.'=','WQ'.'==','SUQ'.'=','c29jaWFsb'.'m'.'V0d29'.'yaw='.'=','YWxsb3dfcGh'.'vdG9'.'fdXN'.'lcg==','SUQ=','c29ja'.'WF'.'sbmV0d29yaw==','YWxs'.'b3dfcGhvdG9'.'fdXNlcg'.'==','Tg'.'==','','','QUNUSVZF','WQ==',''.'c29j'.'aWFsb'.'mV0d29yaw'.'==','YWxs'.'b'.'3df'.'Zm'.'9ydW1'.'fd'.'XNlc'.'g==','WQ='.'=','SUQ=','c29'.'jaWFsbmV'.'0d29y'.'aw'.'='.'=','YWx'.'s'.'b3dfZ'.'m'.'9yd'.'W1'.'fdX'.'Nlc'.'g==','S'.'UQ=','c2'.'9j'.'aWFsbmV'.'0d29y'.'a'.'w==','YW'.'xsb3dfZm9ydW1'.'fdXNl'.'cg'.'='.'=',''.'T'.'g==','','','QUN'.'USVZF','W'.'Q==',''.'c29jaWFsbm'.'V0d2'.'9y'.'a'.'w==',''.'YWx'.'sb3'.'dfd'.'G'.'F'.'za3NfdXNlc'.'g='.'=','W'.'Q==','SUQ'.'=',''.'c29jaWFsbm'.'V0'.'d29y'.'aw==',''.'YWxsb3dfdG'.'Fza'.'3NfdXNl'.'cg==','SUQ=','c29ja'.'WFsbmV0d2'.'9y'.'aw==','YWxs'.'b3dfdGF'.'za3'.'N'.'f'.'dXNlc'.'g==','c29jaWFsbmV0'.'d29yaw==',''.'Y'.'W'.'xsb3dfd'.'GFza3'.'Nf'.'Z'.'3'.'Jv'.'d'.'XA'.'=','WQ==','S'.'UQ=','c'.'29jaWFsbm'.'V0d'.'29yaw='.'=','YWxsb3dfdGFza3Nf'.'Z3J'.'vdXA=','SUQ=','c29j'.'a'.'WFsb'.'mV'.'0'.'d29yaw==','YWxs'.'b3dfdGFza'.'3Nf'.'Z3Jv'.'dXA=',''.'dGFza3'.'M=','Tg==','','','QUNUSVZF',''.'WQ==','c29jaWFsbmV0d29yaw==','YWxsb3dfY2Fs'.'ZW'.'5k'.'YXJfd'.'XNlc'.'g==',''.'W'.'Q==',''.'SUQ=','c29j'.'aWFs'.'b'.'mV0d2'.'9yaw==',''.'YWxsb3df'.'Y2FsZW5kY'.'X'.'JfdXNlcg='.'=','SUQ=','c'.'29ja'.'WFs'.'bmV0'.'d'.'2'.'9yaw'.'==','Y'.'Wxsb3dfY2F'.'s'.'Z'.'W'.'5kY'.'XJfd'.'X'.'Nl'.'c'.'g==','c2'.'9jaW'.'Fs'.'bmV0d'.'2'.'9yaw'.'==','Y'.'W'.'xsb'.'3d'.'fY'.'2FsZW5'.'kYXJfZ3JvdX'.'A'.'=','WQ==','SUQ=','c2'.'9jaW'.'FsbmV0d'.'29ya'.'w='.'=','YWxs'.'b3dfY'.'2FsZW5k'.'YXJ'.'fZ3JvdXA'.'=','SUQ=','c29j'.'a'.'WFsb'.'mV0d29yaw='.'=','YWx'.'sb3d'.'fY2Fs'.'Z'.'W'.'5kY'.'XJfZ3'.'Jvd'.'XA'.'=','QUNUSVZF','WQ'.'==','T'.'g'.'==','ZXh0cmFuZXQ=','aW'.'Jsb2N'.'r','T25BZn'.'Rlck'.'lCbG9ja0VsZ'.'W1'.'lbnR'.'VcGRhdGU=','aW50cmFuZXQ=','Q0l'.'udH'.'JhbmV0RX'.'ZlbnRIYW5kbGVyc'.'w==','U1BS'.'Z'.'W'.'d'.'pc3Rl'.'clVwZGF0'.'ZWRJdGVt','Q0l'.'udHJh'.'bmV'.'0U'.'2hh'.'cmVwb2lu'.'dDo6QWd'.'l'.'bnR'.'MaXN0cygp'.'Ow='.'=','aW50cmFuZXQ=','Tg==','Q0ludHJhbmV'.'0U2hh'.'cmVwb'.'2ludDo6'.'QWdlbnRRdWV1ZSgpOw==','aW5'.'0c'.'mF'.'uZXQ=','Tg='.'=','Q'.'0l'.'udHJh'.'bmV0U2hh'.'c'.'mVwb2ludDo'.'6QWdlbn'.'RV'.'cGRhdGUoKTs=','aW50cmFuZX'.'Q'.'=','Tg='.'=',''.'aW'.'Jsb2N'.'r','T25BZnRlcklCb'.'G9j'.'a0VsZ'.'W1lb'.'nRBZGQ'.'=','aW50'.'cm'.'FuZXQ=','Q'.'0ludHJ'.'hbm'.'V0RXZlbnRI'.'YW5k'.'bGVycw'.'==','U'.'1BSZWdpc3RlclVwZGF0ZWR'.'JdGVt','aWJsb2Nr','T25BZnRlckl'.'CbG'.'9'.'ja'.'0VsZW'.'1lbnRVcG'.'Rhd'.'GU=','aW50cmFuZXQ'.'=','Q0ludHJhbmV0RXZlbnRIYW5kb'.'GV'.'ycw==','U1BSZWd'.'pc'.'3Rlc'.'lVwZGF0ZWRJdGV'.'t','Q'.'0ludHJhbm'.'V'.'0U'.'2hhcmVwb2ludD'.'o6QWdlbnRMaXN0cygpOw'.'==','aW5'.'0cmFuZX'.'Q=','Q0'.'ludHJh'.'bmV0U'.'2hhcmVwb2lu'.'d'.'Do6QWd'.'lbn'.'RRdWV1ZSgpOw'.'==','aW50'.'cm'.'FuZXQ=',''.'Q0lu'.'dHJh'.'bmV0U2hhcmVwb2lu'.'d'.'Do6QWdlbnR'.'VcG'.'RhdGU'.'oKT'.'s'.'=','aW5'.'0cmFuZXQ=','Y'.'3Jt','bWFp'.'bg'.'==','T25C'.'ZWZvcm'.'VQcm9sb2'.'c=','b'.'WFpbg==','Q1d'.'pemFy'.'ZFN'.'vbF'.'Bh'.'bmVsS'.'W5'.'0cmFuZXQ=','U'.'2hvd'.'1Bhb'.'m'.'V'.'s','L'.'2'.'1vZH'.'VsZX'.'MvaW50cmF'.'uZ'.'XQv'.'c'.'GFuZWxfYnV0dG9uL'.'n'.'BocA'.'==','R'.'U5DT0RF','WQ==');return base64_decode($_49855193[$_1882643737]);}};$GLOBALS['____86168232'][0](___1085525023(0), ___1085525023(1));class CBXFeatures{ private static $_1808741481= 30; private static $_795908490= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_403501708= false; private static $_852042894= false; private static function __929372623(){ if(self::$_403501708 == false){ self::$_403501708= array(); foreach(self::$_795908490 as $_345975175 => $_203991768){ foreach($_203991768 as $_1160365722) self::$_403501708[$_1160365722]= $_345975175;}} if(self::$_852042894 == false){ self::$_852042894= array(); $_931923750= COption::GetOptionString(___1085525023(2), ___1085525023(3), ___1085525023(4)); if($GLOBALS['____86168232'][1]($_931923750)>(245*2-490)){ $_931923750= $GLOBALS['____86168232'][2]($_931923750); self::$_852042894= $GLOBALS['____86168232'][3]($_931923750); if(!$GLOBALS['____86168232'][4](self::$_852042894)) self::$_852042894= array();} if($GLOBALS['____86168232'][5](self::$_852042894) <=(824-2*412)) self::$_852042894= array(___1085525023(5) => array(), ___1085525023(6) => array());}} public static function InitiateEditionsSettings($_119521172){ self::__929372623(); $_24552542= array(); foreach(self::$_795908490 as $_345975175 => $_203991768){ $_1187045698= $GLOBALS['____86168232'][6]($_345975175, $_119521172); self::$_852042894[___1085525023(7)][$_345975175]=($_1187045698? array(___1085525023(8)): array(___1085525023(9))); foreach($_203991768 as $_1160365722){ self::$_852042894[___1085525023(10)][$_1160365722]= $_1187045698; if(!$_1187045698) $_24552542[]= array($_1160365722, false);}} $_891962653= $GLOBALS['____86168232'][7](self::$_852042894); $_891962653= $GLOBALS['____86168232'][8]($_891962653); COption::SetOptionString(___1085525023(11), ___1085525023(12), $_891962653); foreach($_24552542 as $_2092993952) self::__451761038($_2092993952[(1000-2*500)], $_2092993952[round(0+1)]);} public static function IsFeatureEnabled($_1160365722){ if($GLOBALS['____86168232'][9]($_1160365722) <= 0) return true; self::__929372623(); if(!$GLOBALS['____86168232'][10]($_1160365722, self::$_403501708)) return true; if(self::$_403501708[$_1160365722] == ___1085525023(13)) $_1393923583= array(___1085525023(14)); elseif($GLOBALS['____86168232'][11](self::$_403501708[$_1160365722], self::$_852042894[___1085525023(15)])) $_1393923583= self::$_852042894[___1085525023(16)][self::$_403501708[$_1160365722]]; else $_1393923583= array(___1085525023(17)); if($_1393923583[(155*2-310)] != ___1085525023(18) && $_1393923583[min(36,0,12)] != ___1085525023(19)){ return false;} elseif($_1393923583[min(2,0,0.66666666666667)] == ___1085525023(20)){ if($_1393923583[round(0+0.2+0.2+0.2+0.2+0.2)]< $GLOBALS['____86168232'][12]((1396/2-698),(227*2-454),(1320/2-660), Date(___1085525023(21)), $GLOBALS['____86168232'][13](___1085525023(22))- self::$_1808741481, $GLOBALS['____86168232'][14](___1085525023(23)))){ if(!isset($_1393923583[round(0+0.4+0.4+0.4+0.4+0.4)]) ||!$_1393923583[round(0+0.5+0.5+0.5+0.5)]) self::__205914690(self::$_403501708[$_1160365722]); return false;}} return!$GLOBALS['____86168232'][15]($_1160365722, self::$_852042894[___1085525023(24)]) || self::$_852042894[___1085525023(25)][$_1160365722];} public static function IsFeatureInstalled($_1160365722){ if($GLOBALS['____86168232'][16]($_1160365722) <= 0) return true; self::__929372623(); return($GLOBALS['____86168232'][17]($_1160365722, self::$_852042894[___1085525023(26)]) && self::$_852042894[___1085525023(27)][$_1160365722]);} public static function IsFeatureEditable($_1160365722){ if($GLOBALS['____86168232'][18]($_1160365722) <= 0) return true; self::__929372623(); if(!$GLOBALS['____86168232'][19]($_1160365722, self::$_403501708)) return true; if(self::$_403501708[$_1160365722] == ___1085525023(28)) $_1393923583= array(___1085525023(29)); elseif($GLOBALS['____86168232'][20](self::$_403501708[$_1160365722], self::$_852042894[___1085525023(30)])) $_1393923583= self::$_852042894[___1085525023(31)][self::$_403501708[$_1160365722]]; else $_1393923583= array(___1085525023(32)); if($_1393923583[min(208,0,69.333333333333)] != ___1085525023(33) && $_1393923583[(164*2-328)] != ___1085525023(34)){ return false;} elseif($_1393923583[(1192/2-596)] == ___1085525023(35)){ if($_1393923583[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____86168232'][21]((752-2*376),(1472/2-736),(138*2-276), Date(___1085525023(36)), $GLOBALS['____86168232'][22](___1085525023(37))- self::$_1808741481, $GLOBALS['____86168232'][23](___1085525023(38)))){ if(!isset($_1393923583[round(0+1+1)]) ||!$_1393923583[round(0+0.4+0.4+0.4+0.4+0.4)]) self::__205914690(self::$_403501708[$_1160365722]); return false;}} return true;} private static function __451761038($_1160365722, $_1960878215){ if($GLOBALS['____86168232'][24]("CBXFeatures", "On".$_1160365722."SettingsChange")) $GLOBALS['____86168232'][25](array("CBXFeatures", "On".$_1160365722."SettingsChange"), array($_1160365722, $_1960878215)); $_1970413455= $GLOBALS['_____867960505'][0](___1085525023(39), ___1085525023(40).$_1160365722.___1085525023(41)); while($_2049864748= $_1970413455->Fetch()) $GLOBALS['_____867960505'][1]($_2049864748, array($_1160365722, $_1960878215));} public static function SetFeatureEnabled($_1160365722, $_1960878215= true, $_1400283820= true){ if($GLOBALS['____86168232'][26]($_1160365722) <= 0) return; if(!self::IsFeatureEditable($_1160365722)) $_1960878215= false; $_1960878215=($_1960878215? true: false); self::__929372623(); $_1229113388=(!$GLOBALS['____86168232'][27]($_1160365722, self::$_852042894[___1085525023(42)]) && $_1960878215 || $GLOBALS['____86168232'][28]($_1160365722, self::$_852042894[___1085525023(43)]) && $_1960878215 != self::$_852042894[___1085525023(44)][$_1160365722]); self::$_852042894[___1085525023(45)][$_1160365722]= $_1960878215; $_891962653= $GLOBALS['____86168232'][29](self::$_852042894); $_891962653= $GLOBALS['____86168232'][30]($_891962653); COption::SetOptionString(___1085525023(46), ___1085525023(47), $_891962653); if($_1229113388 && $_1400283820) self::__451761038($_1160365722, $_1960878215);} private static function __205914690($_345975175){ if($GLOBALS['____86168232'][31]($_345975175) <= 0 || $_345975175 == "Portal") return; self::__929372623(); if(!$GLOBALS['____86168232'][32]($_345975175, self::$_852042894[___1085525023(48)]) || $GLOBALS['____86168232'][33]($_345975175, self::$_852042894[___1085525023(49)]) && self::$_852042894[___1085525023(50)][$_345975175][(1348/2-674)] != ___1085525023(51)) return; if(isset(self::$_852042894[___1085525023(52)][$_345975175][round(0+1+1)]) && self::$_852042894[___1085525023(53)][$_345975175][round(0+1+1)]) return; $_24552542= array(); if($GLOBALS['____86168232'][34]($_345975175, self::$_795908490) && $GLOBALS['____86168232'][35](self::$_795908490[$_345975175])){ foreach(self::$_795908490[$_345975175] as $_1160365722){ if($GLOBALS['____86168232'][36]($_1160365722, self::$_852042894[___1085525023(54)]) && self::$_852042894[___1085525023(55)][$_1160365722]){ self::$_852042894[___1085525023(56)][$_1160365722]= false; $_24552542[]= array($_1160365722, false);}} self::$_852042894[___1085525023(57)][$_345975175][round(0+2)]= true;} $_891962653= $GLOBALS['____86168232'][37](self::$_852042894); $_891962653= $GLOBALS['____86168232'][38]($_891962653); COption::SetOptionString(___1085525023(58), ___1085525023(59), $_891962653); foreach($_24552542 as $_2092993952) self::__451761038($_2092993952[(137*2-274)], $_2092993952[round(0+1)]);} public static function ModifyFeaturesSettings($_119521172, $_203991768){ self::__929372623(); foreach($_119521172 as $_345975175 => $_1896665930) self::$_852042894[___1085525023(60)][$_345975175]= $_1896665930; $_24552542= array(); foreach($_203991768 as $_1160365722 => $_1960878215){ if(!$GLOBALS['____86168232'][39]($_1160365722, self::$_852042894[___1085525023(61)]) && $_1960878215 || $GLOBALS['____86168232'][40]($_1160365722, self::$_852042894[___1085525023(62)]) && $_1960878215 != self::$_852042894[___1085525023(63)][$_1160365722]) $_24552542[]= array($_1160365722, $_1960878215); self::$_852042894[___1085525023(64)][$_1160365722]= $_1960878215;} $_891962653= $GLOBALS['____86168232'][41](self::$_852042894); $_891962653= $GLOBALS['____86168232'][42]($_891962653); COption::SetOptionString(___1085525023(65), ___1085525023(66), $_891962653); self::$_852042894= false; foreach($_24552542 as $_2092993952) self::__451761038($_2092993952[(187*2-374)], $_2092993952[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function SaveFeaturesSettings($_1720922064, $_1222242695){ self::__929372623(); $_1559532039= array(___1085525023(67) => array(), ___1085525023(68) => array()); if(!$GLOBALS['____86168232'][43]($_1720922064)) $_1720922064= array(); if(!$GLOBALS['____86168232'][44]($_1222242695)) $_1222242695= array(); if(!$GLOBALS['____86168232'][45](___1085525023(69), $_1720922064)) $_1720922064[]= ___1085525023(70); foreach(self::$_795908490 as $_345975175 => $_203991768){ if($GLOBALS['____86168232'][46]($_345975175, self::$_852042894[___1085525023(71)])) $_1091117119= self::$_852042894[___1085525023(72)][$_345975175]; else $_1091117119=($_345975175 == ___1085525023(73))? array(___1085525023(74)): array(___1085525023(75)); if($_1091117119[(754-2*377)] == ___1085525023(76) || $_1091117119[(932-2*466)] == ___1085525023(77)){ $_1559532039[___1085525023(78)][$_345975175]= $_1091117119;} else{ if($GLOBALS['____86168232'][47]($_345975175, $_1720922064)) $_1559532039[___1085525023(79)][$_345975175]= array(___1085525023(80), $GLOBALS['____86168232'][48](min(46,0,15.333333333333), min(208,0,69.333333333333),(1392/2-696), $GLOBALS['____86168232'][49](___1085525023(81)), $GLOBALS['____86168232'][50](___1085525023(82)), $GLOBALS['____86168232'][51](___1085525023(83)))); else $_1559532039[___1085525023(84)][$_345975175]= array(___1085525023(85));}} $_24552542= array(); foreach(self::$_403501708 as $_1160365722 => $_345975175){ if($_1559532039[___1085525023(86)][$_345975175][(1188/2-594)] != ___1085525023(87) && $_1559532039[___1085525023(88)][$_345975175][(130*2-260)] != ___1085525023(89)){ $_1559532039[___1085525023(90)][$_1160365722]= false;} else{ if($_1559532039[___1085525023(91)][$_345975175][(166*2-332)] == ___1085525023(92) && $_1559532039[___1085525023(93)][$_345975175][round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____86168232'][52]((188*2-376),(922-2*461),(1332/2-666), Date(___1085525023(94)), $GLOBALS['____86168232'][53](___1085525023(95))- self::$_1808741481, $GLOBALS['____86168232'][54](___1085525023(96)))) $_1559532039[___1085525023(97)][$_1160365722]= false; else $_1559532039[___1085525023(98)][$_1160365722]= $GLOBALS['____86168232'][55]($_1160365722, $_1222242695); if(!$GLOBALS['____86168232'][56]($_1160365722, self::$_852042894[___1085525023(99)]) && $_1559532039[___1085525023(100)][$_1160365722] || $GLOBALS['____86168232'][57]($_1160365722, self::$_852042894[___1085525023(101)]) && $_1559532039[___1085525023(102)][$_1160365722] != self::$_852042894[___1085525023(103)][$_1160365722]) $_24552542[]= array($_1160365722, $_1559532039[___1085525023(104)][$_1160365722]);}} $_891962653= $GLOBALS['____86168232'][58]($_1559532039); $_891962653= $GLOBALS['____86168232'][59]($_891962653); COption::SetOptionString(___1085525023(105), ___1085525023(106), $_891962653); self::$_852042894= false; foreach($_24552542 as $_2092993952) self::__451761038($_2092993952[(1368/2-684)], $_2092993952[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function GetFeaturesList(){ self::__929372623(); $_928001349= array(); foreach(self::$_795908490 as $_345975175 => $_203991768){ if($GLOBALS['____86168232'][60]($_345975175, self::$_852042894[___1085525023(107)])) $_1091117119= self::$_852042894[___1085525023(108)][$_345975175]; else $_1091117119=($_345975175 == ___1085525023(109))? array(___1085525023(110)): array(___1085525023(111)); $_928001349[$_345975175]= array( ___1085525023(112) => $_1091117119[(1224/2-612)], ___1085525023(113) => $_1091117119[round(0+0.5+0.5)], ___1085525023(114) => array(),); $_928001349[$_345975175][___1085525023(115)]= false; if($_928001349[$_345975175][___1085525023(116)] == ___1085525023(117)){ $_928001349[$_345975175][___1085525023(118)]= $GLOBALS['____86168232'][61](($GLOBALS['____86168232'][62]()- $_928001349[$_345975175][___1085525023(119)])/ round(0+86400)); if($_928001349[$_345975175][___1085525023(120)]> self::$_1808741481) $_928001349[$_345975175][___1085525023(121)]= true;} foreach($_203991768 as $_1160365722) $_928001349[$_345975175][___1085525023(122)][$_1160365722]=(!$GLOBALS['____86168232'][63]($_1160365722, self::$_852042894[___1085525023(123)]) || self::$_852042894[___1085525023(124)][$_1160365722]);} return $_928001349;} private static function __28061357($_2090713524, $_989553004){ if(IsModuleInstalled($_2090713524) == $_989553004) return true; $_1313671874= $_SERVER[___1085525023(125)].___1085525023(126).$_2090713524.___1085525023(127); if(!$GLOBALS['____86168232'][64]($_1313671874)) return false; include_once($_1313671874); $_585723509= $GLOBALS['____86168232'][65](___1085525023(128), ___1085525023(129), $_2090713524); if(!$GLOBALS['____86168232'][66]($_585723509)) return false; $_2144889503= new $_585723509; if($_989553004){ if(!$_2144889503->InstallDB()) return false; $_2144889503->InstallEvents(); if(!$_2144889503->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___1085525023(130))) CSearch::DeleteIndex($_2090713524); UnRegisterModule($_2090713524);} return true;} protected static function OnRequestsSettingsChange($_1160365722, $_1960878215){ self::__28061357("form", $_1960878215);} protected static function OnLearningSettingsChange($_1160365722, $_1960878215){ self::__28061357("learning", $_1960878215);} protected static function OnJabberSettingsChange($_1160365722, $_1960878215){ self::__28061357("xmpp", $_1960878215);} protected static function OnVideoConferenceSettingsChange($_1160365722, $_1960878215){ self::__28061357("video", $_1960878215);} protected static function OnBizProcSettingsChange($_1160365722, $_1960878215){ self::__28061357("bizprocdesigner", $_1960878215);} protected static function OnListsSettingsChange($_1160365722, $_1960878215){ self::__28061357("lists", $_1960878215);} protected static function OnWikiSettingsChange($_1160365722, $_1960878215){ self::__28061357("wiki", $_1960878215);} protected static function OnSupportSettingsChange($_1160365722, $_1960878215){ self::__28061357("support", $_1960878215);} protected static function OnControllerSettingsChange($_1160365722, $_1960878215){ self::__28061357("controller", $_1960878215);} protected static function OnAnalyticsSettingsChange($_1160365722, $_1960878215){ self::__28061357("statistic", $_1960878215);} protected static function OnVoteSettingsChange($_1160365722, $_1960878215){ self::__28061357("vote", $_1960878215);} protected static function OnFriendsSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(131); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(132)),($_2060364031= ___1085525023(133)), array(___1085525023(134) => ___1085525023(135))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(136), ___1085525023(137), ___1085525023(138), $_347300664[___1085525023(139)]) != $_136350126){ COption::SetOptionString(___1085525023(140), ___1085525023(141), $_136350126, false, $_347300664[___1085525023(142)]); COption::SetOptionString(___1085525023(143), ___1085525023(144), $_136350126);}}} protected static function OnMicroBlogSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(145); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(146)),($_2060364031= ___1085525023(147)), array(___1085525023(148) => ___1085525023(149))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(150), ___1085525023(151), ___1085525023(152), $_347300664[___1085525023(153)]) != $_136350126){ COption::SetOptionString(___1085525023(154), ___1085525023(155), $_136350126, false, $_347300664[___1085525023(156)]); COption::SetOptionString(___1085525023(157), ___1085525023(158), $_136350126);} if(COption::GetOptionString(___1085525023(159), ___1085525023(160), ___1085525023(161), $_347300664[___1085525023(162)]) != $_136350126){ COption::SetOptionString(___1085525023(163), ___1085525023(164), $_136350126, false, $_347300664[___1085525023(165)]); COption::SetOptionString(___1085525023(166), ___1085525023(167), $_136350126);}}} protected static function OnPersonalFilesSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(168); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(169)),($_2060364031= ___1085525023(170)), array(___1085525023(171) => ___1085525023(172))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(173), ___1085525023(174), ___1085525023(175), $_347300664[___1085525023(176)]) != $_136350126){ COption::SetOptionString(___1085525023(177), ___1085525023(178), $_136350126, false, $_347300664[___1085525023(179)]); COption::SetOptionString(___1085525023(180), ___1085525023(181), $_136350126);}}} protected static function OnPersonalBlogSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(182); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(183)),($_2060364031= ___1085525023(184)), array(___1085525023(185) => ___1085525023(186))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(187), ___1085525023(188), ___1085525023(189), $_347300664[___1085525023(190)]) != $_136350126){ COption::SetOptionString(___1085525023(191), ___1085525023(192), $_136350126, false, $_347300664[___1085525023(193)]); COption::SetOptionString(___1085525023(194), ___1085525023(195), $_136350126);}}} protected static function OnPersonalPhotoSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(196); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(197)),($_2060364031= ___1085525023(198)), array(___1085525023(199) => ___1085525023(200))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(201), ___1085525023(202), ___1085525023(203), $_347300664[___1085525023(204)]) != $_136350126){ COption::SetOptionString(___1085525023(205), ___1085525023(206), $_136350126, false, $_347300664[___1085525023(207)]); COption::SetOptionString(___1085525023(208), ___1085525023(209), $_136350126);}}} protected static function OnPersonalForumSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(210); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(211)),($_2060364031= ___1085525023(212)), array(___1085525023(213) => ___1085525023(214))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(215), ___1085525023(216), ___1085525023(217), $_347300664[___1085525023(218)]) != $_136350126){ COption::SetOptionString(___1085525023(219), ___1085525023(220), $_136350126, false, $_347300664[___1085525023(221)]); COption::SetOptionString(___1085525023(222), ___1085525023(223), $_136350126);}}} protected static function OnTasksSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(224); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(225)),($_2060364031= ___1085525023(226)), array(___1085525023(227) => ___1085525023(228))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(229), ___1085525023(230), ___1085525023(231), $_347300664[___1085525023(232)]) != $_136350126){ COption::SetOptionString(___1085525023(233), ___1085525023(234), $_136350126, false, $_347300664[___1085525023(235)]); COption::SetOptionString(___1085525023(236), ___1085525023(237), $_136350126);} if(COption::GetOptionString(___1085525023(238), ___1085525023(239), ___1085525023(240), $_347300664[___1085525023(241)]) != $_136350126){ COption::SetOptionString(___1085525023(242), ___1085525023(243), $_136350126, false, $_347300664[___1085525023(244)]); COption::SetOptionString(___1085525023(245), ___1085525023(246), $_136350126);}} self::__28061357(___1085525023(247), $_1960878215);} protected static function OnCalendarSettingsChange($_1160365722, $_1960878215){ if($_1960878215) $_136350126= "Y"; else $_136350126= ___1085525023(248); $_1483835802= CSite::GetList(($_1187045698= ___1085525023(249)),($_2060364031= ___1085525023(250)), array(___1085525023(251) => ___1085525023(252))); while($_347300664= $_1483835802->Fetch()){ if(COption::GetOptionString(___1085525023(253), ___1085525023(254), ___1085525023(255), $_347300664[___1085525023(256)]) != $_136350126){ COption::SetOptionString(___1085525023(257), ___1085525023(258), $_136350126, false, $_347300664[___1085525023(259)]); COption::SetOptionString(___1085525023(260), ___1085525023(261), $_136350126);} if(COption::GetOptionString(___1085525023(262), ___1085525023(263), ___1085525023(264), $_347300664[___1085525023(265)]) != $_136350126){ COption::SetOptionString(___1085525023(266), ___1085525023(267), $_136350126, false, $_347300664[___1085525023(268)]); COption::SetOptionString(___1085525023(269), ___1085525023(270), $_136350126);}}} protected static function OnSMTPSettingsChange($_1160365722, $_1960878215){ self::__28061357("mail", $_1960878215);} protected static function OnExtranetSettingsChange($_1160365722, $_1960878215){ $_1495629169= COption::GetOptionString("extranet", "extranet_site", ""); if($_1495629169){ $_629855241= new CSite; $_629855241->Update($_1495629169, array(___1085525023(271) =>($_1960878215? ___1085525023(272): ___1085525023(273))));} self::__28061357(___1085525023(274), $_1960878215);} protected static function OnDAVSettingsChange($_1160365722, $_1960878215){ self::__28061357("dav", $_1960878215);} protected static function OntimemanSettingsChange($_1160365722, $_1960878215){ self::__28061357("timeman", $_1960878215);} protected static function Onintranet_sharepointSettingsChange($_1160365722, $_1960878215){ if($_1960878215){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___1085525023(275), ___1085525023(276), ___1085525023(277), ___1085525023(278), ___1085525023(279)); CAgent::AddAgent(___1085525023(280), ___1085525023(281), ___1085525023(282), round(0+125+125+125+125)); CAgent::AddAgent(___1085525023(283), ___1085525023(284), ___1085525023(285), round(0+75+75+75+75)); CAgent::AddAgent(___1085525023(286), ___1085525023(287), ___1085525023(288), round(0+720+720+720+720+720));} else{ UnRegisterModuleDependences(___1085525023(289), ___1085525023(290), ___1085525023(291), ___1085525023(292), ___1085525023(293)); UnRegisterModuleDependences(___1085525023(294), ___1085525023(295), ___1085525023(296), ___1085525023(297), ___1085525023(298)); CAgent::RemoveAgent(___1085525023(299), ___1085525023(300)); CAgent::RemoveAgent(___1085525023(301), ___1085525023(302)); CAgent::RemoveAgent(___1085525023(303), ___1085525023(304));}} protected static function OncrmSettingsChange($_1160365722, $_1960878215){ if($_1960878215) COption::SetOptionString("crm", "form_features", "Y"); self::__28061357(___1085525023(305), $_1960878215);} protected static function OnClusterSettingsChange($_1160365722, $_1960878215){ self::__28061357("cluster", $_1960878215);} protected static function OnMultiSitesSettingsChange($_1160365722, $_1960878215){ if($_1960878215) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___1085525023(306), ___1085525023(307), ___1085525023(308), ___1085525023(309), ___1085525023(310), ___1085525023(311));} protected static function OnIdeaSettingsChange($_1160365722, $_1960878215){ self::__28061357("idea", $_1960878215);} protected static function OnMeetingSettingsChange($_1160365722, $_1960878215){ self::__28061357("meeting", $_1960878215);} protected static function OnXDImportSettingsChange($_1160365722, $_1960878215){ self::__28061357("xdimport", $_1960878215);}} $GLOBALS['____86168232'][67](___1085525023(312), ___1085525023(313));/**/			//Do not remove this

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

/*ZDUyZmZOTU3ZmE4NDc0NDI4Yjc1ZTcyYzM1Njc4MmQwNWJjZTc=*/$GLOBALS['____1949621346']= array(base64_decode('bXR'.'fcmFuZA=='),base64_decode('ZXhwbG9kZQ=='),base64_decode('cGFjaw=='),base64_decode('b'.'WQ1'),base64_decode('Y29'.'u'.'c3'.'RhbnQ='),base64_decode('a'.'G'.'FzaF'.'9obWFj'),base64_decode(''.'c3Ry'.'Y21w'),base64_decode(''.'a'.'XN'.'f'.'b2JqZ'.'WN0'),base64_decode('Y2Fs'.'bF91c2Vy'.'X'.'2Z1bm'.'M='),base64_decode('Y2Fsb'.'F91c'.'2VyX2Z1'.'bmM'.'='),base64_decode('Y'.'2FsbF9'.'1'.'c2'.'V'.'yX2Z1'.'b'.'mM='),base64_decode('Y2Fs'.'bF9'.'1c2V'.'y'.'X2Z1bmM='),base64_decode(''.'Y2Fs'.'bF91'.'c2VyX2'.'Z'.'1'.'bmM'.'='));if(!function_exists(__NAMESPACE__.'\\___361734892')){function ___361734892($_94898510){static $_848675262= false; if($_848675262 == false) $_848675262=array('R'.'EI=',''.'U0VMR'.'UNUIFZ'.'BTFVFI'.'E'.'ZST'.'00g'.'Yl9'.'v'.'cHRpb2'.'4gV'.'0hFU'.'kUgTkFN'.'RT0'.'nfl'.'B'.'BUk'.'F'.'NX01BWF9VU0VSUycgQU5E'.'IE1P'.'RF'.'VMRV9JR'.'D'.'0nbWFp'.'bicgQU5EIFNJVEVfSUQgSVMgTlV'.'MTA='.'=','VkFM'.'VUU=',''.'L'.'g='.'=','SCo=','Y'.'ml0cml4','TE'.'l'.'DRU5'.'T'.'RV9'.'L'.'RVk'.'=','c2hhMj'.'U2','V'.'V'.'NF'.'U'.'g==','VV'.'NFUg==','VV'.'NFUg==','SX'.'NBd'.'XRo'.'b3JpemV'.'k','VVNF'.'Ug='.'=','SXNBZG1pb'.'g='.'=','QVBQT'.'El'.'DQVRJT04=','Um'.'V'.'zdGFyd'.'EJ1Zm'.'Zlc'.'g==',''.'TG'.'9jYWxSZ'.'WRpcmVj'.'dA==','L2xp'.'Y2'.'Vuc2VfcmVzdHJpY3Rpb'.'24'.'u'.'cGh'.'w',''.'XE'.'Jp'.'dHJpeFxN'.'YWluXENvbmZp'.'Z1xPc'.'H'.'Rpb246OnN'.'l'.'dA'.'==','bWFpb'.'g==','UEFSQU'.'1fTUFYX1VT'.'R'.'VJT');return base64_decode($_848675262[$_94898510]);}};if($GLOBALS['____1949621346'][0](round(0+1), round(0+20)) == round(0+1.75+1.75+1.75+1.75)){ $_683850503= $GLOBALS[___361734892(0)]->Query(___361734892(1), true); if($_509725373= $_683850503->Fetch()){ $_515636041= $_509725373[___361734892(2)]; list($_852379872, $_1158656772)= $GLOBALS['____1949621346'][1](___361734892(3), $_515636041); $_1557957624= $GLOBALS['____1949621346'][2](___361734892(4), $_852379872); $_1690662327= ___361734892(5).$GLOBALS['____1949621346'][3]($GLOBALS['____1949621346'][4](___361734892(6))); $_1757943322= $GLOBALS['____1949621346'][5](___361734892(7), $_1158656772, $_1690662327, true); if($GLOBALS['____1949621346'][6]($_1757943322, $_1557957624) !==(1004/2-502)){ if(isset($GLOBALS[___361734892(8)]) && $GLOBALS['____1949621346'][7]($GLOBALS[___361734892(9)]) && $GLOBALS['____1949621346'][8](array($GLOBALS[___361734892(10)], ___361734892(11))) &&!$GLOBALS['____1949621346'][9](array($GLOBALS[___361734892(12)], ___361734892(13)))){ $GLOBALS['____1949621346'][10](array($GLOBALS[___361734892(14)], ___361734892(15))); $GLOBALS['____1949621346'][11](___361734892(16), ___361734892(17), true);}}} else{ $GLOBALS['____1949621346'][12](___361734892(18), ___361734892(19), ___361734892(20), round(0+2.4+2.4+2.4+2.4+2.4));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
