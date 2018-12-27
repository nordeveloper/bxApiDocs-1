<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Crm\Tracking;

use Bitrix\Main\Loader;
use Bitrix\Intranet;
use Bitrix\Landing;
use Bitrix\Crm\Communication;

/**
 * Class Provider
 *
 * @package Bitrix\Crm\Tracking
 */
class Provider
{
	/**
	 * Get channels.
	 *
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getChannels()
	{
		$list = [
			[
				'CODE' => Channel\Base::Site24,
				'ICON_CLASS' => 'ui-icon ui-icon-service-site-b24',
				'CONFIGURED' => true,
				'CONFIGURABLE' => false,
			],
			[
				'CODE' => Channel\Base::Shop24,
				'ICON_CLASS' => 'ui-icon ui-icon-service-estore',
				'CONFIGURED' => true,
				'CONFIGURABLE' => false,
			],
			[
				'CODE' => Channel\Base::Site,
				'ICON_CLASS' => 'ui-icon ui-icon-service-site',
				'CONFIGURABLE' => true,
				'CONFIGURED' => !empty(self::getReadySites()),
			],
			[
				'CODE' => Channel\Base::Call,
				'ICON_CLASS' => 'ui-icon ui-icon-service-call',
				'CONFIGURED' => self::hasSourcesWithFilledPool(Communication\Type::PHONE),
				'CONFIGURABLE' => true,
			],
			[
				'CODE' => Channel\Base::Mail,
				'ICON_CLASS' => 'ui-icon ui-icon-service-envelope',
				'CONFIGURED' => self::hasSourcesWithFilledPool(Communication\Type::EMAIL),
				'CONFIGURABLE' => true,
			],
		];

		foreach ($list as $index => $item)
		{
			$channel = Channel\Factory::create($item['CODE']);
			$item['NAME'] = $channel->getGridName();
			$list[$index] = $item;
		}

		if (!Loader::includeModule('intranet'))
		{
			return $list;
		}

		$existedCodes = array_column($list, 'CODE');
		$contactCenter = new Intranet\ContactCenter();
		$itemList = $contactCenter->getItems([
			'MODULES' => ['imopenlines', 'crm'],
			'ACTIVE' => 'Y', 'IS_LOAD_INNER_ITEMS' => 'N',
		]);
		foreach ($itemList as $moduleId => $items)
		{
			foreach ($items as $itemId => $item)
			{
				if (!$item['SELECTED'] || in_array($itemId, $existedCodes))
				{
					continue;
				}
				$list[] = [
					'CODE' => $itemId,
					'NAME' => $item['NAME'],
					'ICON_CLASS' => $item['LOGO_CLASS'],
					'CONFIGURED' => true,
					'CONFIGURABLE' => false,
				];
			}
		}

		return $list;
	}

	/**
	 * Get sources.
	 *
	 * @return array
	 */
	public static function getAvailableSources()
	{
		$adsSources = self::getStaticSources();
		$adsSources = array_combine(array_column($adsSources, 'CODE'), $adsSources);

		$list = self::getActualSources();
		foreach ($list as $index => $item)
		{
			if ($item['CODE'] && isset($adsSources[$item['CODE']]))
			{
				unset($adsSources[$item['CODE']]);
			}
		}

		foreach ($adsSources as $index => $item)
		{
			$list[] = $item + [
				'ID' => null,
				'UTM_SOURCE' => null,
				'CONFIGURED' => false,
				'ICON_COLOR' => '',
			];
		}

		usort($list, [__CLASS__, 'sortSourcesByCode']);

		return $list;
	}

	/**
	 * Get static sources.
	 *
	 * @return array
	 */
	public static function getStaticSources()
	{
		$list = [
			[
				'CODE' => 'google',
				'ICON_CLASS' => 'ui-icon ui-icon-service-google-ads',
			],
			[
				'CODE' => 'fb',
				'ICON_CLASS' => 'ui-icon ui-icon-service-fb',
			],
			[
				'CODE' => 'vk',
				'ICON_CLASS' => 'ui-icon ui-icon-service-vk',
			],
			[
				'CODE' => 'yandex',
				'ICON_CLASS' => 'ui-icon ui-icon-service-ya-direct',
			],
		];

		foreach ($list as $index => $item)
		{
			$item['NAME'] = Source\Base::getNameByCode($item['CODE']);
			$list[$index] = $item;
		}

		return $list;
	}

	/**
	 * Return true if it has sources with filled pool.
	 *
	 * @param int $typeId Type ID.
	 * @return bool
	 */
	protected static function hasSourcesWithFilledPool($typeId)
	{
		$typeName = Communication\Type::resolveName($typeId);
		foreach (self::getActualSources() as $source)
		{
			if (empty($source[$typeName]))
			{
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get actual sources.
	 *
	 * @return array
	 */
	public static function getActualSources()
	{
		$adsSources = self::getStaticSources();
		$adsSources = array_combine(array_column($adsSources, 'CODE'), $adsSources);

		$list = Internals\SourceTable::getList([
			'select' => ['ID', 'CODE', 'NAME', 'ICON_COLOR', 'UTM_SOURCE', 'EMAIL', 'PHONE'],
			'order' => ['ID' => 'ASC'],
			'cache' => ['ttl' => 3600]
		])->fetchAll();
		foreach ($list as $index => $item)
		{
			if ($item['CODE'] && isset($adsSources[$item['CODE']]))
			{
				$item = $adsSources[$item['CODE']] + $item;
			}

			$list[$index] = $item + [
				'DESCRIPTION' => Source\Base::getDescriptionByCode($item['CODE'], $item['NAME']),
				'ICON_CLASS' => 'ui-icon ui-icon-service-universal',
				'CONFIGURED' => !empty($item['UTM_SOURCE']),
			];
		}

		usort($list, [__CLASS__, 'sortSourcesByCode']);

		return $list;
	}

	/**
	 * Get ready sources.
	 *
	 * @return array
	 */
	public static function getReadySources()
	{
		$list = [];
		foreach (self::getActualSources() as $source)
		{
			if (empty($source['UTM_SOURCE']))
			{
				continue;
			}
			if (empty($source['EMAIL']) && empty($source['PHONE']))
			{
				continue;
			}

			$list[] = $source;
		}

		return $list;
	}

	/**
	 * Get ready sites.
	 *
	 * @return array
	 */
	public static function getReadySites()
	{
		return Internals\SiteTable::getList([
			'filter' => [
				'=IS_INSTALLED' => 'Y',
				'=ACTIVE' => 'Y'
			]
		])->fetchAll();
	}

	/**
	 * Sort sources.
	 *
	 * @param array $sourceA Source A.
	 * @param array $sourceB Source B.
	 * @return int
	 */
	public static function sortSourcesByCode(array $sourceA, array $sourceB)
	{
		$weights = array_flip(array_column(self::getStaticSources(), 'CODE'));
		$weightA = ($sourceA['CODE'] && isset($weights[$sourceA['CODE']])) ?
			$weights[$sourceA['CODE']]
			:
			100;
		$weightB = ($sourceB['CODE'] && isset($weights[$sourceB['CODE']])) ?
			$weights[$sourceB['CODE']]
			:
			100;

		return $weightA > $weightB ? 1 : 0;
	}

	/**
	 * Get b24 sites.
	 *
	 * @param bool $isStore Return b24 e-stores.
	 * @return array
	 */
	public static function getB24Sites($isStore = null)
	{
		if (!Loader::includeModule('landing'))
		{
			return [];
		}

		$filter = [
			'=ACTIVE' => 'Y'
		];
		if (is_bool($isStore))
		{
			$filter['=TYPE'] = $isStore ?  'STORE' : 'PAGE';
		}

		$list = Landing\Site::getList([
			'select' => [
				'ID', 'TITLE', 'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'DOMAIN_PROTOCOL' => 'DOMAIN.PROTOCOL'
			],
			'filter' => $filter
		])->fetchAll();

		$list = array_filter(
			$list,
			function ($item)
			{
				return !empty($item['DOMAIN_NAME']);
			}
		);
		sort($list);

		$disabledList = array_column(Internals\SiteB24Table::getList()->fetchAll(), 'LANDING_SITE_ID');
		foreach ($list as $index => $site)
		{
			$list[$index]['EXCLUDED'] = in_array($site['ID'], $disabledList);
		}

		return $list;
	}

	/**
	 * Get ready b24 site domains.
	 *
	 * @return array
	 */
	public static function getReadyB24SiteDomains()
	{
		return array_keys(self::getReadyB24SiteIds());
	}

	/**
	 * Get ready b24 site domains.
	 *
	 * @return array
	 */
	public static function getReadyB24SiteIds()
	{
		$result = [];
		foreach (self::getB24Sites() as $site)
		{
			if ($site['EXCLUDED'])
			{
				continue;
			}

			$host = strtolower(trim($site['DOMAIN_NAME']));
			$result[$host] = $site['ID'];
		}

		return $result;
	}
}