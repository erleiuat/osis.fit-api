<?php

class Auth {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "auth";
    private $t_user = "user";
    private $t_refresh = "auth_refresh";
    private $v_auth = "v_auth";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user;

    public $status;
    public $password;
    public $password_stamp;
    
    public $password_code;
    public $verify_code;

    public $refresh_jti;
    public $refresh_phrase;

    /* ------------------ INIT ------------------ */
    public function __construct($db, $user = false) { 
        
        $this->db = $db;
        if($user) {
            $this->user = $user;
        } else {
            $this->user = (object) [
            "id" => null,
            "mail" => null,
            "level" => null,
        ];
        }

    }

    /* ----------------- METHODS ---------------- */
    public function check() {

        $this->status = false;
        if ($this->user->id) {
            $use = ['user_id', $this->user->id];
        } else if($this->user->mail) {
            $use = ['user_mail', $this->user->mail];
        } else {
            return $this;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->v_auth." 
            WHERE `".$use[0]."` = :".$use[0]."
        ");
        $this->db->bind($stmt, [$use[0]], [$use[1]])->execute($stmt);
        if ($stmt->rowCount() !== 1) {
            return $this;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->user->id = ($this->user->id ? $this->user->id : $row['user_id']);
        $this->user->mail = ($this->user->mail ? $this->user->mail : $row['user_mail']);
        $this->user->level = $row['user_level'];
        $this->id = $row['id'];
        $this->status = $row['status'];
        $this->password_stamp = $row['password_stamp'];
        return $this;
        
    }

    public function register() {

        $stmt = $this->db->prepare("
            INSERT INTO ".$this->t_main . " 
            (`user_id`, `password`, `verify_code`, `password_stamp`) VALUES 
            (:user_id, :password, :verify_code, now());
        ");
        $this->db->bind($stmt, 
            ['user_id', 'password', 'verify_code'], 
            [$this->user->id, password_hash($this->password, Env::sec_encryption), password_hash($this->verify_code, Env::sec_encryption)]
        )->execute($stmt);
        $this->id = $this->db->conn->lastInsertId();

    }

    public function verifyMail($code) {

        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_main . " 
            WHERE user_id = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user->id])->execute($stmt);

        if ($stmt->rowCount() === 1 && password_verify($code, ($stmt->fetch(PDO::FETCH_ASSOC))["verify_code"])) {
        
            $stmt = $this->db->prepare("
                UPDATE ".$this->t_main . " SET 
                `status` = 'verified', 
                `verify_stamp` = now(), 
                `verify_code` = null 
                WHERE `user_id` = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user->id])->execute($stmt);

            return true;

        }

        return false;

    }

    public function verifyRefresh($phrase) {

        $stmt = $this->db->prepare("
            SELECT * FROM ".$this->t_refresh . " 
            WHERE `auth_id` = :auth_id 
            AND `refresh_jti` = :refresh_jti
        ");
        $this->db->bind($stmt, 
            ['auth_id', 'refresh_jti'], 
            [$this->id, $this->refresh_jti]
        )->execute($stmt);

        $phrase_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["refresh_phrase"];
        if ($stmt->rowCount() === 1 && password_verify($phrase, $phrase_hash)) {
            return true;
        }
        return false;

    }

    public function setRefreshAuth($oldJti = false) {

        if ($oldJti) {
            $stmt = $this->db->prepare("
                UPDATE ".$this->t_refresh . " SET 
                `refresh_jti` = :refresh_jti,
                `refresh_phrase` = :refresh_phrase,
                `updated_stamp` = now(), 
                `updated_total` = `updated_total` + 1 
                WHERE `auth_id` = :auth_id 
                AND `refresh_jti` = :oldJti
            ");
            $this->db->bind($stmt, 
                ['refresh_jti', 'auth_id', 'oldJti', 'refresh_phrase'], 
                [$this->refresh_jti, $this->id, $oldJti, password_hash($this->refresh_phrase, Env::sec_encryption)]
            )->execute($stmt);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO ".$this->t_refresh . " 
                (`auth_id`, `refresh_jti`, `refresh_phrase`) VALUES
                (:auth_id, :refresh_jti, :refresh_phrase);
            ");
            $this->db->bind($stmt, 
                ['auth_id', 'refresh_jti', 'refresh_phrase'], 
                [$this->id, $this->refresh_jti, password_hash($this->refresh_phrase, Env::sec_encryption)]
            )->execute($stmt);
        }

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `auth_stamp` = now(),
            `auth_total` = `auth_total` + 1  
            WHERE `id` = :id
        ");
        $this->db->bind($stmt, 
            ['id'], [$this->id]
        )->execute($stmt);

    }

    public function removeRefresh() {
        $stmt = $this->db->prepare("
            DELETE FROM ".$this->t_refresh . " WHERE 
            `auth_id` = :auth_id AND 
            `refresh_jti` = :refresh_jti 
        ");
        $this->db->bind($stmt, 
            ['auth_id', 'refresh_jti'], 
            [$this->id, $this->refresh_jti]
        )->execute($stmt);
    }
    
    public function passwordLogin($pw) {

        $stmt = $this->db->prepare("
            SELECT password FROM ".$this->t_main . " 
            WHERE user_id = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id'], 
            [$this->user->id]
        );
        $this->db->execute($stmt);

        $password_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["password"];

        if ($stmt->rowCount() === 1 && password_verify($pw, $password_hash)) {
            return true;
        }
            
        return false;

    }

    public function passwordForgotten($code = false) {

        $password_stamp = date("Y-m-d H:i:s");
        $password_code = password_hash($this->password_code, Env::sec_encryption);

        if ($code) {
            $stmt = $this->db->prepare("
                SELECT * FROM ".$this->t_main . " 
                WHERE user_id = :user_id
            ");
            $this->db->bind($stmt, ['user_id'], [$this->user->id])->execute($stmt);
            
            if ($stmt->rowCount() === 1 && password_verify($code, ($stmt->fetch(PDO::FETCH_ASSOC))["password_code"])) {
                $password_code = null;
                $password_stamp = null;
            } else {
                $this->user->mail = null;
                $this->user->id = null;
                throw new Exception('code_validation_error', 403);
            }
        }

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `password_code` = :password_code, 
            `password_stamp` = :password_stamp 
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, 
            ['user_id', 'password_stamp', 'password_code'], 
            [$this->user->id, $password_stamp, $password_code]
        )->execute($stmt);

        return $this;

    }

    public function passwordChange($pw) {

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `password` = :password,
            `password_stamp` = now()  
            WHERE `id` = :user_id
        ");
        $this->db->bind($stmt,
            ['user_id', 'password'], 
            [$this->user->id, password_hash($pw, Env::sec_encryption)]
        )->execute($stmt);

    }

    public function disable() {

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `status` = 'deleted',
            `password` = concat('DELETED:', password, '|ID:', id) 
            WHERE `user_id` = :user_id
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user->id])->execute($stmt);

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_user . " SET 
            `mail` = concat('DELETED:', mail, '|ID:', id)
            WHERE `id` = :user_id 
        ");
        $this->db->bind($stmt, ['user_id'], [$this->user->id])->execute($stmt);

    }
    
}