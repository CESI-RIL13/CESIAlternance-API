<?php
namespace App;

use Library\Entity;
use Library\Token;
use Library\DB;
use PDO;
use PDOException;
use App\Training;

class Promo extends Entity {

    public function load() {
        $qry = "SELECT p.id, p.begin, p.end, p.number, p.code, p.id_planning, CONCAT(t.alias,' ',LPAD(p.number,2,'0')) AS name
				FROM promo p
				JOIN training_establishment te ON p.id_training_establishment = te.id
				JOIN training t ON te.id_training = t.id
				JOIN user_promo up ON p.id = up.id_promo
				JOIN user u ON up.id_user = u.id
				WHERE u.token = '" . Token::getToken() ."'";

        if(!empty($_GET['id']))
            $qry .= " AND t.id =". $_GET['id'];
        if(!empty($_GET['id_training']))
            $qry .= " AND te.id=".$_GET['id_training'];

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

    public function save() {
        
        $training = new Training();

        if(isset($_POST['id_training'])) {
            $id_training = $_POST['id_training'];
            unset($_POST['id_training']);
        }

        if(isset($_POST['id_establishment'])) {
            $id_establishment = $_POST['id_establishment'];
            unset($_POST['id_establishment']);
            $training->getTrainingEstablishment($id_training,$id_establishment);            
        } else if(empty($_POST['id_establishment']) && Token::getUserRole() == "IF") {
            $training->getTrainingEstablishment($id_training);
        } else {
            throw new \Exception('No establishment id');
        }


        if(empty($training->result))
            throw new \Exception('No establishment define for this training');

        $_POST['id_training_establishment'] = $training->result[0]['id'];
        
        parent::save();
        $this->addTrainingToEstablishement($id_training);
    }

}