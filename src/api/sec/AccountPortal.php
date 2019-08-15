<?php

// TODO: Use new db statement structure
class AccountPortal extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_account = "account";
    private $t_user = "user";

    private $t_auth = "auth";
    private $t_auth_pass = "auth_pass";
    private $t_auth_verify = "auth_verify";

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
            "password" => password_hash($password, Env_sec::pw_encryption),
            "update_stamp" => date('Y-m-d H:i:s', time())
        ]);
        if ($res !== 1) throw new ApiException(500, 'auth_pass_create_error', get_class($this));


        $res = $this->db->makeInsert($this->t_auth_verify, [
            "auth_id" => $authID,
            "code" => password_hash($verify_code, Env_sec::pw_encryption)
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

    public function passReset($authID, $code) {

        $where = ['auth_id' => $authID];
        $res = $this->db->makeUpdate($this->t_auth_pass, [
            'reset_code_stamp' => date('Y-m-d H:i:s', time()),
            'reset_code' => password_hash($code, Env_sec::pw_encryption)
        ], $where);

        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

        return $this;

    }

    public function passResetVerify($authID, $code) {

        $where = ['auth_id' => $authID];
        $res = $this->db->makeSelect($this->t_auth_pass, $where);

        if (count($res) !== 1) throw new ApiException(404, 'reset_not_found', get_class($this));

        if (!password_verify($code, $res[0]["reset_code"])) throw new ApiException(401, 'code_invalid', get_class($this));
        else {

            $res = $this->db->makeUpdate($this->t_auth_pass, [
                'reset_code' => null
            ], $where);
    
            if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

            return $this;

        }

        return false;

    }

    public function passChange($authID, $pw) {

        $where = ['auth_id' => $authID];
        $res = $this->db->makeUpdate($this->t_auth_pass, [
            'update_stamp' => date('Y-m-d H:i:s', time()),
            'password' => password_hash($pw, Env_sec::pw_encryption)
        ], $where);

        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

    }

    public function disable($authID) {


        $where = ['id' => $authID];
        $res = $this->db->makeUpdate($this->t_auth, [
            "status" => 'deleted'
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));


        $where = ['id' => $this->account->id];
        $res = $this->db->makeUpdate($this->t_account, [
            "mail" => 'DELETED:'.$this->account->mail.'|'.$this->account->id
        ], $where);
        if ($res !== 1) throw new ApiException(500, 'verify_change_failed', get_class($this));

        return $this;

    }
    
}