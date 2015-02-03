<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Document extends Entity {

	public function load() {
		$qry  = "SELECT d.id, d.id_establishment, d.id_training, d.id_promo, d.id_user, d.name, d.description, d.path";
		$qry .= "\nFROM document AS d WHERE actif = 1 AND ";

		$where = array();
		
		if(!empty($_GET["id_establishment"])) {
			$rs = $this->get_id_establishment();
			$where[] = "d.id_establishment in(SELECT id FROM establishment WHERE id in(". implode(",", $rs) . "))";		
		}
		
		if(!empty($_GET["id_training"])) {
			$where[] = "d.id_training = '".$_GET["id_training"]."'";
		}
		else
			$where[] = "id_training = 0";
		/*else {
			$rs = $this->get_id_training();
			$qry .= "d.id_training in(SELECT id FROM training WHERE id in(". implode(",", $rs) . ")) AND ";
		}*/
		
		if(!empty($_GET["id_promo"])) {
			$where[] = "d.id_promo = '".$_GET["id_promo"]."'";
		}
		else
			$where[] = "id_promo = 0";
		/*else {
			$rs = $this->get_id_promo();
			$qry .= "d.id_promo in(SELECT id FROM promo WHERE id in(". implode(",", $rs) . ")) ";
		}*/
		
		if(!empty($_GET["id_user"])) {
			$where[] = "d.id_user = '".Token::getUserId()."'";
		}
		else
			$where[] = "id_user = 0";
		
		if(count($where) > 0)
			$qry .= implode(" AND ", $where);
			
		$qry .= "\nORDER BY d.id DESC";
		
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
			$qry  = "SELECT path, id_owner FROM document WHERE id = '".$_GET["id"]."'"; 
			//$this->result['qry'] =  $qry;
			$rs = DB::query($qry);
			if ($rs->rowCount() > 0) {
				$rw = $rs->fetch(PDO::FETCH_ASSOC);
				$rw["path"] = "../".$rw["path"];
				$exist = is_file($rw["path"]);
				//$this->result['isfile'] = $exist;
				if($exist) {
					if(($rw["id_owner"] == Token::getUserId()) || (Token::getUserRole() == "IF")) {
						//$rm = unlink($rw["path"]);
						//$this->result['removed'] = $rm;
						//if($rm) {
						$qry = "UPDATE document SET actif = 0 WHERE id = '".$_GET["id"]."'"; 
						$rs = DB::exec($qry);
						$this->result['success'] = true;
						//}	
						//else {
						//	$this->result['error'] = "Impossible de supprimer";
						//}
					}
					else {
						$this->result['error'] = "Vous n'avez pas les droits pour supprimer ce document";
					}
				}
				else {
					$this->result['error'] = "document ".$rw["path"]." non trouvé";
				}
			}
			else {
				$this->result['error'] = "document non référencé";
			}
		} 
		catch (Exception $e) {
			$this->result['error'] = $e->getMessage();
		}
	}

	public function upload() {
		$success = false;
		if (!empty($_POST["titre"]) && count($_FILES) > 0) {
			$path = '../document/' . Token::getUserId();
			$this->result['path'] = $path;
			$this->result['is_dir'] = is_dir($path);
			if(!is_dir($path)){
				$s = mkdir($path);
				chmod($path, 0777);
				$this->result['create'] = $s;
			}
			foreach ($_FILES as $file) {
				$tmp = explode(".",$file["name"]);
				$ext = array_pop($tmp);
				$path .=  "/" . uniqid() . "." .strtolower($ext);
				$this->result['name'] = $file["name"];
				$success = move_uploaded_file($file["tmp_name"], $path); 
				break;
			}
			if($success){
				$qry = "INSERT INTO document SET id_owner='".Token::getUserId()."'";
				
				$where = array();
				
				if((Token::getUserRole() == "IF") && (!empty($_POST["id_establishment"]))) {
					$rs = $this->get_id_establishment();
					$where[] = "id_establishment ='".$rs[0]."'";
				}
				
				if(!empty($_POST["id_training"]))
					$where[] = "id_training ='" .$_POST["id_training"]."'" ;
					
				if(!empty($_POST["id_promo"]))
					$where[] = "id_promo ='" .$_POST["id_promo"]."'";
					
				if(!empty($_POST["id_user"]))
					$where[] = "id_user ='" .$_POST["id_user"]."'";
					
				if(count($where) > 0)
					$qry .= ", ".implode(", ", $where);
					
				$qry .= ", name='".$_POST["titre"]."', description='".$_POST["description"]."', path='".str_replace("../", "", $path)."'";
				
				if(!DB::exec($qry)) {
					throw new \Exception('error occur during request');
				}
			}
		}
		$this->result['success'] = $success;
		/*echo "Stored in: " . $path . $_FILES["file"]["name"];	*/	
	
	}
} 