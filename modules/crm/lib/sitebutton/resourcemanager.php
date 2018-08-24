<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Crm\SiteButton;

use \Bitrix\Main\Application;
use \Bitrix\Main\Context;
use Bitrix\Main\IO\File;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Web\Uri;

/**
 * Class ResourceManager
 * @package Bitrix\Crm\SiteButton
 */
class ResourceManager
{
	protected $errors = array();
	protected $files = array();
	protected $uploadedFiles = array();
	protected $uploadErrorFilesByAgent = false;
	protected static $instance = null;

	public static function getInstance()
	{
		if(static::$instance !== null)
		{
			return static::$instance;
		}

		return new static();
	}

	public static function removeFiles($files = array())
	{
		$manager = new static();
		foreach($files as $file)
		{
			$manager->removeFile($file);
		}
	}

	public static function uploadFiles($files = array())
	{
		$manager = new static();
		foreach($files as $file)
		{
			$manager->addFile($file);
		}
		$manager->upload();
		return !$manager->hasErrors();
	}

	public function addFile($file)
	{
		return $this->files[] = array(
			'name' => $file['name'],
			'type' => $file['type'],
			'path' => $file['path'],
			'contents' => $file['contents'],
			'provider_function' => $file['provider_function'],
			'provider_params' => $file['provider_params'],
			'provider_module_id' => $file['provider_module_id'],
		);
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return count($this->getErrors()) > 0;
	}

	protected static function removeCdnFile($fileName)
	{
		$cdnFiles = self::getCdnFiles();
		if (isset($cdnFiles[$fileName]))
		{
			unset($cdnFiles[$fileName]);
		}

		self::setCdnFiles($cdnFiles);
	}

	protected static function isUsedDirectFileWriting()
	{
		return !ModuleManager::isModuleInstalled('bitrix24');
	}

	protected static function setCdnFile($fileName, $cdnFileId)
	{
		$cdnFiles = self::getCdnFiles();
		$cdnFiles[$fileName] = $cdnFileId;
		self::setCdnFiles($cdnFiles);
	}

	protected static function setCdnFiles($cdnFiles = array())
	{
		Option::set('crm', 'button_cdn_resources', serialize($cdnFiles));
	}

	protected static function getCdnFile($fileName)
	{
		$cdnFiles = self::getCdnFiles();
		if($cdnFiles[$fileName])
		{
			return $cdnFiles[$fileName];
		}
		else
		{
			return null;
		}
	}

	protected static function getCdnFiles()
	{
		$cdnFiles = Option::get('crm', 'button_cdn_resources', '');
		if($cdnFiles)
		{
			$cdnFiles = unserialize($cdnFiles);
		}
		if (!is_array($cdnFiles))
		{
			$cdnFiles = array();
		}

		return $cdnFiles;
	}

	public static function getServerAddress()
	{
		$server = Context::getCurrent()->getServer();
		$url = $server->getHttpHost();

		$canSave = !empty($url);
		$isRestored = false;

		if (!$url)
		{
			$url = Option::get('crm', 'last_site_button_res_url', null);
			if ($url)
			{
				$isRestored = true;
			}
			else
			{
				$url = $server->getServerName();
			}
		}

		if (!$isRestored)
		{
			if (strpos($url, ':') === false && $server->getServerPort())
			{
				if (!in_array($server->getServerPort(), array('80', '443')))
				{
					$url .= ':' . $server->getServerPort();
				}
			}

			$url = (Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")
				. "://" . $url;
		}

		$uri = new Uri($url);
		$url = $uri->getLocator();
		if (substr($url, -1) == '/')
		{
			$url = substr($url, 0, -1);
		}

		if ($canSave)
		{
			Option::set('crm', 'last_site_button_res_url', $url);
		}

		return $url;
	}

	public static function getFileUrl($fileName)
	{
		$cdnFiles = static::getCdnFiles();
		if (!$cdnFiles[$fileName])
		{
			return false;
		}

		$url = '';
		if (self::isUsedDirectFileWriting())
		{
			if ($cdnFiles[$fileName])
			{
				return self::getServerAddress() . $cdnFiles[$fileName];
			}
		}
		else
		{
			$result = \CFile::GetByID($cdnFiles[$fileName]);
			if ($file = $result->Fetch())
			{
				$url = $file['~src'];
				if (!$url)
				{
					$url = self::getServerAddress() . '/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
				}
			}

			return $url;
		}
	}

	public static function uploadFileAgent($providerFunction, $providerParams, $providerModuleId)
	{
		if(!Loader::includeModule($providerModuleId))
		{
			return '';
		}

		$files = $providerFunction($providerParams);
		if(static::uploadFiles($files))
		{
			return '';
		}
		else
		{
			return self::getAgentName($providerFunction, $providerParams, $providerModuleId);
		}
	}

	protected static function getAgentName($providerFunction, $providerParams = array(), $providerModuleId = 'crm')
	{
		$params = var_export($providerParams, true);

		$agentName = $providerFunction . '(' . $params . ')';
		return '\\Bitrix\\Crm\\SiteButton\ResourceManager::uploadFileAgent(' . $agentName . ', "' . $providerModuleId . '");';
	}

	protected static function addAgent($providerFunction, $providerParams = array(), $providerModuleId = 'crm')
	{
		$agentName = self::getAgentName($providerFunction, $providerParams, $providerModuleId);
		\CAgent::AddAgent(
			$agentName,
			"crm", "N", 60, "", "Y",
			\ConvertTimeStamp(time()+\CTimeZone::GetOffset()+60, "FULL")
		);
	}

	protected static function removeFile($cdnFileArray)
	{
		$cdnFileId = self::getCdnFile($cdnFileArray['name']);
		if ($cdnFileId)
		{
			\CFile::Delete($cdnFileId);
		}

		self::removeCdnFile($cdnFileArray['name']);
	}

	protected static function saveFile($cdnFileArray)
	{
		$id = null;
		if (self::isUsedDirectFileWriting())
		{
			$path = '/' . Option::get("main", "upload_dir", "upload");
			$path .= '/crm/site_button/' . $cdnFileArray['name'];
			if (File::putFileContents(Application::getDocumentRoot() . $path, $cdnFileArray['content']))
			{
				$id = $path;
			}
		}
		else
		{
			$cdnFileId = self::getCdnFile($cdnFileArray['name']);
			if ($cdnFileId)
			{
				\CFile::Delete($cdnFileId);
			}

			$cdnFileArray["MODULE_ID"] = "crm";
			$id = \CFile::SaveFile($cdnFileArray, 'crm', false, false, 'site_button');
		}

		if ($id)
		{
			self::setCdnFile($cdnFileArray['name'], $id);

			return $id;
		}
		else
		{
			return false;
		}
	}

	protected function upload()
	{
		$errorFiles = array();
		$documentRoot = Application::getDocumentRoot();
		foreach ($this->files as $file)
		{
			if(!$file['contents'])
			{
				$cdnFileArray = \CFile::MakeFileArray($documentRoot . $file['path']);
			}
			else
			{
				$cdnFileArray = array(
					'name' => $file['name'],
					'content' => $file['contents']
				);
			}
			$cdnFileArray["name"] = $file['name'];
			$cdnFileArray["type"] = $file['type'];

			$id = self::saveFile($cdnFileArray);
			if (!$id)
			{
				$this->errors[] = $file['name'];
				$errorFiles[] = $file;
			}
		}

		if ($this->uploadErrorFilesByAgent && count($errorFiles) > 0)
		{
			foreach($errorFiles as $errorFile)
			{
				self::addAgent(
					$errorFile['provider_function'],
					$errorFile['provider_params'],
					$errorFile['provider_module_id']
				);
			}
		}

		return "";
	}
}