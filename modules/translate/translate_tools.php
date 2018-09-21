<?
use Bitrix\Main;

IncludeModuleLangFile(__FILE__);

$arLangDirs = NULL;
$arDirs = NULL;
$arFiles = NULL;
$IS_LANG_DIR = NULL;
$arTLangs = NULL;
$arDirFiles = NULL;
$arLangDirFiles = NULL;
$arSearchParam = NULL;

function GetLangDirs($arDirs, $SHOW_LANG_DIFF = false)
{
	global $arLangDirs;
	if (is_array($arDirs))
	{
		if ($SHOW_LANG_DIFF)
		{
			foreach ($arDirs as $arr1)
			{
				if($arr1["IS_LANG"])
					$arLangDirs[] = $arr1;
			}
		}
		else
		{
			$arLangDirs = $arDirs;
		}
	}
}

function DeleteLangFile($abs_path)
{
	if (file_exists($abs_path))
	{
		@chmod($abs_path, BX_FILE_PERMISSIONS);
		@unlink($abs_path);
	}
}

function prepare_path($path)
{
	return preg_replace("#[\\\\\\/]+#", "/", $path);
}

function is_lang_dir($path, $c = false)
{
	if(strpos($path, "/exec/") !== false)
	{
		return false;
	}
	elseif(preg_match("#/lang/(.*?)(/|\$)#", $path, $match))
	{
		if ($c)
		{
			$arr = explode('/', $path);
			$lang_key = array_search('lang', $arr) + 1;
			return array_key_exists($lang_key, $arr) && strlen($arr[$lang_key]) > 0;
		}
		else
		{
			return true;
		}
	}
	else
	{
		return false;
	}
}

function get_lang_id($path)
{
	$arr = explode("/",$path);
	if (in_array("lang",$arr))
	{
		$lang_key = array_search("lang", $arr) + 1;
		return $arr[$lang_key];
	}
	return false;
}

function replace_lang_id($path, $new_lang_id)
{
	//return preg_replace("#^(.*?/lang/)(.*?)(/|$)#", $path, "\\1$new_lang_id)\\3");

	return preg_replace("#^(.*?/lang/)(.*?)(/|$)#", "\\1$new_lang_id\\3", $path);
	/*
	$arr = explode("/",$path);
	if (in_array("lang",$arr))
	{
		$lang_key = array_search("lang", $arr) + 1;
		$arr[$lang_key] = $new_lang_id;
		$path = implode("/",$arr);
	}
	return $path;
	*/
}

function remove_lang_id($path, $arTLangs)
{
	$arr = explode("/",$path);
	if (in_array("lang",$arr))
	{
		$lang_key = array_search("lang", $arr) + 1;
		if (in_array($arr[$lang_key], $arTLangs)) unset($arr[$lang_key]);
		$path = implode("/",$arr);
	}
	return $path;
}

function add_lang_id($path, $lang_id, $arTLangs)
{
	$path_temp = remove_lang_id($path, $arTLangs);
	$arr = explode("/",$path_temp);
	if (in_array("lang", $arr))
	{
		$arr1 = array();
		foreach($arr as $d)
		{
			$arr1[] = $d;
			if ($d=="lang") $arr1[] = $lang_id;
		}
		$path = implode("/",$arr1);
	}
	return $path;
}

function GetTDirList($path, $subDirs = false)
{
	global $arDirs, $arFiles;

	$fullpath = realpath($_SERVER["DOCUMENT_ROOT"]."/".$path."/");

	if (preg_match('|^' . preg_quote(realpath($_SERVER['DOCUMENT_ROOT'] . '/upload'), '|') . '|i' . BX_UTF_PCRE_MODIFIER, $fullpath))
	{
		return false;
	}

	$fullpath = prepare_path($fullpath);
	//flag if dir is lang
	$is_lang = strpos($fullpath, "/lang/") !== false;
	$handle = @opendir($fullpath);
	if($handle)
	{
		$parent = prepare_path("/".$path."/");
		$abs_parent = prepare_path($_SERVER["DOCUMENT_ROOT"].$parent);
		$arList = array();
		while (false!==($file=readdir($handle)))
		{
			if ($file != "." && $file != ".." && $file != ".access.php" && $file != ".htaccess" && $file != '.svn' && $file != '.hg')
			{
				$IS_DIR = (is_dir($abs_parent.$file) ? "Y" : "N");
				$path_prepared = $parent.$file;

				if ($IS_DIR=="Y" &&
					($path_prepared=="/bitrix/updates" ||
					$path_prepared=="/bitrix/updates_enc" ||
					$path_prepared=="/bitrix/updates_enc5" ||
					$path_prepared=="/bitrix/help" ||
					$path_prepared=="/bitrix/cache" ||
					$path_prepared=="/bitrix/cache_image" ||
					$path_prepared=="/bitrix/managed_cache" ||
					$path_prepared=="/bitrix/stack_cache"
					)
				)
					continue;

				$arList[$path_prepared] = array(
					"IS_DIR" => $IS_DIR,
					"PARENT" => $parent,
					"PATH" => ($IS_DIR=="Y") ? $path_prepared."/" : $path_prepared,
					"FILE" => $file,
					"IS_LANG" => $is_lang
				);
				if ($arList[$path_prepared]['IS_DIR'] == 'N')
				{
					$arList[$path_prepared]["LANG"] = $is_lang ? get_lang_id($path_prepared) : '';
				}
			}
		}
		ksort($arList);

		foreach($arList as $path_prepared => $arr)
		{
			if($arr["IS_DIR"]=="Y")
			{
				if($subDirs)
					$arr["IS_LANG"] |= GetTDirList($path_prepared."/", $subDirs);

				$arDirs[] = $arr;
				//dir is lang if any of it's children is lang
				$is_lang = $is_lang || $arr["IS_LANG"];
			}
			elseif(is_lang_dir($path_prepared))
			{
				if(substr($arr["FILE"], -4) == '.php')
					$arFiles[] = $arr;
			}
		}
		closedir($handle);
	}
	//flag for parent
	return $is_lang;
}

function GetTCSVArray()
{
	global $arFiles;

	$arr = array();

	foreach ($arFiles as $f)
	{
		$MESS = array();
		$key = replace_lang_id($f['PATH'], '#LANG_ID#');
		$lang_id = get_lang_id($f['PATH']);
		include($_SERVER["DOCUMENT_ROOT"] . $f['PATH']);

		if (!empty($MESS) && is_array($MESS))
		{
			foreach ($MESS as $m => $v)
			{
				$m = (string)$m;
				if ($m != '')
					$arr[$key][$m][$lang_id] = $v;
			}
		}
	}
	return $arr;
}

function SaveTCSVFile()
{
	global $APPLICATION;

	if (!($APPLICATION->GetGroupRight("translate") >= 'W' && check_bitrix_sessid()))
	{
		$APPLICATION->ThrowException(GetMessage('TR_TOOLS_ERROR_RIGHTS'));
		return false;
	}

	if (!(
		isset($_FILES['csvfile'])
		&& isset($_FILES['csvfile']['tmp_name'])
		&& file_exists($_FILES['csvfile']['tmp_name'])
	))
	{
		$APPLICATION->ThrowException(GetMessage('TR_TOOLS_ERROR_EMPTY_FILE'));
		return false;
	}

	$errors = [];

	$rewrite = isset($_POST['rewrite_lang_files']) && $_POST['rewrite_lang_files'] == 'Y';
	$mergeMode = true;
	if (!$rewrite)
	{
		$mergeMode = (isset($_POST['rewrite_lang_files']) && $_POST['rewrite_lang_files'] == 'U');
	}
	$languageList = GetTLangList();

	$phraseList = array();
	$columnList = [];
	$fileIndex = null;
	$keyIndex = null;

	$csvFile = new CCSVData();
	$csvFile->LoadFile($_FILES['csvfile']['tmp_name']);
	$csvFile->SetFieldsType('R');
	$csvFile->SetFirstHeader(false);
	$csvFile->SetDelimiter(';');

	$csvRow = $csvFile->Fetch();
	if (
		!is_array($csvRow)
		|| empty($csvRow)
		|| (count($csvRow) == 1 && ($csvRow[0] === null || $csvRow[0] === ''))
	)
	{
		$errors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_EMPTY_FIRST_ROW');
	}
	else
	{
		$columnList = array_flip($csvRow);
		foreach ($languageList as $keyLang => $langID)
		{
			if (!isset($columnList[$langID]))
				unset($languageList[$keyLang]);
		}
		if (!isset($columnList['file']))
			$errors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_DESTINATION_FIELD_ABSENT');
		else
			$fileIndex = $columnList['file'];
		if (!isset($columnList['key']))
			$errors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_PHRASE_CODE_FIELD_ABSENT');
		else
			$keyIndex = $columnList['key'];
		if (empty($languageList))
			$errors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_LANGUAGE_LIST_ABSENT');
	}

	if (empty($errors))
	{
		$csvRowCounter = 1;
		while ($csvRow = $csvFile->Fetch())
		{
			$csvRowCounter++;
			if (
				!is_array($csvRow)
				|| empty($csvRow)
				|| (count($csvRow) == 1 && ($csvRow[0] === null || $csvRow[0] === ''))
			)
				continue;
			$file = (isset($csvRow[$fileIndex]) ? $csvRow[$fileIndex] : '');
			$key = (isset($csvRow[$keyIndex]) ? $csvRow[$keyIndex] : '');
			if ($file == '' || $key == '')
			{
				$rowErrors = [];
				if ($file == '')
					$rowErrors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_DESTINATION_FILEPATH_ABSENT');
				if ($key == '')
					$rowErrors[] = GetMessage('BX_TRANSLATE_IMPORT_ERR_PHRASE_CODE_ABSENT');
				$errors[] = GetMessage(
					'TR_TOOLS_ERROR_LINE_FILE_EXT',
					['#LINE#' => $csvRowCounter, '#ERROR#' => implode('; ', $rowErrors)]
				);
				unset($rowErrors);
				continue;
			}

			$rowErrors = [];

			if (!isset($phraseList[$file]))
				$phraseList[$file] = [];
			foreach ($languageList as $languageId)
			{
				if (!isset($phraseList[$file][$languageId]))
					$phraseList[$file][$languageId] = [];

				$langIndex = $columnList[$languageId];
				if (!isset($csvRow[$langIndex]))
				{
					$rowErrors[] = GetMessage(
						'BX_TRANSLATE_IMPORT_ERR_ROW_LANG_ABSENT',
						['#LANG#' => $languageId]
					);
					continue;
				}
				if ($csvRow[$langIndex] === '')
					continue;

				$phrase = str_replace("\\\\", "\\", $csvRow[$langIndex]);
				$checked = true;
				if (defined('BX_UTF'))
				{
					$validPhrase = preg_replace("/[^\x01-\x7F]/","", $phrase);
					if ($validPhrase !== $phrase)
					{
						//TODO: change to Main\Text\Encoding::detectUtf8 after method refactoring
						$prevBits8and7 = 0;
						$isUtf = 0;
						foreach(unpack("C*", $phrase) as $byte)
						{
							$hiBits8and7 = $byte & 0xC0;
							if ($hiBits8and7 == 0x80)
							{
								if ($prevBits8and7 == 0xC0)
									$isUtf++;
								elseif (($prevBits8and7 & 0x80) == 0x00)
									$isUtf--;
							}
							elseif ($prevBits8and7 == 0xC0)
							{
								$isUtf--;
							}
							$prevBits8and7 = $hiBits8and7;
						}
						unset($hiBits8and7, $byte);
						$checked = ($isUtf > 0);
						unset($isUtf, $prevBits8and7);
					}
					unset($validPhrase);
				}

				if ($checked)
				{
					$phraseList[$file][$languageId][$key] = $phrase;
				}
				else
				{
					$rowErrors[] = GetMessage(
						'BX_TRANSLATE_IMPORT_ERR_NO_VALID_UTF8_PHRASE',
						['#LANG#' => $languageId]
					);
				}
				unset($checked, $phrase);
			}

			if (!empty($rowErrors))
			{
				$errors[] = GetMessage(
					'TR_TOOLS_ERROR_LINE_FILE_BIG',
					[
						'#LINE#' => $csvRowCounter,
						'#FILENAME#' => $file,
						'#PHRASE#' => $key,
						'#ERROR#' => implode('; ', $rowErrors)
					]
				);
			}
			unset($rowErrors);
		}
		unset($csvRow);
	}
	$csvFile->CloseFile();
	unset($csvFile);

	foreach ($phraseList as $fileIndex => $translationList)
	{
		if (is_lang_dir($fileIndex, true))
		{
			foreach ($translationList as $languageId => $fileMessages)
			{
				if (empty($fileMessages))
					continue;

				$rawFile = replace_lang_id($fileIndex, $languageId);
				$file = Rel2Abs('/', $rawFile);
				if ($file !== $rawFile)
				{
					$errors[] = GetMessage(
						'BX_TRANSLATE_IMPORT_ERR_BAD_FILEPATH',
						['#FILE#' => $fileIndex]
					);
					break;
				}

				$MESS = [];
				if (!$rewrite && file_exists($_SERVER['DOCUMENT_ROOT'].$file))
				{
					include($_SERVER['DOCUMENT_ROOT'].$file);
					if (!is_array($MESS))
					{
						$MESS = [];
					}
					else
					{
						foreach (array_keys($MESS) as $index)
						{
							if ($MESS[$index] === '')
								unset($MESS[$index]);
						}
						unset($index);
					}
				}

				if ($mergeMode)
					$MESS = array_merge($MESS, $fileMessages);
				else
					$MESS = array_merge($fileMessages, $MESS);

				if (!empty($MESS))
				{
					$strMess = "";
					foreach ($MESS as $key => $value)
					{
						$value = str_replace("\n\r", "\n", $value);
						$strMess .= '$MESS["'.EscapePHPString($key).'"] = "'.EscapePHPString($value).'";'."\n";
					}

					if (!TR_BACKUP($file))
					{
						$errors[] = GetMessage("TR_TOOLS_ERROR_CREATE_BACKUP", array('%FILE%' => $file));
					}
					else
					{
						if (!RewriteFile($_SERVER["DOCUMENT_ROOT"].$file, "<?\n".$strMess."?".">"))
						{
							$errors[] = GetMessage('TR_TOOLS_ERROR_WRITE_FILE', array('%FILE%' => $file));
						}
					}
				}
			}
		}
		else
		{
			$errors[] = GetMessage('TR_TOOLS_ERROR_FILE_NOT_LANG', array('%FILE%' => $fileIndex));
		}
	}

	if (!empty($errors))
	{
		$APPLICATION->ThrowException(implode('<br>', $errors));
		return false;
	}
	unset($errors);
	return true;
}

function GetTLangList()
{
	$result = [];
	$iterator = Main\Localization\LanguageTable::getList([
		'select' => ['LID', 'SORT'],
		'filter' => ['=ACTIVE' => 'Y'],
		'order' => ['SORT' => 'ASC']
	]);
	while ($row = $iterator->fetch())
		$result[] = $row['LID'];
	unset($row, $iterator);
	return $result;
}

function GetTLangFiles($path, $IS_LANG_DIR = false)
{
	global $arTLangs, $arFiles, $arDirFiles, $arLangDirFiles;

	if (is_dir(prepare_path($_SERVER["DOCUMENT_ROOT"]."/".$path."/")))
	{
		if ($IS_LANG_DIR)
		{
			if (is_array($arTLangs))
			{
				foreach ($arTLangs as $lng)
				{
					$path = replace_lang_id($path, $lng);
					$path_l = strlen($path);

					foreach($arFiles as $arr)
					{
						if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
						{
							$arDirFiles[] = $arr["PATH"];
						}
					}
				}
			}
		}
		else
		{
			if (is_array($arLangDirFiles))
			{
				$path_l = strlen($path);

				foreach ($arLangDirFiles as $arr)
				{
					if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
					{
						$arDirFiles[] = $arr["PATH"];
					}
				}
			}
		}
	}
	else
	{
		foreach ($arTLangs as $lng)
			$arDirFiles[] = replace_lang_id($path, $lng);
	}
}

function TSEARCH($file, &$count)
{
	global $arSearchParam, $USER;

	if (!$USER->CanDoOperation('edit_php'))
		return false ;

	$_mess = __IncludeLang($file, true);

	if (!is_array($_mess))
		return false;

	$_phrase = $phrase = $arSearchParam['search'];
	if (!$arSearchParam['bCaseSens'])
		$_phrase = strtolower($arSearchParam['search']);
	$I_PCRE_MODIFIER = $arSearchParam['bCaseSens'] ? '' : 'i';

	$_bMessage = true;
	$_bMnemonic = false;
	$_arSearchData = array();
	if ($arSearchParam['bSearchMessage'] && $arSearchParam['bSearchMnemonic'])
	{
		$_bMessage = true;
		$_bMnemonic = true;
	}
	elseif ($arSearchParam['bSearchMnemonic'])
	{
		$_bMnemonic = true;
	}

	$_bResult = false;
	$count = 0;
	foreach ($_mess as $_sMn =>  $_sMe)
	{
		$__sMe = $_sMe;
		$__sMn = $_sMn;
		if (!$arSearchParam['bCaseSens'])
		{
			$__sMe = strtolower($_sMe);
			$__sMn = strtolower($_sMn);
		}

		$_bSearch = false;

		if ($_bMessage)
		{
			if (strpos($__sMe, $_phrase) !== false)
					$_bSearch = true;
		}
		if ($_bMnemonic)
		{
			if (strpos($__sMn, $_phrase) !== false)
				$_bSearch = true;
		}

		if ($_bSearch)
		{
			$_bResult = true;
			$res = array();
			//Replace
			if ($arSearchParam['is_replace'])
			{
				$pattern = '/'.preg_quote($phrase, '/').'/S'.$I_PCRE_MODIFIER.BX_UTF_PCRE_MODIFIER;

				TR_BACKUP($file);
				if ($_bMessage)
				{
					preg_match_all($pattern, $_sMe, $res);
					$count += count($res[0]);
					$_sMe = preg_replace($pattern, $arSearchParam['replace'], $_sMe);
				}
				if ($_bMnemonic)
				{
					preg_match_all($pattern, $_sMn, $res);
					$count += count($res[0]);
					$_sMn = preg_replace($pattern, $arSearchParam['replace'], $_sMn);
				}
			}
			else
			{
				$pattern = '/'.preg_quote($phrase, '/').'/'.$I_PCRE_MODIFIER.BX_UTF_PCRE_MODIFIER;
				if ($_bMessage)
				{
					preg_match_all($pattern, $_sMe, $res);
					$count += count($res[0]);
				}
				if ($_bMnemonic)
				{
					preg_match_all($pattern, $_sMn, $res);
					$count += count($res[0]);
				}
			}
		}

		if ($arSearchParam['is_replace'])
		{
			$_arSearchData[] = "\$MESS[\"".EscapePHPString($_sMn)."\"] = \"".
								EscapePHPString(str_replace("\r", "", $_sMe))."\"";
		}
	}

	if ($arSearchParam['is_replace'] && $_bResult)
	{
		$strContent = "";
		foreach ($_arSearchData as $M)
		{
			if (strlen($M)>0) $strContent .= "\n".$M.";";
		}
		RewriteFile($file, "<?".$strContent."\n?".">");
	}

	return $_bResult;
}

function TR_BACKUP($file)
{
	$bReturn = true;

	if (COption::GetOptionString('translate', 'BACKUP_FILES', 'N') == 'Y')
	{
		if (strpos($file, $_SERVER["DOCUMENT_ROOT"]) === 0)
			$file = str_replace($_SERVER["DOCUMENT_ROOT"], '', $file);

		$backUPPath = $_SERVER["DOCUMENT_ROOT"].'/bitrix/tmp/translate/_backup'.dirname($file).'/';

		$backUPFile = basename($file);
		CheckDirPath($backUPPath);
		if (file_exists($backUPPath) && is_dir($backUPPath))
		{
			$prfx = date('YmdHi');
			$_backUPFile = $prfx.'_'.$backUPFile;
			if (file_exists($backUPPath.$_backUPFile))
			{
				$i = 1;
				while (file_exists($backUPPath.'/'.$_backUPFile))
				{
					$i++;
					$_backUPFile = $prfx.'_'.$i.'_'.$backUPFile;
				}
			}

			@copy($_SERVER['DOCUMENT_ROOT'].$file, $backUPPath.$_backUPFile);
			@chmod($backUPPath.$_backUPFile, BX_FILE_PERMISSIONS);
		}
		else
		{
			$bReturn = false;
		}
	}
	return $bReturn;
}