<?php
namespace App;

use Library\Entity;
use Library\Token;
use Library\DB;
use PDO;

class User extends Entity {

    public function load() {
        $qry = "SELECT `u`.`id`, `u`.`name`, `u`.`role`, `up`.`id_promo`, `u`.`picture_path` FROM `user` AS `u` 
                LEFT JOIN `user_promo` AS `up` ON `u`.`id` = `up`.`id_user` 
                WHERE `u`.`token` = '" . Token::getToken() . "' LIMIT 0 , 1";
        $rs = DB::query($qry);
        if ($rs->rowCount() == 1) {
            $this->result['result'] = $rs->fetch(PDO::FETCH_ASSOC);
            $this->result['result']['id'] = (int) $this->result['id'];
			$qry = "SELECT * FROM link WHERE user_id = ".$this->result['result']['id'];
			$rs = DB::query($qry);
			if($rs->rowCount() > 0){
				while($rw = $rs->fetch(PDO::FETCH_ASSOC)) {
		            $rw['id'] = (int)$rw['id'];
		            $this->result['links'][] = $rw;
            	} 
			}
        } else {
            $this->result['error'] = 'No User Found';
        }
    }

    public function login() {
        if (empty($_POST['email'])) $this->result['error'] = 'Email is needed';
        else if (empty($_POST['password'])) $this->result['error'] = 'Password is needed';
        else {
            $qry = "SELECT `u`.`id`, `u`.`token`, `u`.`expire` FROM  `user` AS  `u` WHERE `u`.`email` = '" . $_POST['email'] . "' AND `u`.`password` = '" . $_POST['password'] . "' LIMIT 0 , 1";
            $rs = DB::query($qry);
            if ($rs && $rs->rowCount() == 1) {
                $user = $rs->fetch(PDO::FETCH_ASSOC);
                if (empty($user['token']) || time() > strtotime($user['expire'])) {
                    $user['token'] = uniqid();
                    $user['expire'] = date("Y-m-d H:i:s", time() + (24 * 60 * 60));
                    $qry = "UPDATE user SET token='" . $user['token'] . "', expire='" . $user['expire'] . "' WHERE id = " . $user['id'];
                    DB::exec($qry);
                }
                $this->result['success'] = true;
                $this->result['result']['token'] = $user['token'];
            } else {
                $this->result['error'] = 'Email or Password invalid';
            }
        }
    }

    protected function listUser() {
        
       $qry = 'SELECT `u`.`id`, `u`.`name`, `u`.`email`,`u`.role, `u`.`phone`, `u`.`picture_path` FROM `user` AS `u`';
        
        if(!empty($_GET)) {
            
            if(isset($_GET["promo"]))
                $qry .= " LEFT JOIN `user_promo` AS `up` ON `u`.`id` = `up`.`id_user` ";

            $whereClause = array();
            foreach($_GET as $key => $value) {
                switch($key){
                    case 'promo':
                        $whereClause[] = "`up`.`id_promo`='$value'";
                        break;
                    case 'role':
                        $whereClause[] = "`u`.`$key`='$value'";
                        break;                    
                }
            }
            if(count($whereClause) > 0)
                $qry .= " WHERE " . implode(" AND ",$whereClause);
        }

        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(PDO::FETCH_ASSOC)) {
                $rw['id'] = (int)$rw['id'];
                $this->result['result'][] = $rw;
            } 
        } else {
            $this->result['error'] = 'No User Found';
        }
   }

   public function addUser(){

        $qry = "INSERT INTO user (name,email,role,phone,password)
                VALUES ('".$_POST['name']."','".$_POST['email']."','".$_POST['role']."','".$_POST['phone']."','".$_POST['pwd']."')";

        try{
            if(!DB::exec($qry))
                return false;
            $_GET['id'] = DB::lastInsertId();
            $this->result['result']['id'] = (int)$_GET['id'];
            $qry = "INSERT INTO user_promo SET id_user='". $_GET['id'] ."', id_promo='".$_POST['id_promo']."'";
            DB::exec($qry);
        }catch(PDOExeption $e){
            echo $e->getMessage();
        }

   }


}
