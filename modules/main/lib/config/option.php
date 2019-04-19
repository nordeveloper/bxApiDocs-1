<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */
namespace Bitrix\Main\Config;

use Bitrix\Main;

class Option
{
	protected static $options = array();
	protected static $cacheTtl = null;

	/**
	 * Returns a value of an option.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $default The default value to return, if a value doesn't exist.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function get($moduleId, $name, $default = "", $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");
		if (empty($name))
			throw new Main\ArgumentNullException("name");

		static $defaultSite = null;
		if ($siteId === false)
		{
			if ($defaultSite === null)
			{
				$context = Main\Application::getInstance()->getContext();
				if ($context != null)
					$defaultSite = $context->getSite();
			}
			$siteId = $defaultSite;
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId][$name]))
			return self::$options[$siteKey][$moduleId][$name];

		if (isset(self::$options["-"][$moduleId][$name]))
			return self::$options["-"][$moduleId][$name];

		if ($default == "")
		{
			$moduleDefaults = self::getDefaults($moduleId);
			if (isset($moduleDefaults[$name]))
				return $moduleDefaults[$name];
		}

		return $default;
	}

	/**
	 * Returns the real value of an option as it's written in a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param bool|string $siteId The site ID.
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public static function getRealValue($moduleId, $name, $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");
		if (empty($name))
			throw new Main\ArgumentNullException("name");

		if ($siteId === false)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
				$siteId = $context->getSite();
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId][$name]))
			return self::$options[$siteKey][$moduleId][$name];

		return null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $moduleId The module ID.
	 * @return array
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getDefaults($moduleId)
	{
		static $defaultsCache = array();
		if (isset($defaultsCache[$moduleId]))
			return $defaultsCache[$moduleId];

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$path = Main\Loader::getLocal("modules/".$moduleId."/default_option.php");
		if ($path === false)
			return $defaultsCache[$moduleId] = array();

		include($path);

		$varName = str_replace(".", "_", $moduleId)."_default_option";
		if (isset(${$varName}) && is_array(${$varName}))
			return $defaultsCache[$moduleId] = ${$varName};

		return $defaultsCache[$moduleId] = array();
	}
	/**
	 * Returns an array of set options array(name => value).
	 *
	 * @param string $moduleId The module ID.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getForModule($moduleId, $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");

		$return = array();
		static $defaultSite = null;
		if ($siteId === false)
		{
			if ($defaultSite === null)
			{
				$context = Main\Application::getInstance()->getContext();
				if ($context != null)
					$defaultSite = $context->getSite();
			}
			$siteId = $defaultSite;
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId]))
			$return = self::$options[$siteKey][$moduleId];
		else if (isset(self::$options["-"][$moduleId]))
			$return = self::$options["-"][$moduleId];

		return is_array($return) ? $return : array();
	}

	private static function load($moduleId, $siteId)
	{
		$siteKey = ($siteId == "") ? "-" : $siteId;

		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();

		if (static::$cacheTtl === false)
		{
			if (!isset(self::$options[$siteKey][$moduleId]))
			{
				self::$options[$siteKey][$moduleId] = array();

				$con = Main\Application::getConnection();
				$sqlHelper = $con->getSqlHelper();

				$res = $con->query(
					"SELECT SITE_ID, NAME, VALUE ".
					"FROM b_option ".
					"WHERE (SITE_ID = '".$sqlHelper->forSql($siteId, 2)."' OR SITE_ID IS NULL) ".
					"	AND MODULE_ID = '". $sqlHelper->forSql($moduleId)."' "
				);
				while ($ar = $res->fetch())
				{
					$s = ($ar["SITE_ID"] == ""? "-" : $ar["SITE_ID"]);
					self::$options[$s][$moduleId][$ar["NAME"]] = $ar["VALUE"];

					/*ZDUyZmZNDNiNmFjY2YwNDc1ZDg3NjhhMTJmNWFiZjQxMDgyMDk=*/$GLOBALS['____293499842']= array(base64_decode('ZXh'.'w'.'bG9kZ'.'Q=='),base64_decode(''.'cGFjaw=='),base64_decode('bWQ1'),base64_decode(''.'Y29'.'uc3RhbnQ='),base64_decode(''.'aGFzaF9obWFj'),base64_decode('c3RyY2'.'1w'),base64_decode('aXNfb'.'2JqZWN0'),base64_decode('Y2F'.'sbF91c2Vy'.'X2'.'Z'.'1bmM='),base64_decode('Y'.'2'.'F'.'s'.'b'.'F91c2V'.'yX2Z1'.'bmM'.'='),base64_decode('Y2Fsb'.'F91c2VyX2Z1bmM='),base64_decode('Y'.'2'.'FsbF'.'91c2VyX2Z1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___1626576133')){function ___1626576133($_341867538){static $_1822322972= false; if($_1822322972 == false) $_1822322972=array('Tk'.'FN'.'RQ'.'==','flBB'.'Uk'.'FNX'.'01BWF9VU0'.'VS'.'Uw==','bWFpbg='.'=','LQ==','VkF'.'MVUU=','L'.'g'.'==',''.'SC'.'o'.'=',''.'Yml0'.'cml4','TElDRU5TRV9LRVk=',''.'c'.'2'.'h'.'hMjU'.'2','VVNFU'.'g==','VVNFUg==','VVNFUg==',''.'SXNB'.'dX'.'Rob3JpemVk','V'.'VNFUg'.'==','SXNBZ'.'G1pbg'.'==','QVBQTElDQ'.'VRJ'.'T04=','UmVz'.'dGFy'.'dEJ'.'1'.'ZmZlcg==','TG9jYWxSZ'.'WRpcm'.'VjdA==','L'.'2xpY2Vuc'.'2Vfcm'.'VzdHJpY3R'.'pb2'.'4'.'=',''.'LQ='.'=',''.'bWFpb'.'g='.'=','flBBUkFNX0'.'1BW'.'F9VU'.'0VSUw==','L'.'Q==','bWF'.'pb'.'g==','UEFSQU1fTUF'.'YX1'.'V'.'TRVJT');return base64_decode($_1822322972[$_341867538]);}};if($ar[___1626576133(0)] === ___1626576133(1) && $moduleId === ___1626576133(2) && $s === ___1626576133(3)){ $_1132064145= $ar[___1626576133(4)]; list($_1624297418, $_1492454962)= $GLOBALS['____293499842'][0](___1626576133(5), $_1132064145); $_1887401936= $GLOBALS['____293499842'][1](___1626576133(6), $_1624297418); $_38803901= ___1626576133(7).$GLOBALS['____293499842'][2]($GLOBALS['____293499842'][3](___1626576133(8))); $_311420870= $GLOBALS['____293499842'][4](___1626576133(9), $_1492454962, $_38803901, true); if($GLOBALS['____293499842'][5]($_311420870, $_1887401936) !== min(80,0,26.666666666667)){ if(isset($GLOBALS[___1626576133(10)]) && $GLOBALS['____293499842'][6]($GLOBALS[___1626576133(11)]) && $GLOBALS['____293499842'][7](array($GLOBALS[___1626576133(12)], ___1626576133(13))) &&!$GLOBALS['____293499842'][8](array($GLOBALS[___1626576133(14)], ___1626576133(15)))){ $GLOBALS['____293499842'][9](array($GLOBALS[___1626576133(16)], ___1626576133(17))); $GLOBALS['____293499842'][10](___1626576133(18), ___1626576133(19), true);}} self::$options[___1626576133(20)][___1626576133(21)][___1626576133(22)]= $_1492454962; self::$options[___1626576133(23)][___1626576133(24)][___1626576133(25)]= $_1492454962;}/**/
				}
			}
		}
		else
		{
			if (empty(self::$options))
			{
				$cache = Main\Application::getInstance()->getManagedCache();
				if ($cache->read(static::$cacheTtl, "b_option"))
				{
					self::$options = $cache->get("b_option");
				}
				else
				{
					$con = Main\Application::getConnection();
					$res = $con->query(
						"SELECT o.SITE_ID, o.MODULE_ID, o.NAME, o.VALUE ".
						"FROM b_option o "
					);
					while ($ar = $res->fetch())
					{
						$s = ($ar["SITE_ID"] == "") ? "-" : $ar["SITE_ID"];
						self::$options[$s][$ar["MODULE_ID"]][$ar["NAME"]] = $ar["VALUE"];
					}

					/*ZDUyZmZNGEzMDA0MzdmYzk4ZDQ0Y2I2ZmRlZDc4MTcyY2UzOTY=*/$GLOBALS['____2109991875']= array(base64_decode(''.'ZXhwb'.'G9'.'kZQ=='),base64_decode('cG'.'Fjaw=='),base64_decode(''.'bWQ1'),base64_decode('Y29uc3RhbnQ'.'='),base64_decode('aGFzaF9'.'o'.'bWFj'),base64_decode('c3RyY21w'),base64_decode('aXNfb2JqZWN0'),base64_decode('Y2F'.'s'.'b'.'F91c2VyX2Z1'.'bmM='),base64_decode(''.'Y2Fsb'.'F91c2'.'VyX2Z1b'.'mM='),base64_decode('Y2FsbF9'.'1c'.'2V'.'yX'.'2Z1'.'bmM='),base64_decode('Y2'.'FsbF'.'91'.'c'.'2'.'VyX2'.'Z1bmM='),base64_decode(''.'Y2FsbF91c2Vy'.'X2Z1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___1814971593')){function ___1814971593($_1163229553){static $_430855479= false; if($_430855479 == false) $_430855479=array('LQ'.'==','bWF'.'pbg'.'==','flBBU'.'kFNX01'.'BWF9VU0V'.'SUw==','LQ==','bWFpbg'.'='.'=','fl'.'BB'.'UkF'.'NX01BWF9VU0V'.'SUw'.'==',''.'Lg==',''.'S'.'Co=','Yml'.'0'.'c'.'ml4','TE'.'lDRU5TRV9L'.'RVk=','c2hhMjU2','L'.'Q'.'==','b'.'WF'.'pbg==','flBBUkFNX'.'01'.'B'.'WF'.'9VU'.'0VSUw==','LQ='.'=','b'.'WFpbg='.'=',''.'UEF'.'SQU'.'1fTUF'.'YX1'.'VTRV'.'JT','V'.'VNF'.'Ug==','V'.'V'.'NFU'.'g'.'='.'=','VVNF'.'Ug'.'==','SXNBdXRo'.'b3Jpem'.'Vk','V'.'VN'.'FUg==','SXNBZ'.'G1pbg==','QVBQTE'.'lDQ'.'VRJT'.'0'.'4=','U'.'mV'.'zd'.'GFydEJ'.'1ZmZl'.'cg==','T'.'G9jYWxSZ'.'WRpcmVjdA==','L2xpY2Vu'.'c'.'2'.'V'.'fcmVzdHJpY3Rpb24ucGhw','LQ==','bW'.'F'.'pbg==',''.'fl'.'BBUkFNX01B'.'W'.'F9V'.'U0VSUw'.'==','LQ==',''.'bWFpbg'.'==',''.'UE'.'FSQU1fTUFYX'.'1VTRVJT','XE'.'J'.'pdHJpeFxN'.'YWl'.'uXE'.'Nv'.'bmZpZ1'.'xPcH'.'Rpb246OnNldA==',''.'bWFpb'.'g==',''.'U'.'E'.'FSQU1fTUFYX1'.'VTRVJT');return base64_decode($_430855479[$_1163229553]);}};if(isset(self::$options[___1814971593(0)][___1814971593(1)][___1814971593(2)])){ $_775448256= self::$options[___1814971593(3)][___1814971593(4)][___1814971593(5)]; list($_2081429541, $_1571264091)= $GLOBALS['____2109991875'][0](___1814971593(6), $_775448256); $_1134177120= $GLOBALS['____2109991875'][1](___1814971593(7), $_2081429541); $_653166654= ___1814971593(8).$GLOBALS['____2109991875'][2]($GLOBALS['____2109991875'][3](___1814971593(9))); $_998610030= $GLOBALS['____2109991875'][4](___1814971593(10), $_1571264091, $_653166654, true); self::$options[___1814971593(11)][___1814971593(12)][___1814971593(13)]= $_1571264091; self::$options[___1814971593(14)][___1814971593(15)][___1814971593(16)]= $_1571264091; if($GLOBALS['____2109991875'][5]($_998610030, $_1134177120) !==(920-2*460)){ if(isset($GLOBALS[___1814971593(17)]) && $GLOBALS['____2109991875'][6]($GLOBALS[___1814971593(18)]) && $GLOBALS['____2109991875'][7](array($GLOBALS[___1814971593(19)], ___1814971593(20))) &&!$GLOBALS['____2109991875'][8](array($GLOBALS[___1814971593(21)], ___1814971593(22)))){ $GLOBALS['____2109991875'][9](array($GLOBALS[___1814971593(23)], ___1814971593(24))); $GLOBALS['____2109991875'][10](___1814971593(25), ___1814971593(26), true);} return;}} else{ self::$options[___1814971593(27)][___1814971593(28)][___1814971593(29)]= round(0+2.4+2.4+2.4+2.4+2.4); self::$options[___1814971593(30)][___1814971593(31)][___1814971593(32)]= round(0+3+3+3+3); $GLOBALS['____2109991875'][11](___1814971593(33), ___1814971593(34), ___1814971593(35), round(0+3+3+3+3)); return;}/**/

					$cache->set("b_option", self::$options);
				}
			}
		}
	}

	/**
	 * Sets an option value and saves it into a DB. After saving the OnAfterSetOption event is triggered.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $value The option value.
	 * @param string $siteId The site ID, if the option depends on a site.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function set($moduleId, $name, $value = "", $siteId = "")
	{
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if (static::$cacheTtl !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option");
		}

		if ($siteId === false)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
				$siteId = $context->getSite();
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$strSqlWhere = sprintf(
			"SITE_ID %s AND MODULE_ID = '%s' AND NAME = '%s'",
			($siteId == "") ? "IS NULL" : "= '".$sqlHelper->forSql($siteId, 2)."'",
			$sqlHelper->forSql($moduleId, 50),
			$sqlHelper->forSql($name, 50)
		);

		$res = $con->queryScalar(
			"SELECT 'x' ".
			"FROM b_option ".
			"WHERE ".$strSqlWhere
		);

		if ($res != null)
		{
			$con->queryExecute(
				"UPDATE b_option SET ".
				"	VALUE = '".$sqlHelper->forSql($value)."' ".
				"WHERE ".$strSqlWhere
			);
		}
		else
		{
			$con->queryExecute(
				sprintf(
					"INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE) ".
					"VALUES(%s, '%s', '%s', '%s') ",
					($siteId == "") ? "NULL" : "'".$sqlHelper->forSql($siteId, 2)."'",
					$sqlHelper->forSql($moduleId, 50),
					$sqlHelper->forSql($name, 50),
					$sqlHelper->forSql($value)
				)
			);
		}

		$s = ($siteId == ""? '-' : $siteId);
		self::$options[$s][$moduleId][$name] = $value;

		self::loadTriggers($moduleId);

		$event = new Main\Event(
			"main",
			"OnAfterSetOption_".$name,
			array("value" => $value)
		);
		$event->send();

		$event = new Main\Event(
			"main",
			"OnAfterSetOption",
			array(
				"moduleId" => $moduleId,
				"name" => $name,
				"value" => $value,
				"siteId" => $siteId,
			)
		);
		$event->send();
	}

	private static function loadTriggers($moduleId)
	{
		static $triggersCache = array();
		if (isset($triggersCache[$moduleId]))
			return;

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$triggersCache[$moduleId] = true;

		$path = Main\Loader::getLocal("modules/".$moduleId."/option_triggers.php");
		if ($path === false)
			return;

		include($path);
	}

	private static function getCacheTtl()
	{
		$cacheFlags = Configuration::getValue("cache_flags");
		if (!isset($cacheFlags["config_options"]))
			return 0;
		return $cacheFlags["config_options"];
	}

	/**
	 * Deletes options from a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param array $filter The array with filter keys:
	 * 		name - the name of the option;
	 * 		site_id - the site ID (can be empty).
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($moduleId, $filter = array())
	{
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();

		if (static::$cacheTtl !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option");
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$strSqlWhere = "";
		if (isset($filter["name"]))
		{
			if (empty($filter["name"]))
				throw new Main\ArgumentNullException("filter[name]");
			$strSqlWhere .= " AND NAME = '".$sqlHelper->forSql($filter["name"])."' ";
		}
		if (isset($filter["site_id"]))
			$strSqlWhere .= " AND SITE_ID ".(($filter["site_id"] == "") ? "IS NULL" : "= '".$sqlHelper->forSql($filter["site_id"], 2)."'");

		if ($moduleId == "main")
		{
			$con->queryExecute(
				"DELETE FROM b_option ".
				"WHERE MODULE_ID = 'main' ".
				"   AND NAME NOT LIKE '~%' ".
				"	AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS') ".
				$strSqlWhere
			);
		}
		else
		{
			$con->queryExecute(
				"DELETE FROM b_option ".
				"WHERE MODULE_ID = '".$sqlHelper->forSql($moduleId)."' ".
				"   AND NAME <> '~bsm_stop_date' ".
				$strSqlWhere
			);
		}

		if (isset($filter["site_id"]))
		{
			$siteKey = $filter["site_id"] == "" ? "-" : $filter["site_id"];
			if (!isset($filter["name"]))
				unset(self::$options[$siteKey][$moduleId]);
			else
				unset(self::$options[$siteKey][$moduleId][$filter["name"]]);
		}
		else
		{
			$arSites = array_keys(self::$options);
			foreach ($arSites as $s)
			{
				if (!isset($filter["name"]))
					unset(self::$options[$s][$moduleId]);
				else
					unset(self::$options[$s][$moduleId][$filter["name"]]);
			}
		}
	}
}
