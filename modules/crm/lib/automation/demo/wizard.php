<?php
namespace Bitrix\Crm\Automation\Demo;

use Bitrix\Main;
use Bitrix\Bizproc;
use Bitrix\Crm\Automation\Factory;
class Wizard
{
	public static function addAgent()
	{
		\CAgent::AddAgent('\Bitrix\Crm\Automation\Demo\Wizard::installOnAgent();', 'crm', 'N', 60);
		return true;
	}

	public static function installOnAgent()
	{
		static::installVersion(1);
		return '';
	}

	public static function installOnNewPortal()
	{
		static::installVersion(2);
		return true;
	}

	public static function installSimpleCRM()
	{
		if (!Factory::isAutomationAvailable(\CCrmOwnerType::Lead, true))
		{
			return false;
		}
		static::installAutomation(\CCrmOwnerType::Lead, 3);
		return true;
	}

	public static function unInstallSimpleCRM()
	{
		if (!Factory::isAutomationAvailable(\CCrmOwnerType::Lead, true))
		{
			return false;
		}
		static::unInstallAutomation(\CCrmOwnerType::Lead, 3);
		return true;
	}

	private static function installVersion($version)
	{
		$version = (int)$version;
		if ($version <= 0)
			$version = 1;

		if (static::isNeedleFor(\CCrmOwnerType::Lead))
		{
			static::installAutomation(\CCrmOwnerType::Lead, $version);
		}

		if (static::isNeedleFor(\CCrmOwnerType::Deal))
		{
			static::installAutomation(\CCrmOwnerType::Deal, $version);
		}
	}

	private static function installAutomation($entityTypeId, $version = 1)
	{
		$robotsRelation = static::getRobots($entityTypeId, $version);
		if ($robotsRelation)
		{
			foreach ($robotsRelation as $status => $robots)
			{
				static::addTemplate($entityTypeId, $status, $robots);
			}
		}
	}

	private static function unInstallAutomation($entityTypeId, $version = 1)
	{
		$robotsRelation = static::getRobots($entityTypeId, $version);
		if ($robotsRelation)
		{
			foreach (array_keys($robotsRelation) as $status)
			{
				static::addTemplate($entityTypeId, $status, array());
			}
		}
	}

	private static function addTemplate($entityTypeId, $entityStatus, $robots)
	{
		$documentType = \CCrmBizProcHelper::ResolveDocumentType($entityTypeId);
		$template = new Bizproc\Automation\Engine\Template($documentType, (string)$entityStatus);
		return $template->save($robots, 1); // USER_ID = 1, there is no other way to identify system import
	}

	private static function getRobots($entityTypeId, $version = 1)
	{
		if ($entityTypeId === \CCrmOwnerType::Lead)
			return static::loadFromFile('lead_'.$version);
		if ($entityTypeId === \CCrmOwnerType::Deal)
			return static::loadFromFile('deal_'.$version);

		return false;
	}

	private static function loadFromFile($filename)
	{
		$result = array();

		$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'robots' . DIRECTORY_SEPARATOR . $filename . '.php';
		$file = new Main\IO\File($filePath);
		if ($file->isExists() && $file->isReadable())
			$result = include($file->getPhysicalPath());

		return is_array($result) ? $result : false;
	}

	private static function isNeedleFor($entityTypeId)
	{
		//Check automation status
		if (!Factory::isAutomationAvailable($entityTypeId, true))
			return false;

		//Check bizproc autostart workflows
		if (\CCrmBizProcHelper::HasAutoWorkflows($entityTypeId,  \CCrmBizProcEventType::Create)
			|| \CCrmBizProcHelper::HasAutoWorkflows($entityTypeId,  \CCrmBizProcEventType::Edit)
		)
			return false;

		return (static::countTemplates($entityTypeId) === 0);
	}

	private static function countTemplates($entityTypeId)
	{
		$documentType = \CCrmBizProcHelper::ResolveDocumentType($entityTypeId);

		return (int)Bizproc\WorkflowTemplateTable::getCount(array(
			'=MODULE_ID' => $documentType[0],
			'=ENTITY' => $documentType[1],
			'=DOCUMENT_TYPE' => $documentType[2],
			'=AUTO_EXECUTE' => \CBPDocumentEventType::Automation
		));
	}
}