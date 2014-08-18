<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR);
session_start();
setlocale(LC_ALL, 'fr_FR.UTF8');


define("DEBUG", true);

define("MYSQL_HOST", "mysql");
define("MYSQL_DB", "cesi_alternance");
define("MYSQL_USER", "cesi_alternance");
define("MYSQL_PASS", "UYLrQwHHMpNUyqKb");
define("AUTH_HEADER", "X-CESI-App-Auth");

function autoload($class) {
    require 'Class/' . strtoupper($_REQUEST['v']) . '/' . str_replace('\\', '/', $class).'.class.php';
}
spl_autoload_register('autoload');

function exist($class) {
    return file_exists('Class/' . strtoupper($_REQUEST['v']) . '/' . str_replace('\\', '/', $class).'.class.php');
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