<?php

class WeightLog {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_log_weight";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;
    public $weight;
    public $stamp;

    /* --------- PUBLIC EXTENDED PARAMS --------- */

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main." 
            WHERE `user_id` = :user_id
            ORDER BY `stamp` DESC
        ");

        $this->db->bind($stmt, ['user_id'], [$this->user_id]);
        $this->db->execute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formObject($row));
        }

        return $entries;

    }

    public function add(){

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main." 
            (`user_id`, `weight`, `stamp`) VALUES 
            (:user_id, :weight, :stamp);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'weight', 'stamp'],
            [$this->user_id, $this->weight, $this->stamp]
        );

        $this->db->execute($stmt);
        $this->id = $this->db->conn->lastInsertId();

        return $this->formObject();
        
    }

    public function delete(){

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main." WHERE 
            `id` = :id AND 
            `user_id` = :user_id
        ");

        $this->db->bind($stmt, ['id', 'user_id'], [$this->id, $this->user_id]);
        $this->db->execute($stmt);

        if($stmt->rowCount() !== 1) throw new Exception("delete_failed"); 

    }

    public function formObject($obj = false) {

        if(!$obj) $obj = (array) $this;

        $timestamp = strtotime($obj['stamp']);

        return [
            "id" => $obj['id'],
            "weight" => $obj['weight'],
            "stamp" => $obj['stamp'],
            "date" => date('Y-m-d', $timestamp),
            "time" => date('H:i:s', $timestamp)
        ];
        
    }
    
}