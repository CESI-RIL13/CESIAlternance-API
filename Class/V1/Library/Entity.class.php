<?php
namespace Library;
use Library\DB;
use PDO;
class Entity {

    protected $table;
    protected $action = null;
    protected $result = array();

    public function __construct($url) {
        $p = explode('/', $url);
        $this->table = array_shift($p);
        //$this->action = array_shift($p);
        if (count($p) > 0) {
            while (count($p) > 0) {
                $key = array_shift($p);
                if (!is_numeric($key) && empty($this->action)) $this->action = $key;
                else if (is_numeric($key) && empty($_GET['id'])) $_GET['id'] = $key;
                else if (count($p) > 0) {
                    $value = array_shift($p);
                    $_GET[$key] = $value;
                } else $_GET['param'] = $key;
            }
        }
        if (empty($this->action)) $this->action = 'load';
        //echo 'table: ' . $this->table . "\n";
        //echo 'action: ' . $this->action . "\n";
    }
    public function process() {
        // echo 'exist: ' . method_exists($this, $this->action) . "\n";
        if (!method_exists($this, $this->action)) {
            $this->result['error'] = 'Undefined method request!';
        } else {
            $this->{$this->action}();
        }
        return $this->result;
    }

    public function save() {
        $arg = array();
        foreach ($_POST as $key => $value) {
            $arg[] = $key . "='".$value."'";
        }
        
        try {
            
            $qry = (!empty($_GET['id']) ? "UPDATE " . $this->table . " SET " . implode(", ", $arg) . " WHERE id =".$_GET['id'] : "INSERT INTO " . $this->table . " SET " . implode(", ", $arg));
            $rs = DB::query($qry);

            if(empty($_GET['id']))
                $_GET['id'] = DB::lastInsertId();

            $this->result['result']['id'] = (int)$_GET['id'];
        
        } catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }
    }

    public function delete(){
        try{
            $qry  = "UPDATE ".$this->table." SET actif = 0 WHERE id = '".$_GET["id"]."'"; 
            $rs = DB::query($qry);
        } 
        catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }
    }

    public function restore() {
        try{
            $qry  = "UPDATE ".$this->table." SET actif = 1 WHERE id = '".$_GET["id"]."'"; 
            $rs = DB::query($qry);
        } 
        catch (Exception $e) {
            $this->result['error'] = $e->getMessage();
        }        
    }

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

    public function get_id_establishment() {
        $rs = $this->get_id_promo();
        $qry = "SELECT id FROM establishment WHERE id in ";
        $qry .= "(SELECT id_establishment FROM training_establishment WHERE id in ";
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

} 