<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Training extends Entity {

    public function load() {

        $admin = isset($_GET['admin']) && $_GET['admin'];
        if (!$admin) {
            $qry  = "SELECT `t`.`id`, `t`.`name`, `t`.`alias`, `t`.`duration` 
                    FROM `user_promo` AS `up` 
                    LEFT OUTER JOIN `promo` AS `p` ON `up`.`id_promo` = `p`.`id` 
                    LEFT OUTER JOIN `training` AS `t` ON `p`.`id_training_establishment` = `t`.`id`
                    WHERE `up`.`id_user` = " .Token::getUserId() ;
            if (!empty($_GET['id'])) 
                $qry .= " AND t.id = ". $_GET['id'];
        } else {
            $qry  = "SELECT `t`.`id`, `t`.`name`, `t`.`alias`, `t`.`duration`  
                    FROM `training` AS `t` 
                    WHERE 1";
            if (!empty($_GET['id'])) 
                $qry .= " AND t.id = ". $_GET['id'];
        }

        $qry  .= " GROUP BY `t`.`id`
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

    public function establishment() {
        $admin = isset($_GET['admin']) && $_GET['admin'];
        if ($admin && isset($_GET['id'])) {
            $qry = "SELECT `e`.`id`, `e`.`name`, 
                        IF((SELECT `e`.`id` FROM training_establishment AS `t` 
                            WHERE `t`.`id_training` = ".$_GET['id']." AND `t`.`id_establishment` = `e`.`id`) , 1, 0) 
                        AS training
                    FROM establishment as e";

            $rs = DB::query($qry);
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $this->result['result'][] = $rw;
            }
        } else {
            $this->result['error'] = 'Admin only or id empty';
        }
    }

    public function save() {
        if ($_GET['id'] == 0) {
            $qry = "INSERT INTO training ";
        } else {
            $qry = "UPDATE training ";
        }
        $qry .= "SET `name` = '".$_POST['name']."', `alias` = '".$_POST['alias']."', `duration` = ".$_POST['duration'];
        if ($_GET['id'] > 0) {
            $qry .= " WHERE `id` = ".$_GET['id'];
        }
        $rs = DB::exec($qry);

        if ($_GET['id'] == 0) {
            $rs = DB::query("SELECT LAST_INSERT_ID() AS id FROM training LIMIT 0,1");
            while($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $_GET['id'] = $rw['id'];
            }
        }
        
        if (count($_POST['establishments'])>0) {
            $rs = DB::exec("DELETE FROM `training_establishment` WHERE `id_training` = ".$_GET['id']);
            for ($i = 0; $i < count($_POST['establishments']); $i++) {
                $rs = DB::exec("INSERT INTO `training_establishment` SET `id_training` = ".$_GET['id'].", `id_establishment` = ".$_POST['establishments'][$i]);
            }
        }
    }

    public function delete() {
        $rs = DB::exec("DELETE FROM `training_establishment` WHERE `id_training` = ".$_GET['id']);
        $rs = DB::exec("DELETE FROM `training` WHERE `id` = ".$_GET['id']);
    }
}
