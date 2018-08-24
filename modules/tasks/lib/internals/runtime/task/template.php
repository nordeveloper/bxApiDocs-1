<?
/**
 * @internal
 * @access private
 */

namespace Bitrix\Tasks\Internals\RunTime\Task;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlExpression;

use Bitrix\Main\TaskOperationTable;
use Bitrix\Tasks\Internals\Task\Template\AccessTable;
use Bitrix\Tasks\Util\User;
use Bitrix\Tasks\Integration\SocialNetwork;

final class Template extends \Bitrix\Tasks\Internals\Runtime
{
	/**
	 * Returns runtime field that is, being attached to an ORM query, leaves visible only items with certain operations allowed
	 *
	 * @param array $parameters
	 *  <li>OPERATION_NAME string[]|string
	 *  <li>OPERATION_ID int[]|int
	 *  <li>USER_ID int
	 * @return array
	 */
	public static function getAccessCheck(array $parameters)
	{
		$result = array();

		$parameters = static::checkParameters($parameters);

		// in socnet super-admin mode we can see all templates, but in other case...
		if(!($parameters['USER_ID'] == User::getId() && SocialNetwork\User::isAdmin()))
		{
			$query = static::getAccessCheckSql($parameters);

			$rtName = (string) $parameters['NAME'] != '' ? (string) $parameters['NAME'] : 'ACCESS';
			$rf = $parameters['REF_FIELD'];
			$rfName = ((string) $rf != '' ? $rf : 'ID');

			$sql = $query['sql'];

			// make virtual entity to be able to join it
			$entity = Entity\Base::compileEntity('TasksAccessCheck'.randString().'Table', array(
				new Entity\IntegerField('TEMPLATE_ID', array(
					'primary' => true
				))
			), array(
				'table_name' => '('.preg_replace('#/\*[^(/\*)(\*/)]*\*/#', '', $sql).')', // remove possible comments, orm does not like them
			));

			$result[] = new Entity\ReferenceField(
				$rtName,
				$entity,
				array(
					'=this.'.$rfName => 'ref.TEMPLATE_ID',
				),
				array('join_type' => 'inner')
			);
		}

		return array('runtime' => $result);
	}

	/**
	 * Returns sql that is, being attached to a select query, leaves visible only items with certain operations allowed
	 *
	 * @param array $parameters
	 *  <li>OPERATION_NAME string[]|string
	 *  <li>OPERATION_ID int[]|int
	 *  <li>USER_ID int
	 * @return array
	 */
	public static function getAccessCheckSql(array $parameters)
	{
		$parameters = static::checkParameters($parameters);

		// update b_user_access for the chosen user
		$acc = new \CAccess();
		$acc->updateCodes(array(
			'USER_ID' => $parameters['USER_ID'],
		));

		$q = new Entity\Query(AccessTable::getEntity());
		$q->setSelect(array('TEMPLATE_ID' => 'ENTITY_ID'));
		$q->registerRuntimeField('', new Entity\ReferenceField(
			'T2OP',
			TaskOperationTable::getEntity(),
			array(
				'=this.TASK_ID' => 'ref.TASK_ID',
			) + static::getOperationCondition($parameters),
			array('join_type' => 'inner')
		));
		$q->setFilter(array(
			// todo: currently we have only user-based access, but if you want to set access rights to some
			// todo: socnet group (for example), you will have to join b_user_access here
			'=GROUP_CODE' => 'U'.$parameters['USER_ID'],
		));
		$q->setGroup(array('ENTITY_ID')); // to avoid duplicates

		return array(
			'sql' => $q->getQuery(),
		);
	}

	private static function getOperationCondition(array $parameters)
	{
		$result = array();

		if(array_key_exists('OPERATION_NAME', $parameters))
		{
			$names = $parameters['OPERATION_NAME'];
			if(!is_array($names))
			{
				$names = trim((string) $names);
				if($names !== '')
				{
					$names = array($names);
				}
			}
			$parameters['OPERATION_ID'] = User::mapAccessOperationNames('TASK_TEMPLATE', $names);
		}

		if(array_key_exists('OPERATION_ID', $parameters))
		{
			if(is_array($parameters['OPERATION_ID']))
			{
				$parameters['OPERATION_ID'] = array_unique(array_map('intval', $parameters['OPERATION_ID']));
				if(count($parameters['OPERATION_ID']))
				{
					$result = array(
						'@ref.OPERATION_ID' => new SqlExpression(implode(', ', $parameters['OPERATION_ID']))
					);
				}
			}
			else
			{
				$operation = intval($parameters['OPERATION_ID']);
				if($operation)
				{
					$result = array(
						'=ref.OPERATION_ID' => array('?', $parameters['OPERATION_ID']),
					);
				}
			}
		}

		return $result;
	}
}