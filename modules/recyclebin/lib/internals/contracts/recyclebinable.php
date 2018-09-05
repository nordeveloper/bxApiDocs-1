<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 16.05.18
 * Time: 15:25
 */

namespace Bitrix\Recyclebin\Internals\Contracts;

use Bitrix\Recyclebin\Internals\Entity;

interface Recyclebinable
{
	//	public static function moveToRecyclebin(RecyclebinEntity $entity);

	/**
	 * @param Entity $entity
	 *
	 * @return boolean
	 */
	public static function moveFromRecyclebin(Entity $entity);

	/**
	 * @param Entity $entity
	 *
	 * @return boolean
	 */
	public static function removeFromRecyclebin(Entity $entity);

	/**
	 * @param Entity $entity
	 *
	 * @return boolean
	 */
	public static function previewFromRecyclebin(Entity $entity);


	/**
	 * @return array
	 *
	 * [
	 * 	'NOTIFY'=> [
	 * 		'RESTORE' => Loc::getMessage(''),
	 * 		'REMOVE' => Loc::getMessage(''),
	 * 	],
	 * 	'CONFIRM' => [
	 * 		'RESTORE' => Loc::getMessage(''),
	 * 		'REMOVE' => Loc::getMessage('')
	 * 	]
	 */
	public static function getNotifyMessages();
}