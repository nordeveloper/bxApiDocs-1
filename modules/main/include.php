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

/*ZDUyZmZNDM3YjRjNGZlYzM1Y2E1OGRjZTU0ZGI0YmQ2OTg2M2Y=*/$GLOBALS['_____223748094']= array(base64_decode(''.'R2V0TW9k'.'d'.'Wx'.'lR'.'XZlbnRz'),base64_decode('RX'.'hlY'.'3'.'V0ZU1vZHVsZU'.'V2ZW50RXg='));$GLOBALS['____1018800033']= array(base64_decode(''.'ZG'.'V'.'maW5l'),base64_decode('c3R'.'ybG'.'Vu'),base64_decode('YmFzZTY0X2'.'RlY'.'2'.'9kZ'.'Q=='),base64_decode('d'.'W5zZXJp'.'YWx'.'pem'.'U='),base64_decode('aXN'.'fYXJyY'.'X'.'k='),base64_decode('Y2'.'91'.'bnQ='),base64_decode('aW5fYXJ'.'yYXk='),base64_decode('c2VyaWF'.'saXpl'),base64_decode('YmF'.'zZTY0X2V'.'uY29kZQ='.'='),base64_decode(''.'c3R'.'ybGVu'),base64_decode(''.'YXJyY'.'X'.'lfa2V5'.'X'.'2'.'V4a'.'XN0cw=='),base64_decode('YXJyYXlfa'.'2V5X2V4aXN'.'0cw=='),base64_decode(''.'bWt0a'.'W1'.'l'),base64_decode('ZGF0'.'ZQ=='),base64_decode('ZGF0ZQ='.'='),base64_decode('YXJyY'.'X'.'lf'.'a2V5X2V4aXN0cw=='),base64_decode(''.'c3R'.'ybGVu'),base64_decode('YXJyYXlfa2V5'.'X2V4aXN'.'0c'.'w=='),base64_decode('c'.'3'.'R'.'ybGVu'),base64_decode('YX'.'Jy'.'YXl'.'fa2'.'V5'.'X2V4'.'aX'.'N0cw'.'=='),base64_decode('Y'.'X'.'JyYXlf'.'a'.'2'.'V'.'5X2'.'V'.'4aXN0c'.'w=='),base64_decode('bW'.'t0a'.'W1'.'l'),base64_decode(''.'ZGF0ZQ=='),base64_decode('ZGF'.'0ZQ=='),base64_decode('bWV0aG9kX2V'.'4a'.'X'.'N0cw=='),base64_decode('Y'.'2FsbF'.'9'.'1c2Vy'.'X'.'2Z1bmN'.'fYX'.'Jy'.'YXk='),base64_decode('c3RybGVu'),base64_decode('YXJyYXlfa2'.'V5X'.'2V4aXN'.'0cw'.'=='),base64_decode(''.'YXJyYXlf'.'a2V5'.'X2V4aXN0'.'cw'.'='.'='),base64_decode(''.'c'.'2VyaW'.'FsaX'.'pl'),base64_decode(''.'YmFzZTY0X2Vu'.'Y2'.'9'.'k'.'Z'.'Q'.'=='),base64_decode('c'.'3RybGVu'),base64_decode('YX'.'JyYXlf'.'a2V5X2'.'V4aXN0'.'cw='.'='),base64_decode('YXJ'.'yYXlf'.'a'.'2V5X2V4aXN0cw=='),base64_decode('YXJy'.'YXl'.'fa2'.'V'.'5X2V4aXN0cw='.'='),base64_decode('aXNf'.'YXJyYXk='),base64_decode('YX'.'JyYXlfa'.'2V5X2'.'V4aXN0'.'cw=='),base64_decode(''.'c'.'2V'.'ya'.'WFsaXpl'),base64_decode('Ym'.'F'.'zZTY0X'.'2VuY29k'.'Z'.'Q=='),base64_decode(''.'YXJy'.'YXlf'.'a2V5X2V'.'4aXN0c'.'w=='),base64_decode('YXJyYXlfa2'.'V5X2V4a'.'XN0c'.'w=='),base64_decode('c2V'.'yaWFsa'.'Xpl'),base64_decode('YmFzZTY0X2'.'VuY2'.'9'.'kZQ'.'=='),base64_decode('aXNfYX'.'JyYXk='),base64_decode('aXN'.'fYXJyYX'.'k='),base64_decode('aW5f'.'YX'.'JyYXk='),base64_decode('YXJ'.'yYXlfa2'.'V5X2V4aXN'.'0cw=='),base64_decode('aW5'.'fYXJyYXk='),base64_decode('bW'.'t0aW1l'),base64_decode('ZGF'.'0ZQ'.'=='),base64_decode('Z'.'GF0Z'.'Q=='),base64_decode(''.'ZGF0ZQ=='),base64_decode('b'.'Wt0'.'aW1l'),base64_decode(''.'ZGF0ZQ=='),base64_decode('ZGF'.'0ZQ=='),base64_decode(''.'aW5fYXJyYXk='),base64_decode('Y'.'XJ'.'yYXlf'.'a'.'2V5X2V'.'4aXN0'.'cw=='),base64_decode(''.'YXJyY'.'Xlfa2V5X2'.'V4a'.'XN0'.'cw=='),base64_decode('c2VyaW'.'FsaXp'.'l'),base64_decode('YmFz'.'ZTY0X2'.'VuY29kZQ=='),base64_decode('YX'.'JyYXl'.'fa2'.'V5X'.'2V'.'4aXN0cw=='),base64_decode(''.'aW5'.'0'.'dmF'.'s'),base64_decode('dGltZQ=='),base64_decode('YX'.'JyYXlf'.'a2V'.'5X2'.'V4aXN'.'0'.'cw=='),base64_decode(''.'ZmlsZV9'.'leG'.'lz'.'dHM'.'='),base64_decode('c3'.'RyX3JlcGxhY'.'2U='),base64_decode('Y2xhc'.'3NfZX'.'h'.'pc3'.'R'.'z'),base64_decode('Z'.'GV'.'ma'.'W5'.'l'));if(!function_exists(__NAMESPACE__.'\\___632040786')){function ___632040786($_1072223521){static $_1109912327= false; if($_1109912327 == false) $_1109912327=array('SU5UU'.'kF'.'OR'.'VRf'.'R'.'URJ'.'VE'.'lPT'.'g==','W'.'Q==','bWF'.'pbg='.'=',''.'fm'.'N'.'wZ'.'l9t'.'Y'.'XBf'.'d'.'mFsd'.'WU=','','ZQ'.'==','Zg==',''.'ZQ==','R'.'g='.'=','W'.'A==','Zg==','bWFpbg==','fm'.'NwZl9'.'tYXBfd'.'mFsd'.'WU=',''.'UG'.'9ydGFs','Rg='.'=','ZQ==','ZQ'.'==','WA='.'=','Rg==','R'.'A='.'=','RA==','bQ='.'=',''.'ZA==','WQ='.'=','Zg='.'=','Zg==','Zg='.'=','Z'.'g==','U'.'G'.'9yd'.'GFs','Rg==','ZQ==','ZQ==','WA==',''.'Rg'.'='.'=','RA==','RA==',''.'bQ'.'==','ZA'.'==','WQ='.'=','bW'.'Fp'.'b'.'g==','T'.'24=','U2V0dG'.'luZ'.'3NDa'.'GFuZ2U=',''.'Zg==','Z'.'g==','Zg='.'=',''.'Z'.'g==','bWFpbg'.'='.'=',''.'fmNwZl'.'9tYX'.'BfdmFsd'.'WU=','ZQ==','ZQ==','ZQ='.'=','R'.'A==','ZQ='.'=','ZQ==','Zg==','Zg==','Zg='.'=','ZQ='.'=','bWF'.'pbg==','fmNwZ'.'l9t'.'Y'.'XBf'.'dmFsdWU=','ZQ='.'=','Z'.'g='.'=','Zg==','Zg==',''.'Zg='.'=',''.'bW'.'Fpbg==',''.'fmNwZl'.'9tYXBfdmFsdWU=',''.'ZQ'.'==','Zg='.'=','UG9'.'y'.'d'.'GFs','UG9'.'ydGFs','Z'.'Q='.'=','ZQ==','UG9'.'ydGFs','Rg==','WA==','Rg==',''.'RA==','Z'.'Q==','ZQ'.'='.'=','RA==','bQ'.'==',''.'ZA==','W'.'Q'.'==',''.'Z'.'Q==','WA'.'==','ZQ==','Rg'.'='.'=','Z'.'Q==','RA'.'==','Z'.'g='.'=',''.'ZQ'.'==','RA==','ZQ='.'=','b'.'Q==',''.'ZA==','WQ'.'==','Z'.'g='.'=',''.'Z'.'g='.'=',''.'Zg==','Zg==','Z'.'g==','Zg==','Z'.'g==','Zg='.'=','bWFpbg==','fmNwZl9tY'.'XBfdmFsdWU'.'=',''.'ZQ='.'=',''.'ZQ='.'=','UG9'.'ydGF'.'s','Rg==','WA='.'=',''.'VFlQ'.'RQ==','REFURQ==',''.'RkVB'.'VFVSRV'.'M'.'=','RVhQ'.'SVJF'.'R'.'A==','V'.'FlQRQ==','RA='.'=',''.'V'.'FJZX'.'0'.'RBWV'.'Nf'.'Q09VTl'.'Q'.'=','REF'.'UR'.'Q==','VFJZX'.'0RBW'.'VNfQ'.'09VTlQ=','RVhQSVJFRA==','R'.'kVBVFVSRVM=','Zg'.'==','Z'.'g'.'==','RE9'.'DVU'.'1F'.'TlRfUk9P'.'VA==','L2Jpd'.'H'.'JpeC'.'9tb2R1bGVzL'.'w'.'='.'=','L2lu'.'c3Rhb'.'GwvaW5kZXgucGhw','L'.'g==','Xw==','c2VhcmNo','Tg==','','','Q'.'UN'.'U'.'SVZF',''.'W'.'Q==','c29'.'ja'.'WFsbmV'.'0'.'d29ya'.'w'.'==','YWxsb3dfZn'.'JpZWxkcw==',''.'WQ='.'=','SUQ'.'=','c29j'.'aWFs'.'bmV0d29yaw==','YW'.'x'.'sb'.'3dfZnJpZWxkcw==','S'.'UQ'.'=','c29j'.'aWF'.'sb'.'mV0d2'.'9y'.'aw==','YWxsb'.'3d'.'fZnJpZWx'.'kcw==','Tg==','','','QU'.'NU'.'SVZF',''.'WQ==',''.'c29jaWFsbm'.'V0d29yaw='.'=','YWx'.'s'.'b3df'.'bWl'.'j'.'cm9'.'ibG9'.'nX3VzZXI=','WQ==','SUQ=','c29ja'.'WFsbmV'.'0d29yaw==','YWxs'.'b3'.'d'.'fbW'.'ljcm'.'9'.'ib'.'G9nX'.'3VzZXI'.'=','SUQ=','c'.'29j'.'aWFsbm'.'V0d29'.'yaw'.'==','YWxsb'.'3d'.'fbWljc'.'m9ib'.'G9nX3Vz'.'Z'.'XI'.'=',''.'c29jaW'.'FsbmV0d29'.'yaw'.'==','Y'.'Wxsb3'.'dfbWljcm'.'9ibG9nX'.'2dyb3Vw','WQ==','SUQ=','c29j'.'aWFsbmV0d29yaw==','YWxs'.'b3d'.'fbWl'.'jc'.'m9ibG9'.'nX2dyb3'.'V'.'w','SUQ=','c29j'.'aWFsbmV0d29ya'.'w'.'==','YWxs'.'b3'.'dfb'.'Wljcm9ib'.'G9nX'.'2dyb3V'.'w',''.'T'.'g='.'=','','','Q'.'UNU'.'S'.'V'.'ZF','W'.'Q==','c29jaWFsbmV0d'.'29y'.'aw'.'='.'=','Y'.'Wxsb3dfZmlsZXNfdXNlcg==','WQ==','SU'.'Q=',''.'c2'.'9jaWF'.'s'.'bmV0'.'d29'.'yaw==','YWxsb'.'3'.'df'.'ZmlsZXNfdXN'.'lcg'.'==','SU'.'Q=','c29jaWFsbmV0d2'.'9ya'.'w==','Y'.'Wxsb3d'.'f'.'ZmlsZXNfd'.'XNl'.'cg='.'=','Tg==','','','QUNUSVZF',''.'WQ'.'==','c2'.'9j'.'aW'.'FsbmV0d'.'2'.'9yaw==','YW'.'xsb3'.'df'.'Ymxv'.'Z1'.'91c2Vy','WQ==','SUQ'.'=','c'.'29'.'jaW'.'F'.'s'.'bmV0d29ya'.'w==','YWxsb3dfYmxvZ191c'.'2'.'Vy','SUQ=',''.'c29j'.'a'.'WFsb'.'mV0d29yaw'.'==',''.'YWxsb3'.'dfY'.'mxvZ'.'1'.'9'.'1c2Vy','Tg'.'==','','','QUNU'.'SV'.'ZF','WQ==','c'.'29jaWFsb'.'mV0d29yaw==','YWxsb'.'3dfcGh'.'v'.'dG9fdXNlcg==','WQ='.'=','SU'.'Q=','c29j'.'aW'.'F'.'sbm'.'V0d2'.'9'.'y'.'aw==','YWxsb3dfcGhvdG9fdX'.'N'.'lcg==','SUQ=','c29ja'.'WFsbm'.'V0d29ya'.'w'.'==',''.'YWxsb'.'3dfcGhvd'.'G'.'9fdXNlcg='.'=','Tg'.'==','','',''.'Q'.'UN'.'USV'.'ZF','WQ'.'==','c29jaWFsbmV0d29ya'.'w==','YW'.'x'.'sb3d'.'fZ'.'m'.'9ydW1fdXNl'.'cg==','WQ==','SUQ=','c29jaW'.'FsbmV0d29y'.'aw==','YWxsb'.'3dfZm9y'.'dW1fdXN'.'l'.'c'.'g==','SUQ=',''.'c'.'29ja'.'WFsbmV0d29ya'.'w==',''.'YWxsb3dfZ'.'m9yd'.'W1fdX'.'Nl'.'cg==',''.'Tg==','','','QUNU'.'SVZF','WQ='.'=','c2'.'9'.'jaW'.'FsbmV0d2'.'9yaw==','YWxsb3'.'df'.'dGFza3NfdXNlcg==','WQ==','SU'.'Q=','c29jaWFsbmV0d29yaw==','YWx'.'sb3'.'dfdGFz'.'a3Nfd'.'XNl'.'cg==','SU'.'Q=','c29ja'.'WFsbmV'.'0d29'.'y'.'aw'.'==','YW'.'xsb3dfd'.'GF'.'za3Nf'.'dXN'.'lcg'.'='.'=','c29jaWF'.'sbmV'.'0d2'.'9'.'yaw='.'=','YWxsb3dfdGF'.'z'.'a3'.'NfZ'.'3J'.'vdXA'.'=','WQ'.'==','SUQ=','c29'.'jaWFsb'.'mV0d29y'.'aw'.'='.'=',''.'Y'.'W'.'xs'.'b3dfdGFza3N'.'fZ3J'.'vdXA=','SU'.'Q'.'=','c'.'29jaWFsbm'.'V0d29yaw='.'=','YW'.'xsb3dfdGFza'.'3NfZ3J'.'v'.'d'.'XA'.'=','dGFza3'.'M'.'=','Tg==','','',''.'QUN'.'USVZF','WQ==','c29ja'.'WF'.'sbmV0'.'d29ya'.'w==',''.'Y'.'W'.'xs'.'b'.'3d'.'fY2Fs'.'ZW5kY'.'XJfd'.'XN'.'lcg==',''.'WQ==','SU'.'Q=','c29'.'ja'.'WFsbmV0d'.'29ya'.'w'.'==','YW'.'xsb'.'3dfY'.'2FsZW'.'5kYXJf'.'d'.'XNlcg='.'=','SUQ=',''.'c29jaW'.'FsbmV0d29ya'.'w==','YWxsb3'.'dfY2FsZ'.'W5kYX'.'JfdXNl'.'cg==','c29jaWFsb'.'mV0d29yaw==','YWxsb3df'.'Y'.'2F'.'sZ'.'W5kYXJ'.'fZ3Jv'.'dXA=',''.'WQ='.'=','SU'.'Q=','c'.'2'.'9jaWFsbmV0d2'.'9yaw='.'=','YW'.'xsb3dfY'.'2FsZW5kY'.'XJfZ3Jvd'.'XA=','SUQ=',''.'c2'.'9ja'.'WFsbmV'.'0'.'d29y'.'aw==','YW'.'x'.'sb3dfY'.'2'.'Fs'.'ZW5'.'kYXJfZ'.'3J'.'vdXA=','Q'.'UNUSV'.'ZF','WQ==','Tg==','ZXh0cmFuZXQ=','aWJ'.'sb2Nr','T25'.'BZ'.'n'.'RlcklCbG9ja'.'0'.'Vs'.'Z'.'W1lbnRVcGR'.'hdGU=','aW50'.'cm'.'Fu'.'ZX'.'Q=','Q0lu'.'dHJ'.'h'.'bm'.'V0RXZ'.'l'.'bn'.'R'.'IYW5kbGVycw'.'='.'=','U1BSZW'.'dp'.'c3R'.'lclVw'.'ZGF0ZW'.'RJ'.'dGVt','Q'.'0ludH'.'J'.'hb'.'mV0U2hhcmVwb2lu'.'d'.'Do'.'6QWdlbnRM'.'aXN0'.'cygpOw==',''.'aW50'.'cmFuZXQ=',''.'Tg==','Q0ludH'.'J'.'hbmV0U'.'2'.'hhc'.'mVwb'.'2lu'.'dDo'.'6Q'.'WdlbnRRdW'.'V1ZS'.'g'.'pOw'.'='.'=','aW50'.'cmFuZXQ=',''.'T'.'g==','Q0ludHJhb'.'mV0U2hhcmVwb'.'2'.'lu'.'dDo6QWdlbnR'.'VcGR'.'hdGUoK'.'Ts=','aW50cmFu'.'ZX'.'Q=','Tg'.'==','aWJsb2Nr',''.'T'.'25BZnR'.'l'.'c'.'k'.'lCbG9'.'j'.'a0Vs'.'ZW1'.'lbn'.'RBZGQ=',''.'a'.'W50cm'.'Fu'.'ZXQ=',''.'Q0lu'.'dHJhbmV0RXZlbnRIYW5kbGVycw==','U1BS'.'ZWd'.'pc3Rlc'.'lVw'.'ZG'.'F0ZWRJdG'.'Vt','aWJsb'.'2'.'Nr',''.'T25BZ'.'nR'.'lc'.'klCbG9ja0'.'VsZW'.'1lbn'.'RVcGRh'.'dGU'.'=','aW5'.'0cmFuZXQ=','Q'.'0l'.'u'.'dHJhbm'.'V0RXZlb'.'nRIYW'.'5'.'k'.'b'.'GVycw==','U1B'.'SZWdpc3'.'R'.'l'.'clVwZG'.'F0Z'.'WR'.'Jd'.'GVt','Q'.'0ludHJh'.'bmV0'.'U2'.'hhcmVwb2lu'.'dDo6Q'.'Wdl'.'bn'.'RMaXN0cy'.'g'.'pOw='.'=',''.'a'.'W50cmF'.'u'.'ZXQ'.'=','Q0ludHJhb'.'mV0U2hhcm'.'Vwb'.'2'.'ludDo'.'6Q'.'WdlbnR'.'R'.'dWV1ZS'.'gpOw='.'=','a'.'W'.'50cmFuZX'.'Q'.'=','Q'.'0l'.'ud'.'HJhb'.'mV'.'0U2hhcmVwb2'.'lud'.'Do6'.'QWdlbnR'.'V'.'cGRhd'.'G'.'UoKTs'.'=','aW5'.'0'.'c'.'m'.'Fu'.'ZXQ=','Y'.'3J'.'t','bW'.'Fpbg==','T2'.'5'.'CZ'.'WZvcm'.'VQ'.'cm9sb'.'2'.'c=','bWF'.'pbg==','Q'.'1dpemFyZFNvbFBhbmVsSW5'.'0cm'.'FuZX'.'Q=','U2h'.'vd1BhbmVs',''.'L'.'21vZH'.'VsZ'.'X'.'M'.'vaW50c'.'mFu'.'ZXQvcG'.'FuZWxfYnV0'.'dG9uLnBocA==','RU'.'5'.'DT0'.'RF','W'.'Q='.'=');return base64_decode($_1109912327[$_1072223521]);}};$GLOBALS['____1018800033'][0](___632040786(0), ___632040786(1));class CBXFeatures{ private static $_1832862674= 30; private static $_828603987= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_1856073278= false; private static $_1017401475= false; private static function __552435400(){ if(self::$_1856073278 == false){ self::$_1856073278= array(); foreach(self::$_828603987 as $_412480204 => $_528149938){ foreach($_528149938 as $_1828909940) self::$_1856073278[$_1828909940]= $_412480204;}} if(self::$_1017401475 == false){ self::$_1017401475= array(); $_28449140= COption::GetOptionString(___632040786(2), ___632040786(3), ___632040786(4)); if($GLOBALS['____1018800033'][1]($_28449140)>(1436/2-718)){ $_28449140= $GLOBALS['____1018800033'][2]($_28449140); self::$_1017401475= $GLOBALS['____1018800033'][3]($_28449140); if(!$GLOBALS['____1018800033'][4](self::$_1017401475)) self::$_1017401475= array();} if($GLOBALS['____1018800033'][5](self::$_1017401475) <=(1052/2-526)) self::$_1017401475= array(___632040786(5) => array(), ___632040786(6) => array());}} public static function InitiateEditionsSettings($_865985981){ self::__552435400(); $_1422811012= array(); foreach(self::$_828603987 as $_412480204 => $_528149938){ $_912116815= $GLOBALS['____1018800033'][6]($_412480204, $_865985981); self::$_1017401475[___632040786(7)][$_412480204]=($_912116815? array(___632040786(8)): array(___632040786(9))); foreach($_528149938 as $_1828909940){ self::$_1017401475[___632040786(10)][$_1828909940]= $_912116815; if(!$_912116815) $_1422811012[]= array($_1828909940, false);}} $_293400298= $GLOBALS['____1018800033'][7](self::$_1017401475); $_293400298= $GLOBALS['____1018800033'][8]($_293400298); COption::SetOptionString(___632040786(11), ___632040786(12), $_293400298); foreach($_1422811012 as $_2049468087) self::__151339723($_2049468087[(126*2-252)], $_2049468087[round(0+1)]);} public static function IsFeatureEnabled($_1828909940){ if($GLOBALS['____1018800033'][9]($_1828909940) <= 0) return true; self::__552435400(); if(!$GLOBALS['____1018800033'][10]($_1828909940, self::$_1856073278)) return true; if(self::$_1856073278[$_1828909940] == ___632040786(13)) $_1241865429= array(___632040786(14)); elseif($GLOBALS['____1018800033'][11](self::$_1856073278[$_1828909940], self::$_1017401475[___632040786(15)])) $_1241865429= self::$_1017401475[___632040786(16)][self::$_1856073278[$_1828909940]]; else $_1241865429= array(___632040786(17)); if($_1241865429[(170*2-340)] != ___632040786(18) && $_1241865429[(1256/2-628)] != ___632040786(19)){ return false;} elseif($_1241865429[(762-2*381)] == ___632040786(20)){ if($_1241865429[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]< $GLOBALS['____1018800033'][12]((127*2-254),(1324/2-662), min(242,0,80.666666666667), Date(___632040786(21)), $GLOBALS['____1018800033'][13](___632040786(22))- self::$_1832862674, $GLOBALS['____1018800033'][14](___632040786(23)))){ if(!isset($_1241865429[round(0+0.5+0.5+0.5+0.5)]) ||!$_1241865429[round(0+0.4+0.4+0.4+0.4+0.4)]) self::__1953776282(self::$_1856073278[$_1828909940]); return false;}} return!$GLOBALS['____1018800033'][15]($_1828909940, self::$_1017401475[___632040786(24)]) || self::$_1017401475[___632040786(25)][$_1828909940];} public static function IsFeatureInstalled($_1828909940){ if($GLOBALS['____1018800033'][16]($_1828909940) <= 0) return true; self::__552435400(); return($GLOBALS['____1018800033'][17]($_1828909940, self::$_1017401475[___632040786(26)]) && self::$_1017401475[___632040786(27)][$_1828909940]);} public static function IsFeatureEditable($_1828909940){ if($GLOBALS['____1018800033'][18]($_1828909940) <= 0) return true; self::__552435400(); if(!$GLOBALS['____1018800033'][19]($_1828909940, self::$_1856073278)) return true; if(self::$_1856073278[$_1828909940] == ___632040786(28)) $_1241865429= array(___632040786(29)); elseif($GLOBALS['____1018800033'][20](self::$_1856073278[$_1828909940], self::$_1017401475[___632040786(30)])) $_1241865429= self::$_1017401475[___632040786(31)][self::$_1856073278[$_1828909940]]; else $_1241865429= array(___632040786(32)); if($_1241865429[(213*2-426)] != ___632040786(33) && $_1241865429[(756-2*378)] != ___632040786(34)){ return false;} elseif($_1241865429[(205*2-410)] == ___632040786(35)){ if($_1241865429[round(0+1)]< $GLOBALS['____1018800033'][21]((1080/2-540),(1092/2-546),(155*2-310), Date(___632040786(36)), $GLOBALS['____1018800033'][22](___632040786(37))- self::$_1832862674, $GLOBALS['____1018800033'][23](___632040786(38)))){ if(!isset($_1241865429[round(0+0.5+0.5+0.5+0.5)]) ||!$_1241865429[round(0+1+1)]) self::__1953776282(self::$_1856073278[$_1828909940]); return false;}} return true;} private static function __151339723($_1828909940, $_1503624578){ if($GLOBALS['____1018800033'][24]("CBXFeatures", "On".$_1828909940."SettingsChange")) $GLOBALS['____1018800033'][25](array("CBXFeatures", "On".$_1828909940."SettingsChange"), array($_1828909940, $_1503624578)); $_490552507= $GLOBALS['_____223748094'][0](___632040786(39), ___632040786(40).$_1828909940.___632040786(41)); while($_1234837276= $_490552507->Fetch()) $GLOBALS['_____223748094'][1]($_1234837276, array($_1828909940, $_1503624578));} public static function SetFeatureEnabled($_1828909940, $_1503624578= true, $_855589778= true){ if($GLOBALS['____1018800033'][26]($_1828909940) <= 0) return; if(!self::IsFeatureEditable($_1828909940)) $_1503624578= false; $_1503624578=($_1503624578? true: false); self::__552435400(); $_2043287978=(!$GLOBALS['____1018800033'][27]($_1828909940, self::$_1017401475[___632040786(42)]) && $_1503624578 || $GLOBALS['____1018800033'][28]($_1828909940, self::$_1017401475[___632040786(43)]) && $_1503624578 != self::$_1017401475[___632040786(44)][$_1828909940]); self::$_1017401475[___632040786(45)][$_1828909940]= $_1503624578; $_293400298= $GLOBALS['____1018800033'][29](self::$_1017401475); $_293400298= $GLOBALS['____1018800033'][30]($_293400298); COption::SetOptionString(___632040786(46), ___632040786(47), $_293400298); if($_2043287978 && $_855589778) self::__151339723($_1828909940, $_1503624578);} private static function __1953776282($_412480204){ if($GLOBALS['____1018800033'][31]($_412480204) <= 0 || $_412480204 == "Portal") return; self::__552435400(); if(!$GLOBALS['____1018800033'][32]($_412480204, self::$_1017401475[___632040786(48)]) || $GLOBALS['____1018800033'][33]($_412480204, self::$_1017401475[___632040786(49)]) && self::$_1017401475[___632040786(50)][$_412480204][(848-2*424)] != ___632040786(51)) return; if(isset(self::$_1017401475[___632040786(52)][$_412480204][round(0+0.4+0.4+0.4+0.4+0.4)]) && self::$_1017401475[___632040786(53)][$_412480204][round(0+2)]) return; $_1422811012= array(); if($GLOBALS['____1018800033'][34]($_412480204, self::$_828603987) && $GLOBALS['____1018800033'][35](self::$_828603987[$_412480204])){ foreach(self::$_828603987[$_412480204] as $_1828909940){ if($GLOBALS['____1018800033'][36]($_1828909940, self::$_1017401475[___632040786(54)]) && self::$_1017401475[___632040786(55)][$_1828909940]){ self::$_1017401475[___632040786(56)][$_1828909940]= false; $_1422811012[]= array($_1828909940, false);}} self::$_1017401475[___632040786(57)][$_412480204][round(0+0.66666666666667+0.66666666666667+0.66666666666667)]= true;} $_293400298= $GLOBALS['____1018800033'][37](self::$_1017401475); $_293400298= $GLOBALS['____1018800033'][38]($_293400298); COption::SetOptionString(___632040786(58), ___632040786(59), $_293400298); foreach($_1422811012 as $_2049468087) self::__151339723($_2049468087[(1436/2-718)], $_2049468087[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function ModifyFeaturesSettings($_865985981, $_528149938){ self::__552435400(); foreach($_865985981 as $_412480204 => $_1459663907) self::$_1017401475[___632040786(60)][$_412480204]= $_1459663907; $_1422811012= array(); foreach($_528149938 as $_1828909940 => $_1503624578){ if(!$GLOBALS['____1018800033'][39]($_1828909940, self::$_1017401475[___632040786(61)]) && $_1503624578 || $GLOBALS['____1018800033'][40]($_1828909940, self::$_1017401475[___632040786(62)]) && $_1503624578 != self::$_1017401475[___632040786(63)][$_1828909940]) $_1422811012[]= array($_1828909940, $_1503624578); self::$_1017401475[___632040786(64)][$_1828909940]= $_1503624578;} $_293400298= $GLOBALS['____1018800033'][41](self::$_1017401475); $_293400298= $GLOBALS['____1018800033'][42]($_293400298); COption::SetOptionString(___632040786(65), ___632040786(66), $_293400298); self::$_1017401475= false; foreach($_1422811012 as $_2049468087) self::__151339723($_2049468087[(1376/2-688)], $_2049468087[round(0+1)]);} public static function SaveFeaturesSettings($_975269711, $_877117230){ self::__552435400(); $_1256999221= array(___632040786(67) => array(), ___632040786(68) => array()); if(!$GLOBALS['____1018800033'][43]($_975269711)) $_975269711= array(); if(!$GLOBALS['____1018800033'][44]($_877117230)) $_877117230= array(); if(!$GLOBALS['____1018800033'][45](___632040786(69), $_975269711)) $_975269711[]= ___632040786(70); foreach(self::$_828603987 as $_412480204 => $_528149938){ if($GLOBALS['____1018800033'][46]($_412480204, self::$_1017401475[___632040786(71)])) $_502732722= self::$_1017401475[___632040786(72)][$_412480204]; else $_502732722=($_412480204 == ___632040786(73))? array(___632040786(74)): array(___632040786(75)); if($_502732722[(245*2-490)] == ___632040786(76) || $_502732722[min(244,0,81.333333333333)] == ___632040786(77)){ $_1256999221[___632040786(78)][$_412480204]= $_502732722;} else{ if($GLOBALS['____1018800033'][47]($_412480204, $_975269711)) $_1256999221[___632040786(79)][$_412480204]= array(___632040786(80), $GLOBALS['____1018800033'][48]((1228/2-614),(203*2-406),(1340/2-670), $GLOBALS['____1018800033'][49](___632040786(81)), $GLOBALS['____1018800033'][50](___632040786(82)), $GLOBALS['____1018800033'][51](___632040786(83)))); else $_1256999221[___632040786(84)][$_412480204]= array(___632040786(85));}} $_1422811012= array(); foreach(self::$_1856073278 as $_1828909940 => $_412480204){ if($_1256999221[___632040786(86)][$_412480204][(174*2-348)] != ___632040786(87) && $_1256999221[___632040786(88)][$_412480204][(1140/2-570)] != ___632040786(89)){ $_1256999221[___632040786(90)][$_1828909940]= false;} else{ if($_1256999221[___632040786(91)][$_412480204][min(236,0,78.666666666667)] == ___632040786(92) && $_1256999221[___632040786(93)][$_412480204][round(0+1)]< $GLOBALS['____1018800033'][52]((1100/2-550), min(150,0,50),(1096/2-548), Date(___632040786(94)), $GLOBALS['____1018800033'][53](___632040786(95))- self::$_1832862674, $GLOBALS['____1018800033'][54](___632040786(96)))) $_1256999221[___632040786(97)][$_1828909940]= false; else $_1256999221[___632040786(98)][$_1828909940]= $GLOBALS['____1018800033'][55]($_1828909940, $_877117230); if(!$GLOBALS['____1018800033'][56]($_1828909940, self::$_1017401475[___632040786(99)]) && $_1256999221[___632040786(100)][$_1828909940] || $GLOBALS['____1018800033'][57]($_1828909940, self::$_1017401475[___632040786(101)]) && $_1256999221[___632040786(102)][$_1828909940] != self::$_1017401475[___632040786(103)][$_1828909940]) $_1422811012[]= array($_1828909940, $_1256999221[___632040786(104)][$_1828909940]);}} $_293400298= $GLOBALS['____1018800033'][58]($_1256999221); $_293400298= $GLOBALS['____1018800033'][59]($_293400298); COption::SetOptionString(___632040786(105), ___632040786(106), $_293400298); self::$_1017401475= false; foreach($_1422811012 as $_2049468087) self::__151339723($_2049468087[(126*2-252)], $_2049468087[round(0+0.33333333333333+0.33333333333333+0.33333333333333)]);} public static function GetFeaturesList(){ self::__552435400(); $_1200175577= array(); foreach(self::$_828603987 as $_412480204 => $_528149938){ if($GLOBALS['____1018800033'][60]($_412480204, self::$_1017401475[___632040786(107)])) $_502732722= self::$_1017401475[___632040786(108)][$_412480204]; else $_502732722=($_412480204 == ___632040786(109))? array(___632040786(110)): array(___632040786(111)); $_1200175577[$_412480204]= array( ___632040786(112) => $_502732722[min(122,0,40.666666666667)], ___632040786(113) => $_502732722[round(0+1)], ___632040786(114) => array(),); $_1200175577[$_412480204][___632040786(115)]= false; if($_1200175577[$_412480204][___632040786(116)] == ___632040786(117)){ $_1200175577[$_412480204][___632040786(118)]= $GLOBALS['____1018800033'][61](($GLOBALS['____1018800033'][62]()- $_1200175577[$_412480204][___632040786(119)])/ round(0+28800+28800+28800)); if($_1200175577[$_412480204][___632040786(120)]> self::$_1832862674) $_1200175577[$_412480204][___632040786(121)]= true;} foreach($_528149938 as $_1828909940) $_1200175577[$_412480204][___632040786(122)][$_1828909940]=(!$GLOBALS['____1018800033'][63]($_1828909940, self::$_1017401475[___632040786(123)]) || self::$_1017401475[___632040786(124)][$_1828909940]);} return $_1200175577;} private static function __333337652($_1300231084, $_788470559){ if(IsModuleInstalled($_1300231084) == $_788470559) return true; $_1312800734= $_SERVER[___632040786(125)].___632040786(126).$_1300231084.___632040786(127); if(!$GLOBALS['____1018800033'][64]($_1312800734)) return false; include_once($_1312800734); $_1026434262= $GLOBALS['____1018800033'][65](___632040786(128), ___632040786(129), $_1300231084); if(!$GLOBALS['____1018800033'][66]($_1026434262)) return false; $_1004191943= new $_1026434262; if($_788470559){ if(!$_1004191943->InstallDB()) return false; $_1004191943->InstallEvents(); if(!$_1004191943->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___632040786(130))) CSearch::DeleteIndex($_1300231084); UnRegisterModule($_1300231084);} return true;} protected static function OnRequestsSettingsChange($_1828909940, $_1503624578){ self::__333337652("form", $_1503624578);} protected static function OnLearningSettingsChange($_1828909940, $_1503624578){ self::__333337652("learning", $_1503624578);} protected static function OnJabberSettingsChange($_1828909940, $_1503624578){ self::__333337652("xmpp", $_1503624578);} protected static function OnVideoConferenceSettingsChange($_1828909940, $_1503624578){ self::__333337652("video", $_1503624578);} protected static function OnBizProcSettingsChange($_1828909940, $_1503624578){ self::__333337652("bizprocdesigner", $_1503624578);} protected static function OnListsSettingsChange($_1828909940, $_1503624578){ self::__333337652("lists", $_1503624578);} protected static function OnWikiSettingsChange($_1828909940, $_1503624578){ self::__333337652("wiki", $_1503624578);} protected static function OnSupportSettingsChange($_1828909940, $_1503624578){ self::__333337652("support", $_1503624578);} protected static function OnControllerSettingsChange($_1828909940, $_1503624578){ self::__333337652("controller", $_1503624578);} protected static function OnAnalyticsSettingsChange($_1828909940, $_1503624578){ self::__333337652("statistic", $_1503624578);} protected static function OnVoteSettingsChange($_1828909940, $_1503624578){ self::__333337652("vote", $_1503624578);} protected static function OnFriendsSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(131); $_1708961897= CSite::GetList(($_912116815= ___632040786(132)),($_424751600= ___632040786(133)), array(___632040786(134) => ___632040786(135))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(136), ___632040786(137), ___632040786(138), $_984051508[___632040786(139)]) != $_1022940749){ COption::SetOptionString(___632040786(140), ___632040786(141), $_1022940749, false, $_984051508[___632040786(142)]); COption::SetOptionString(___632040786(143), ___632040786(144), $_1022940749);}}} protected static function OnMicroBlogSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(145); $_1708961897= CSite::GetList(($_912116815= ___632040786(146)),($_424751600= ___632040786(147)), array(___632040786(148) => ___632040786(149))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(150), ___632040786(151), ___632040786(152), $_984051508[___632040786(153)]) != $_1022940749){ COption::SetOptionString(___632040786(154), ___632040786(155), $_1022940749, false, $_984051508[___632040786(156)]); COption::SetOptionString(___632040786(157), ___632040786(158), $_1022940749);} if(COption::GetOptionString(___632040786(159), ___632040786(160), ___632040786(161), $_984051508[___632040786(162)]) != $_1022940749){ COption::SetOptionString(___632040786(163), ___632040786(164), $_1022940749, false, $_984051508[___632040786(165)]); COption::SetOptionString(___632040786(166), ___632040786(167), $_1022940749);}}} protected static function OnPersonalFilesSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(168); $_1708961897= CSite::GetList(($_912116815= ___632040786(169)),($_424751600= ___632040786(170)), array(___632040786(171) => ___632040786(172))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(173), ___632040786(174), ___632040786(175), $_984051508[___632040786(176)]) != $_1022940749){ COption::SetOptionString(___632040786(177), ___632040786(178), $_1022940749, false, $_984051508[___632040786(179)]); COption::SetOptionString(___632040786(180), ___632040786(181), $_1022940749);}}} protected static function OnPersonalBlogSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(182); $_1708961897= CSite::GetList(($_912116815= ___632040786(183)),($_424751600= ___632040786(184)), array(___632040786(185) => ___632040786(186))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(187), ___632040786(188), ___632040786(189), $_984051508[___632040786(190)]) != $_1022940749){ COption::SetOptionString(___632040786(191), ___632040786(192), $_1022940749, false, $_984051508[___632040786(193)]); COption::SetOptionString(___632040786(194), ___632040786(195), $_1022940749);}}} protected static function OnPersonalPhotoSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(196); $_1708961897= CSite::GetList(($_912116815= ___632040786(197)),($_424751600= ___632040786(198)), array(___632040786(199) => ___632040786(200))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(201), ___632040786(202), ___632040786(203), $_984051508[___632040786(204)]) != $_1022940749){ COption::SetOptionString(___632040786(205), ___632040786(206), $_1022940749, false, $_984051508[___632040786(207)]); COption::SetOptionString(___632040786(208), ___632040786(209), $_1022940749);}}} protected static function OnPersonalForumSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(210); $_1708961897= CSite::GetList(($_912116815= ___632040786(211)),($_424751600= ___632040786(212)), array(___632040786(213) => ___632040786(214))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(215), ___632040786(216), ___632040786(217), $_984051508[___632040786(218)]) != $_1022940749){ COption::SetOptionString(___632040786(219), ___632040786(220), $_1022940749, false, $_984051508[___632040786(221)]); COption::SetOptionString(___632040786(222), ___632040786(223), $_1022940749);}}} protected static function OnTasksSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(224); $_1708961897= CSite::GetList(($_912116815= ___632040786(225)),($_424751600= ___632040786(226)), array(___632040786(227) => ___632040786(228))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(229), ___632040786(230), ___632040786(231), $_984051508[___632040786(232)]) != $_1022940749){ COption::SetOptionString(___632040786(233), ___632040786(234), $_1022940749, false, $_984051508[___632040786(235)]); COption::SetOptionString(___632040786(236), ___632040786(237), $_1022940749);} if(COption::GetOptionString(___632040786(238), ___632040786(239), ___632040786(240), $_984051508[___632040786(241)]) != $_1022940749){ COption::SetOptionString(___632040786(242), ___632040786(243), $_1022940749, false, $_984051508[___632040786(244)]); COption::SetOptionString(___632040786(245), ___632040786(246), $_1022940749);}} self::__333337652(___632040786(247), $_1503624578);} protected static function OnCalendarSettingsChange($_1828909940, $_1503624578){ if($_1503624578) $_1022940749= "Y"; else $_1022940749= ___632040786(248); $_1708961897= CSite::GetList(($_912116815= ___632040786(249)),($_424751600= ___632040786(250)), array(___632040786(251) => ___632040786(252))); while($_984051508= $_1708961897->Fetch()){ if(COption::GetOptionString(___632040786(253), ___632040786(254), ___632040786(255), $_984051508[___632040786(256)]) != $_1022940749){ COption::SetOptionString(___632040786(257), ___632040786(258), $_1022940749, false, $_984051508[___632040786(259)]); COption::SetOptionString(___632040786(260), ___632040786(261), $_1022940749);} if(COption::GetOptionString(___632040786(262), ___632040786(263), ___632040786(264), $_984051508[___632040786(265)]) != $_1022940749){ COption::SetOptionString(___632040786(266), ___632040786(267), $_1022940749, false, $_984051508[___632040786(268)]); COption::SetOptionString(___632040786(269), ___632040786(270), $_1022940749);}}} protected static function OnSMTPSettingsChange($_1828909940, $_1503624578){ self::__333337652("mail", $_1503624578);} protected static function OnExtranetSettingsChange($_1828909940, $_1503624578){ $_1682910633= COption::GetOptionString("extranet", "extranet_site", ""); if($_1682910633){ $_379236340= new CSite; $_379236340->Update($_1682910633, array(___632040786(271) =>($_1503624578? ___632040786(272): ___632040786(273))));} self::__333337652(___632040786(274), $_1503624578);} protected static function OnDAVSettingsChange($_1828909940, $_1503624578){ self::__333337652("dav", $_1503624578);} protected static function OntimemanSettingsChange($_1828909940, $_1503624578){ self::__333337652("timeman", $_1503624578);} protected static function Onintranet_sharepointSettingsChange($_1828909940, $_1503624578){ if($_1503624578){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___632040786(275), ___632040786(276), ___632040786(277), ___632040786(278), ___632040786(279)); CAgent::AddAgent(___632040786(280), ___632040786(281), ___632040786(282), round(0+125+125+125+125)); CAgent::AddAgent(___632040786(283), ___632040786(284), ___632040786(285), round(0+75+75+75+75)); CAgent::AddAgent(___632040786(286), ___632040786(287), ___632040786(288), round(0+1800+1800));} else{ UnRegisterModuleDependences(___632040786(289), ___632040786(290), ___632040786(291), ___632040786(292), ___632040786(293)); UnRegisterModuleDependences(___632040786(294), ___632040786(295), ___632040786(296), ___632040786(297), ___632040786(298)); CAgent::RemoveAgent(___632040786(299), ___632040786(300)); CAgent::RemoveAgent(___632040786(301), ___632040786(302)); CAgent::RemoveAgent(___632040786(303), ___632040786(304));}} protected static function OncrmSettingsChange($_1828909940, $_1503624578){ if($_1503624578) COption::SetOptionString("crm", "form_features", "Y"); self::__333337652(___632040786(305), $_1503624578);} protected static function OnClusterSettingsChange($_1828909940, $_1503624578){ self::__333337652("cluster", $_1503624578);} protected static function OnMultiSitesSettingsChange($_1828909940, $_1503624578){ if($_1503624578) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___632040786(306), ___632040786(307), ___632040786(308), ___632040786(309), ___632040786(310), ___632040786(311));} protected static function OnIdeaSettingsChange($_1828909940, $_1503624578){ self::__333337652("idea", $_1503624578);} protected static function OnMeetingSettingsChange($_1828909940, $_1503624578){ self::__333337652("meeting", $_1503624578);} protected static function OnXDImportSettingsChange($_1828909940, $_1503624578){ self::__333337652("xdimport", $_1503624578);}} $GLOBALS['____1018800033'][67](___632040786(312), ___632040786(313));/**/			//Do not remove this

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

//magic short URI
if(defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri())
{
	//local redirect inside
	die();
}

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

/*ZDUyZmZZTMxMmVkMzkzZjI3YjA0Y2U1NGYyZWY3MzQ3MGYwY2Y=*/$GLOBALS['____56643472']= array(base64_decode('bXRfc'.'mF'.'uZA=='),base64_decode('ZX'.'hwbG9kZQ=='),base64_decode('cGFjaw=='),base64_decode('b'.'W'.'Q1'),base64_decode('Y'.'29uc3R'.'hbnQ='),base64_decode('aGF'.'zaF9'.'obWFj'),base64_decode(''.'c3'.'RyY2'.'1'.'w'),base64_decode('a'.'XN'.'f'.'b2JqZWN0'),base64_decode('Y2'.'F'.'sb'.'F91c2'.'V'.'yX2Z1'.'bmM='),base64_decode('Y2F'.'sbF'.'91'.'c2V'.'yX2Z1bmM='),base64_decode('Y2F'.'s'.'bF'.'91c2'.'VyX2Z1bmM='),base64_decode('Y2FsbF91c2V'.'yX'.'2'.'Z'.'1bm'.'M='),base64_decode(''.'Y2FsbF91c2'.'Vy'.'X2Z1'.'bmM='));if(!function_exists(__NAMESPACE__.'\\___1336019523')){function ___1336019523($_1521036562){static $_695454884= false; if($_695454884 == false) $_695454884=array('REI=','U0VM'.'RU'.'NUIFZ'.'BT'.'F'.'VFI'.'EZST00gYl9vcHRpb'.'2'.'4'.'gV0'.'hFUkUgTkF'.'NRT0nflB'.'BUkF'.'NX01BWF9'.'VU0VS'.'U'.'ycgQU5EIE'.'1PRF'.'VM'.'R'.'V9JRD0'.'nbWFpbicgQU5EIF'.'NJ'.'VE'.'V'.'fSUQgSVMg'.'TlVM'.'TA==','VkFMVU'.'U=','Lg==',''.'SCo=','Yml0c'.'ml'.'4','TElDRU'.'5'.'TRV9'.'LRV'.'k=','c'.'2hh'.'Mj'.'U'.'2','VVN'.'FU'.'g==','VVNFUg'.'='.'=','VVNFUg='.'=','SXNB'.'dXRob3J'.'pemVk','VVNFUg==',''.'SXN'.'BZ'.'G1p'.'bg'.'==','QVBQT'.'El'.'DQVRJ'.'T04'.'=','UmV'.'zdGFydEJ1ZmZ'.'lcg==','T'.'G9jYWxSZWRpcmV'.'j'.'dA==','L2x'.'pY2'.'Vuc'.'2V'.'fcmV'.'zdHJpY3Rpb24u'.'cGhw','XEJ'.'pdHJ'.'peFxNYWl'.'u'.'XE'.'NvbmZp'.'Z1xPcH'.'Rpb246OnN'.'ldA==',''.'bWF'.'pbg='.'=','U'.'EFSQU1f'.'TUFYX1VT'.'RVJ'.'T');return base64_decode($_695454884[$_1521036562]);}};if($GLOBALS['____56643472'][0](round(0+0.33333333333333+0.33333333333333+0.33333333333333), round(0+4+4+4+4+4)) == round(0+1.4+1.4+1.4+1.4+1.4)){ $_224245868= $GLOBALS[___1336019523(0)]->Query(___1336019523(1), true); if($_169157868= $_224245868->Fetch()){ $_705061887= $_169157868[___1336019523(2)]; list($_430241587, $_169068972)= $GLOBALS['____56643472'][1](___1336019523(3), $_705061887); $_1451475686= $GLOBALS['____56643472'][2](___1336019523(4), $_430241587); $_561671716= ___1336019523(5).$GLOBALS['____56643472'][3]($GLOBALS['____56643472'][4](___1336019523(6))); $_748210814= $GLOBALS['____56643472'][5](___1336019523(7), $_169068972, $_561671716, true); if($GLOBALS['____56643472'][6]($_748210814, $_1451475686) !==(922-2*461)){ if(isset($GLOBALS[___1336019523(8)]) && $GLOBALS['____56643472'][7]($GLOBALS[___1336019523(9)]) && $GLOBALS['____56643472'][8](array($GLOBALS[___1336019523(10)], ___1336019523(11))) &&!$GLOBALS['____56643472'][9](array($GLOBALS[___1336019523(12)], ___1336019523(13)))){ $GLOBALS['____56643472'][10](array($GLOBALS[___1336019523(14)], ___1336019523(15))); $GLOBALS['____56643472'][11](___1336019523(16), ___1336019523(17), true);}}} else{ $GLOBALS['____56643472'][12](___1336019523(18), ___1336019523(19), ___1336019523(20), round(0+4+4+4));}}/**/       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
