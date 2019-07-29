<?php

class Food {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;
    
    public $title;
    public $amount;
    public $calories_per_100;

    public $img_url;
    public $img_url_lazy;
    public $img_phrase;

    /* --------- PUBLIC EXTENDED PARAMS --------- */

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read($id = false) {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " 
            WHERE `user_id` = :user_id
        ");

        $this->db->bind($stmt, ['user_id'], [$this->user_id]);

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
            (`user_id`, `title`, `amount`, `calories_per_100`, `img_url`, `img_lazy`, `img_phrase`) VALUES 
            (:user_id, :title, :amount, :calories_per_100, :img_url, :img_lazy, :img_phrase);
        ");

        $this->db->bind($stmt, 
            ['user_id', 'title', 'amount', 'calories_per_100', 'img_url', 'img_lazy', 'img_phrase'],
            [$this->user_id, $this->title, $this->amount, $this->calories_per_100, $this->img_url, $this->img_lazy, $this->img_phrase]
        );

        $this->db->execute($stmt);
        $this->id = $this->db->conn->lastInsertId();
        
        return $this->formObject();

    }


    public function edit() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_main . " SET 
            `title` = :title, 
            `amount` = :amount, 
            `calories_per_100` = :calories_per_100,
            `img_url` = :img_url,
            `img_lazy` = :img_lazy,
            `img_phrase` = :img_phrase
            WHERE `id` = :id AND `user_id` = :user_id;
        ");

        $this->db->bind($stmt, 
            ['title', 'amount', 'calories_per_100', 'img_url', 
            'img_lazy', 'img_phrase', 'id', 'user_id'],
            [$this->title, $this->amount, $this->calories_per_100, $this->img_url, 
            $this->img_lazy, $this->img_phrase, $this->id, $this->user_id]
        );

        $this->db->execute($stmt);

    }


    public function delete() {

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main . " WHERE 
            `id` = :id AND 
            `user_id` = :user_id
        ");

        $this->db->bind($stmt, ['id', 'user_id'], [$this->id, $this->user_id]);
        $this->db->execute($stmt);

        if($stmt->rowCount() !== 1) {
            throw new Exception("delete_failed");
        }

    }


    public function formObject($obj = false) {

        if(!$obj) {
            $obj = (array) $this;
        }

        if($obj) {
            return [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "amount" => $obj['amount'],
            "caloriesPer100" => $obj['calories_per_100'],
            "imgUrl" => $obj['img_url'],
            "imgLazy" => $obj['img_lazy'],
            "imgPhrase" => $obj['img_phrase']
        ];
        }
        
    }
    
}