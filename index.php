<?php
require_once 'bootstrap.php';

use \Library\Token;
use \App\User;
use \App\Calendar;
use \App\Document;
use \App\Promo;
use \App\Training;
use \App\Establishment;

$result['success'] = false;
$request = null;

if (isset($_GET['q'])) $request = trim($_GET['q'], '/');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
    case 'PUT':
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = array();
            if (substr($input, 0, 1) === "{" || substr($input, 0, 1) === "[")
                $data = (array) json_decode($input);
            else if (strpos($input, "=") > 0)
                parse_str($input, $data);
            if (count($data) > 0) $_POST = array_merge($_POST, $data);
        }
        break;
}

if (empty($request)) $result['error'] = 'Invalid request';
else {
    try {
        switch ($request) {
            case 'user/login':
                $provider = new User($request);
                $result = array_merge($result, $provider->process());
                break;
            default:
                if (0 === strpos($request, 'api/v1/')) $request = str_replace('api/v1/', '', $request);
                $tokenValid = Token::isValid();
                if (requestNeedToken($request) && !$tokenValid) $result['error'] = 'Invalid token';
                else if ($request != 'verify') {
                    $tmp   = explode('/', $request);
                    $class = array_shift($tmp);
                    $class = '\\App\\' . ucwords($class);
                    if (exist($class)) {
                        require_once get_class_path($class);
                        $provider = new $class($request);
                        $result = array_merge($result, $provider->process());
                    } else {
                        $result['error'] = 'Request method undefined';
                    }
                }
                break;
        }
    } catch (PDOException $e) {
        $result['error'] = $e->getMessage();
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }
}

function requestNeedToken($path) {
    $free = array('password/query', 'password/request', 'password/change', 'password/update');
    return !in_array($path, $free);
}

$result['success'] = empty($result['error']);
header("Content-type: application/json");
echo json_encode($result);