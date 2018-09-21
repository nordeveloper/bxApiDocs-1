<?php

namespace Bitrix\Disk\Controller;

use Bitrix\Disk;
use Bitrix\Disk\Internals\Engine;

final class Storage extends Engine\Controller
{
	public function isEnabledSizeLimitRestrictionAction(Disk\Storage $storage)
	{
		if ($storage->isEnabledSizeLimitRestriction())
		{
			return [
				'isEnabledSizeLimitRestriction' => $storage->isEnabledSizeLimitRestriction(),
				'sizeLimitRestriction' => $storage->getSizeLimit(),
			] ;
		}

		return [
			'isEnabledSizeLimitRestriction' => false,
		];
	}
}