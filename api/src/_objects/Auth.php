<?php

class Auth {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_aim = "user_aim";
    private $t_detail = "user_detail";
    private $t_status = "user_status";
    private $t_refresh = "user_refresh_jti";

    private $v_state = "v_user_state";
    private $v_token = "v_user_token";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $user_id;

    public $mail;
    public $password;
    public $level = "user";

    public $state;
    public $pw_stamp;
    public $pw_code;
    public $verify_code;

    public $refresh_jti;
    public $refresh_phrase;

    public $firstname; //TODO: Move to other Class
    public $lastname;
    public $gender;
    public $height;
    public $birth;

    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function register() {

        // Insert into t_main 
        // TODO: Move to other Class
        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_main." 
            (`mail`, `password`, `level`) VALUES
            (:mail, :password, :level);
        ");
        $this->db->bind($stmt, 
            ['mail', 'password', 'level'], 
            [$this->mail, password_hash($this->password, Env::sec_encryption), $this->level]
        )->execute($stmt);

        $this->user_id = $this->db->conn->lastInsertId();


        // Insert into t_status
        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_status." 
            (`user_id`, `verify_code`, `pw_stamp`) VALUES 
            (:user_id, :verify_code, now());
        ");
        $this->db->bind($stmt, 
            ['user_id', 'verify_code'], 
            [$this->user_id, password_hash($this->verify_code, Env::sec_encryption)]
        )->execute($stmt);


        // Insert into t_detail
        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_detail." 
            (`user_id`, `firstname`, `lastname`) VALUES 
            (:user_id, :firstname, :lastname);
        ");
        $this->db->bind($stmt, 
            ['user_id', 'firstname', 'lastname'], 
            [$this->user_id, $this->firstname, $this->lastname]
        )->execute($stmt);

        // Insert into t_aim
        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_aim." 
            (`user_id`) VALUES 
            (:user_id);
        ");
        $this->db->bind($stmt, 
            ['user_id'], [$this->user_id]
        )->execute($stmt);

    }

    public function checkStatus() {

        if($this->mail){
            $stmt = $this->db->prepare("
                SELECT * FROM ".$this->v_state." 
                WHERE `mail` = :mail
            ");
            $this->db->bind($stmt, ['mail'], [$this->mail])->execute($stmt);
        } else if ($this->user_id){
            $stmt = $this->db->prepare("
                SELECT * FROM ".$this->v_state." 
                WHERE `user_id` = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);
        }

        $this->state = false;
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            $this->state = $row['state'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
        }

        return $this;
        
    }

    public function verifyRefresh($phrase, $pw_stamp){
        if($pw_stamp !== $this->pw_stamp) return false;

        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_refresh." 
            WHERE `user_id` = :user_id 
            AND `refresh_jti` = :refresh_jti
        ");
        $this->db->bind($stmt, 
            ['user_id', 'refresh_jti'], 
            [$this->user_id, $this->refresh_jti]
        )->execute($stmt);

        $phrase_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["refresh_phrase"];

        if ($stmt->rowCount() === 1 && password_verify($phrase, $phrase_hash)) return true;
        return false;

    }

    public function setRefreshAuth($oldJti = false) {

        if($oldJti){
            $stmt = $this->db->prepare("
                UPDATE ".$this->t_refresh." SET 
                `refresh_jti` = :refresh_jti,
                `refresh_phrase` = :refresh_phrase,
                `updated_stamp` = now(), 
                `updated_total` = `updated_total` + 1 
                WHERE `user_id` = :user_id 
                AND `refresh_jti` = :oldJti
            ");
            $this->db->bind($stmt, 
                ['refresh_jti', 'user_id', 'oldJti', 'refresh_phrase'], 
                [$this->refresh_jti, $this->user_id, $oldJti, password_hash($this->refresh_phrase, Env::sec_encryption)]
            )->execute($stmt);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO ".$this->t_refresh." 
                (`user_id`, `refresh_jti`, `refresh_phrase`) VALUES
                (:user_id, :refresh_jti, :refresh_phrase);
            ");
            $this->db->bind($stmt, 
                ['user_id', 'refresh_jti', 'refresh_phrase'], 
                [$this->user_id, $this->refresh_jti, password_hash($this->refresh_phrase, Env::sec_encryption)]
            )->execute($stmt);
        }

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_status." SET 
            `auth_stamp` = now(),
            `auth_total` = `auth_total` + 1  
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'], [$this->user_id]
        )->execute($stmt);

    }

    public function removeRefresh(){
        $stmt = $this->db->prepare("
            DELETE FROM ".$this->t_refresh." WHERE 
            `user_id` = :user_id AND 
            `refresh_jti` = :refresh_jti 
        ");
        $this->db->bind($stmt, 
            ['user_id', 'refresh_jti'], 
            [$this->user_id, $this->refresh_jti]
        )->execute($stmt);
    }
    
    public function passwordLogin($pw) {

        $stmt = $this->db->prepare("
            SELECT password FROM ".$this->t_main." 
            WHERE id = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'], 
            [$this->user_id]
        );
        $this->db->execute($stmt);

        $password_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["password"];

        if ($stmt->rowCount() === 1 && password_verify($pw, $password_hash)) {
            return true;
        }
            
        return false;

    }

    public function passwordForgotten($code = false) {

        $stamp = date("Y-m-d H:i:s");
        $pw_code = password_hash($this->pw_code, Env::sec_encryption);

        if($code){
            $stmt = $this->db->prepare("
                SELECT * FROM ".$this->t_status." 
                WHERE user_id = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);
            
            if ($stmt->rowCount() === 1 && password_verify($code, ($stmt->fetch(PDO::FETCH_ASSOC))["pw_code"])) {
                $pw_code = null;
                $stamp = null;
            } else {
                $this->mail = null;
                $this->user_id = null;
                throw new Exception('code_validation_error', 403);
            }
        }

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_status." SET 
            `pw_code` = :pw_code, 
            `pw_code_stamp` = :stamp 
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id', 'stamp', 'pw_code'], 
            [$this->user_id, $stamp, $pw_code]
        )->execute($stmt);

        return $this;

    }

    public function passwordChange($pw) {

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main." SET 
            `password` = :password 
            WHERE `id` = :user_id
        ");
        $this->db->bind($stmt,
            ['user_id', 'password'], 
            [$this->user_id, password_hash($pw, Env::sec_encryption)]
        )->execute($stmt);

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_status." SET 
            `pw_stamp` = now()  
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

    }

    public function verifyMail($code) {

        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_status." 
            WHERE user_id = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        if ($stmt->rowCount() === 1 && password_verify($code, ($stmt->fetch(PDO::FETCH_ASSOC))["verify_code"])) {
        
            $stmt = $this->db->prepare("
                UPDATE ".$this->t_status." SET 
                `state` = 'verified', 
                `verify_stamp` = now(), 
                `verify_code` = null 
                WHERE `user_id` = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        } else throw new Exception('verification_error', 403);

    }
    
    public function readToken() {
        
        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->v_token." 
            WHERE user_id = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        if ($stmt->rowCount() !== 1) throw new Exception('mail_not_found', 500);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->mail = $row['mail'];
        $this->level = $row['level'];
        $this->state = $row['state'];
        $this->pw_stamp = $row['pw_stamp'];
        return $this;

    }

    public function disable() {

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_status." SET 
            `state` = 'deleted',
            `deleted` = 'true'
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main." SET 
            `mail` = concat('DELETED:', mail, '|ID:', id),
            `password` = concat('DELETED:', password, '|ID:', id)
            WHERE `id` = :user_id 
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user_id])->execute($stmt);

    }
    
}