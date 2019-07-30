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

    public function set($obj) {

        $keys = [
            'id', 'mail', 'level', 
            'firstname', 'lastname', 'birth', 'height', 'gender',
            'aim_weight', 'aim_bmi', 'aim_date'
        ];

        if(!is_object($obj)) $obj = (object) $obj;

        foreach ($keys as $key) {
            $this->$key = (isset($obj->$key) ? $obj->$key : null);
        }

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

        return [
            "firstname" => $obj['firstname'],
            "lastname" => $obj['lastname'],
            "birthdate" => $obj['birth'],
            "height" => $obj['height'],
            "gender" => $obj['gender'],
            "aims" => [
                "weight" => $obj['aim_weight'],
                "date" => $obj['aim_date']
            ]
        ];
        
    }
    
}