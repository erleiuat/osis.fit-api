<?php

// TODO: Use new db statement structure
class AccountPortal extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_account = "account";
    private $t_user = "user";

    private $t_auth = "auth";
    private $t_auth_pass = "auth_pass";
    private $t_auth_verify = "auth_verify";

    /* ----------- PUBLIC BASIC PARAMS ---------- */


    /* ----------------- METHODS ---------------- */
    public function createAccount($mail) {

        $unique = uniqid('', true);
        $time = date('Y_m_d_H_i_s', time());
        $id = hash('ripemd160', $time.':'.$unique);

        $vals = [
            'id' => $id,
            'mail' => $mail,
        ];
        $result = $this->db->makeInsert($this->t_account, $vals);

        if ($result !== 1) throw new ApiException(500, 'acount_create_error', get_class($this));
        
        $this->setAccount([
            "id" => $id,
            "mail" => $mail
        ]);
        
        return $this;

    }

    public function createAuth($password, $verify_code){

        $res = $this->db->makeInsert($this->t_auth, [
            "account_id" => $this->account->id
        ]);
        if ($res !== 1) throw new ApiException(500, 'auth_create_error', get_class($this));

        $authID = $this->db->conn->lastInsertId();

        $res = $this->db->makeInsert($this->t_auth_pass, [
            "auth_id" => $authID,
            "password" => password_hash($password, Env_auth::pw_crypt),
            "update_stamp" => date('Y-m-d H:i:s', time())
        ]);
        if ($res !== 1) throw new ApiException(500, 'auth_pass_create_error', get_class($this));


        $res = $this->db->makeInsert($this->t_auth_verify, [
            "auth_id" => $authID,
            "code" => password_hash($verify_code, Env_auth::pw_crypt)
        ]);
        if ($res !== 1) throw new ApiException(500, 'auth_pass_create_error', get_class($this));

        return $this;

    }

    public function verify($authID, $code) {

        $where = ['auth_id' => $authID];

        $res = $this->db->makeSelect($this->t_auth_verify, $where);

        if (count($res) !== 1) throw new ApiException(500, 'verify_not_found', get_class($this));
        if (!password_verify($code, $res[0]["code"])) throw new ApiException(500, 'verify_code_incorrect', get_class($this));
        
        $res = $this->db->makeUpdate($this->t_auth_verify, [
            'stamp' => date('Y-m-d H:i:s', time()),
            'code' => null
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

        $where = ['id' => $authID];
        $res = $this->db->makeUpdate($this->t_auth, [
            'status' => "verified",
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

        return $this;

    }

    public function passwordForgotten($code = false) {

        $password_stamp = date("Y-m-d H:i:s");
        $password_code = password_hash($this->password_code, Env_auth::pw_crypt);

        if ($code) {
            $stmt = $this->db->prepare("
                SELECT * FROM ".$this->t_main . " 
                WHERE account_id = :account_id
            ");
            $this->db->bind($stmt, ['account_id'], [$this->account->id])->execute($stmt);
            
            if ($stmt->rowCount() === 1 && password_verify($code, ($stmt->fetch(PDO::FETCH_ASSOC))["password_code"])) {
                $password_code = null;
                $password_stamp = null;
            } else {
                $this->account->mail = null;
                $this->account->id = null;
                throw new Exception('code_validation_error', 403);
            }
        }

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `password_code` = :password_code, 
            `password_stamp` = :password_stamp 
            WHERE `account_id` = :account_id
        ");
        $this->db->bind($stmt, 
            ['account_id', 'password_stamp', 'password_code'], 
            [$this->account->id, $password_stamp, $password_code]
        )->execute($stmt);

        return $this;

    }

    public function passwordChange($pw) {

        $stmt = $this->db->prepare("
            UPDATE ".$this->t_main . " SET 
            `password` = :password,
            `password_stamp` = now()  
            WHERE `id` = :account_id
        ");
        $this->db->bind($stmt,
            ['account_id', 'password'], 
            [$this->account->id, password_hash($pw, Env_auth::pw_crypt)]
        )->execute($stmt);

    }

    public function disable($authID) {


        $where = ['id' => $authID];
        $res = $this->db->makeUpdate($this->t_auth, [
            "status" => 'deleted'
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));


        $where = ['id' => $this->account->id];
        $res = $this->db->makeUpdate($this->t_account, [
            "mail" => 'DELETED:'.$this->account->mail
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

        return $this;

    }
    
}