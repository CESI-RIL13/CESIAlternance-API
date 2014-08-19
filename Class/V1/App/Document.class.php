<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Document extends Entity {

    public function get_id_promo() {
    	$qry = "SELECT id_promo FROM user_promo WHERE id_user = " . Token::getUserId() . "";
    	
        $result = array();
    	$rs = DB::query($qry);
    	if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $rw['id_promo'];
            }
    	}
        return $result;
    }


    public function get_id_training() {
        $rs = $this->get_id_promo();
    	$qry = "SELECT id FROM training WHERE id in ";
    	$qry .= "(SELECT id_training FROM training_establishment WHERE id in ";
    	$qry .= "(SELECT id_training_establishment FROM promo WHERE id in (" . implode(",", $rs) . ")))";

        $result = array();
    	$rs = DB::query($qry);
    	if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $rw['id'];
            }
        }
        return $result;
    }	

    public function load() {
        $qry  = "SELECT `d`.`id`, `d`.`id_establishment`, `d`.`id_training`, `d`.`id_promo`, `d`.`id_user`, `d`.`name`, `d`.`description`, 
`d`.`path`";
        $qry .= "\nFROM `document` AS `d`";
        $qry .= "\nWHERE `d`.`id_establishment` IN (SELECT `te`.`id_establishment` FROM `user_promo` AS `up` INNER JOIN `promo` AS `p` ON `p`.`id` = 
`up`.`id_promo` INNER JOIN `training_establishment` AS `te` ON `te`.`id` = `p`.`id_training_establishment` WHERE `up`.`id_user` = " . Token::
getUserId() . " GROUP BY `te`.`id_establishment`)";

		if($_GET["id_promo"]){
			$qry .= "\nAND `d`.`id_promo` = '".$_GET["id_promo"]."'";
		}elseif($_GET["id_training"]){
			$qry .= "\nAND `d`.`id_training` = '".$_GET["id_training"]."'";
		}else{
			$qry .= "\nAND `d`.`id_training` = '0' AND `d`.`id_promo` = '0'";
		}
		
		
		
		// {
		// $rs = $this->get_id_promo();
        // $qry .= "\nAND (`d`.`id_promo` = 0 OR `d`.`id_promo` IN (" . implode(",", $rs) . "))";
		// }
		
		// if($_GET["id_training"]){
			// $qry .= "\nAND `d`.`id_training` = '".$_GET["id_training"]."'";
		// }else{
		// $rs = $this->get_id_training();
        // $qry .= "\nAND (`d`.`id_training` = 0 OR `d`.`id_training` IN (" . implode(",", $rs) . "))";
		// }

        $qry .= "\nGROUP BY `d`.`id`";
        $qry .= "\nORDER BY `d`.`id_establishment`, `d`.`id_training`, `d`.`id_promo`";
        $this->result["qry"] = $qry;

        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $this->result['result'][] = $rw;
            }
        } else {
            $this->result['error'] = 'No Document Found';
        }
    }
	
	public function delete() {
		try{
			$qry  = "SELECT CONCAT(path,'/',name) as path FROM document WHERE id = '".$_GET["id"]."'"; 
			//$this->result['qry'] =  $qry;
			$rs = DB::query($qry);
			if ($rs->rowCount() > 0) {
				$rw = $rs->fetch(PDO::FETCH_ASSOC);
				$rw["path"] = "../".$rw["path"];
				$exist = is_file($rw["path"]);
				//$this->result['isfile'] = $exist;
				if ($exist) {
					$rm = unlink($rw["path"]);
					//$this->result['removed'] = $rm;
					if ($rm) {
						$qry = "DELETE FROM document WHERE id = '".$_GET["id"]."'"; 
						$rs = DB::exec($qry);
						$this->result['success'] = true;
					}else{
						$this->result['error'] = "Impossible de supprimer";
			}
				}else{
					$this->result['error'] = "document non trouvé";
			}
			}else{
				$this->result['error'] = "document non référencé";
			}
		} catch (Exception $e) {
        $this->result['error'] = $e->getMessage();
		 }
	
	}

	public function upload() {
		$path = $_POST['path'];
		$path = "../" . $path;
		$this->result['path'] = $path;
		$this->result['is_dir'] = is_dir($path);
		if(!is_dir($path)){
			$s = mkdir($path);
			$this->result['create'] = $s;
		}
      	//$success = move_uploaded_file($_FILES["file"]["tmp_name"], $path . $_FILES["file"]["name"]);
      	//$this->result['success'] = $success;
      	//$this->result['result'] = "Stored in: " . $path . $_FILES["file"]["name"];
      	/*echo "Stored in: " . $path . $_FILES["file"]["name"];	*/	
	
	}
} 