<?php
CJSCore::RegisterExt('documentpreview', [
	'js' => '/bitrix/js/documentgenerator/documentpreview.js',
	'lang' => '/bitrix/modules/documentgenerator/lang/'.LANGUAGE_ID.'/install/js/documentpreview.php',
	'rel' => ['core', 'ajax', 'pull', 'sidepanel', 'loader'],
]);

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"documentgenerator",
	[
		"petrovich" => "lib/external/petrovich.php",
	]
);