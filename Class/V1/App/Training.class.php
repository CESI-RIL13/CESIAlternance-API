<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Training extends Entity {

    public function load() {

        $qry  = "SELECT `t`.`id`, `t`.`name`, `t`.`alias`, `t`.`duration` 
                FROM `user_promo` AS `up` 
                LEFT JOIN `promo` AS `p` ON `up`.`id_promo` = `p`.`id` 
                LEFT JOIN `training` AS `t` ON `p`.`id_training_establishment` = `t`.`id`
                WHERE `up`.`id_user` = " .Token::getUserId() ."
                GROUP BY `t`.`id`
                ORDER BY `t`.`id` DESC";

        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $this->result['result'][] = $rw;
            }
        } else {
            $this->result['error'] = 'No Training Found';
        }

    }
} 