<?php

class Log {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    private $level_options = [
        'trace', 'degub', 'info', 
        'warn', 'error', 'fatal'
    ];

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "log";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user;
    public $level = "trace";
    public $process;
    public $information = "";
    public $identity;
    public $trace;
    public $stamp;

    /* ------------------ INIT ------------------ */
    public function __construct($db, $process) { 

        $this->db = $db;
        $this->process = $process;
        $this->stamp = date('Y-m-d G:i:s');
        $this->user = (object) [
            "id" => null,
            "mail" => null,
            "level" => null
        ];

        $this->trace = (
            "USER_AGENT|" . $_SERVER['HTTP_USER_AGENT'] . "|;" .
            "REMOTE_ADDRESS|" . $_SERVER['REMOTE_ADDR'] . "|;" .
            "REMOTE_PORT|" . $_SERVER['REMOTE_PORT'] . "|;" .
            "PHP_SELF|" . $_SERVER['PHP_SELF'] . "|;"
        );

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->trace .= "X_FORWARDED_FOR|" . $_SERVER['HTTP_X_FORWARDED_FOR'] . "|;";
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->trace .= "REFERER|" . $_SERVER['HTTP_REFERER'] . "|;";
        }
        
    }

    /* ----------------- METHODS ---------------- */

    public function setUser($obj) {
        $keys = ['id', 'mail', 'level'];
        if(!is_object($obj)) $obj = (object) $obj;
        foreach ($keys as $key) $this->user->$key = (isset($obj->$key) ? $obj->$key : null);
        return $this;
    }
    
    public function setStatus($level, $info) {
        $this->level = $level;
        $this->addInfo($info);
    }

    public function addInfo($info) {
        $this->information .= $info . "; ";
    }

    public function write() {

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `level`, `process`, `information`, `identity`, `trace`, `stamp`) VALUES 
            (:user_id, :level, :process, :information, :identity, :trace, :stamp);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'level', 'process', 'information', 'identity', 'trace', 'stamp'], 
            [$this->user->id, $this->level, $this->process, $this->information, $this->identity, $this->trace, $this->stamp]
        );

        $this->db->execute($stmt);

    }
    
}