<?php

namespace Bitrix\DocumentGenerator\Model;

use Bitrix\DocumentGenerator\DataProvider;
use Bitrix\DocumentGenerator\DataProviderManager;
use Bitrix\DocumentGenerator\Driver;
use Bitrix\DocumentGenerator\Integration\Bitrix24Manager;
use Bitrix\Main;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DocumentTable extends FileModel
{
	protected static $fileFieldNames = [
		'FILE_ID', 'IMAGE_ID', 'PDF_ID',
	];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_documentgenerator_document';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Main\Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new Main\Entity\StringField('TITLE', [
				'required' => true,
			]),
			new Main\Entity\StringField('NUMBER', [
				'required' => true,
			]),
			new Main\Entity\IntegerField('TEMPLATE_ID', [
				'required' => true,
			]),
			new Main\Entity\StringField('PROVIDER', [
				'validation' => function()
				{
					return [
						function($value)
						{
							if(DataProviderManager::checkProviderName($value) || empty($value))
							{
								return true;
							}
							else
							{
								return Loc::getMessage('DOCUMENTGENERATOR_MODEL_TEMPLATE_CLASS_VALIDATION', ['#CLASSNAME#' => $value, '#PARENT#' => DataProvider::class]);
							}
						},
					];
				},
			]),
			new Main\Entity\StringField('VALUE', [
				'required' => true,
			]),
			new Main\Entity\IntegerField('FILE_ID', [
				'required' => true,
			]),
			new Main\Entity\IntegerField('IMAGE_ID'),
			new Main\Entity\IntegerField('PDF_ID'),
			new Main\Entity\DatetimeField('CREATE_TIME', [
				'required' => true,
				'default_value' => function(){return new Main\Type\DateTime();},
			]),
			new Main\Entity\DatetimeField('UPDATE_TIME', [
				'required' => true,
				'default_value' => function(){return new Main\Type\DateTime();},
			]),
			new Main\Entity\TextField('VALUES', [
				'serialized' => true
			]),
			new Main\Entity\ReferenceField(
				'TEMPLATE',
				'\Bitrix\DocumentGenerator\Model\Template',
				['=this.TEMPLATE_ID' => 'ref.ID']
			),
		];
	}

	public static function onAfterDelete(Event $event)
	{
		$eventData = $event->getParameters();
		ExternalLinkTable::deleteByDocumentId($eventData['primary']['ID']);
		return parent::onAfterDelete($event);
	}

	public static function onAfterAdd(Event $event)
	{
		$eventData = $event->getParameters();
		if($eventData['fields']['ACTIVE'] === 'Y')
		{
			Bitrix24Manager::increaseDocumentsCount();
		}
	}
}