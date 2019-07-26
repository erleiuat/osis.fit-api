<?php

class Auth {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user";
    private $t_aim = "user_aim";
    private $t_detail = "user_detail";
    private $t_verification = "user_verification";

    private $v_state = "v_user_state";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;

    public $mail;
    public $password;
    public $level = "user";

    public $firstname;
    public $lastname;
    public $gender;
    public $height;
    public $birth;

    public $state;
    public $code;
    public $stamp;


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
        $this->db->stmtBind($stmt, 
            ['mail', 'password', 'level'], 
            [$this->mail, $this->password, $this->level]
        );
        $this->db->stmtExecute($stmt);

        $this->id = $this->db->conn->lastInsertId();


        // Insert into t_verification
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_verification." 
            (`user_id`, `code`) VALUES 
            (:id, :code);
        ");
        $this->db->stmtBind($stmt, 
            ['id', 'code'], 
            [$this->id, $this->code]
        );
        $this->db->stmtExecute($stmt);


        // Insert into t_detail
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_detail." 
            (`user_id`, `firstname`, `lastname`) VALUES 
            (:id, :firstname, :lastname);
        ");
        $this->db->stmtBind($stmt, 
            ['id', 'firstname', 'lastname'], 
            [$this->id, $this->firstname, $this->lastname]
        );
        $this->db->stmtExecute($stmt);

        // Insert into t_aim
        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_aim." 
            (`user_id`) VALUES 
            (:id);
        ");
        $this->db->stmtBind($stmt, 
            ['id'], 
            [$this->id]
        );
        $this->db->stmtExecute($stmt);

        
    }


    public function check_state() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_state." 
            WHERE mail = :mail
        ");

        $this->db->stmtBind($stmt, 
            ['mail'], [$this->mail]
        );
        $this->db->stmtExecute($stmt);

        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->state = $row['state'];
            $this->stamp = $row['stamp'];

            return true;

        }

        $this->state = false;
        return false;
        
    }
    

    public function password_login($password) {

        $stmt = $this->db->conn->prepare("
            SELECT Password FROM ".$this->t_main." 
            WHERE id = :id
        ");
        $this->db->stmtBind($stmt, 
            ['id'], 
            [$this->id]
        );
        $this->db->stmtExecute($stmt);

        $password_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["Password"];

        if ($stmt->rowCount() === 1 && password_verify($password, $password_hash)) {
            return true;
        }
            
        return false;

    }


    public function verify_mail($code) {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_verification." 
            WHERE user_id = :id
        ");
        $this->db->stmtBind($stmt, 
            ['id'], 
            [$this->id]
        );

        $this->db->stmtExecute($stmt);
        $code_hash = ($stmt->fetch(PDO::FETCH_ASSOC))["code"];

        if ($stmt->rowCount() === 1 && password_verify($code, $code_hash)) {
        
            $stmt = $this->db->conn->prepare("
                UPDATE ".$this->t_verification." SET 
                `state` = 'verified', 
                `stamp` = now() 
                WHERE `user_id` = :id
            ");
            $this->db->stmtBind($stmt,
                ['id'],
                [$this->id]
            );
            $this->db->stmtExecute($stmt);

        } else throw new Exception('verification_error', 403);

    }
    

    public function read_token() {
        
        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main." 
            WHERE id = :id
        ");
        $this->db->stmtBind($stmt, ['id'], [$this->id]);
        $this->db->stmtExecute($stmt);

        if ($stmt->rowCount() === 1) {
            $this->level = ($stmt->fetch(PDO::FETCH_ASSOC))['level'];
        } else {
            throw new Exception('mail_not_found', 500);
        }

    }
    
}