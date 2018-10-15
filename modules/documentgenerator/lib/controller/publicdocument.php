<?php

namespace Bitrix\DocumentGenerator\Controller;

use Bitrix\DocumentGenerator\Engine\CheckHash;

class PublicDocument extends Document
{
	/**
	 * @return array
	 */
	public function getDefaultPreFilters()
	{
		return [new CheckHash()];
	}

	public function getImageAction(\Bitrix\DocumentGenerator\Document $document, $hash = '')
	{
		return parent::getImageAction($document);
	}

	public function getFileAction(\Bitrix\DocumentGenerator\Document $document, $fileName = '', $hash = '')
	{
		return parent::getFileAction($document, $fileName);
	}

	public function getPdfAction(\Bitrix\DocumentGenerator\Document $document, $fileName = '', $hash = '')
	{
		return parent::getPdfAction($document, $fileName);
	}

	public function showPdfAction(\Bitrix\DocumentGenerator\Document $document, $print = 'y', $hash = '')
	{
		return parent::showPdfAction($document, $print);
	}
}