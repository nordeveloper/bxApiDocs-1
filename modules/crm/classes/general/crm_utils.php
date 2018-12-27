<?php
class CCrmUtils
{
	private static $ENABLE_TRACING = false;
	public static function EnableTracing($enable)
	{
		self::$ENABLE_TRACING = $enable;
	}

	public static function Trace($id, $msg, $forced = false)
	{
		if(!$forced && !self::$ENABLE_TRACING)
		{
			return;
		}

		\Bitrix\Main\Diag\Debug::writeToFile($msg, $id, 'crm.log');
	}

	public static function Dump($id, $obj)
	{
		\Bitrix\Main\Diag\Debug::dump($obj, $id);
	}

	public static function AddObserver()
	{
		global $DB;

		$sql = "select * from b_user_option where ID = 9216";
		$dbResult = $DB->Query($sql, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		while($fields = $dbResult->Fetch())
		{
			//echo mydump($fields);
			$config = unserialize($fields['VALUE']);
			//echo "<pre>", mydump($config), "</pre>";

			$isFound = false;
			$sectionIndex = -1;
			$elementIndex = -1;
			for($i = 0, $configQty = count($config); $i < $configQty; $i++)
			{
				if($config[$i]['type'] !== 'section')
				{
					continue;
				}

				if($sectionIndex < 0 && $config[$i]['name'] == 'additional')
				{
					$sectionIndex = $i;
				}

				for($j = 0, $elementQty = count($config[$i]['elements']); $j < $elementQty; $j++)
				{
					$element = $config[$i]['elements'][$j];
					if($element['name'] == 'OBSERVER')
					{
						$isFound = true;
						break;
					}

					if($element['name'] == 'ASSIGNED_BY_ID')
					{
						$sectionIndex = $i;
						$elementIndex = $j + 1;
					}
				}

				if($isFound)
				{
					break;
				}
			}

			if($isFound)
			{
				continue;
			}

			if($sectionIndex < 0)
			{
				$sectionIndex = 0;
			}

			if($elementIndex < 0)
			{
				$elementIndex = count($config[$sectionIndex]['elements']);
			}

			$configItem = $config[$sectionIndex];
			array_splice($configItem, $elementIndex, 0, array(array('name' => 'OBSERVER', 'optionFlags' => "1")));
			$config[$sectionIndex] = $configItem;

			$sql = "update b_user_option set VALUE = '".serialize($config)."' where ID = ".$fields['ID'];
			//$DB->Query($sql, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		}
	}
}
