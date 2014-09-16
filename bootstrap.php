<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

session_start();
setlocale(LC_ALL, 'fr_FR.UTF8');


define("DEBUG", true);

define("MYSQL_HOST", "mysql");
define("MYSQL_DB", "jdossantos_cesialternance");
define("MYSQL_USER", "90919_cesi");
define("MYSQL_PASS", "UYLrQwHHMpNUyqKb");
define("AUTH_HEADER", "X-Cesi-App-Auth");
define("BASE_URL", "http://cesialternance.jdossantos.com/v1/");

function get_class_path($class) {
	return 'Class/' . strtoupper($_REQUEST['v']) . '/' . str_replace('\\', '/', $class).'.class.php';
}

function autoload($class) {
	if (exist($class))
		require get_class_path($class);
}

function exist($class) {
	return file_exists(get_class_path($class));
}

if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
	if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
		spl_autoload_register('autoload', true, true);
	} else {
		spl_autoload_register('autoload');
	}
} else {
	function __autoload($classname) {
		autoload($classname);
	}
}

if (!function_exists('getallheaders')) {
	function getallheaders() {
		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}