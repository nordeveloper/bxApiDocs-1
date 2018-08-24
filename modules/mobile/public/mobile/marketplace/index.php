<?
use Bitrix\Main\Page\Asset;

define("SKIP_MOBILEAPP_INIT", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
Asset::getInstance()->addString(Bitrix\MobileApp\Mobile::getInstance()->getViewPort());
?>


<?$APPLICATION->IncludeComponent(
	"bitrix:app.layout",
	".default",
	array(
		"ID" => $_GET["id"],
		"COMPONENT_TEMPLATE" => ".default",
		"MOBILE"=>"Y"
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>