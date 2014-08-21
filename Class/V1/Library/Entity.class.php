<?php
namespace Library;

class Entity {

    protected $table;
    protected $action = null;
    protected $result = array();

    public function __construct($url) {
        $p = explode('/', $url);
        $this->table = array_shift($p);
        $this->action = array_shift($p);
        if (count($p) > 0) {
            for ($i=0; $i<count($p); $i++) {
                if ($i == 0 && is_numeric($p[$i])) $_GET['id'] = $p[$i];
                else {
                    $_GET[$p[$i]] = $p[$i+1];
                    $i++;
                }
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