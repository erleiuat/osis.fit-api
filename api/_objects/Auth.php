<?php

class Auth {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_aim = "user_aim";
    private $t_detail = "user_detail";
    private $t_status = "user_status";
    private $v_state = "v_user_state";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $user_id;

    public $mail;
    public $password;
    public $level = "user";

    public $firstname;
    public $lastname;
    public $gender;
    public $height;
    public $birth;

    public $state;
    public $refresh_jti;
    public $verify_code;


    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function register() {

        // Insert into t_main
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main." 
            (`mail`, `password`, `level`) VALUES
            (:mail, :password, :level);
        ");
        $this->db->bind($stmt, 
            ['mail', 'password', 'level'], 
            [$this->mail, password_hash($this->password, Env::sec_encryption), $this->level]
        );
        $this->db->execute($stmt);

        $this->user_id = $this->db->conn->lastInsertId();


        // Insert into t_verification
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_status." 
            (`user_id`, `verify_code`) VALUES 
            (:user_id, :verify_code);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'verify_code'], 
            [$this->user_id, password_hash($this->verify_code, Env::sec_encryption)]
        );
        $this->db->execute($stmt);


        // Insert into t_detail
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_detail." 
            (`user_id`, `firstname`, `lastname`) VALUES 
            (:user_id, :firstname, :lastname);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'firstname', 'lastname'], 
            [$this->user_id, $this->firstname, $this->lastname]
        );
        $this->db->execute($stmt);

        // Insert into t_aim
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_aim." 
            (`user_id`) VALUES 
            (:user_id);
        ");
        $this->db->bind($stmt, 
            ['user_id'], 
            [$this->user_id]
        );
        $this->db->execute($stmt);

    }

    public function checkState() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_state." 
            WHERE mail = :mail
        ");
        $this->db->bind($stmt, ['mail'], [$this->mail])->execute($stmt);

        $this->state = false;
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            $this->refresh_jti = $row['refresh_jti'];
            $this->state = $row['state'];
        }

        return $this;
        
    }

    public function updateStatus() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_status." SET 
            `refresh_jti` = :refresh_jti,
            `login_stamp` = now() 
            WHERE `user_id` = :user_id
        ");

        $this->db->bind($stmt, 
            ['user_id', 'refresh_jti'], 
            [$this->user_id, password_hash($this->refresh_jti, Env::sec_encryption)]
        )->execute($stmt);

    }
    
    public function passwordLogin($password) {

        $stmt = $this->db->conn->prepare("
            SELECT Password FROM ".$this->t_main." 
            WHERE id = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'], 
            [$this->user_id]
        );
        $this->db->execute($stmt);

        $password_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["Password"];

        if ($stmt->rowCount() === 1 && password_verify($password, $password_hash)) {
            return true;
        }
            
        return false;

    }

    public function verifyMail($code) {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_status." 
            WHERE user_id = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'], 
            [$this->user_id]
        );

        $this->db->execute($stmt);
        $code_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["verify_code"];

        if ($stmt->rowCount() === 1 && password_verify($code, $code_hash)) {
        
            $stmt = $this->db->conn->prepare("
                UPDATE ".$this->t_status." SET 
                `state` = 'verified', 
                `verify_stamp` = now() 
                WHERE `user_id` = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        } else throw new Exception('verification_error', 403);

    }
    
    public function readToken() {
        
        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_main." 
            WHERE id = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        if ($stmt->rowCount() === 1) {
            $this->level = ($stmt->fetch(PDO::FETCH_ASSOC))['level'];
        } else {
            throw new Exception('mail_not_found', 500);
        }

        return $this;

    }

    public function disable() {

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_status." SET 
            `state` = 'deleted',
            `deleted` = 'true'
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_main." SET 
            `mail` = concat('DELETED:', mail, '|ID:', id),
            `password` = concat('DELETED:', password, '|ID:', id)
            WHERE `id` = :user_id 
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

    }
    
}