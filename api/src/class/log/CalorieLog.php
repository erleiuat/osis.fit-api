<?php

class CalorieLog {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_log_calorie";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;
    public $title;
    public $calories;
    public $stamp;

    /* --------- PUBLIC EXTENDED PARAMS --------- */

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read($from, $to) {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            `user_id` = :user_id AND 
            `stamp` >= :from AND 
            `stamp` < :to 
            ORDER BY `stamp` DESC
        ");

        $this->db->bind($stmt, 
            ['user_id', 'from', 'to'], 
            [$this->user_id, $from, $to]
        );
        $this->db->execute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formObject($row));
        }

        return $entries;

    }


    public function add() {

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `title`, `calories`, `stamp`) VALUES 
            (:user_id, :title, :calories, :stamp);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'title', 'calories', 'stamp'],
            [$this->user_id, $this->title, $this->calories, $this->stamp]
        );

        $this->db->execute($stmt);
        $this->id = $this->db->conn->lastInsertId();

        return $this->formObject();
        
    }


    public function delete() {

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main . " WHERE 
            `id` = :id AND 
            `user_id` = :user_id
        ");

        $this->db->bind($stmt, 
            ['id', 'user_id'], 
            [$this->id, $this->user_id]
        );
        $this->db->execute($stmt);

        if($stmt->rowCount() !== 1) {
            throw new Exception("delete_failed");
        }

    }


    public function formObject($obj = false) {

        if(!$obj) {
            $obj = (array) $this;
        }

        $timestamp = strtotime($obj['stamp']);

        return [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "calories" => $obj['calories'],
            "stamp" => $obj['stamp'],
            "date" => date('Y-m-d', $timestamp),
            "time" => date('H:i:s', $timestamp)
        ];
        
    }
    
}