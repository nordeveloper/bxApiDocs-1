<?php

namespace Bitrix\Crm\Integration\Report\Filter;

use Bitrix\Main\Loader;
use Bitrix\Report\VisualConstructor\Helper\Filter;

class Base extends Filter
{
	public function getJsList()
	{
		return [

			'/bitrix/js/crm/crm.js',
			'/bitrix/js/crm/common.js',
			'/bitrix/js/crm/interface_grid.js'
		];
	}

	public function getCssList()
	{
		return [
			'/bitrix/js/crm/css/crm.css'
		];
	}

	public static function getFieldsList()
	{
		Loader::includeModule('socialnetwork');
		\CJSCore::init(array('socnetlogdest'));
		return parent::getFieldsList();
	}

}