<?

namespace Bitrix\Main\UI\Viewer;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use Bitrix\Main\FileTable;
use Bitrix\Main\Type\DateTime;

final class FilePreviewTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_file_preview';
	}

	/**
	 * Returns entity map definition.
	 * To get initialized fields @see \Bitrix\Main\Entity\Base::getFields() and \Bitrix\Main\Entity\Base::getField()
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new Entity\IntegerField('FILE_ID', [
				'required' => true,
			]),
			new Entity\IntegerField('PREVIEW_ID'),
			new Entity\IntegerField('PREVIEW_IMAGE_ID'),
			new Entity\DatetimeField('CREATED_AT', [
				'default_value' => function () {
					return new DateTime();
				},
			]),
			new Entity\DatetimeField('TOUCHED_AT'),
			new Entity\ReferenceField('FILE',FileTable::class,
				['=this.FILE_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			new Entity\ReferenceField('PREVIEW',FileTable::class,
				['=this.PREVIEW_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			new Entity\ReferenceField('PREVIEW_IMAGE',FileTable::class,
				['=this.PREVIEW_IMAGE_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}
}