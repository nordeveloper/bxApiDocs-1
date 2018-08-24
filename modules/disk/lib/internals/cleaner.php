<?php

namespace Bitrix\Disk\Internals;


use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\Rights\Table\RightSetupSessionTable;
use Bitrix\Disk\ObjectTtl;
use Bitrix\Disk\ShowSession;
use Bitrix\Disk\SystemUser;
use Bitrix\Main\Application;
use Bitrix\Main\FileTable as MainFileTable;
use Bitrix\Main\Entity\Query;

final class Cleaner
{
	const DELETE_TYPE_PORTION = 2;
	const DELETE_TYPE_TIME    = 3;

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * Deletes show session and connected files from cloud.
	 *
	 * @param int $type Deleting type. You can choose delete files by portion or by time.
	 * @param int $limit Limit which will be used for deleting files by portion or by time.
	 * So, count of files which we want to delete or maximum duration of the removal process.
	 * @return string
	 */
	public static function deleteShowSession($type = self::DELETE_TYPE_PORTION, $limit = 10)
	{
		$portion = $limit;
		if($type === self::DELETE_TYPE_TIME)
		{
			$portion = 100;
		}

		$startTime = time();
		foreach(ShowSession::getModelList(array(
			'filter' => array(
				'=IS_EXPIRED' => true,
			),
			'limit' => $portion,
		)) as $showSession)
		{
			if($type === self::DELETE_TYPE_TIME && (time() - $startTime > $limit))
			{
				break;
			}
			$showSession->delete();
		}
		unset($showSession);


		return static::className() . "::deleteShowSession({$type}, {$limit});";
	}

	/**
	 * Deletes unnecessary files, which don't relate to version or object.
	 *
	 * @param int $type Deleting type. You can choose delete files by portion or by time.
	 * @param int $limit Limit which will be used for deleting files by portion or by time.
	 * So, count of files which we want to delete or maximum duration of the removal process.
	 * @return string
	 */
	public static function deleteUnnecessaryFiles($type = self::DELETE_TYPE_PORTION, $limit = 10)
	{
		$portion = $limit;
		if($type === self::DELETE_TYPE_TIME)
		{
			$portion = 100;
		}

		$query = new Query(MainFileTable::getEntity());
		$query
			->addSelect('ID')
			->addFilter('=EXTERNAL_ID', 'unnecessary')
			->addFilter('=MODULE_ID', Driver::INTERNAL_MODULE_ID)
			->setLimit($portion)
		;

		$workLoad = false;
		$dbResult = $query->exec();
		$startTime = time();
		while($row = $dbResult->fetch())
		{
			$workLoad = true;
			if($type === self::DELETE_TYPE_TIME && (time() - $startTime > $limit))
			{
				break;
			}
			\CFile::delete($row['ID']);
		}

		if(!$workLoad)
		{
			return '';
		}

		return static::className() . "::deleteUnnecessaryFiles({$type}, {$limit});";
	}

	/**
	 * Deletes files which have to die by ttl.
	 *
	 * @param int $type Deleting type. You can choose delete files by portion or by time.
	 * @param int $limit Limit which will be used for deleting files by portion or by time.
	 * So, count of files which we want to delete or maximum duration of the removal process.
	 * @return string
	 */
	public static function deleteByTtl($type = self::DELETE_TYPE_PORTION, $limit = 10)
	{
		$portion = $limit;
		if($type === self::DELETE_TYPE_TIME)
		{
			$portion = 100;
		}

		/** @var ObjectTtl[] $ttls */
		$ttls = ObjectTtl::getModelList(array(
			'with' => array('object'),
			'limit' => $portion,
			'order' => array('DEATH_TIME' => 'ASC')
		));

		$workLoad = false;
		$startTime = time();
		foreach ($ttls as $ttl)
		{
			$workLoad = true;
			if($type === self::DELETE_TYPE_TIME && (time() - $startTime > $limit))
			{
				break;
			}

			if ($ttl->isReadyToDeath())
			{
				$ttl->deleteObject(SystemUser::SYSTEM_USER_ID);
				$ttl->delete(SystemUser::SYSTEM_USER_ID);
			}
		}

		if(!$workLoad)
		{
			return '';
		}

		return static::className() . "::deleteByTtl({$type}, {$limit});";
	}

	/**
	 * Deletes old right setup sessions.
	 *
	 * @return string
	 */
	public static function deleteRightSetupSession()
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$tableName = RightSetupSessionTable::getTableName();
		$statusFinished = RightSetupSessionTable::STATUS_FINISHED;

		$deathTime = $sqlHelper->addSecondsToDateTime(60*60*24*31, 'CREATE_TIME');

		$connection->queryExecute("
			  DELETE FROM {$tableName} WHERE STATUS = {$statusFinished} AND NOW() > {$deathTime}
		");

		return static::className() . "::deleteRightSetupSession();";
	}
}