<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LiveChatManager
{
	private $error = null;
	private $id = null;

	const TEMPLATE_COLOR = 'color';
	const TEMPLATE_COLORLESS = 'colorless';

	const TYPE_WIDGET = 'widget';
	const TYPE_BUTTON = 'button';

	static $availableCount = null;

	public function __construct($configId)
	{
		$this->id = intval($configId);
		$this->config = false;
		$this->error = new Error(null, '', '');

		\Bitrix\Main\Loader::includeModule("im");
	}

	public function add($fields = Array())
	{
		$configData = Model\LivechatTable::getById($this->id)->fetch();
		if ($configData)
		{
			$this->id = $configData['CONFIG_ID'];
			$this->config = false;

			return true;
		}

		$add['CONFIG_ID'] = $this->id;

		if (isset($fields['ENABLE_PUBLIC_LINK']))
		{
			$specifiedName = true;
			if (!isset($fields['URL_CODE_PUBLIC']))
			{
				$configManager = new \Bitrix\ImOpenLines\Config();
				$config = $configManager->get($this->id);
				$fields['URL_CODE_PUBLIC'] = $config['LINE_NAME'];
				$specifiedName = false;
			}

			$add['URL_CODE_PUBLIC'] = self::prepareAlias($fields['URL_CODE_PUBLIC']);
			$add['URL_CODE_PUBLIC_ID'] = \Bitrix\Im\Alias::add(Array(
				'ALIAS' => $add['URL_CODE_PUBLIC'],
				'ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE,
				'ENTITY_ID' => $this->id
			));

			if (!$add['URL_CODE_PUBLIC_ID'])
			{
				if ($specifiedName)
				{
					$this->error = new Error(__METHOD__, 'CODE_ERROR', Loc::getMessage('IMOL_LCM_CODE_ERROR'));
					return false;
				}
				else
				{
					$result = \Bitrix\Im\Alias::addUnique(Array(
						'ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE,
						'ENTITY_ID' => $this->id
					));
					$add['URL_CODE_PUBLIC'] = $result['ALIAS'];
					$add['URL_CODE_PUBLIC_ID'] = $result['ID'];
				}
			}
		}

		$result = \Bitrix\Im\Alias::addUnique(Array(
			'ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE,
			'ENTITY_ID' => $this->id
		));
		$add['URL_CODE'] = $result['ALIAS'];
		$add['URL_CODE_ID'] = $result['ID'];

		if (isset($fields['TEMPLATE_ID']) && in_array($fields['TEMPLATE_ID'], Array(self::TEMPLATE_COLOR, self::TEMPLATE_COLORLESS)))
		{
			$add['TEMPLATE_ID'] = $fields['TEMPLATE_ID'];
		}
		if (isset($fields['BACKGROUND_IMAGE']))
		{
			$add['BACKGROUND_IMAGE'] = intval($fields['BACKGROUND_IMAGE']);
		}
		if (isset($fields['CSS_ACTIVE']))
		{
			$add['CSS_ACTIVE'] = $fields['CSS_ACTIVE'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CSS_PATH']))
		{
			$add['CSS_PATH'] = substr($fields['CSS_PATH'], 0, 255);
		}
		if (isset($fields['CSS_TEXT']))
		{
			$add['CSS_TEXT'] = $fields['CSS_TEXT'];
		}
		if (isset($fields['COPYRIGHT_REMOVED']) && Limit::canRemoveCopyright())
		{
			$add['COPYRIGHT_REMOVED'] = $fields['COPYRIGHT_REMOVED'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CACHE_WIDGET_ID']))
		{
			$add['CACHE_WIDGET_ID'] = intval($fields['CACHE_WIDGET_ID']);
		}
		if (isset($fields['CACHE_BUTTON_ID']))
		{
			$add['CACHE_BUTTON_ID'] = intval($fields['CACHE_BUTTON_ID']);
		}
		if (isset($fields['PHONE_CODE']))
		{
			$add['PHONE_CODE'] = $fields['PHONE_CODE'];
		}

		$result = Model\LivechatTable::add($add);
		if ($result->isSuccess())
		{
			$this->id = $result->getId();
			$this->config = false;
		}

		return $result->isSuccess();
	}

	public function update($fields)
	{
		$prevConfig = $this->get();

		$update = Array();
		if (isset($fields['URL_CODE_PUBLIC']))
		{
			$fields['URL_CODE_PUBLIC'] = trim($fields['URL_CODE_PUBLIC']);
			if (empty($fields['URL_CODE_PUBLIC']))
			{
				if ($prevConfig['URL_CODE_PUBLIC_ID'] > 0)
				{
					\Bitrix\Im\Alias::delete($prevConfig['URL_CODE_PUBLIC_ID']);
				}
				$update['URL_CODE_PUBLIC'] = '';
				$update['URL_CODE_PUBLIC_ID'] = 0;
			}
			else
			{
				$fields['URL_CODE_PUBLIC'] = self::prepareAlias($fields['URL_CODE_PUBLIC']);
				if ($prevConfig['URL_CODE_PUBLIC_ID'] > 0)
				{
					if ($prevConfig['URL_CODE_PUBLIC'] != $fields['URL_CODE_PUBLIC'])
					{
						$result = \Bitrix\Im\Alias::update($prevConfig['URL_CODE_PUBLIC_ID'], Array('ALIAS' => $fields['URL_CODE_PUBLIC']));
						if ($result)
						{
							$update['URL_CODE_PUBLIC'] = $fields['URL_CODE_PUBLIC'];
						}
					}
				}
				else
				{
					$fields['URL_CODE_PUBLIC_ID'] = \Bitrix\Im\Alias::add(Array(
						'ALIAS' => $fields['URL_CODE_PUBLIC'],
						'ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE,
						'ENTITY_ID' => $this->id
					));
					if ($fields['URL_CODE_PUBLIC_ID'])
					{
						$update['URL_CODE_PUBLIC'] = $fields['URL_CODE_PUBLIC'];
						$update['URL_CODE_PUBLIC_ID'] = $fields['URL_CODE_PUBLIC_ID'];
					}
				}
			}
		}

		if (isset($fields['TEMPLATE_ID']) && in_array($fields['TEMPLATE_ID'], Array(self::TEMPLATE_COLOR, self::TEMPLATE_COLORLESS)))
		{
			$update['TEMPLATE_ID'] = $fields['TEMPLATE_ID'];
		}
		if (isset($fields['CSS_ACTIVE']))
		{
			$update['CSS_ACTIVE'] = $fields['CSS_ACTIVE'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['BACKGROUND_IMAGE']))
		{
			$update['BACKGROUND_IMAGE'] = intval($fields['BACKGROUND_IMAGE']);
		}
		if (isset($fields['CSS_PATH']))
		{
			$update['CSS_PATH'] = substr($fields['CSS_PATH'], 0, 255);
		}
		if (isset($fields['CSS_TEXT']))
		{
			$update['CSS_TEXT'] = $fields['CSS_TEXT'];
		}
		if (isset($fields['COPYRIGHT_REMOVED']) && Limit::canRemoveCopyright())
		{
			$update['COPYRIGHT_REMOVED'] = $fields['COPYRIGHT_REMOVED'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CACHE_WIDGET_ID']))
		{
			$update['CACHE_WIDGET_ID'] = intval($fields['CACHE_WIDGET_ID']);
		}
		if (isset($fields['CACHE_BUTTON_ID']))
		{
			$update['CACHE_BUTTON_ID'] = intval($fields['CACHE_BUTTON_ID']);
		}
		if (isset($fields['PHONE_CODE']))
		{
			$update['PHONE_CODE'] = $fields['PHONE_CODE'];
		}

		$result = Model\LivechatTable::update($this->id, $update);
		if ($result->isSuccess() && $this->config)
		{
			foreach ($update as $key => $value)
			{
				$this->config[$key] = $value;
			}
		}

		return $result->isSuccess();
	}

	public function delete()
	{
		$prevConfig = $this->get();

		if ($prevConfig['URL_CODE_PUBLIC_ID'])
		{
			\Bitrix\Im\Alias::delete($prevConfig['URL_CODE_PUBLIC_ID']);
		}
		if ($prevConfig['URL_CODE_ID'])
		{
			\Bitrix\Im\Alias::delete($prevConfig['URL_CODE_ID']);
		}

		if ($prevConfig['CACHE_WIDGET_ID'])
		{
			\CFile::Delete($prevConfig['CACHE_WIDGET_ID']);
		}
		if ($prevConfig['CACHE_BUTTON_ID'])
		{
			\CFile::Delete($prevConfig['CACHE_BUTTON_ID']);
		}

		Model\LivechatTable::delete($this->id);
		$this->config = false;

		return true;
	}

	public static function prepareAlias($alias)
	{
		if (!\Bitrix\Main\Loader::includeModule("im"))
			return false;

		$alias = \CUtil::translit($alias, LANGUAGE_ID, array(
			"max_len"=>255,
			"safe_chars"=>".",
			"replace_space" => '-',
			"replace_other" => '-'
		));

		return \Bitrix\Im\Alias::prepareAlias($alias);
	}

	public function checkAvailableName($alias)
	{
		if (!\Bitrix\Main\Loader::includeModule("im"))
			return false;

		$alias = self::prepareAlias($alias);
		$orm = \Bitrix\Im\Model\AliasTable::getList(Array(
			'filter' => Array(
				'=ALIAS' => $alias,
				'=ENTITY_TYPE' => \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE,
				'!=ENTITY_ID' => $this->id
			)
		));

		return $orm->fetch()? false: true;
	}

	public static function canRemoveCopyright()
	{
		return \Bitrix\Imopenlines\Limit::canRemoveCopyright();
	}

	public static function getFormatedUrl($alias = '')
	{
		return \Bitrix\ImOpenLines\Common::getServerAddress().'/online/'.$alias;
	}

	public function get($configId = null)
	{
		if ($configId)
		{
			$this->id = intval($configId);
		}

		if ($this->id <= 0)
		{
			return false;
		}

		$orm = Model\LivechatTable::getById($this->id);
		$this->config = $orm->fetch();
		if (!$this->config)
			return false;

		$this->config['URL'] = self::getFormatedUrl($this->config['URL_CODE']);
		$this->config['URL_PUBLIC'] = self::getFormatedUrl($this->config['URL_CODE_PUBLIC']);
		$this->config['URL_SERVER'] = self::getFormatedUrl();
		$this->config['COPYRIGHT_REMOVED'] = self::canRemoveCopyright()? $this->config['COPYRIGHT_REMOVED']: "N";
		$this->config['CAN_REMOVE_COPYRIGHT'] = self::canRemoveCopyright()? 'Y':'N';
		$this->config['BACKGROUND_IMAGE_LINK'] = $this->config['BACKGROUND_IMAGE']? \CFile::GetPath($this->config['BACKGROUND_IMAGE']): "";
		$this->config['PHONE_CODE'] = $this->config['PHONE_CODE'];

		return $this->config;
	}

	public function getPublicLink()
	{
		$orm = Model\LivechatTable::getList(array(
			'select' => Array('BACKGROUND_IMAGE', 'CONFIG_NAME' => 'CONFIG.LINE_NAME', 'URL_CODE_PUBLIC'),
			'filter' => Array('=CONFIG_ID' => $this->id)
		));
		$config = $orm->fetch();
		if (!$config)
			return false;

		$picture = '';
		if ($config['BACKGROUND_IMAGE'] > 0)
		{
			$image = \CFile::ResizeImageGet(
				$config['BACKGROUND_IMAGE'],
				array('width' => 300, 'height' => 200), BX_RESIZE_IMAGE_PROPORTIONAL, false
			);
			if($image['src'])
			{
				$picture = $image['src'];
			}
		}

		$result = Array(
			'ID' => $this->id,
			'NAME' => Loc::getMessage('IMOL_LCM_PUBLIC_NAME'),
			'LINE_NAME' => $config['CONFIG_NAME'],
			'PICTURE' => $picture,
			'URL' => self::getFormatedUrl($config['URL_CODE_PUBLIC']),
			'URL_IM' => self::getFormatedUrl($config['URL_CODE_PUBLIC'])
		);

		return $result;
	}

	public function getWidget($type = self::TYPE_BUTTON, $lang = null, $config = array(), $force = false)
	{
		$charset = SITE_CHARSET;

		$jsData = $this->getWidgetSource(Array('LANG' => $lang, 'CONFIG' => $config, 'FORCE' => $force ? 'Y' : 'N'));
		if (!$jsData)
			return false;

		$codeWidget = '<script type="text/javascript">'.$jsData."</script>";

		return $codeWidget;
	}

	/**
	 * @deprecated
	 *
	 * @param array $params
	 * @return string
	 */
	public static function updateCommonFiles($params = array())
	{
		if(\Bitrix\Main\Loader::includeModule('Crm'))
		{
			\Bitrix\Crm\SiteButton\Manager::updateScriptCacheAgent();
		}

		return "";
	}

	public static function getListForSelect()
	{
		$select = Array();
		$orm = \Bitrix\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array(
				'CONFIG_ID', 'LINE_NAME' => 'CONFIG.LINE_NAME'
			)
		));
		while ($row = $orm->fetch())
		{
			$select[$row['CONFIG_ID']] = $row['LINE_NAME']? $row['LINE_NAME']: $row['CONFIG_ID'];
		}
		return $select;
	}

	private function getWidgetSource($params = array())
	{
		$config = $this->get();

		$params['LANG'] = isset($params['LANG'])? $params['LANG']: null;
		$params['CONFIG'] = is_array($params['CONFIG'])? $params['CONFIG']: Array();

		$charset = SITE_CHARSET;

		$cssData = \CUtil::JSEscape( Main\IO\File::getFileContents($_SERVER["DOCUMENT_ROOT"] . '/bitrix/js/imopenlines/livechat.css'));
		$jsData = \CUtil::JSEscape( Main\IO\File::getFileContents($_SERVER["DOCUMENT_ROOT"] . '/bitrix/js/imopenlines/livechat.js'));

		$localize = \Bitrix\ImOpenLines\LiveChat::getLocalize($params['LANG'], false);

		$params['CONFIG']["bitrix24"] = \Bitrix\ImOpenLines\Common::getServerAddress();
		$params['CONFIG']["code"] = $config['URL_CODE'];
		$params['CONFIG']["lang"] = $params['LANG'];
		$params['CONFIG']["copyright"] = isset($params['CONFIG']["copyright"])? $params['CONFIG']["copyright"]: true;
		$params['CONFIG']["copyrightUrl"] = \Bitrix\ImOpenLines\Common::getBitrixUrlByLang($params['LANG']);

		$params['CONFIG']["button"] = false;

		$codeButton =
			'(function (d) {'.
				'var f = function (d) {'.
					'var n1 = document.getElementsByTagName("style")[0], s1 = document.createElement("style");s1.innerHTML = "' . $cssData . '";'.
					'var n2 = document.getElementsByTagName("script")[0], s2 = document.createElement("script");s2.type = "text/javascript";s2.charset = "'.$charset.'";s2.innerHTML = "' . $jsData . '";'.
					'if (n1) {n1.parentNode.insertBefore(s1, n1);} else { n2.parentNode.insertBefore(s1, n2); }'.
					'n2.parentNode.insertBefore(s2, n2);'.
				'};'.
				'if (typeof(BX)!="undefined" && typeof(BX.ready)!="undefined") {BX.ready(f)}'.
				'else if (typeof(jQuery)!="undefined") {jQuery(f)}'.
				'else { f() }'.
			'})(document);'.
			'(window.BxLiveChatLoader = window.BxLiveChatLoader || []).push(function() {'.
				$localize.
				'BX.LiveChat.init('.Main\Web\Json::encode($params['CONFIG']).');'.
			'});';

		return $codeButton;
	}

	public static function available()
	{
		if (!is_null(static::$availableCount))
		{
			return static::$availableCount > 0;
		}
		$orm = \Bitrix\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array('CNT'),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			),
		));
		$row = $orm->fetch();
		static::$availableCount = $row['CNT'];

		return ($row['CNT'] > 0);
	}

	public static function availableCount()
	{
		return is_null(static::$availableCount)? 0: static::$availableCount;
	}

	public function getError()
	{
		return $this->error;
	}
}