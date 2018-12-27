<?php
namespace Bitrix\ImConnector\Connectors;

use \Bitrix\Main\UserTable,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\ImConnector\Status,
	\Bitrix\ImConnector\Output,
	\Bitrix\ImConnector\Library,
	\Bitrix\ImConnector\Connector,
	\Bitrix\ImOpenLines\Model\SessionTable;

Loc::loadMessages(__FILE__);

class Instagram
{
	public static function sendMessageProcessing($value, $connector)
	{
		if(($connector == Library::ID_INSTAGRAM_CONNECTOR || $connector == Library::ID_FBINSTAGRAM_CONNECTOR) && !Library::isEmpty($value['message']['text']))
		{
			$usersTitle = array();

			preg_match_all("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", $value['message']['text'], $users);
			if(!empty($users))
			{
				$filterUser = array(
					'LOGIC' => 'OR'
				);
				foreach ($users[1] as $user)
					$filterUser[] = array('=ID' => $user);

				$rawUsers = UserTable::getList(
					array(
						'select' => array(
							'ID',
							'TITLE',
							'NAME'
						),
						'filter' => $filterUser
					)
				);

				while ($rowUser = $rawUsers->fetch())
				{
					if(!Library::isEmpty($rowUser['TITLE']))
						$usersTitle[$rowUser['ID']] = $rowUser['TITLE'];
					elseif(!Library::isEmpty($rowUser['NAME'])) //case for new fb instagram connector
						$usersTitle[$rowUser['ID']] = $rowUser['NAME'];
				}

				if(!empty($usersTitle))
				{
					$search = array();
					$replace = array();

					foreach ($users[1] as $cell=>$user)
					{
						if(!Library::isEmpty($usersTitle[$user]))
						{
							$search[] = $users[0][$cell];
							$replace[] = '@' . $usersTitle[$user];
						}
					}

					if(!empty($search) && !empty($replace))
						$value['message']['text'] = str_replace($search, $replace, $value['message']['text']);
				}
			}
		}

		return $value;
	}

	/**
	 * Agent
	 *
	 * @return string
	 */
	public static function initializeReceiveMessages()
	{
		if(Loader::includeModule('imconnector') && defined('\Bitrix\ImConnector\Library::ID_INSTAGRAM_CONNECTOR') && Connector::isConnector(Library::ID_INSTAGRAM_CONNECTOR, true))
		{
			$statuses = Status::getInstanceAllLine(Library::ID_INSTAGRAM_CONNECTOR);

			if(!empty($statuses))
			{
				foreach ($statuses as $line=>$status)
				{
					if($status->isStatus())
					{
						$connectorOutput = new Output(Library::ID_INSTAGRAM_CONNECTOR, $line);

						$connectorOutput->initializeReceiveMessages($status->getData());
					}
				}
			}
		}

		return '\Bitrix\ImConnector\Connectors\Instagram::initializeReceiveMessages();';
	}

	public static function newMediaProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;

			if(!empty($message['chat']['id']))
			{
				if(is_object($message['message']['date']))
					$datetime = $message['message']['date']->getTimestamp();
				else
					$datetime = $message['message']['date'];

				if(empty($data[$message['chat']['id']]))
					$data[$message['chat']['id']] = array(
						'datetime' => $datetime,
						'comments' => array()
					);
				else
					$data[$message['chat']['id']]['datetime'] = $datetime;

				if(count($data)>Library::INSTAGRAM_MAX_COUNT)
				{
					uasort(
						$data,
						function ($a, $b)
						{
							if ($a['datetime'] == $b['datetime'])
								return 0;
							return ($a['datetime'] > $b['datetime']) ? -1 : 1;
						}
					);

					$data = array_slice($data, 0, Library::INSTAGRAM_MAX_COUNT, true);
				}
			}

			if(!empty($data) && $dataOld!==$data)
			{
				$status->setData($data);
				Status::save();
			}
		}
	}

	public static function newCommentProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;

			if(!empty($message['chat']['id']))
			{
				if(empty($data[$message['chat']['id']]['comments']) || !in_array($message['message']['id'], $data[$message['chat']['id']]['comments']))
				{
					$data[$message['chat']['id']]['comments'][] = $message['message']['id'];
				}
			}

			if(!empty($data) && $dataOld!==$data)
			{
				$status->setData($data);
				Status::save();
			}
		}
	}

	public static function newCommentDeliveryProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;

			if(!empty($message['chat']['id']))
			{
				if(empty($data[$message['chat']['id']]['comments']) || !in_array($message['message']['id'], $data[$message['chat']['id']]['comments']))
				{
					foreach ($message['message']['id'] as $messageId)
						$data[$message['chat']['id']]['comments'][] = $messageId;
				}
			}

			if(!empty($data) && $dataOld!==$data)
			{
				$status->setData($data);
				Status::save();
			}
		}
	}

	/**
	 * Agent for movement from old instagram connector to new
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function initPrepareActionsForNewConnector()
	{
		Loader::includeModule('imopenlines');
		$configManager = new \Bitrix\ImOpenLines\Config();
		$configList = $configManager->getList(array());
		$instagramConnected = false;

		foreach ($configList as $config)
		{
			$connectorList = Connector::getListConnectedConnector($config['ID']);
			$instagramConnected = array_key_exists(Library::ID_INSTAGRAM_CONNECTOR, $connectorList);

			if ($instagramConnected)
				break;
		}

		if ($instagramConnected)
		{
			self::sendNewConnectorInfoMessage();
		}
		else
		{
			self::disableOldConnector();
		}
	}

	/**
	 * Send info messages about new connector for all users, who should to know about it
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function sendNewConnectorInfoMessage()
	{
		Loader::includeModule('imopenlines');
		$mailingUsers = array();
		$operators = SessionTable::getList(
			array(
				'select' => array('OPERATOR_ID'),
				'group' => array('OPERATOR_ID')
			)
		);
		while ($operator = $operators->fetch())
			$mailingUsers[] = $operator['OPERATOR_ID'];

		$groups = array("13"); //bitrix24 admin group
		if (!Loader::includeModule('bitrix24'))
			$groups[] = "1";

		$admins = array();
		$by = 'ID';
		$order = 'ASC';
		$users = \CUser::GetList($by, $order, array('GROUPS_ID' => $groups), array('SELECT'=>array('ID')));

		while ($user = $users->Fetch())
			$admins[] = $user['ID'];

		$mailingUsers = array_unique(array_merge($mailingUsers, $admins));

		foreach ($mailingUsers as $user)
		{
			self::sendInfoNotify($user);
		}
	}

	/**
	 * Send notify message for user
	 *
	 * @param $userId
	 *
	 * @return bool|int
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function sendInfoNotify($userId)
	{
		Loader::includeModule('im');
		Loader::includeModule('imopenlines');

		$conenctorSettingUrl =  \Bitrix\ImOpenLines\Common::getPublicFolder() . "connector/?ID=fbinstagram";
		$notifyFields = array(
			"TO_USER_ID" => $userId,
			"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
			"NOTIFY_MODULE" => "imconnector",
			"NOTIFY_EVENT" => "default",
			"NOTIFY_TAG" => "CONNECTOR|FBINSTAGRAM|".$userId."|NOTIFICATION",
			"NOTIFY_MESSAGE" => Loc::getMessage(
				"CONNECTORS_INSTAGRAM_NEW_CONNECTOR_NOTIFY_MESSAGE",
				array('#CONNECTOR_URL#' => $conenctorSettingUrl)
			),
			"NOTIFY_MESSAGE_OUT" => Loc::getMessage(
				"CONNECTORS_INSTAGRAM_NEW_CONNECTOR_NOTIFY_MESSAGE_OUT",
				array('#CONNECTOR_URL#' => $conenctorSettingUrl)
			),
			"RECENT_ADD" => "Y"
		);

		return \CIMNotify::Add($notifyFields);
	}

	/**
	 * Disable old instagram connector from options
	 *
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected static function disableOldConnector()
	{
		$connectors = Connector::getListConnectorActive();

		if($key = array_search(Library::ID_INSTAGRAM_CONNECTOR, $connectors))
		{
			unset($connectors[$key]);
			\Bitrix\Main\Config\Option::set('imconnector', 'list_connector', implode(",", $connectors));
		}
	}
}