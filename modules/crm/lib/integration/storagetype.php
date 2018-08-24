<?php
namespace Bitrix\Crm\Integration;
class StorageType
{
	const Undefined = 0;
	const File = 1;
	const WebDav = 2;
	const Disk = 3;

	private static $defaultTypeID = null;

	public static function isDefined($typeID)
	{
		$typeID = (int)$typeID;
		return $typeID > self::Undefined && $typeID <= self::Disk;
	}
	public static function getDefaultTypeID()
	{
		if(self::$defaultTypeID === null)
		{
			if(IsModuleInstalled('disk') && \COption::GetOptionString('disk', 'successfully_converted', 'N') === 'Y')
			{
				self::$defaultTypeID = self::Disk;
			}
			elseif(IsModuleInstalled('webdav'))
			{
				self::$defaultTypeID = self::WebDav;
			}
			else
			{
				self::$defaultTypeID = self::File;
			}
		}
		return self::$defaultTypeID;
	}
}