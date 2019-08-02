<?php

class Food extends ApiObject {

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_food";

    /* ----------- BASIC PARAMS ---------- */
    protected $keys = ['id', 'image', 'title', 'amount', 'calories_per_100'];

    public $id;
    public $image;

    public $title;
    public $amount;
    public $calories_per_100;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `image_id`, `title`, `amount`, `calories_per_100`) VALUES 
            (:user_id, :image_id, :title, :amount, :calories_per_100);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'image_id', 'title', 'amount', 'calories_per_100'],
            [$this->user->id, $this->image->id, $this->title, $this->amount, $this->calories_per_100]
        )->execute($stmt);

        $this->id = $this->db->conn->lastInsertId();
        return $this;

    }

    public function edit() {

        /* TODO */

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

    public function read($id) {
        if(!$id) $id = $this->id;
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id AND id = :id
        ");
        $this->db->bind($stmt, 
            ['user_id', 'id'],
            [$this->user->id, $id]
        )->execute($stmt);

        if ($stmt->rowCount() !== 1) throw new ApiException(404,'entry_not_found', 'food');

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->set([
            'id' => $row['id'],
            'image' => $row['image_id'],
            'title' => $row['title'],
            'amount' => $row['amount'],
            'calories_per_100' => $row['calories_per_100']
        ]);
        return $this;

    }

    public function readAll() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'],
            [$this->user->id]
        )->execute($stmt);

        if ($stmt->rowCount() < 1) throw new Exception('no_entries_found', 204);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getObject($obj = false) {
        
        if(!$obj) $obj = (array) $this;
        else if(!is_array($obj)) $obj = (array) $obj;

        return (object) [
            "id" => $obj['id'],
            "title" => $obj['title'],
            "amount" => $obj['amount'],
            "caloriesPer100" => $obj['calories_per_100'],
            "image" => (isset($obj['image']) ? $obj['image'] : false)
        ];
        
    }
    
}