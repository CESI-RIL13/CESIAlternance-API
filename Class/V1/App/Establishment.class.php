<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Establishment extends Entity {

    public function load() {

        $qry  = "SELECT `e`.`id`, `e`.`name`, `e`.`adresse`, `e`.`phone`, `e`.`fax`, `e`.`alias` 
                FROM `user_promo` AS `up` 
                LEFT OUTER JOIN `promo` AS `p` ON `up`.`id_promo` = `p`.`id` 
                LEFT OUTER JOIN `training` AS `t` ON `p`.`id_training_establishment` = `t`.`id`
                LEFT OUTER JOIN `training_establishment` AS `te` ON `t`.`id` = `te`.`id_training`
                LEFT OUTER JOIN `establishment` AS `e` ON `e`.`id` = `te`.`id_establishment`
                WHERE e.actif = 1 AND `up`.`id_user` = " .Token::getUserId() ;

        if($_GET['id'] > 0)
            $qry .= " AND e.id =". $_GET['id'];

        $qry  .= " GROUP BY `e`.`id`
                ORDER BY `e`.`name` DESC";

        //$this->result['qry'] = $qry;

        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $this->result['result'][] = $rw;
            }
        } else {
            $this->result['error'] = 'No Establishment Found';
        }

    }
} 