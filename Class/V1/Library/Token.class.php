<?php
namespace Library;

use PDO;
use PDOException;

class Token {
    private static $token = null;
    private static $user = array();

    public static function isValid() {
        $headers = getallheaders();
        Token::$user = array();
        Token::$token = null;
        if (!empty($headers[AUTH_HEADER])) Token::$token = $headers[AUTH_HEADER];
        else if (DEBUG && !empty($_REQUEST['token'])) Token::$token = $_REQUEST['token'];
        if (!empty(Token::$token)) {
            $qry = "SELECT `u`.`id`, `u`.`name`, `u`.`email`, `u`.`role` FROM `user` AS  `u` WHERE `u`.`token` = '" . Token::getToken() . "' AND `u`.`expire` > NOW() LIMIT 0 , 1";
            $rs = DB::query($qry);
            if ($rs->rowCount() == 1) {
                Token::$user = $rs->fetch(PDO::FETCH_ASSOC);
                $expire = date("Y-m-d H:i:s", time() + (24 * 60 * 60));
                $qry = "UPDATE user SET expire='" . $expire . "' WHERE id = " . Token::getUserId();
                DB::exec($qry);
                return true;
            }
        }
        return false;
    }

    public static function getToken() {
        return Token::$token;
    }

    public static function getUserId() {
        return Token::$user['id'];
    }

    public static function getUserRole() {
        return Token::$user['role'];
    }

    public static function getUserName() {
        return Token::$user['name'];
    }

    public static function getUserEmail() {
        return Token::$user['email'];
    }
}