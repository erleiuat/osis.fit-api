<?php

// TODO: Use new db statement structure
class Auth extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "auth";
    private $t_pass = "auth_pass";
    private $t_verify = "auth_verify";
    private $t_refresh = "auth_refresh";

    private $v_check = "v_auth_check";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = ['password', 'verify_code', 'password_code'];

    public $id;
    public $level;
    public $status = false;
    public $subscription;

    public $pass_stamp;
    public $refresh_jti;
    public $refresh_phrase;

    /* Verify: code, stamp */
    public $verify;


    /* ----------------- METHODS ---------------- */
    public function addSubscription($subID) {

        $where = ['id' => $this->id];
        $res = $this->db->makeUpdate($this->t_main, [
            "subscription" => $subID
        ], $where);
        
        if ($res !== 1) throw new ApiException(500, 'sub_add_error', get_class($this));
        
        return $this;

    }

    public function setSubscription($subID = null) {

        $sub = false;

        if ($subID) {
            ChargeBee_Environment::configure(Env_sec::sub_site, Env_sec::sub_tkn);
            $result = ChargeBee_Subscription::retrieve($subID);
            $sub = $result->subscription();
        }

        $this->subscription = (object) [
            "id" => ($sub ? $sub->id : null),
            "status" => ($sub ? $sub->status : null),
            "deleted" => ($sub ? $sub->deleted : null),
            "expiration_stamp" => ($sub ? $sub->currentTermEnd : null),
            "plan" => ($sub ? $sub->planId : null)
        ];

    }

    public function check($mail) {

        $result = $this->db->makeSelect($this->v_check, [
            'account_mail' => $mail
        ]);

        if (count($result) !== 1) return $this;

        $this->id = $result[0]['auth_id'];
        $this->status = $result[0]['auth_status'];
        $this->level = $result[0]['auth_level'];

        $this->pass_stamp = $result[0]['pass_update_stamp'];

        $this->setSubscription($result[0]['auth_subscription']);
        $this->setAccount([
            "id" => $result[0]['account_id'],
            "mail" => $mail,
        ]);

        return $this;
        
    }

    public function pass($pw) {

        $result = $this->db->makeSelect($this->t_pass, [
            'auth_id' => $this->id
        ]);

        if (count($result) !== 1) return false;
        if (password_verify($pw, $result[0]['password'])) return true;

        return false;

    }

    public function token() {

        return (object) [
            "level" => $this->level,
            "account" => (object) [
                "id" => $this->account->id,
                "mail" => $this->account->mail
            ],
            "subscription" => $this->subscription,
            "refresh" => (object) [
                "mail" => $this->account->mail,
                "phrase" => $this->refresh_phrase,
                "jti" => $this->refresh_jti
            ]
        ];

    }

    public function refresh($jti, $phrase) {
        
        $where = ['auth_id' => $this->id, 'jti' => $jti];
        $res = $this->db->makeUpdate($this->t_refresh, [
            "phrase" => password_hash($phrase, Env_sec::pw_encryption),
            "update_total" => "`update_total`+1"
        ], $where);
        
        if ($res !== 1) throw new ApiException(500, 'refresh_error', get_class($this));
        
        $this->refresh_jti = $jti;
        $this->refresh_phrase = $phrase;
        
        return $this;
        
    }

    public function initRefresh($jti, $phrase) {

        $res = $this->db->makeInsert($this->t_refresh, [
            "auth_id" => $this->id,
            "jti" => $jti,
            "phrase" => password_hash($phrase, Env_sec::pw_encryption)
        ]);

        if ($res !== 1) throw new ApiException(500, 'refresh_init_error', get_class($this));
        
        $this->refresh_jti = $jti;
        $this->refresh_phrase = $phrase;

        return $this;

    }
    
    public function verifyRefresh($jti, $phrase) {

        $result = $this->db->makeSelect($this->t_refresh, [
            'auth_id' => $this->id, 'jti' => $jti
        ]);

        if (count($result) !== 1) return false;
        if (password_verify($phrase, $result[0]['phrase'])) return true;

        return false;

    }

    public function removeRefresh($jti = false) {

        $where = ['auth_id' => $this->id];

        if($jti) array_push($where, ['jti' => $jti]);

        $result = $this->db->makeDelete($this->t_refresh, $where);

        if ($jti && $result !== 1) throw new ApiException(404, 'refresh_remove_error', get_class($this));
        else if($result < 1) throw new ApiException(404, 'refresh_remove_error', get_class($this));

        return true;

    }
    
    
    
}