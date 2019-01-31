<?php
namespace Bitrix\Tasks\Rest\Controllers\Task;

use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Tasks\Exception;
use Bitrix\Tasks\Rest\Controllers\Base;
use Bitrix\Forum\MessageTable;

/**
 * Class Search
 * @package Bitrix\Tasks\Rest\Controllers\Task
 */
class Search extends Base
{
	/**
	 * BX.ajax.runAction("tasks.task.search.comment", {data: {search: "text"}});
	 *
	 * @param $search
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function commentAction($search)
	{
		$items = [];

		if (!Loader::includeModule('forum'))
		{
			return $items;
		}

		$userId = $this->getCurrentUser()->getId();

		$defaultPathTemplate = '/company/personal/user/#user_id#/tasks/task/view/#task_id#/';
		try
		{
			$pathTemplate = \CTasksTools::GetOptionPathTaskUserEntry(SITE_ID, $defaultPathTemplate);
		}
		catch (Exception $exception)
		{
			$pathTemplate = $defaultPathTemplate;
		}

		$filter = [
			'POST_MESSAGE' => $search,
			'NEW_TOPIC' => 'N'
		];
		$params = [
			'SEARCH_MODE' => 'Y',
			'USER_ID' => $userId,
			'FORCE_SELECT' => [
				'FM.ID AS MESSAGE_ID',
				'FM.POST_MESSAGE AS MESSAGE_TEXT',
				'T.ID AS TASK_ID',
				'T.TITLE AS TASK_TITLE'
			],
			'MAKE_ACCESS_FILTER' => true,
			'ACCESS_FILTER_RUNTIME_OPTIONS' => [
				'FIELDS' => [
					[
						'NAME' => 'FM',
						'FIELD' => new Entity\ReferenceField(
							'FM',
							MessageTable::getEntity(),
							['=this.FORUM_TOPIC_ID' => 'ref.TOPIC_ID'],
							['JOIN_TYPE' => 'INNER']
						)
					]
				],
				'FILTERS' => [
					Entity\Query::filter()->where('ZOMBIE', 'N'),
					Entity\Query::filter()->where('FM.NEW_TOPIC', 'N'),
					Entity\Query::filter()->whereMatch('FM.POST_MESSAGE', '(+' . $search . '*)')
				]
			]
		];

		$messagesDbResult = \CForumMessage::GetList([], $filter, false, 0, $params);
		while ($message = $messagesDbResult->Fetch())
		{
			$items[] = [
				'module' => 'tasks',
				'entityType' => 'TASKS',
				'entityId' => $message['TASK_ID'],
				'title' => $message['TASK_TITLE'],
				'subtitle' => $message['MESSAGE_TEXT'],
				'showUrl' => \CComponentEngine::MakePathFromTemplate($pathTemplate, ['user_id' => $userId, 'task_id' => $message['TASK_ID']]),
			];
		}

		return $items;
	}
}