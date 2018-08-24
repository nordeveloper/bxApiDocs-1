<?php

namespace Bitrix\Disk\Controller;

use Bitrix\Disk;
use Bitrix\Disk\Internals\Engine;
use Bitrix\Disk\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;

final class TrashCan extends Engine\Controller
{
	public function emptyAction(Disk\Storage $storage)
	{
		$securityContext = $storage->getSecurityContext($this->getCurrentUser()->getId());
		if (!$storage->getRootObject()->canRead($securityContext))
		{
			$this->errorCollection[] = new Error(
				Loc::getMessage("DISK_CHECK_READ_PERMISSION_ERROR_MESSAGE")
			);

			return;
		}

		$indicator = new Disk\Volume\Storage\TrashCan();
		$indicator
			->setOwner($this->getCurrentUser()->getId())
			->addFilter('STORAGE_ID', $storage->getId())
			->purify()
			->measure([
				Disk\Volume\Base::DISK_FILE
		  	])
		;

		$task = $indicator->getMeasurementResult()->fetch();
		$taskId = $task['ID'];

		$agentParams = [
			'delay' => 5,
			'filterId' => $taskId,
			'ownerId' => $this->getCurrentUser()->getId(),
			'storageId' => $storage->getId(),
			Disk\Volume\Task::DROP_TRASHCAN => true,
		];

		Disk\Volume\Cleaner::addWorker($agentParams);
	}
}