<?php
namespace App;

use Library\Entity;
use Library\Token;
use Library\DB;
use PDO;

class User extends Entity {

    public function load() {
        $qry = "SELECT `u`.`id`, `u`.`name`, `u`.`role`, `up`.`id_promo`, `u`.`picture_path`, `u`.`phone`, `u`.`email` FROM `user` AS `u` 
                LEFT JOIN `user_promo` AS `up` ON `u`.`id` = `up`.`id_user` 
                WHERE `u`.`token` = '" . Token::getToken() . "' AND u.actif = 1 LIMIT 0 , 1";
        $rs = DB::query($qry);
        if ($rs->rowCount() == 1) {
            $this->result['result'] = $rs->fetch(PDO::FETCH_ASSOC);
            $this->result['result']['id'] = (int) $this->result['result']['id'];
			$qry = "SELECT * FROM link WHERE user_id = ".$this->result['result']['id'];
			$rs = DB::query($qry);
			$this->result['links'] = array();
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
                $qry .= " WHERE actif = 1 AND " . implode(" AND ",$whereClause);
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

   public function save() {
        if(isset($_POST['id_promo'])) {
            $id_promo = $_POST['id_promo'];
            unset($_POST['id_promo']);
        }
        parent::save();

        if(empty($_GET['id']) && isset($id_promo) && !empty($id_promo))
            $this->addPromo($id_promo);
   }

   public function addPromo($id_promo = 0) {

        if(empty($id_promo) && !empty($_POST['id_promo']))
            $id_promo = $_POST['id_promo'];
        else if(empty($id_promo))
            throw new \Exception('no promo id');

        try {
            $qry = "INSERT INTO user_promo SET id_user =".(isset($_GET['id']) ? $_GET['id'] : Token::getUserId()).", id_promo=".$id_promo;
            DB::exec($qry);
           /* if(!DB::exec($qry))
                throw new \Exception('error occur during request');*/
        } catch (Exception $e) {
            if($e->getCode() == 1062) {
                $this->result['errorCode'] = $e->getCode();
                $this->result['error'] = "user already link to this promo";
            } else
                $this->result['error'] = $e->getMessage();
        }
   }

	public function saveLink(){
		$isnew = !isset($_GET['id']) && $_GET['id'] > 0;
		$qry = $isnew ? "INSERT INTO link SET " : "UPDATE link SET ";
		foreach($_POST as $key => $value)
			$field[] = $key."='".$value."'";
		$qry.=implode(", ",$field);
		if(!$isnew) $qry.=" WHERE id=".$_GET['id'];

		if(!DB::exec($qry)){
			throw new \Exception('Impossible de sauvegarder un lien.');
		}

		$this->result['result']['id'] = (int)($isnew ? DB::lastInsertId() : $_GET['id']);
	}

	public function deleteLink(){
		$qry= "DELETE FROM link WHERE id='".$_GET['id']."'";

		if(!DB::exec($qry)){
			throw new \Exception('error occur during request');
		}

		$this->result["success"]=true;
	}

	public function listLink(){
		$qry="SELECT * FROM link WHERE user_id=".$_GET['id'];

		$rs=DB::query($qry);
		if($rs->rowCount() > 0){
			while($rw=$rs->fetch(PDO::FETCH_ASSOC)){
				$rw['id'] = (int)$rw['id'];
				$this->result['result'][] = $rw;
			}
		}
	}	

    public function picture(){
        $path = "../" . $_POST['path'];
        if(!is_dir($path)){
            $s = mkdir($path);
            chmod($path, 0777);
            $this->result['create'] = $s;
        }
        foreach ($_FILES as $file) {
            $tmp = explode(".",$file["name"]);
            $ext = array_pop($tmp);
			$endpath =$_POST['user_id'] . "." .strtolower($ext);
            $path .=  $endpath;
            $success = move_uploaded_file($file["tmp_name"], $path); 
            break;
        }
        if($success){
            $qry = "UPDATE user SET picture_path = '".$endpath."' WHERE id ='".$_POST['user_id']."'";            
            if(!DB::exec($qry)) {
                throw new \Exception('error occur during request');
            }
        }
        $this->result['success'] = $success;
		$this->result['result']['picture_path'] = $endpath;
    }
}
