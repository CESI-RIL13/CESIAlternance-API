<?php
require_once 'bootstrap.php';

use \Library\Token;
use \App\User;
use \App\Calendar;
use \App\Document;
use \App\Promo;
use \App\Training;

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
                if (!Token::isValid()) $result['error'] = 'Invalid token';
                else if ($request != 'verify') {
                    if (strpos($request, 'user') === 0) {
                        $provider = new User($request);
                        $result = array_merge($result, $provider->process());
                    } else if (strpos($request, 'calendar') === 0) {
                        $provider = new Calendar($request);
                        $result = array_merge($result, $provider->process());
                    } else if (strpos($request, 'document') === 0) {
                        $provider = new Document($request);
                        $result = array_merge($result, $provider->process());
                    } else if (strpos($request, 'promo') === 0) {
                        $provider = new Promo($request);
                        $result = array_merge($result, $provider->process());
                    } else if (strpos($request, 'training') === 0) {
                        $provider = new Training($request);
                        $result = array_merge($result, $provider->process());
                    } else {
                        $result['error'] = 'Request method undefined';
                    }
                }
                break;
        }
    } catch (PDOException $e) {
        $result['error'] = $e->getMessage();
    }
}

$result['success'] = empty($result['error']);
header("Content-type: application/json");
echo json_encode($result);