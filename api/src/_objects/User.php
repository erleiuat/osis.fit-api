<?php

class User {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_aim = "user_aim";
    private $t_detail = "user_detail";
    private $v_info = "v_user_info";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $mail;
    public $type;

    public $firstname;
    public $lastname;
    public $birth;
    public $height;
    public $gender;

    public $aim_weight;
    public $aim_bmi;
    public $aim_date;

    /* --------- PUBLIC EXTENDED PARAMS --------- */

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function read() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_info." 
            WHERE id = :id
        ");
        $this->db->bind($stmt, ['id'], [$this->id]);
        $this->db->execute($stmt);

        if ($stmt->rowCount() !== 1) throw new Exception('entry_not_found', 404);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->formObject($row);

    }


    public function editAims() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_aim." SET 
            `weight` = :aim_weight, 
            `date` = :aim_date 
            WHERE `user_id` = :id;
        ");

        $this->db->bind($stmt, 
            ['aim_weight', 'aim_date', 'id'],
            [$this->aim_weight, $this->aim_date, $this->id]
        );

        $this->db->execute($stmt);

    }

    public function editProfile() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_detail." SET 
            `firstname` = :firstname,
            `lastname` = :lastname,
            `birth` = :birth,
            `gender` = :gender,
            `height` = :height
            WHERE `user_id` = :id;
        ");

        $this->db->bind($stmt, 
            ['firstname', 'lastname', 'birth', 'gender', 'height', 'id'],
            [$this->firstname, $this->lastname, $this->birth, $this->gender, $this->height, $this->id]
        );

        $this->db->execute($stmt);

    }

    public function formObject($obj = false) {

        if($obj) return [
            "id" => $obj['id'],
            "firstname" => $obj['firstname'],
            "lastname" => $obj['lastname'],
            "birthdate" => $obj['birth'],
            "height" => $obj['height'],
            "gender" => $obj['gender'],
            "aims" => [
                "weight" => $obj['aim_weight'],
                "bmi" => $obj['aim_bmi'],
                "date" => $obj['aim_date']
            ]
        ];
        
    }
    
}