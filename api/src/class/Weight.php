<?php

class Weight extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_weight";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'weight', 'stamp', 'date', 'time'];

    public $id;
    public $weight;
    public $stamp;
    public $date;
    public $time;

    /* ----------------- METHODS ---------------- */
    public function create() {

        if(!$this->stamp) $this->stamp = date('Y-m-d H:i:s', strtotime($this->date." ".$this->time));

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `weight`, `stamp`) VALUES 
            (:user_id, :weight, :stamp);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'weight', 'stamp'],
            [$this->user->id, $this->weight, $this->stamp]
        )->execute($stmt);

        $this->id = $this->db->conn->lastInsertId();
        return $this;

    }

    public function edit() {

        /* TODO? */

    }

    public function delete() {

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main." WHERE 
            id = :id AND 
            user_id = :user_id 
        ");
        $this->db->bind($stmt, 
            ['id', 'user_id'],
            [$this->id, $this->user->id]
        )->execute($stmt);

        if($stmt->rowCount() !== 1) throw new Exception('entry_not_found', 404);
        return $this;

    }

    public function read() {
        
        /* TODO Read unique by id
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id AND
            stamp >= CONCAT(:date, ' 00:00:00') AND 
            stamp <= CONCAT(:date, ' 23:59:59')
        ");

        $this->db->bind($stmt, 
            ['user_id', 'date'],
            [$this->user->id, $date]
        )->execute($stmt);

        if ($stmt->rowCount() > 1) throw new Exception('no_entries_found', 404);
        */
        return $this;

    }

    public function get($from = false, $to = false) {

        if(!$from) $from = '1990-01-01';
        if(!$to) $to = date('Y-m-d', time());

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id AND
            stamp >= CONCAT(:from, ' 00:00:00') AND 
            stamp <= CONCAT(:to, ' 23:59:59')
        ");

        $this->db->bind($stmt, 
            ['user_id', 'from', 'to'],
            [$this->user->id, $from, $to]
        )->execute($stmt);

        if ($stmt->rowCount() < 1) throw new Exception('no_entries_found', 204);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getObject($obj = false) {
        
        if(!$obj) $obj = (array) $this;
        else if(!is_array($obj)) $obj = (array) $obj;

        $timestamp = strtotime($obj['stamp']);
        return (object) [
            "id" => $obj['id'],
            "weight" => $obj['weight'],
            "date" => date('Y-m-d', $timestamp),
            "time" => date('H:i', $timestamp),
            "stamp" => $obj['stamp']
        ];
        
    }
    
}