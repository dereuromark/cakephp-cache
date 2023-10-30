<?php

use Cache\CachePlugin;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use TestApp\View\AppView;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('APP', rtrim(sys_get_temp_dir(), DS) . DS . APP_DIR . DS);
if (!is_dir(APP)) {
	mkdir(APP, 0770, true);
}

define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
	mkdir(TMP, 0770, true);
}
define('CONFIG', ROOT . DS . 'config' . DS);

define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

if (!defined('SECOND')) {
	define('SECOND', 1);
}
if (!defined('MINUTE')) {
	define('MINUTE', 60);
}
if (!defined('HOUR')) {
	define('HOUR', 3600);
}
if (!defined('DAY')) {
	define('DAY', 86400);
}
if (!defined('WEEK')) {
	define('WEEK', 604800);
}
if (!defined('MONTH')) {
	define('MONTH', 2592000);
}
if (!defined('YEAR')) {
	define('YEAR', 31536000);
}

require dirname(__DIR__) . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';
require CAKE . 'functions.php';

Configure::write('App', [
	'namespace' => 'TestApp',
	'encoding' => 'UTF-8',
]);
Configure::write('debug', true);

class_alias(AppView::class, 'App\View\AppView');

$cache = [
	'default' => [
		'engine' => 'File',
	],
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];

Cache::setConfig($cache);

Plugin::getCollection()->add(new CachePlugin());
