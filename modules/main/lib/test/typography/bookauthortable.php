<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Test\Typography;

use Bitrix\Main\Test\Typography\Book;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * @package    bitrix
 * @subpackage main
 */
class BookAuthorTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 1 AS BOOK_ID, 18 AS AUTHOR_ID)
			UNION
			(SELECT 2 AS BOOK_ID, 17 AS AUTHOR_ID)
			UNION
			(SELECT 2 AS BOOK_ID, 18 AS AUTHOR_ID)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('BOOK_ID'))
				->configurePrimary(true),

			(new Reference('BOOK', BookTable::class,
				Join::on('this.BOOK_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new IntegerField('AUTHOR_ID'))
				->configurePrimary(true),

			(new Reference('AUTHOR', AuthorTable::class,
				Join::on('this.AUTHOR_ID', 'ref.ID')))
				->configureJoinType('inner'),
		];
	}

}
