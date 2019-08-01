<?php

class ApiObject {
     
    /* ----------- BASIC PARAMS ---------- */
    protected $db;
    protected $keys = [];
    public $user;

    /* ------------------ INIT ------------------ */
    public function __construct($db, $user = false) {
        $this->db = $db;
        if ($user) $this->setUser($user);
        else $this->setUser();
    }

    /* ----------------- METHODS ---------------- */
    public function setUser($obj = []) {
        if(!is_object($obj)) $obj = (object) $obj;
        $this->user = (object) [
            "id" => (isset($obj->id) ? $obj->id : null),
            "mail" => (isset($obj->mail) ? $obj->mail : null),
            "level" => (isset($obj->level) ? $obj->level : null),
        ];
        return $this;
    }

    public function set($obj) {
        if(!is_object($obj)) $obj = (object) $obj;
        foreach ($this->keys as $key) $this->$key = (isset($obj->$key) ? $obj->$key : null);
        return $this;
    }

}