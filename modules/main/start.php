<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/start.php"))."/bx_root.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/loader.php");

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"main",
	array(
		"bitrix\\main\\application" => "lib/application.php",
		"bitrix\\main\\httpapplication" => "lib/httpapplication.php",
		"bitrix\\main\\argumentexception" => "lib/exception.php",
		"bitrix\\main\\argumentnullexception" => "lib/exception.php",
		"bitrix\\main\\argumentoutofrangeexception" => "lib/exception.php",
		"bitrix\\main\\argumenttypeexception" => "lib/exception.php",
		"bitrix\\main\\notimplementedexception" => "lib/exception.php",
		"bitrix\\main\\notsupportedexception" => "lib/exception.php",
		"bitrix\\main\\invalidoperationexception" => "lib/exception.php",
		"bitrix\\main\\objectpropertyexception" => "lib/exception.php",
		"bitrix\\main\\objectnotfoundexception" => "lib/exception.php",
		"bitrix\\main\\objectexception" => "lib/exception.php",
		"bitrix\\main\\systemexception" => "lib/exception.php",
		"bitrix\\main\\accessdeniedexception" => "lib/exception.php",
		"bitrix\\main\\decodingexception" => "lib/exception.php",
		"bitrix\\main\\io\\invalidpathexception" => "lib/io/ioexception.php",
		"bitrix\\main\\io\\filenotfoundexception" => "lib/io/ioexception.php",
		"bitrix\\main\\io\\filedeleteexception" => "lib/io/ioexception.php",
		"bitrix\\main\\io\\fileopenexception" => "lib/io/ioexception.php",
		"bitrix\\main\\io\\filenotopenedexception" => "lib/io/ioexception.php",
		"bitrix\\main\\context" => "lib/context.php",
		"bitrix\\main\\httpcontext" => "lib/httpcontext.php",
		"bitrix\\main\\dispatcher" => "lib/dispatcher.php",
		"bitrix\\main\\environment" => "lib/environment.php",
		"bitrix\\main\\event" => "lib/event.php",
		"bitrix\\main\\eventmanager" => "lib/eventmanager.php",
		"bitrix\\main\\eventresult" => "lib/eventresult.php",
		"bitrix\\main\\request" => "lib/request.php",
		"bitrix\\main\\httprequest" => "lib/httprequest.php",
		"bitrix\\main\\response" => "lib/response.php",
		"bitrix\\main\\httpresponse" => "lib/httpresponse.php",
		"bitrix\\main\\modulemanager" => "lib/modulemanager.php",
		"bitrix\\main\\server" => "lib/server.php",
		"bitrix\\main\\config\\configuration" => "lib/config/configuration.php",
		"bitrix\\main\\config\\option" => "lib/config/option.php",
		"bitrix\\main\\context\\culture" => "lib/context/culture.php",
		"bitrix\\main\\context\\site" => "lib/context/site.php",
		"bitrix\\main\\data\\cache" => "lib/data/cache.php",
		"bitrix\\main\\data\\cacheengineapc" => "lib/data/cacheengineapc.php",
		"bitrix\\main\\data\\cacheenginememcache" => "lib/data/cacheenginememcache.php",
		"bitrix\\main\\data\\cacheenginefiles" => "lib/data/cacheenginefiles.php",
		"bitrix\\main\\data\\cacheenginenone" => "lib/data/cacheenginenone.php",
		"bitrix\\main\\data\\connection" => "lib/data/connection.php",
		"bitrix\\main\\data\\connectionpool" => "lib/data/connectionpool.php",
		"bitrix\\main\\data\\icacheengine" => "lib/data/cache.php",
		"bitrix\\main\\data\\hsphpreadconnection" => "lib/data/hsphpreadconnection.php",
		"bitrix\\main\\data\\managedcache" => "lib/data/managedcache.php",
		"bitrix\\main\\data\\taggedcache" => "lib/data/taggedcache.php",
		"bitrix\\main\\data\\memcacheconnection" => "lib/data/memcacheconnection.php",
		"bitrix\\main\\data\\memcachedconnection" => "lib/data/memcachedconnection.php",
		"bitrix\\main\\data\\nosqlconnection" => "lib/data/nosqlconnection.php",
		"bitrix\\main\\db\\arrayresult" => "lib/db/arrayresult.php",
		"bitrix\\main\\db\\result" => "lib/db/result.php",
		"bitrix\\main\\db\\connection" => "lib/db/connection.php",
		"bitrix\\main\\db\\sqlexception" => "lib/db/sqlexception.php",
		"bitrix\\main\\db\\sqlqueryexception" => "lib/db/sqlexception.php",
		"bitrix\\main\\db\\sqlexpression" => "lib/db/sqlexpression.php",
		"bitrix\\main\\db\\sqlhelper" => "lib/db/sqlhelper.php",
		"bitrix\\main\\db\\mysqlconnection" => "lib/db/mysqlconnection.php",
		"bitrix\\main\\db\\mysqlresult" => "lib/db/mysqlresult.php",
		"bitrix\\main\\db\\mysqlsqlhelper" => "lib/db/mysqlsqlhelper.php",
		"bitrix\\main\\db\\mysqliconnection" => "lib/db/mysqliconnection.php",
		"bitrix\\main\\db\\mysqliresult" => "lib/db/mysqliresult.php",
		"bitrix\\main\\db\\mysqlisqlhelper" => "lib/db/mysqlisqlhelper.php",
		"bitrix\\main\\db\\mssqlconnection" => "lib/db/mssqlconnection.php",
		"bitrix\\main\\db\\mssqlresult" => "lib/db/mssqlresult.php",
		"bitrix\\main\\db\\mssqlsqlhelper" => "lib/db/mssqlsqlhelper.php",
		"bitrix\\main\\db\\oracleconnection" => "lib/db/oracleconnection.php",
		"bitrix\\main\\db\\oracleresult" => "lib/db/oracleresult.php",
		"bitrix\\main\\db\\oraclesqlhelper" => "lib/db/oraclesqlhelper.php",
		"bitrix\\main\\diag\\httpexceptionhandleroutput" => "lib/diag/httpexceptionhandleroutput.php",
		"bitrix\\main\\diag\\fileexceptionhandlerlog" => "lib/diag/fileexceptionhandlerlog.php",
		"bitrix\\main\\diag\\exceptionhandler" => "lib/diag/exceptionhandler.php",
		"bitrix\\main\\diag\\iexceptionhandleroutput" => "lib/diag/iexceptionhandleroutput.php",
		"bitrix\\main\\diag\\exceptionhandlerlog" => "lib/diag/exceptionhandlerlog.php",
		"bitrix\\main\\io\\file" => "lib/io/file.php",
		"bitrix\\main\\io\\fileentry" => "lib/io/fileentry.php",
		"bitrix\\main\\io\\path" => "lib/io/path.php",
		"bitrix\\main\\io\\filesystementry" => "lib/io/filesystementry.php",
		"bitrix\\main\\io\\ifilestream" => "lib/io/ifilestream.php",
		"bitrix\\main\\localization\\loc" => "lib/localization/loc.php",
		"bitrix\\main\\mail\\mail" => "lib/mail/mail.php",
		"bitrix\\main\\mail\\tracking" => "lib/mail/tracking.php",
		"bitrix\\main\\mail\\eventmanager" => "lib/mail/eventmanager.php",
		"bitrix\\main\\mail\\eventmessagecompiler" => "lib/mail/eventmessagecompiler.php",
		"bitrix\\main\\mail\\eventmessagethemecompiler" => "lib/mail/eventmessagethemecompiler.php",
		"bitrix\\main\\mail\\internal\\event" => "lib/mail/internal/event.php",
		"bitrix\\main\\mail\\internal\\eventattachment" => "lib/mail/internal/eventattachment.php",
		"bitrix\\main\\mail\\internal\\eventmessage" => "lib/mail/internal/eventmessage.php",
		"bitrix\\main\\mail\\internal\\eventmessagesite" => "lib/mail/internal/eventmessagesite.php",
		"bitrix\\main\\mail\\internal\\eventmessageattachment" => "lib/mail/internal/eventmessageattachment.php",
		"bitrix\\main\\mail\\internal\\eventtype" => "lib/mail/internal/eventtype.php",
		"bitrix\\main\\text\\converter" => "lib/text/converter.php",
		"bitrix\\main\\text\\emptyconverter" => "lib/text/emptyconverter.php",
		"bitrix\\main\\text\\encoding" => "lib/text/encoding.php",
		"bitrix\\main\\text\\htmlconverter" => "lib/text/htmlconverter.php",
		"bitrix\\main\\text\\binarystring" => "lib/text/binarystring.php",
		"bitrix\\main\\text\\xmlconverter" => "lib/text/xmlconverter.php",
		"bitrix\\main\\type\\collection" => "lib/type/collection.php",
		"bitrix\\main\\type\\date" => "lib/type/date.php",
		"bitrix\\main\\type\\datetime" => "lib/type/datetime.php",
		"bitrix\\main\\type\\dictionary" => "lib/type/dictionary.php",
		"bitrix\\main\\type\\filterabledictionary" => "lib/type/filterabledictionary.php",
		"bitrix\\main\\type\\parameterdictionary" => "lib/type/parameterdictionary.php",
		"bitrix\\main\\web\\cookie" => "lib/web/cookie.php",
		"bitrix\\main\\web\\uri" => "lib/web/uri.php",
		"bitrix\\main\\sendereventhandler" => "lib/senderconnector.php",
		"bitrix\\main\\senderconnectoruser" => "lib/senderconnector.php",
		"bitrix\\main\\urlrewriterrulemaker" => "lib/urlrewriter.php",
		"bitrix\\main\\update\\stepper" => "lib/update/stepper.php",
		"CTimeZone" => "classes/general/time.php",
		"bitrix\\main\\composite\\abstractresponse" => "lib/composite/responder.php",
		"bitrix\\main\\composite\\fileresponse" => "lib/composite/responder.php",
		"bitrix\\main\\composite\\memcachedresponse" => "lib/composite/responder.php",
		"bitrix\\main\\security\\otpexception" => "lib/security/securityexception.php",
	)
);

// old class names compatibility
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/compatibility.php";

function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

define("START_EXEC_TIME", getmicrotime());
define("B_PROLOG_INCLUDED", true);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/version.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");

//TODO remove this
if(version_compare(PHP_VERSION, "5.0.0") >= 0 && @ini_get_bool("register_long_arrays") != true)
{
	$HTTP_POST_FILES  = $_FILES;
	$HTTP_SERVER_VARS = $_SERVER;
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
	$HTTP_COOKIE_VARS = $_COOKIE;
	$HTTP_ENV_VARS = $_ENV;
}

if(version_compare(PHP_VERSION, "5.4.0") < 0)
{
	UnQuoteAll();
}
FormDecode();

$application = \Bitrix\Main\HttpApplication::getInstance();
$application->initializeBasicKernel();

//Defined in dbconn.php
global $DBType, $DBDebug, $DBDebugToFile, $DBHost, $DBName, $DBLogin, $DBPassword;

//read database connection parameters
require_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

if(defined('BX_UTF'))
{
	define('BX_UTF_PCRE_MODIFIER', 'u');
}
else
{
	define('BX_UTF_PCRE_MODIFIER', '');
}

if(!defined("CACHED_b_lang")) define("CACHED_b_lang", 3600);
if(!defined("CACHED_b_option")) define("CACHED_b_option", 3600);
if(!defined("CACHED_b_lang_domain")) define("CACHED_b_lang_domain", 3600);
if(!defined("CACHED_b_site_template")) define("CACHED_b_site_template", 3600);
if(!defined("CACHED_b_event")) define("CACHED_b_event", 3600);
if(!defined("CACHED_b_agent")) define("CACHED_b_agent", 3660);
if(!defined("CACHED_menu")) define("CACHED_menu", 3600);
if(!defined("CACHED_b_file")) define("CACHED_b_file", false);
if(!defined("CACHED_b_file_bucket_size")) define("CACHED_b_file_bucket_size", 100);
if(!defined("CACHED_b_user_field")) define("CACHED_b_user_field", 3600);
if(!defined("CACHED_b_user_field_enum")) define("CACHED_b_user_field_enum", 3600);
if(!defined("CACHED_b_task")) define("CACHED_b_task", 3600);
if(!defined("CACHED_b_task_operation")) define("CACHED_b_task_operation", 3600);
if(!defined("CACHED_b_rating")) define("CACHED_b_rating", 3600);
if(!defined("CACHED_b_rating_vote")) define("CACHED_b_rating_vote", 86400);
if(!defined("CACHED_b_rating_bucket_size")) define("CACHED_b_rating_bucket_size", 100);
if(!defined("CACHED_b_user_access_check")) define("CACHED_b_user_access_check", 3600);
if(!defined("CACHED_b_user_counter")) define("CACHED_b_user_counter", 3600);
if(!defined("CACHED_b_group_subordinate")) define("CACHED_b_group_subordinate", 31536000);
if(!defined("CACHED_b_smile")) define("CACHED_b_smile", 31536000);
if(!defined("TAGGED_user_card_size")) define("TAGGED_user_card_size", 100);

//connect to database, from here global variable $DB is available (CDatabase class)
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/database.php");

$GLOBALS["DB"] = new CDatabase;
$GLOBALS["DB"]->debug = $DBDebug;
if ($DBDebugToFile)
{
	$GLOBALS["DB"]->DebugToFile = true;
	$application->getConnection()->startTracker()->startFileLog($_SERVER["DOCUMENT_ROOT"]."/".$DBType."_debug.sql");
}

//magic parameters: show sql queries statistics
$show_sql_stat = "";
if(array_key_exists("show_sql_stat", $_GET))
{
	$show_sql_stat = (strtoupper($_GET["show_sql_stat"]) == "Y"? "Y":"");
	setcookie("show_sql_stat", $show_sql_stat, false, "/");
}
elseif(array_key_exists("show_sql_stat", $_COOKIE))
{
	$show_sql_stat = $_COOKIE["show_sql_stat"];
}

if ($show_sql_stat == "Y")
{
	$GLOBALS["DB"]->ShowSqlStat = true;
	$application->getConnection()->startTracker();
}

if(!($GLOBALS["DB"]->Connect($DBHost, $DBName, $DBLogin, $DBPassword)))
{
	if(file_exists(($fname = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php")))
		include($fname);
	else
		include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/dbconn_error.php");
	die();
}

//licence key
$LICENSE_KEY = "";
if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/license_key.php")))
	include($_fname);
if($LICENSE_KEY == "" || strtoupper($LICENSE_KEY) == "DEMO")
	define("LICENSE_KEY", "DEMO");
else
	define("LICENSE_KEY", $LICENSE_KEY);

//language independed classes
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/punycode.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/charset_converter.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/main.php");	//main class
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/option.php");	//options and settings class
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache.php");	//various cache classes
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/module.php");

error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

if (file_exists(($fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php")))
{
	$US_HOST_PROCESS_MAIN = True;
	include($fname);
}
