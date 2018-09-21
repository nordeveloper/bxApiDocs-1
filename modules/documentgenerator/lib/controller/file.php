<?php

namespace Bitrix\DocumentGenerator\Controller;

use Bitrix\DocumentGenerator\Driver;
use Bitrix\DocumentGenerator\Model\FileTable;
use Bitrix\Main\UI\Uploader\Uploader;

class File extends Base
{
	const FILE_PARAM_NAME = 'file';

	protected $uploader;

	/**
	 * @return array|bool
	 */
	public function uploadAction()
	{
		return $this->getUploader()->checkPost();
	}

	/**
	 * @param $fileId
	 * @throws \Exception
	 */
	public function deleteAction($fileId)
	{
		$result = FileTable::delete($fileId);
		if(!$result->isSuccess())
		{
			$this->errorCollection = $result->getErrorCollection();
		}
	}

	/**
	 * @param $hash
	 * @param $file
	 * @param $package
	 * @param $upload
	 * @param $error
	 * @return bool
	 */
	public function uploadFile($hash, &$file, &$package, &$upload, &$error)
	{
		$uploadResult = FileTable::saveFile($file['files']['default']);
		if($uploadResult->isSuccess())
		{
			$file['FILE_ID'] = $uploadResult->getId();
			$file['name'] = GetFileNameWithoutExtension($file['name']);
			return true;
		}
		else
		{
			$this->errorCollection = $uploadResult->getErrorCollection();
		}
	}

	/**
	 * @return Uploader
	 */
	protected function getUploader()
	{
		if($this->uploader === null)
		{
			$this->uploader = new Uploader([
				"events" => [
					"onFileIsUploaded" => [$this, "uploadFile"],
				],
				"storage" => [
					"cloud" => true,
					"moduleId" => Driver::MODULE_ID,
				],
			]);
		}

		return $this->uploader;
	}
}