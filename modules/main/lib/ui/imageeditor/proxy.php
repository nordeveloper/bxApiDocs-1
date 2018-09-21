<?

namespace Bitrix\Main\UI\ImageEditor;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

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
		global $USER;

		if (!$USER->isAuthorized() ||
			!check_bitrix_sessid())
		{
			header('HTTP/1.0 404 Not Found');
			die('Not authorized');
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
			header('HTTP/1.0 404 Not Found');
			die('Host not allowed '.$this->uri->getUri());
		}
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
		return in_array($host, $this->getAllowedHosts());
	}


	public function output()
	{
		switch (Path::getExtension($this->uri->getUri()))
		{
			case 'gif':
				header('Content-Type: image/gif');
				break;
			case 'png':
				header('Content-Type: image/png');
				break;
			case 'jpg':
				header('Content-Type: image/jpeg');
				break;
			default:
				header('HTTP/1.0 404 Not Found');
				die('File not exists '.$this->uri->getUri());
				break;
		}

		if (!$this->uri->getHost())
		{
			$path = Path::convertRelativeToAbsolute($this->uri->getPath());
			$file = new File($path);
			$file->readFile();
		}
		else
		{
			readfile($this->uri->getUri());
		}
	}
}