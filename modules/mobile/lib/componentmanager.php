<?

namespace Bitrix\Mobile;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization;
use Bitrix\Main\Web\Json;

class ComponentManager
{
	private static $componentPath = "/bitrix/components/bitrix/mobile.jscomponent/jscomponents/";
	private static $webComponentPath = "/bitrix/components/bitrix/mobile.webcomponent/webcomponents/";

	public static function getComponentVersion($componentName)
	{
		$componentFolder = new Directory(Application::getDocumentRoot() . self::$componentPath . $componentName);
		$versionFile = new File($componentFolder->getPath()."/version.php");
		$componentFile = new File($componentFolder->getPath()."/component.js");
		$componentPhpFile = new File($componentFolder->getPath()."/component.php");
		$version = 1;
		if($versionFile->isExists())
		{
			$versionDesc = include($versionFile->getPath());
			$version = $versionDesc["version"];
		}

		if($componentFile->isExists())
			$version .="_".$componentFile->getModificationTime();

		if($componentPhpFile->isExists())
			$version .="_".$componentPhpFile->getModificationTime();


		return $version;
	}

	public static function getComponentPath($componentName)
	{
		return "/mobile/mobile_component/$componentName/?version=". self::getComponentVersion($componentName);
	}

	public static function getWebComponentVersion($componentName)
	{
		$componentFolder = new Directory(Application::getDocumentRoot() . self::$webComponentPath . $componentName);
		$versionFile = new File($componentFolder->getPath()."/version.php");
		if($versionFile->isExists())
		{
			$versionDesc = include($versionFile->getPath());
			return $versionDesc["version"];
		}

		return 1;
	}

	public static function getWebComponentPath($componentName)
	{
		return "/mobile/web_mobile_component/$componentName/?version=". self::getWebComponentVersion($componentName);
	}

}