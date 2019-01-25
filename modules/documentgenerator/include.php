<?php

CJSCore::RegisterExt('documentpreview', [
	'js' => '/bitrix/js/documentgenerator/documentpreview.js',
	'css' => '/bitrix/js/documentgenerator/css/documentpreview.css',
	'lang' => '/bitrix/modules/documentgenerator/lang/'.LANGUAGE_ID.'/install/js/documentpreview.php',
	'rel' => ['core', 'ajax', 'pull', 'sidepanel', 'loader', 'popup'],
]);

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"documentgenerator",
	[
		"petrovich" => "lib/external/petrovich.php",
		"html2text\html2text" => "lib/external/html2text.php",
	]
);