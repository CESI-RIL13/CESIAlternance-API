<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Training extends Entity {

    public function load() {

        // TODO:
        // get id_establishment
        // statut de la formation (en cours, done)

        if (Token::getUserRole() == "IF") {

            $qry = "(SELECT `t`.`id` , `t`.`name` , `t`.`alias` , `t`.`duration` 
                FROM training as t
                LEFT JOIN training_establishment as te
                    ON t.id = te.id_training
                LEFT JOIN  establishment as e
                    ON te.id_establishment = e.id
                LEFT JOIN user as u
                    ON e.id = u.id_establishment
                WHERE t.actif = 1 AND te.actif = 1 AND `u`.`id` = ".Token::getUserId()."
                GROUP BY `t`.`id` 
                ORDER BY `t`.`id` DESC)";        }
        else {
            $qry  = "SELECT `t`.`id`, `t`.`name`, `t`.`alias`, `t`.`duration`
                FROM `user_promo` AS `up` 
                LEFT JOIN `promo` AS `p` ON `up`.`id_promo` = `p`.`id` 
                LEFT JOIN `training` AS `t` ON `p`.`id_training_establishment` = `t`.`id`
                LEFT JOIN `establishment` AS `e` ON `t`.`id` = `t`.`id`
                LEFT JOIN `training_establishment` AS `te` ON `te`.`id_training` = `t`.`id`
                WHERE t.actif = 1 AND te.actif = 1 AND `up`.`id_user` = " .Token::getUserId() ;
            
            if (!empty($_GET['id'])) 
                $qry .= " AND t.id = ". $_GET['id'];

            $qry  .= " GROUP BY `t`.`id`
                ORDER BY `t`.`id` DESC";
        }

        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $this->result['result'][] = $rw;
            }
        } else {
            $this->result['error'] = 'No Training Found';
        }

    }

    public function save() {

        $arg = array();
        foreach ($_POST as $key => $value) {
            $arg[] = $key . "='".$value."'";
        }

        try {
            $qry = (!empty($_GET['id']) && $_GET['id'] != 0 ? "UPDATE " . $this->table . " SET " . implode(", ", $arg) . " WHERE id =".$_GET['id'] : "INSERT INTO " . $this->table . " SET " . implode(", ", $arg));
            $rs = DB::query($qry); 

            // si nouvel formation
            if (empty($_GET['id'])) {
                $_GET['id'] = DB::lastInsertId();
                $qry = "INSERT INTO training_establishment SET id_training = ".(int)$_GET['id'].", id_establishment = ".(int)$this->get_id_establishment();
                $rs = DB::exec($qry);
                $this->result['result']['id_establishment'] = $this->get_id_establishment();
            }

            $this->result['result']['id'] = (int)$_GET['id'];
            //$this->result['result']['id_establishment'] = $this->get_id_establishment();

        } catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }
    }

    public function check_training($alias, $name) {
        $qry2 = "SELECT * FROM training WHERE alias = '"+$alias+"' OR name = '"+$name+"'";
        $rs2 = DB::query($qry2);
        return $rs2->rowCount();
    }

    public function delete() {
        //parent::delete();
        $this->removeTrainingFromEstablishments($_GET["id"]);
    }

    private function removeTrainingFromEstablishments($id_training = 0,$id_establishment = 0) {
        if(empty($id_training))
            throw new \Exception('no training id');
        
        $qry = "UPDATE `training_establishment` SET actif = 0 WHERE `id_training` = ".$id_training." AND id_establishment = ".(int)$this->get_id_establishment();

        try{
            $rs = DB::exec($qry);
            $this->result['result']['id'] = (int)$_GET['id'];
        } 
        catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }   
    }

    public function getTrainingEstablishment($id_training = 0, $id_establishment = 0) {

        if(isset($_POST['id_training'])) {
            $id_training = $_POST['id_training'];
            unset($_POST['id_training']);
        }

        if(empty($id_training))
            throw new \Exception('no training id');

        if(empty($id_establishment) && isset($_POST['id_establishment'])) {
            $id_establishment = $_POST['id_establishment'];
            unset($_POST['id_establishment']);
        } else if (empty($id_establishment) && Token::getUserRole() == "IF") {
            try {
                $rs = DB::query("SELECT id_establishment FROM user WHERE id =".Token::getUserId());
                $rw = $rs->fetch(PDO::FETCH_ASSOC);
                $id_establishment = $rw['id_establishment'];
            } 
            catch (Exception $e) {
                $this->result['error'] = $e->getMessage();
            }
  
        }

        if(empty($id_establishment))
            throw new \Exception('no establishment id');
        
        $qry = "SELECT * FROM training_establishment WHERE id_training = " . $id_training . " AND id_establishment = ".$id_establishment;

        try {
            $rs = DB::query($qry);

            if ($rs->rowCount() > 0) {
                while($rw = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $this->result[] = array(
                        'id'=>$rw['id'],
                        'id_training'=>$rw['id_training'],
                        'id_establishment'=>$rw['id_establishment'],
                    );
                }
            }
        } 
        catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }
    
    }

}
