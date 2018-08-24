<?
/**
 * This class is for private use only, it can be changed in any time, partially or entirely, so use it on your own risk.
 * However, we will provide backward compatibility as possible.
 *
 * @internal
 * @access private
 */

namespace Bitrix\Tasks\Internals\RunTime;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlExpression;

use Bitrix\Tasks\Internals\Task\MemberTable;
use Bitrix\Tasks\Internals\TaskTable;
use Bitrix\Tasks\Util\User;
use Bitrix\Tasks\Integration\SocialNetwork;
use Bitrix\Tasks\Integration\Intranet;
use Bitrix\Tasks\Internals\DataBase\Helper;

final class Task extends \Bitrix\Tasks\Internals\Runtime
{
	public static function getTask(array $parameters)
	{
		$parameters = static::checkParameters($parameters);
		$rf = $parameters['REF_FIELD'];

		return array(
			'runtime' => array(
				new Entity\ReferenceField(
					'TASK',
					TaskTable::getEntity(),
					array(
						'=this.'.((string) $rf != '' ? $rf : 'ID') => 'ref.ID',
					),
					array(
						'join_type' => array_key_exists('JOIN_TYPE', $parameters) ? $parameters['JOIN_TYPE'] : 'left'
					)
				)
			),
		);
	}

	/**
	 * Returns runtime fields to attach legacy task filter to the orm-query
	 *
	 * @param $parameters
	 * @return \Bitrix\Main\Entity\ReferenceField[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated Do not use legacy filter on ORM, performance is not good
	 */
	public static function getLegacyFilter(array $parameters)
	{
		$result = array('runtime' => array());

		$parameters = static::checkParameters($parameters);
		if(!is_array($parameters['FILTER_PARAMETERS']))
		{
			$parameters['FILTER_PARAMETERS'] = array();
		}
		$parameters['FILTER_PARAMETERS']['USER_ID'] = $parameters['USER_ID'];

		$selectSql = \CTasks::getSelectSqlByFilter($parameters['FILTER'], '', $parameters['FILTER_PARAMETERS']);

		$query = new \Bitrix\Main\Entity\Query('Bitrix\\Tasks\\Task');
		$query->setFilter(
			array(
				'@ID' => new SqlExpression($selectSql)
			)
		);
		$query->setSelect(array('ID'));

		$rf = $parameters['REF_FIELD'];
		$result['runtime'][] = new Entity\ReferenceField(
			$parameters['NAME'],
			\Bitrix\Main\Entity\Base::getInstanceByQuery($query),
			array(
				'=this.'.((string) $rf != '' ? $rf : 'ID') => 'ref.ID'
			),
			array('join_type' => 'inner')
		);

		return $result;
	}

	/**
	 * Returns runtime fields to attach rights checker
	 *
	 * @param $parameters
	 * @return mixed[]
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getAccessCheck(array $parameters)
	{
		$result = array();

		$parameters = static::checkParameters($parameters);

		if(!User::isAdmin($parameters['USER_ID']))
		{
			$query = static::getAccessCheckSql($parameters);

			$rtName = (string) $parameters['NAME'] != '' ? (string) $parameters['NAME'] : 'ACCESS';
			$rf = $parameters['REF_FIELD'];
			$rfName = ((string) $rf != '' ? $rf : 'ID');

			$sql = $query['sql'];

			// make virtual entity to be able to join it
			$entity = Entity\Base::compileEntity('TasksAccessCheck'.randString().'Table', array(
				new Entity\IntegerField('TASK_ID', array(
					'primary' => true
				))
			), array(
				'table_name' => '('.preg_replace('#/\*[^(/\*)(\*/)]*\*/#', '', $sql).')', // remove possible comments, orm does not like them
			));

			$result[] = new Entity\ReferenceField(
				$rtName,
				$entity,
				array(
					'=this.'.$rfName => 'ref.TASK_ID',
				),
				array('join_type' => 'inner')
			);
		}

		return array('runtime' => $result);
	}

	/**
	 * Returns a piece of sql that, being attached to a query, filters out inaccessible tasks
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getAccessCheckSql(array $parameters)
	{
		$parameters = static::checkParameters($parameters);

		$result = array(
			'sql' => '',
		);

		if(!User::isAdmin($parameters['USER_ID']))
		{
			$result['sql'] = static::getAccessibleTaskIdsSql($parameters);
		}

		return $result;
	}

	public static function getAccessibleTaskIdsSql(array $parameters)
	{
		$result = array();

		$parameters = static::checkParameters($parameters);
		$filter = static::getForwardedFilter($parameters['APPLY_FILTER'], $parameters);
		$memberCondition = static::getMemberConditions($parameters['APPLY_MEMBER_FILTER'], $parameters);

		// todo: where 1 = 0 here if $parameters['USER_ID'] is 0

		// if we have accessible groups
		$allowedGroups = static::getAllowedGroups($parameters);
		if(!empty($allowedGroups))
		{
			// then attach sql that returns tasks that belong to these groups
			$q = new Entity\Query(TaskTable::getEntity());
			$q->setSelect(array('TASK_ID' => 'ID'));

			// todo: possible bottleneck here, in case of having lots of groups. refactor it when group access check is available on sql
			$gaFilter = $filter;
			$gaFilter['GROUP_ID'] = $allowedGroups;
			$q->setFilter($gaFilter);
			$result[] = "\n/*tasks in accessible groups*/\n".$q->getQuery();
		}

		// if passed user is a director
		if(static::isDirector($parameters))
		{
			// then attach sql that returns tasks accessible by user`s employee
			$q = new Entity\Query(TaskTable::getEntity());
			$q->setSelect(array('TASK_ID' => 'ID'));
			$q->registerRuntimeField('', new Entity\ReferenceField(
				'TM',
				MemberTable::getEntity(),
				array(
					array(
						'=this.ID' => 'ref.TASK_ID',
					),
					$memberCondition,
				),
				array('join_type' => 'inner')
			));

			$subordinate = Intranet\Internals\Runtime\UserDepartment::getSubordinateFilter(array(
				'USER_ID' => $parameters['USER_ID'],
				'REF_FIELD' => 'this.TM'
			));
			$q->registerRuntimeField('', $subordinate['runtime'][0]);

			if(!empty($filter))
			{
				$q->setFilter($filter);
			}

			$result[] = "\n/*tasks accessible by my sub-employees*/\n".$q->getQuery();
		}

		// attach sql that returns tasks accessible by me directly
		$q = new Entity\Query(TaskTable::getEntity());
		$q->setSelect(array('TASK_ID' => 'ID'));
		$q->registerRuntimeField('', new Entity\ReferenceField(
			'TM',
			MemberTable::getEntity(),
			array(
				array(
					'=this.ID' => 'ref.TASK_ID',
					'=ref.USER_ID' => array('?', $parameters['USER_ID']),
				),
				$memberCondition,
			),
			array('join_type' => 'inner')
		));

		if(!empty($filter))
		{
			$q->setFilter($filter);
		}

		$result[] = "\n/*tasks accessible by me*/\n".$q->getQuery();

		if(count($result) == 1)
		{
			$result[] = "\n/*eliminate possible duplicates*/\nSELECT 0 as TASK_ID";
		}

		return "\n".implode("\n\nUNION\n\n", $result)."\n";
	}

	/**
	 * Returns runtime field that indicates whether task is expired or not
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getExpirationFlag(array $parameters)
	{
		$result = array();

		$parameters = static::checkParameters($parameters);
		$result[] = new Entity\ExpressionField(
			$parameters['NAME'],
			"CASE WHEN %s IS NOT NULL AND (%s < %s OR (%s IS NULL AND %s < ".$GLOBALS['DB']->currentTimeFunction().")) THEN 'Y' ELSE 'N' END",
			array('DEADLINE', 'DEADLINE', 'CLOSED_DATE', 'CLOSED_DATE', 'DEADLINE')
		);

		return array('runtime' => $result);
	}

	/**
	 * Returns runtime fields to attach legacy rights checker to the orm-query
	 *
	 * @param $parameters
	 * @return \Bitrix\Main\Entity\ReferenceField[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated Use static::getAccessCheck() instead, it is faster
	 */
	public static function getLegacyRightsChecker(array $parameters)
	{
		$result = array();

		$parameters = static::checkParameters($parameters);

		if (!\Bitrix\Tasks\Util\User::isSuper($parameters['USER_ID']))
		{
			list($conditions, $expression) = \CTasks::getPermissionFilterConditions($parameters, array('USE_PLACEHOLDERS' => true));

			$conditions = "(case when (".implode(' OR ', $conditions).") then '1' else '0' end)";
			array_unshift($expression, $conditions);

			$query = new \Bitrix\Main\Entity\Query('Bitrix\\Tasks\\Task');
			$query->registerRuntimeField('F', array(
				'data_type' => 'string',
				'expression' => $expression
			));
			$query->setFilter(array('=F' => '1'));
			$query->setSelect(array('TASK_ID' => 'ID'));

			//print_r($query->getQuery());

			$rf = $parameters['REF_FIELD'];
			$result[] = new Entity\ReferenceField(
				$parameters['NAME'],
				\Bitrix\Main\Entity\Base::getInstanceByQuery($query),
				array(
					'=this.'.((string) $rf != '' ? $rf : 'ID') => 'ref.TASK_ID'
				),
				array('join_type' => 'inner')
			);
		}

		return $result;
	}

	private static function getMemberConditions($filter, $parameters)
	{
		$conditions = array();
		$helper = \Bitrix\Main\HttpApplication::getConnection()->getSqlHelper();

		if(is_array($filter))
		{
			$filter = Helper\Common::parseFilter($filter);

			// todo: this will fail on sub-filters, LOGIC key
			foreach($filter as $info)
			{
				if((string) $info['FIELD'] === (string) '0')
				{
					$conditions[] = static::getMemberConditions($info['VALUE'], $parameters);
				}
				else
				{
					if(
						$info['FIELD'] != 'USER_ID' && $info['FIELD'] != 'TASK_ID' && $info['FIELD'] != 'TYPE'
					)
					{
						continue;
					}

					if(is_array($info['VALUE']) && !empty($info['VALUE']))
					{
						$key = ($info['NOT'] ? '!' : '').'@ref.'.$info['FIELD'];
						$value = new SqlExpression(implode(', ', array_map(function ($value) use($helper) {
							return $helper->forSql($value);
						}, $info['VALUE'])));
					}
					else
					{
						$key = \CAllSQLWhere::getOperationByCode($info['OPERATION']).'ref.'.$info['FIELD'];
						$value = array('?', $info['VALUE']);
					}

					$conditions[$key] = $value;
				}
			}
		}

		return $conditions;
	}

	private static function getForwardedFilter($filter, $parameters)
	{
		$forwardedFields = static::getForwardedFields();

		if(is_array($filter) && !empty($filter))
		{
			$filter = Helper\Common::parseFilter($filter);
			$result = array();
			foreach($filter as $info)
			{
				if($forwardedFields[$info['FIELD']])
				{
					// todo: we need translations date into DateTime here
					$result[$info['ORIG_KEY']] = $info['VALUE'];
				}
			}

			return $result;
		}

		return array();
	}

	private static function getForwardedFields()
	{
		static $fields = null;

		if($fields === null)
		{
			$map = TaskTable::getMap();
			foreach($map as $k => $v)
			{
				if($v instanceof Entity\ScalarField)
				{
					$fields[$v->getName()] = true;
				}
				else
				{
					$type = $v['data_type'];
					if($type == 'integer' || $type == 'string' || $type == 'boolean' || $type == 'datetime')
					{
						$fields[$k] = true;
					}
				}
			}
		}

		return $fields;
	}

	private static function getAllowedGroups(&$parameters)
	{
		if(!array_key_exists('ALLOWED_GROUPS', $parameters) && SocialNetwork::isInstalled())
		{
			$parameters['ALLOWED_GROUPS'] = SocialNetwork\Group::getIdsByAllowedAction('view_all', true, $parameters['USER_ID']);
		}

		return $parameters['ALLOWED_GROUPS'];
	}

	private static function isDirector(&$parameters)
	{
		if(!array_key_exists('IS_DIRECTOR', $parameters))
		{
			$parameters['IS_DIRECTOR'] = Intranet::isInstalled() && Intranet\User::isDirector($parameters['USER_ID']);
		}

		return $parameters['IS_DIRECTOR'];
	}
}