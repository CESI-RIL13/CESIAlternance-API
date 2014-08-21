<?php
namespace Library;

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

} 