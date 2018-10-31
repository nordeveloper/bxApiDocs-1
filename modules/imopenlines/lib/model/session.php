<?php
namespace Bitrix\ImOpenLines\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

use Bitrix\Main\Entity;
use Bitrix\ImOpenLines\Integrations\Report\Statistics;
use Bitrix\Main\Entity\Event;

Loc::loadMessages(__FILE__);

/**
 * Class SessionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MODE string(255)  default 'input'
 * <li> SOURCE string(255) optional
 * <li> STATUS int optional
 * <li> CONFIG_ID int optional
 * <li> USER_ID int mandatory
 * <li> OPERATOR_ID int mandatory
 * <li> USER_CODE string(255) optional
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_COUNT int optional
 * <li> START_ID int mandatory
 * <li> END_ID int mandatory
 * <li> CRM bool optional default 'N'
 * <li> CRM_CREATE bool optional default 'N'
 * <li> CRM_ENTITY_TYPE string(50) optional
 * <li> CRM_ENTITY_ID int optional
 * <li> CRM_ACTIVITY_ID int optional
 * <li> CRM_DEAL_ID int optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_MODIFY datetime optional
 * <li> WAIT_ANSWER bool optional default 'Y'
 * <li> WAIT_ACTION bool optional default 'N'
 * <li> VOTE_ACTION bool optional default 'N'
 * <li> CLOSED bool optional default 'N'
 * <li> PAUSE bool optional default 'N'
 * <li> WORKTIME bool optional default 'Y'
 * <li> QUEUE_HISTORY string optional
 * <li> VOTE int optional
 * <li> VOTE_HEAD int optional
 * </ul>
 *
 * @package Bitrix\Imopenlines
 **/

class SessionTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_session';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_ID_FIELD'),
			),
			'MODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMode'),
				'title' => Loc::getMessage('SESSION_ENTITY_MODE_FIELD'),
				'default_value' => 'input',
			),
			'SOURCE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSource'),
				'title' => Loc::getMessage('SESSION_ENTITY_SOURCE_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'integer',
				'default_value' => '0',
			),
			'CONFIG_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_CONFIG_ID_FIELD'),
				'default_value' => '0',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_USER_ID_FIELD'),
				'default_value' => '0',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'OPERATOR_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_OPERATOR_ID_FIELD'),
				'default_value' => '0',
			),
			'OPERATOR' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.OPERATOR_ID' => 'ref.ID'),
			),
			'USER_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUserCode'),
				'title' => Loc::getMessage('SESSION_ENTITY_USER_CODE_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_CHAT_ID_FIELD'),
				'default_value' => '0',
			),
			'MESSAGE_COUNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_MESSAGE_FIELD_NEW'),
				'default_value' => '0',
			),
			'LIKE_COUNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_LIKE_COUNT_FIELD'),
				'default_value' => '0',
			),
			'START_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_START_ID_FIELD'),
				'default_value' => '0',
			),
			'END_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_END_ID_FIELD'),
				'default_value' => '0',
			),
			'CRM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_FIELD'),
				'default_value' => 'N',
			),
			'CRM_CREATE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_CREATE_FIELD'),
				'default_value' => 'N',
			),
			'CRM_ENTITY_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCrmEntityType'),
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_ENTITY_TYPE_FIELD'),
				'default_value' => 'NONE',
			),
			'CRM_ENTITY_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_ENTITY_ID_FIELD'),
				'default_value' => 0,
			),
			'CRM_ACTIVITY_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_ACTIVITY_ID_FIELD'),
				'default_value' => 0,
			),
			'CRM_DEAL_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_CRM_DEAL_ID_FIELD'),
				'default_value' => 0,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_CREATE_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'DATE_OPERATOR' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_OPERATOR_FIELD'),
			),
			'DATE_MODIFY' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_MODIFY_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'DATE_OPERATOR_ANSWER' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_OPERATOR_ANSWER_FIELD'),
			),
			'DATE_OPERATOR_CLOSE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_OPERATOR_CLOSE_FIELD'),
			),
			'DATE_CLOSE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_CLOSE_FIELD'),
			),
			'DATE_FIRST_ANSWER' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_FIRST_ANSWER_FIELD'),
			),
			'DATE_LAST_MESSAGE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_ENTITY_DATE_LAST_MESSAGE_FIELD'),
			),
			'TIME_BOT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_TIME_BOT_FIELD'),
				'default_value' => 0
			),
			'TIME_FIRST_ANSWER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_TIME_FIRST_ANSWER_FIELD'),
				'default_value' => 0
			),
			'TIME_ANSWER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_TIME_ANSWER_FIELD'),
				'default_value' => 0
			),
			'TIME_CLOSE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_TIME_CLOSE_FIELD'),
				'default_value' => 0
			),
			'TIME_DIALOG' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_TIME_DIALOG_FIELD'),
				'default_value' => 0
			),
			'WAIT_ACTION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_WAIT_ACTION_FIELD'),
				'default_value' => 'N',
			),
			'WAIT_VOTE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_WAIT_VOTE_FIELD'),
				'default_value' => 'N',
			),
			'WAIT_ANSWER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_WAIT_ANSWER_FIELD'),
				'default_value' => 'Y',
			),
			'CLOSED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_CLOSED_FIELD'),
				'default_value' => 'N',
			),
			'PAUSE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_PAUSE_FIELD'),
				'default_value' => 'N',
			),
			'SPAM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_SPAM_FIELD'),
				'default_value' => 'N',
			),
			'WORKTIME' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_WORKTIME_FIELD'),
				'default_value' => 'Y',
			),
			'SEND_NO_ANSWER_TEXT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SESSION_ENTITY_WORKTIME_FIELD'),
				'default_value' => 'N',
			),
			'QUEUE_HISTORY' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('SESSION_ENTITY_QUEUE_HISTORY_FIELD'),
				'default_value' => Array(),
				'serialized' => true
			),
			'VOTE' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_VOTE_FIELD'),
				'default_value' => '0',
			),
			'VOTE_HEAD' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SESSION_ENTITY_VOTE_HEAD_FIELD'),
				'default_value' => '0',
			),
			'CATEGORY_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SESSION_ENTITY_CATEGORY_ID_FIELD'),
				'default_value' => 0,
			),
			'EXTRA_REGISTER' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'EXTRA_USER_LEVEL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExtraUserLevel')
			),
			'EXTRA_TARIFF' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExtraTariff')
			),
			'EXTRA_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExtraUrl')
			),
			'SEND_FORM' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSendForm'),
				'default_value' => 'none',
			),
			'SEND_HISTORY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'INDEX' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\SessionIndex',
				'reference' => array('=this.ID' => 'ref.SESSION_ID'),
				'join_type' => 'INNER',
			),
			'CONFIG' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\Config',
				'reference' => array('=this.CONFIG_ID' => 'ref.ID'),
			),
			'CHAT' => array(
				'data_type' => 'Bitrix\Im\Model\Chat',
				'reference' => array('=this.CHAT_ID' => 'ref.ID'),
			),
			'CHECK' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\SessionCheck',
				'reference' => array('=this.ID' => 'ref.SESSION_ID'),
			),
			'LIVECHAT' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\Livechat',
				'reference' => array('=this.CONFIG_ID' => 'ref.CONFIG_ID'),
			),
			'IS_FIRST' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
		);
	}

	public static function getUfId()
	{
		return 'IMOPENLINES_SESSION';
	}

	/**
	 * Returns selection by entity's primary key without slow fields

	 * @param mixed $id Primary key of the entity
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getByIdPerformance($id)
	{
		return parent::getByPrimary($id, Array(
			'select' => self::getSelectFieldsPerformance()
		));
	}

	/**
	 * Returns fields for select without slow fields
	 *
	 * @param string $prefix
	 * @return array
	 */
	public static function getSelectFieldsPerformance($prefix = '')
	{
		$skipList = Array();

		$whiteList = Array();
		$map = self::getMap();

		foreach ($map as $key => $value)
		{
			if (in_array($key, $skipList) || isset($value['reference']))
			{
				continue;
			}
			$whiteList[] = $prefix? $prefix.'.'.$key: $key;
		}

		$ufData = \CUserTypeEntity::GetList(array(), array('ENTITY_ID' => self::getUfId()));

		while($ufResult = $ufData->Fetch())
		{
			$whiteList[] = $prefix? $prefix.'.'.$ufResult["FIELD_NAME"]: $ufResult["FIELD_NAME"];;
		}

		return $whiteList;
	}

	public static function onAfterAdd(Entity\Event $event)
	{
		$id = $event->getParameter("id");
		static::indexRecord($id);
		Statistics\EventHandler::onSessionCreate($event);
		return new Entity\EventResult();
	}

	public static function onBeforeUpdate(Event $event)
	{
		Statistics\EventHandler::onSessionBeforeUpdate($event);
	}

	public static function onAfterUpdate(Entity\Event $event)
	{
		$primary = $event->getParameter("id");
		$id = $primary["ID"];
		static::indexRecord($id);
		Statistics\EventHandler::onSessionUpdate($event);
		return new Entity\EventResult();
	}

	public static function indexRecord($id)
	{
		$id = (int)$id;
		if($id == 0)
			return;

		$select = self::getSelectFieldsPerformance();
		$select['CONFIG_LINE_NAME'] = 'CONFIG.LINE_NAME';

		$record = parent::getByPrimary($id, Array(
			'select' => $select
		))->fetch();
		if(!is_array($record))
			return;

		SessionIndexTable::merge(array(
			'SESSION_ID' => $id,
			'SEARCH_CONTENT' => self::generateSearchContent($record)
		));
	}

	/**
	 * @param array $fields Record as returned by getList
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function generateSearchContent(array $fields)
	{
		if($fields['CRM_ENTITY_TYPE'] != '' && $fields['CRM_ENTITY_ID'] > 0)
			$crmEntityCaption = \Bitrix\ImOpenLines\Crm::getEntityCaption($fields['CRM_ENTITY_TYPE'], $fields['CRM_ENTITY_ID']);
		else
			$crmEntityCaption = '';

		$userId = array();

		if($fields['CHAT_ID'] > 0 && $fields['CLOSED'] == 'Y' && \Bitrix\Main\Loader::includeModule('im'))
		{
			$userId[$fields['OPERATOR_ID']] = $fields['OPERATOR_ID'];
			$userId[$fields['USER_ID']] = $fields['USER_ID'];

			$transcriptLines = Array();
			$cursor = \Bitrix\Im\Model\MessageTable::getList(array(
				'select' => Array('MESSAGE', 'AUTHOR_ID'),
				'filter' => array(
					'=CHAT_ID' => $fields['CHAT_ID'],
					'>=ID' => $fields['START_ID'],
					'<=ID' => $fields['END_ID'],
				),
			));
			while ($row = $cursor->fetch())
			{
				if ($row['AUTHOR_ID'] == 0)
				{
					continue;
				}
				$userId[$row['AUTHOR_ID']] = $row['AUTHOR_ID'];
				$transcriptLines[] = $row['MESSAGE'];
			}

			$transcriptLines = implode(" ", $transcriptLines);
			$transcriptLines = \Bitrix\Im\Text::removeBbCodes($transcriptLines);
			if (strlen($transcriptLines) > 5000000)
			{
				$transcriptLines = substr($transcriptLines, 0, 5000000);
			}
		}
		else
		{
			$transcriptLines = "";
			$userId[$fields['OPERATOR_ID']] = $fields['OPERATOR_ID'];
			$userId[$fields['USER_ID']] = $fields['USER_ID'];
		}

		$result = \Bitrix\Main\Search\MapBuilder::create()
			->addUser($userId)
			->addText($crmEntityCaption)
			->addText($fields['EXTRA_URL'])
			->addInteger($fields['ID'])
			->addText('imol|'.$fields['ID'])
			->addText($transcriptLines)
			->build();

		return $result;
	}

	/**
	 * Returns validators for SOURCE field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateSource()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for SOURCE field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateMode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for USER_CODE field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateUserCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for EXTRA_TARIFF field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateExtraTariff()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for EXTRA_USER_LEVEL field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateExtraUserLevel()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for EXTRA_URL field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateExtraUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CRM_ENTITY_TYPE field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateCrmEntityType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for CRM_ENTITY_TYPE field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateSendForm()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Return current date for DATE_CREATE field.
	 */
	public static function getCurrentDate()
	{
		return new \Bitrix\Main\Type\DateTime();
	}
}