<?php
namespace App;

use Library\Entity;
use Library\Token;
use Library\DB;
use PDO;
use PDOException;

class Promo extends Entity {

    public function load() {
        $qry = "SELECT p.id, p.begin, p.end, p.number, p.code, p.id_planning, CONCAT(t.alias,' ',LPAD(p.number,2,'0')) AS name
				FROM promo p
				JOIN training_establishment te ON p.id_training_establishment = te.id
				JOIN training t ON te.id_training = t.id
				JOIN user_promo up ON p.id = up.id_promo
				JOIN user u ON up.id_user = u.id
				WHERE u.token = '" . Token::getToken() ."'";

        if($_GET['training'] > 0)
            $qry .= " AND t.id =". $_GET['training'];

        try{
        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
        	$result = array();
        	while ($rw = $rs->fetch(PDO::FETCH_ASSOC)) $result[] = $rw;
            $this->result['result'] = $result;
        } else {
            $this->result['error'] = 'No Promotion Found';
        }
        } catch(PDOException $e){
            echo $e->getMessage();
        }

    }

}