<?php

class ActivityLog {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_log_activity";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;
    public $title;
    public $duration;
    public $calories;

    /* --------- PUBLIC EXTENDED PARAMS --------- */

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read($from, $to) {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main." WHERE 
            `user_id` = :user_id AND 
            `stamp` >= :from AND 
            `stamp` < :to 
            ORDER BY `stamp` DESC
        ");

        $this->db->stmtBind($stmt, 
            ['user_id','from', 'to'], 
            [$this->user_id, $from, $to]
        );
        $this->db->stmtExecute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formObject($row));
        }

        return $entries;

    }


    public function add(){

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main." 
            (`user_id`, `title`, `duration`, `calories`) VALUES 
            (:user_id, :title, :duration, :calories);
        ");

        $this->db->stmtBind($stmt, 
            ['user_id', 'title', 'duration', 'calories'],
            [$this->user_id, $this->title, $this->duration, $this->calories]
        );

        $this->db->stmtExecute($stmt);
        $this->id = $this->db->conn->lastInsertId();

        return $this->formObject();
        
    }


    public function delete(){

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main." 
            WHERE `id` = :id 
            AND `user_id` = :user_id
        ");

        $this->db->stmtBind($stmt, ['id', 'user_id'], [$this->id, $this->user_id]);
        $this->db->stmtExecute($stmt);

        if($stmt->rowCount() !== 1) throw new Exception("delete_failed"); 

    }


    public function formObject($obj = false) {

        if(!$obj) $obj = (array) $this;

        $timestamp = strtotime($obj['stamp']);

        return [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "duration" => $obj['duration'],
            "calories" => $obj['calories'],
            "stamp" => $obj['stamp'],
            "date" => date('Y-m-d', $timestamp),
            "time" => date('H:i:s', $timestamp)
        ];
        
    }
    
}