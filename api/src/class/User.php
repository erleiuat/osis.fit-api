<?php

class User {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_detail = "user_detail";
    private $t_aim = "user_aim";
    private $v_info = "v_user_info";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $mail;
    public $level;

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
    public function __construct($db, $userid = false) { 
        $this->db = $db;
        if ($userid) {
            $this->id = $userid;
            $this->read();
        }
    }

    /* ----------------- METHODS ---------------- */

    public function create() {

        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_main . " 
            (`mail`, `level`) VALUES
            (:mail, :level);
        ");
        $this->db->bind($stmt, 
            ['mail', 'level'], 
            [$this->mail, $this->level]
        )->execute($stmt);
        $this->id = $this->db->conn->lastInsertId();

        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_detail . " 
            (`user_id`, `firstname`, `lastname`) VALUES 
            (:user_id, :firstname, :lastname);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'firstname', 'lastname'], 
            [$this->id, $this->firstname, $this->lastname]
        )->execute($stmt);

        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_aim . " 
            (`user_id`) VALUES (:user_id);
        ");
        $this->db->bind($stmt, 
            ['user_id'], [$this->id]
        )->execute($stmt);

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
        $this->mail = ($this->mail ? $this->mail : $row['mail']);
        $this->level = $row['level'];

        $this->firstname = $row['firstname'];
        $this->lastname = $row['lastname'];
        $this->birth = $row['birth'];
        $this->height = $row['height'];
        $this->gender = $row['gender'];

        $this->aim_weight = $row['aim_weight'];
        $this->aim_bmi = $row['aim_bmi'];
        $this->aim_date = $row['aim_date'];

        return $this;

    }

    public function editAims() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_aim . " SET 
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
            UPDATE ".$this->t_detail . " SET 
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

        if($obj) {
            return [
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
    
}