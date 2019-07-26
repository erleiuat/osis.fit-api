<?php

class FoodFavorite {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food_favorite";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;
    
    public $title;
    public $amount;
    public $calories_per_100;
    public $information;
    public $source;

    public $img_url;
    public $img_lazy;
    public $img_phrase;


    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read(){

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main." 
            WHERE `user_id` = :user_id
        ");

        $this->db->stmtBind($stmt, ['user_id'], [$this->user_id]);
        $this->db->stmtExecute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formObject($row));
        }

        return $entries;

    }

    public function toggle(){

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main." WHERE 
            `user_id` = :user_id AND
            `id` = :id
        ");

        $this->db->stmtBind($stmt, 
            ['user_id', 'id'], 
            [$this->user_id, $this->id]
        );
        $this->db->stmtExecute($stmt);

        if($stmt->rowCount() === 1){

            $stmt = $this->db->conn->prepare("
                DELETE FROM ".$this->t_main." WHERE 
                `user_id` = :user_id AND
                `id` = :id
            ");

            $this->db->stmtBind($stmt, 
                ['user_id', 'id'], 
                [$this->user_id, $this->id]
            );
            $this->db->stmtExecute($stmt);

            if($stmt->rowCount() !== 1) throw new Exception("delete_failed"); 

            return false;

        } else if($stmt->rowCount() === 0){

            $stmt = $this->db->conn->prepare("
                INSERT INTO ".$this->t_main." 
                (`id`, `user_id`, `title`, `amount`, `calories_per_100`, `information`, `source`, `img_url`, `img_lazy`, `img_phrase`) VALUES 
                (:id, :user_id, :title, :amount, :calories_per_100, :information, :source, :img_url, :img_lazy, :img_phrase);
            ");

            $this->db->stmtBind($stmt, 
                ['id', 'user_id', 'title', 'amount', 'calories_per_100', 'information', 'source', 'img_url', 'img_lazy', 'img_phrase'], 
                [$this->id, $this->user_id, $this->title, $this->amount, $this->calories_per_100, $this->information, $this->source, $this->img_url, $this->img_lazy, $this->img_phrase]
            );

            $this->db->stmtExecute($stmt);
            $this->id = $this->db->conn->lastInsertId();

            return true;

        } else {
            throw new Exception("user_fav_toggle_rowCount_error"); 
        }

    }

    public function formObject($obj = false) {

        if(!$obj) $obj = (array) $this;

        return [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "amount" => $obj['amount'],
            "caloriesPer100" => $obj['calories_per_100'],
            "img_url" => $obj['img_url'],
            "img_lazy" => $obj['img_lazy'],
            "img_phrase" => $obj['img_phrase']
        ];
        
    }
    
}