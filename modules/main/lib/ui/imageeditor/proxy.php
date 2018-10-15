<?

namespace Bitrix\Main\UI\ImageEditor;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\HttpClient;

/**
 * Class Proxy
 * @package Bitrix\Main\UI\ImageEditor
 */
class Proxy
{
	/** @var Uri */
	protected $uri;

	/** @var array<string> */
	protected $allowedHosts = [];

	/**
	 * Proxy constructor.
	 * @param string $url
	 * @param array<string> $allowedHosts
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($url, $allowedHosts = [])
	{
		$response = static::getResponse();

		if (!static::isAuthorized())
		{
			$response->setStatus(401)->flush();
			die('Unauthorized');
		}

		if (is_array($allowedHosts))
		{
			$this->allowedHosts = array_filter($allowedHosts, function($item) {
				return is_string($item) && !empty($item);
			});
		}

		$this->uri = new Uri(rawurldecode($url));
		$host = $this->uri->getHost();

		if (!!$host && !$this->isAllowedHost($host))
		{
			$response->setStatus(400)->flush();
			die('Host not allowed');
		}

		$ext = Path::getExtension($this->uri->getPath());

		if (!static::isAllowedExtension($ext))
		{
			$response->setStatus(400)->flush();
			die('File extension not allowed');
		}
	}

	/**
	 * @return \Bitrix\Main\HttpResponse
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getResponse()
	{
		return Application::getInstance()->getContext()->getResponse();
	}

	/**
	 * @return null|string
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getDocumentRoot()
	{
		return Application::getInstance()->getContext()->getServer()->getDocumentRoot();
	}

	/**
	 * @return bool
	 */
	protected static function isAuthorized()
	{
		global $USER;
		return ($USER->isAuthorized() && check_bitrix_sessid());
	}

	/**
	 * Gets current http host
	 * @return null|string
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getCurrentHttpHost()
	{
		static $server = null;

		if ($server === null)
		{
			$server = Application::getInstance()->getContext()->getServer();
		}

		return explode(':', $server->getHttpHost())[0];
	}

	/**
	 * Gets allowed hosts
	 * @return array<string>
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getAllowedHosts()
	{
		return array_merge(
			[$this->getCurrentHttpHost()],
			$this->allowedHosts
		);
	}

	/**
	 * Checks that this host is allowed
	 * @param string $host
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function isAllowedHost($host)
	{
		return (
			in_array($host, $this->getAllowedHosts()) ||
			in_array('*', $this->getAllowedHosts())
		);
	}


	/**
	 * @param ?string $ext
	 * @return bool
	 */
	protected static function isAllowedExtension($ext)
	{
		return (
			$ext !== false && (
				strpos($ext, 'gif') !== false ||
				strpos($ext, 'png') !== false ||
				strpos($ext, 'jpg') !== false ||
				strpos($ext, 'jpeg') !== false
			)
		);
	}

	/**
	 * @return bool
	 */
	protected function isLocalFile()
	{
		return !$this->uri->getHost();
	}

	/**
	 * @return bool|string
	 */
	protected function getContentType()
	{
		$ext = Path::getExtension($this->uri->getPath());

		if (strpos($ext, 'gif') !== false)
		{
			return 'image/gif';
		}

		if (strpos($ext, 'png') !== false)
		{
			return 'image/png';
		}

		if (strpos($ext, 'jpg') !== false ||
			strpos($ext, 'jpeg') !== false)
		{
			return 'image/jpeg';
		}

		return false;
	}

	/**
	 * @param $path
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function isAllowedPath($path)
	{
		$documentRoot = static::getDocumentRoot();
		return stripos($path, $documentRoot) === 0;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function output()
	{
		$response = static::getResponse();

		if ($this->isLocalFile())
		{
			$path = Path::convertRelativeToAbsolute($this->uri->getPath());

			if (static::isAllowedPath($path))
			{
				$file = new File($path);

				if ($file->isExists())
				{
					$response->addHeader('Content-Type', $file->getContentType());
					$response->flush($file->getContents());
					return;
				}
			}

			$response->setStatus(404);
			$response->flush('404 Not found');
			return;
		}

		$client = new HttpClient();
		$fileName = Path::getName($this->uri->getPath());

		if ($fileName)
		{
			$filePath = \CFile::getTempName(false, $fileName);

			if ($client->download($this->uri->getUri(), $filePath))
			{
				$file = new File($filePath);

				if ($file->isExists())
				{
					$response->addHeader('Content-Type', $file->getContentType());
					$response->flush($file->getContents());
					$file->delete();
					return;
				}
			}
		}

		$response->setStatus(404);
		$response->flush('404 Not found');
		return;
	}
}