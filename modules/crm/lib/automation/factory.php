<?php
namespace Bitrix\Crm\Automation;

use Bitrix\Bitrix24\Feature;
use Bitrix\Bizproc;
use Bitrix\Crm\Automation\Target;
use Bitrix\Crm\Automation\Trigger\BaseTrigger;
use Bitrix\Main\Loader;
use Bitrix\Main\NotSupportedException;
use Bitrix\Crm\Settings\LeadSettings;
use Bitrix\Crm\Activity\AutocompleteRule;
use Bitrix\Crm\ActivityTable;

class Factory
{
	private static $supportedEntityTypes = array(
		\CCrmOwnerType::Lead,
		\CCrmOwnerType::Deal,
		\CCrmOwnerType::Order,
		//\CCrmOwnerType::Invoice,
	);

	private static $triggerRegistry;
	private static $featuresCache = array();

	private static $targets = [];

	private static $newActivities = [];

	public static function isAutomationAvailable($entityTypeId, $ignoreLicense = false)
	{
		if (!Helper::isBizprocEnabled() || !static::isSupported($entityTypeId))
			return false;

		if (!$ignoreLicense && Loader::includeModule('bitrix24'))
		{
			$feature = 'crm_automation_'.strtolower(\CCrmOwnerType::ResolveName($entityTypeId));

			if (!isset(static::$featuresCache[$feature]))
			{
				static::$featuresCache[$feature] = Feature::isFeatureEnabled($feature);
			}

			return static::$featuresCache[$feature];
		}

		return true;
	}

	public static function canUseBizprocDesigner()
	{
		if (Loader::includeModule('bitrix24'))
		{
			$feature = 'crm_automation_designer';
			if (!isset(static::$featuresCache[$feature]))
			{
				static::$featuresCache[$feature] = Feature::isFeatureEnabled($feature);
			}

			return static::$featuresCache[$feature];
		}

		return true;
	}

	public static function canUseAutomation()
	{
		foreach (static::$supportedEntityTypes as $entityTypeId)
		{
			if (static::isAutomationAvailable($entityTypeId))
				return true;
		}
		return false;
	}

	public static function isSupported($entityTypeId)
	{
		return in_array((int)$entityTypeId, static::$supportedEntityTypes, true);
	}

	public static function runOnAdd($entityTypeId, $entityId)
	{
		//We need to ignore license restrictions in Simple CRM mode for Leads
		$ignoreLicense = false;
		if ($entityTypeId === \CCrmOwnerType::Lead && !LeadSettings::isEnabled())
		{
			$ignoreLicense = true;
		}

		if (empty($entityId) || !static::isAutomationAvailable($entityTypeId, $ignoreLicense))
			return;

		$automationTarget = static::getTarget($entityTypeId, $entityId);
		$automationTarget->getRuntime()->onDocumentAdd();
	}

	public static function runOnStatusChanged($entityTypeId, $entityId)
	{
		if (empty($entityId) || !static::isAutomationAvailable($entityTypeId))
			return;

		static::doAutocompleteActivities($entityTypeId, $entityId);

		$automationTarget = static::getTarget($entityTypeId, $entityId);

		//refresh target entity fields
		$automationTarget->setEntityById($entityId);

		$automationTarget->getRuntime()->onDocumentStatusChanged();
	}

	/**
	 * Create Target instance by entity type.
	 * @param int $entityTypeId Entity type id from \CCrmOwnerType.
	 * @return Target\BaseTarget Target instance, child of BaseTarget.
	 * @throws NotSupportedException
	 */
	public static function createTarget($entityTypeId)
	{
		$entityTypeId = (int)$entityTypeId;

		if ($entityTypeId === \CCrmOwnerType::Deal)
		{
			return new Target\DealTarget();
		}
		elseif ($entityTypeId === \CCrmOwnerType::Lead)
		{
			return new Target\LeadTarget();
		}
		elseif ($entityTypeId === \CCrmOwnerType::Order)
		{
			return new Target\OrderTarget();
		}
		elseif ($entityTypeId === \CCrmOwnerType::Invoice)
		{
			return new Target\InvoiceTarget();
		}
		else
		{
			$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeId);
			throw new NotSupportedException("Entity '{$entityTypeName}' not supported in current context.");
		}
	}

	public static function getTarget($entityTypeId, $entityId)
	{
		if (isset(self::$targets[$entityTypeId]) && isset(self::$targets[$entityTypeId][$entityId]))
		{
			return self::$targets[$entityTypeId][$entityId];
		}
		$target = self::createTarget($entityTypeId);
		$target->setEntityById($entityId);

		self::setTarget($target);

		return $target;
	}

	private static function setTarget(Target\BaseTarget $target)
	{
		if (!isset(self::$targets[$target->getEntityTypeId()]))
		{
			self::$targets[$target->getEntityTypeId()] = [];
		}
		self::$targets[$target->getEntityTypeId()][$target->getEntityId()] = $target;
	}

	/**
	 * Create Runtime instance.
	 * @return Engine\Runtime Runtime instance.
	 * @deprecated
	 * @see Bizproc\Automation\Engine\Runtime
	 */
	public static function createRuntime()
	{
		return new Engine\Runtime();
	}

	/**
	 * @return Trigger\BaseTrigger[] Registered triggers array.
	 */
	private static function getTriggerRegistry()
	{
		if (self::$triggerRegistry === null)
		{
			self::$triggerRegistry = [];
			foreach ([
					Trigger\EmailTrigger::className(),
					Trigger\EmailReadTrigger::className(),
					Trigger\EmailLinkTrigger::className(),
					Trigger\CallTrigger::className(),
					Trigger\MissedCallTrigger::className(),
					Trigger\WebFormTrigger::className(),
					Trigger\InvoiceTrigger::className(),
					Trigger\PaymentTrigger::className(),
					Trigger\AllowDeliveryTrigger::className(),
					Trigger\DeductedTrigger::className(),
					Trigger\OrderCanceledTrigger::className(),
					Trigger\WebHookTrigger::className(),
					Trigger\VisitTrigger::className(),
					Trigger\GuestReturnTrigger::className(),
					Trigger\OpenLineTrigger::className(),
					Trigger\AppTrigger::className()
				 ]
				 as $triggerClass
			)
			{
				if ($triggerClass::isEnabled())
				{
					self::$triggerRegistry[] = $triggerClass;
				}
			}
		}

		return self::$triggerRegistry;
	}

	/**
	 * @param int $entityTypeId Entity type id.
	 * @return array
	 */
	public static function getAvailableTriggers($entityTypeId)
	{
		$entityTypeId = (int)$entityTypeId;
		$description = array();
		/**
		 * @var BaseTrigger $triggerClass
		 */
		foreach (self::getTriggerRegistry() as $triggerClass)
		{
			if ($triggerClass::isSupported($entityTypeId))
			{
				$description[] = $triggerClass::toArray();
			}
		}

		return $description;
	}

	/**
	 * @param $code Trigger string code.
	 * @return bool|Trigger\BaseTrigger Trigger class name or false.
	 */
	public static function getTriggerByCode($code)
	{
		$code = (string)$code;

		foreach (self::getTriggerRegistry() as $triggerClass)
		{
			if ($triggerClass::getCode() === $code)
			{
				return $triggerClass::className();
			}
		}

		return false;
	}

	/**
	 * @param int $entityTypeId Entity type id.
	 * @param string $entityStatus Entity status for check.
	 * @return bool
	 */
	public static function hasRobotsForStatus($entityTypeId, $entityStatus)
	{
		if (!Helper::isBizprocEnabled() || !static::isSupported($entityTypeId))
			return false;

		$documentType = \CCrmBizProcHelper::ResolveDocumentType($entityTypeId);

		$template = new Bizproc\Automation\Engine\Template($documentType, $entityStatus);

		return ($template->getId() > 0 && ($template->isExternalModified() || count($template->getRobots()) > 0));
	}

	public static function registerActivity($id)
	{
		static::$newActivities[$id] = true;
	}

	private static function doAutocompleteActivities($entityTypeId, $entityId)
	{
		$result = ActivityTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=COMPLETED' => 'N',
				'=AUTOCOMPLETE_RULE' => AutocompleteRule::AUTOMATION_ON_STATUS_CHANGED,
				'=BINDINGS.OWNER_TYPE_ID' => $entityTypeId,
				'=BINDINGS.OWNER_ID' => $entityId,
			),
			'order' => array('ID' => 'ASC')
		));

		while ($row = $result->fetch())
		{
			if (!isset(static::$newActivities[$row['ID']]))
			{
				\CCrmActivity::SetAutoCompleted($row['ID']);
			}
			else
			{
				unset(static::$newActivities[$row['ID']]);
			}
		}
	}
}