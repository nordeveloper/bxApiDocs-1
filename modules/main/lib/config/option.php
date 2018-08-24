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

					/*ZDUyZmZZDhhMzk3MTNhMDQ4MjJlOWM3M2MwM2U4YzJlODg5MzA=*/$GLOBALS['____385312639']= array(base64_decode('ZXhw'.'bG9'.'kZQ=='),base64_decode('cGF'.'jaw'.'=='),base64_decode('bWQ1'),base64_decode(''.'Y2'.'9uc3RhbnQ='),base64_decode('aG'.'Fza'.'F9ob'.'WF'.'j'),base64_decode('c3RyY'.'21w'),base64_decode('aX'.'Nfb2'.'J'.'qZWN'.'0'),base64_decode('Y2'.'FsbF91'.'c2VyX2Z1bmM='),base64_decode('Y'.'2Fs'.'bF'.'91c2VyX2Z1bmM'.'='),base64_decode(''.'Y'.'2Fs'.'bF91c2Vy'.'X2'.'Z1bmM'.'='),base64_decode('Y2Fs'.'bF'.'91'.'c2V'.'yX'.'2Z1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___1354931223')){function ___1354931223($_1846874017){static $_867163420= false; if($_867163420 == false) $_867163420=array(''.'TkFNRQ='.'=','flBB'.'UkFN'.'X01BWF'.'9V'.'U0VSUw==','b'.'W'.'Fpbg='.'=','LQ==','VkFMVUU=','Lg==','SCo'.'=','Yml0cml4','TElDRU5T'.'RV9L'.'RVk=','c2'.'h'.'hM'.'j'.'U2','V'.'V'.'NFUg==','V'.'V'.'NFUg==','VVNFUg'.'==','SXNB'.'dX'.'Rob3'.'JpemV'.'k','VV'.'N'.'FUg==','SX'.'NBZG1pbg==',''.'Q'.'VBQTElDQV'.'RJT04=','UmVzdGFydEJ1Z'.'mZlcg='.'=','TG9j'.'YWxSZWRp'.'cmVj'.'dA==','L'.'2'.'xp'.'Y2VuY2VfcmVzdHJpY3Rpb'.'24ucGhw','LQ==','bWFpbg'.'==',''.'flBB'.'UkFNX01BW'.'F9V'.'U0'.'V'.'S'.'U'.'w'.'==','LQ'.'==','bWFpbg==','U'.'EFSQU1f'.'TUFYX'.'1'.'VTRV'.'JT');return base64_decode($_867163420[$_1846874017]);}};if($ar[___1354931223(0)] === ___1354931223(1) && $moduleId === ___1354931223(2) && $s === ___1354931223(3)){ $_619382558= $ar[___1354931223(4)]; list($_776338531, $_1723979409)= $GLOBALS['____385312639'][0](___1354931223(5), $_619382558); $_1555343380= $GLOBALS['____385312639'][1](___1354931223(6), $_776338531); $_700303255= ___1354931223(7).$GLOBALS['____385312639'][2]($GLOBALS['____385312639'][3](___1354931223(8))); $_1939497068= $GLOBALS['____385312639'][4](___1354931223(9), $_1723979409, $_700303255, true); if($GLOBALS['____385312639'][5]($_1939497068, $_1555343380) !==(1316/2-658)){ if(isset($GLOBALS[___1354931223(10)]) && $GLOBALS['____385312639'][6]($GLOBALS[___1354931223(11)]) && $GLOBALS['____385312639'][7](array($GLOBALS[___1354931223(12)], ___1354931223(13))) &&!$GLOBALS['____385312639'][8](array($GLOBALS[___1354931223(14)], ___1354931223(15)))){ $GLOBALS['____385312639'][9](array($GLOBALS[___1354931223(16)], ___1354931223(17))); $GLOBALS['____385312639'][10](___1354931223(18), ___1354931223(19), true);}} self::$options[___1354931223(20)][___1354931223(21)][___1354931223(22)]= $_1723979409; self::$options[___1354931223(23)][___1354931223(24)][___1354931223(25)]= $_1723979409;}/**/
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

					/*ZDUyZmZMDI5MzgxOGMwOTUyMzE1ZDMzMGZkMTAzNTMyZDNiNmE=*/$GLOBALS['____1931647343']= array(base64_decode(''.'Z'.'Xhwb'.'G9kZQ='.'='),base64_decode('cGFjaw=='),base64_decode('bWQ1'),base64_decode('Y29uc3'.'RhbnQ='),base64_decode('aGF'.'z'.'aF'.'9'.'obWFj'),base64_decode('c3Ry'.'Y21w'),base64_decode('aXNf'.'b2J'.'qZWN0'),base64_decode('Y2FsbF91c2V'.'yX2'.'Z1bmM='),base64_decode('Y2FsbF91c2VyX2'.'Z'.'1b'.'mM='),base64_decode('Y2FsbF91c'.'2VyX2Z1'.'bmM='),base64_decode('Y2FsbF91c2VyX'.'2Z'.'1b'.'mM='),base64_decode('Y2FsbF91c2'.'VyX2Z1b'.'mM'.'='));if(!function_exists(__NAMESPACE__.'\\___1170220186')){function ___1170220186($_1791939894){static $_1137159457= false; if($_1137159457 == false) $_1137159457=array('L'.'Q==','bWFp'.'bg==','flB'.'BUk'.'FNX'.'01'.'BW'.'F9V'.'U0VSUw'.'==','LQ==','bW'.'Fpbg==','flBBUkFNX01B'.'WF9V'.'U0V'.'SUw'.'==',''.'Lg'.'==','SCo=',''.'Yml'.'0'.'cm'.'l4','TEl'.'DR'.'U5TRV9LRVk=','c2hh'.'Mj'.'U2','LQ==','bWFpbg'.'==',''.'flBBUkF'.'NX01BWF9VU0V'.'SUw==','LQ==',''.'bWFpbg==','UE'.'FSQU'.'1fTUFY'.'X1V'.'TR'.'V'.'JT',''.'VVN'.'FUg==','VVNFUg==','V'.'VNF'.'U'.'g'.'==',''.'SX'.'N'.'BdX'.'Rob3'.'JpemVk',''.'V'.'VNFU'.'g==','S'.'X'.'NBZG1pbg==','QV'.'BQT'.'ElDQVRJT04=',''.'UmVzdGF'.'yd'.'EJ1ZmZ'.'l'.'cg==','TG9j'.'YWxS'.'Z'.'W'.'R'.'pcmVjdA==','L2xpY2VuY'.'2VfcmVzdHJ'.'pY3Rpb24uc'.'Ghw',''.'LQ==','bWFp'.'bg==','flB'.'BUkFN'.'X01BWF9VU0V'.'SU'.'w='.'=','LQ'.'==','bWFpbg='.'=','UE'.'F'.'SQU1fTUFYX1VTRV'.'JT','XE'.'JpdHJpeFxNYWluXENvbmZpZ'.'1xPcH'.'R'.'pb'.'2'.'46OnNld'.'A==','bWFpbg==','UEFSQ'.'U'.'1'.'fTUFYX1VTR'.'VJT');return base64_decode($_1137159457[$_1791939894]);}};if(isset(self::$options[___1170220186(0)][___1170220186(1)][___1170220186(2)])){ $_662452557= self::$options[___1170220186(3)][___1170220186(4)][___1170220186(5)]; list($_1250594854, $_1717738401)= $GLOBALS['____1931647343'][0](___1170220186(6), $_662452557); $_707418199= $GLOBALS['____1931647343'][1](___1170220186(7), $_1250594854); $_929396934= ___1170220186(8).$GLOBALS['____1931647343'][2]($GLOBALS['____1931647343'][3](___1170220186(9))); $_1142148467= $GLOBALS['____1931647343'][4](___1170220186(10), $_1717738401, $_929396934, true); self::$options[___1170220186(11)][___1170220186(12)][___1170220186(13)]= $_1717738401; self::$options[___1170220186(14)][___1170220186(15)][___1170220186(16)]= $_1717738401; if($GLOBALS['____1931647343'][5]($_1142148467, $_707418199) !==(1056/2-528)){ if(isset($GLOBALS[___1170220186(17)]) && $GLOBALS['____1931647343'][6]($GLOBALS[___1170220186(18)]) && $GLOBALS['____1931647343'][7](array($GLOBALS[___1170220186(19)], ___1170220186(20))) &&!$GLOBALS['____1931647343'][8](array($GLOBALS[___1170220186(21)], ___1170220186(22)))){ $GLOBALS['____1931647343'][9](array($GLOBALS[___1170220186(23)], ___1170220186(24))); $GLOBALS['____1931647343'][10](___1170220186(25), ___1170220186(26), true);} return;}} else{ self::$options[___1170220186(27)][___1170220186(28)][___1170220186(29)]= round(0+2.4+2.4+2.4+2.4+2.4); self::$options[___1170220186(30)][___1170220186(31)][___1170220186(32)]= round(0+2.4+2.4+2.4+2.4+2.4); $GLOBALS['____1931647343'][11](___1170220186(33), ___1170220186(34), ___1170220186(35), round(0+12)); return;}/**/

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
