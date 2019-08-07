<?php

// TODO: Use new db statement structure
class Auth extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "auth";
    private $t_user = "user";
    private $t_refresh = "auth_refresh";
    private $t_subs = "user_subscription";
    private $v_auth = "v_auth";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = ['password', 'verify_code', 'password_code'];

    public $status;
    public $premium;
    public $password;
    public $password_stamp;
    
    public $password_code;
    public $verify_code;

    public $refresh_jti;
    public $refresh_phrase;

    /* ----------------- METHODS ---------------- */
    public function check() {

        $this->status = false;
        if ($this->user->id) $where = ['user_id' => $this->user->id];
        else if($this->user->mail) $where = ['user_mail' => $this->user->mail];
        else return $this;

        $result = $this->db->makeSelect($this->v_auth, $where);
        if (count($result) !== 1) return $this;

        $this->user->id = $result[0]['user_id'];
        $this->user->mail = $result[0]['user_mail'];
        $this->user->level = $result[0]['user_level'];
        $this->id = $result[0]['id'];
        $this->status = $result[0]['status'];
        $this->password_stamp = $result[0]['password_stamp'];

        if($result[0]['active']){
            // TODO: Check subscription
            $this->premium = true;
        } else {
            $this->premium = false;
        }

        return $this;
        
    }

    public function setSubscription($info) {

        $vals = [
            'user_id' => $this->user->id, 
            'subscription_id' => $info->subscription,
            'plan_id' => $info->plan,
            'active' => $info->active
        ];
        $changed = $this->db->makeInsert($this->t_subs, $vals);

        if ($changed !== 1) throw new ApiException(500, 'subscription_insert_failed', get_class($this));

    }

    public function register() {

        $vals = [
            'user_id' => $this->user->id, 
            'password' => password_hash($this->password, Env_auth::pw_crypt),
            'verify_code' => password_hash($this->verify_code, Env_auth::pw_crypt),
            'password_stamp' => date('Y-m-d H:i:s', time())
        ];
        $this->db->makeInsert($this->t_main, $vals);

        $this->id = $this->db->conn->lastInsertId();        

        return $this;

    }

    public function verifyMail($code) {

        $where = ['user_id' => $this->user->id];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) === 1 && password_verify($code, $result[0]["verify_code"])) {
        
            $params = [ 
                'status' => 'verified',
                'verify_stamp' => date('Y-m-d H:i:s', time()),
                'verify_code' => null
            ];
            
            $changed = $this->db->makeUpdate($this->t_main, $params, $where);
            if ($changed > 1) throw new ApiException(500, 'too_many_changed', get_class($this));

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
                [$this->refresh_jti, $this->id, $oldJti, password_hash($this->refresh_phrase, Env_auth::pw_crypt)]
            )->execute($stmt);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO ".$this->t_refresh . " 
                (`auth_id`, `refresh_jti`, `refresh_phrase`) VALUES
                (:auth_id, :refresh_jti, :refresh_phrase);
            ");
            $this->db->bind($stmt, 
                ['auth_id', 'refresh_jti', 'refresh_phrase'], 
                [$this->id, $this->refresh_jti, password_hash($this->refresh_phrase, Env_auth::pw_crypt)]
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
        $password_code = password_hash($this->password_code, Env_auth::pw_crypt);

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
            [$this->user->id, password_hash($pw, Env_auth::pw_crypt)]
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