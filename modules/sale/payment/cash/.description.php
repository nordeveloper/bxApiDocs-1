<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/cash.php"));

$psTitle = GetMessage("SCSP_DTITLE");
$psDescription = GetMessage("SCSP_DDESCR");
$psDomain = \Bitrix\Sale\PaySystem\Manager::HANDLER_DOMAIN_NONE;

$arPSCorrespondence = array();

?>