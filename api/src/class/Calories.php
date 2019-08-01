<?php

class Calories extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_calories";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['title', 'calories', 'stamp', 'date', 'time'];

    public $id;
    public $title;
    public $calories;
    public $stamp;
    public $date;
    public $time;

    /* ----------------- METHODS ---------------- */
    public function create() {

        if(!$this->stamp) $this->stamp = date('Y-m-d H:i:s', strtotime($this->date." ".$this->time));

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `title`, `calories`, `stamp`) VALUES 
            (:user_id, :title, :calories, :stamp);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'title', 'calories', 'stamp'],
            [$this->user->id, $this->title, $this->calories, $this->stamp]
        )->execute($stmt);

        $this->id = $this->db->conn->lastInsertId();
        return $this;

    }

    public function read() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_info . " 
            WHERE id = :id
        ");
        $this->db->bind($stmt, ['id'], [$this->id])->execute($stmt);

        if ($stmt->rowCount() !== 1) {
            throw new Exception('entry_not_found', 404);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->set($row);

    }

    public function edit() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_detail . " SET 
            `firstname` = :firstname, 
            `lastname` = :lastname, 
            `gender` = :gender, 
            `height` = :height, 
            `birth` = :birth 
            WHERE `user_id` = :user_id;
        ");
        $this->db->bind($stmt, 
            ['user_id', 'firstname', 'lastname', 'gender', 'height', 'birth'],
            [$this->id, $this->firstname, $this->lastname, $this->gender, $this->height, $this->birth]
        )->execute($stmt);

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_aim . " SET 
            `weight` = :aim_weight, 
            `date` = :aim_date 
            WHERE `user_id` = :user_id;
        ");
        $this->db->bind($stmt, 
            ['user_id', 'aim_weight', 'aim_date'],
            [$this->id, $this->aim_weight, $this->aim_date]
        )->execute($stmt);

    }

    public function getObject($obj = false) {

        if(!$obj) $obj = (array) $this;
        
        else if (is_array($obj)){
            $arr = [];
            while ($val = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($val, $this->getObject($val));
            }
            return $arr;
        }

        $timestamp = strtotime($obj['stamp']);
        return (object) [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "calories" => $obj['calories'],
            "stamp" => $obj['stamp'],
            "date" => date('Y-m-d', $timestamp),
            "time" => date('H:i:s', $timestamp)
        ];
        
    }
    
}