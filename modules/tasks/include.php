<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/lang.php");

// all common phrases place here
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$moduleRoot = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/tasks";

require_once($moduleRoot."/tools.php");
require_once($moduleRoot."/include/autoloader.php");
require_once($moduleRoot."/include/compatibility.php");
require_once($moduleRoot."/include/asset.php");