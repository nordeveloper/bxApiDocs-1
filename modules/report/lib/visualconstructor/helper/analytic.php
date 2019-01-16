<?php
namespace Bitrix\Report\VisualConstructor\Helper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Class Analytic
 */
class Analytic
{
	/**
	 * @TODO maybe need to add some logic of access for different analytic pages
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function isEnable()
	{
		Loader::includeModule('crm');
		return Option::get("report", '~analytics_enabled', 'N') === 'Y' && \CCrmPerms::IsAccessEnabled();
	}
}