<?php

class ApiObject {
     
    /* ----------- BASIC PARAMS ---------- */
    protected $db;
    protected $keys = [];
    public $account;

    /* ------------------ INIT ------------------ */
    public function __construct($db, $account = false) {
        $this->db = $db;
        if ($account) $this->setAccount($account);
        else $this->setAccount();
    }

    /* ----------------- METHODS ---------------- */
    public function setAccount($obj = []) {
        if(!is_object($obj)) $obj = (object) $obj;
        $this->account = (object) [
            "id" => (isset($obj->id) ? $obj->id : null),
            "mail" => (isset($obj->mail) ? $obj->mail : null),
            "username" => (isset($obj->username) ? $obj->username : null)
        ];
    }
    
    public function getAccount(){
        return [
            "id" => $this->account->id,
            "mail" => $this->account->mail,
            "username" => $this->account->username
        ];
    }

    public function set($obj) {
        if(!is_object($obj)) $obj = (object) $obj;
        foreach ($this->keys as $key) {
            if (isset($obj->$key)) $this->$key = $obj->$key;
        };
        return $this;
    }

}