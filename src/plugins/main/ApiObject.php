<?php

class ApiObject {
     
    /* ----------- BASIC PARAMS ---------- */
    protected $db;
    protected $keys = [];
    public $account;

    /* ------------------ INIT ------------------ */
    public function __construct($account = false) {
        if ($account) $this->setAccount($account);
        else $this->setAccount();
    }

    /* ----------------- METHODS ---------------- */
    public function setAccount($obj = []) {
        if(!is_object($obj)) $obj = (object) $obj;
        $this->account = (object) [
            "id" => (isset($obj->id) ? $obj->id : null),
            "mail" => (isset($obj->mail) ? $obj->mail : null)
        ];
    }
    
    public function getAccount(){
        return [
            "id" => $this->account->id,
            "mail" => $this->account->mail
        ];
    }

    public function set($obj) {
        if(!is_object($obj)) $obj = (object) $obj;
        foreach ($this->keys as $key) $this->$key = (isset($obj->$key) ? $obj->$key : null);
        return $this;
    }

}