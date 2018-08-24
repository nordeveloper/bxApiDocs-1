<?php
$pull_default_option = array(
	'path_to_listener' => "http://#DOMAIN#/bitrix/sub/",
	'path_to_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
	'path_to_modern_listener' => "http://#DOMAIN#/bitrix/sub/",
	'path_to_modern_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
	'path_to_mobile_listener' => "http://#DOMAIN#:8893/bitrix/sub/",
	'path_to_mobile_listener_secure' => "https://#DOMAIN#:8894/bitrix/sub/",
	'path_to_websocket' => "ws://#DOMAIN#/bitrix/subws/",
	'path_to_websocket_secure' => "wss://#DOMAIN#/bitrix/subws/",
	'path_to_publish' => 'http://127.0.0.1:8895/bitrix/pub/',
	'nginx_version' => 2,
	'nginx_command_per_hit' => 100,
	'nginx' => 'N',
	'nginx_headers' => 'Y',
	'push' => 'N',
	'push_message_per_hit' => 100,
	'websocket' => 'Y',
	'signature_key' => '',
	'signature_algo' => 'sha1',
	'guest' => 'N',
);

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/pull.php"))
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/pull.php");
}
?>