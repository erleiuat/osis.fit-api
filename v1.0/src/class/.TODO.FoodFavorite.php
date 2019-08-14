<?php

// TODO

class FoodFavorite {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food_favorite";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $account_id;
    
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

    public function read() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " 
            WHERE `account_id` = :account_id
        ");

        $this->db->bind($stmt, ['account_id'], [$this->account_id]);
        $this->db->execute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formObject($row));
        }

        return $entries;

    }

    public function toggle() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            `account_id` = :account_id AND
            `id` = :id
        ");

        $this->db->bind($stmt, 
            ['account_id', 'id'], 
            [$this->account_id, $this->id]
        );
        $this->db->execute($stmt);

        if ($stmt->rowCount() === 1) {

            $stmt = $this->db->conn->prepare("
                DELETE FROM ".$this->t_main . " WHERE 
                `account_id` = :account_id AND
                `id` = :id
            ");

            $this->db->bind($stmt, 
                ['account_id', 'id'], 
                [$this->account_id, $this->id]
            );
            $this->db->execute($stmt);

            if($stmt->rowCount() !== 1) {
                throw new Exception("delete_failed");
            }

            return false;

        } else if($stmt->rowCount() === 0){

            $stmt = $this->db->conn->prepare("
                INSERT INTO ".$this->t_main." 
                (`id`, `account_id`, `title`, `amount`, `calories_per_100`, `information`, `source`, `img_url`, `img_lazy`, `img_phrase`) VALUES 
                (:id, :account_id, :title, :amount, :calories_per_100, :information, :source, :img_url, :img_lazy, :img_phrase);
            ");

            $this->db->bind($stmt, 
                ['id', 'account_id', 'title', 'amount', 'calories_per_100', 'information', 'source', 'img_url', 'img_lazy', 'img_phrase'], 
                [$this->id, $this->account_id, $this->title, $this->amount, $this->calories_per_100, $this->information, $this->source, $this->img_url, $this->img_lazy, $this->img_phrase]
            );

            $this->db->execute($stmt);
            $this->id = $this->db->conn->lastInsertId();

            return true;

        } else {
            throw new Exception("user_fav_toggle_rowCount_error"); 
        }

    }

    public function formObject($obj = false) {

        if(!$obj) {
            $obj = (array) $this;
        }

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