<?php

use \Firebase\JWT\JWT;

class Sec {

    public static function decode($token, $secret, $alg = Env::tkn_algorithm){
        return JWT::decode($token, $secret, $alg);
    }

    public static function getAuth($auth) {

        try {

            $now = time();
            $phrase = Env::sec_phrase.$auth->mail;
            $phrase_hash = password_hash($phrase, PASSWORD_BCRYPT);
            $half = (int) ( (strlen($phrase) / 2) );

            $contents_sec = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Env::tkn_lifetime,
                "nbf" => $now,
                "data" => [
                    "phrase1" => substr($phrase_hash, 0, $half)
                ]
            ];
        
            $contents_app = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Env::tkn_lifetime,
                "nbf" => $now,
                "data" => [
                    "phrase2" => substr($phrase_hash, $half),
                    "user" => [
                        "id" => (int) (isset($auth->id) ? $auth->id : $auth->user_id),
                        "mail" => $auth->mail,
                        "level" => $auth->level
                    ]
                ]
            ];

            $contents_refresh = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Env::rtkn_lifetime,
                "nbf" => $now + Env::tkn_lifetime,
                "jti" => $auth->refresh_jti,
                "data" => [
                    "mail" => $auth->mail,
                    "phrase" => $auth->refresh_phrase,
                    "stamp" => $now
                ]
            ];

            $jwt_sec = JWT::encode($contents_sec, Env::tkn_secret_sec);
            $jwt_app = JWT::encode($contents_app, Env::tkn_secret_app);
            $jwt_refresh = JWT::encode($contents_refresh, Env::rtkn_secret);
            
            $c = [
                "name" => Env::coo_name,
                "data" => $jwt_sec,
                "expire" => $now + Env::coo_lifetime,
                "path" => Env::coo_path,
                "domain" => Env::coo_domain,
                "secure" => Env::coo_secure,
                "httponly" => true
            ];

            $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);

            if($cookie) return [
                "expire" => $now + Env::tkn_lifetime,
                "access_token" => $jwt_app,
                "refresh_token" => $jwt_refresh
            ];

            throw new Exception("cookie_error", 500);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

    }

    public static function removeAuth(){

        $now = time();

        $c = [
            "name" => Env::coo_name,
            "data" => false,
            "expire" => $now - 3600,
            "path" => Env::coo_path,
            "domain" => Env::coo_domain,
            "secure" => Env::coo_secure,
            "httponly" => true
        ];

        $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);

        if(!$cookie){
            throw new Exception("cookie_remove_error", 500);
        }

    }

    public static function auth($required = true){

        if (!isset($_COOKIE[Env::coo_name]) || !isset(getallheaders()['Authorization'])) {
            if($required) throw new Exception("Required Tokens not found.", 403);
            return false;
        }

        list($type, $data) = explode(" ", getallheaders()['Authorization'], 2);
        if (strcasecmp($type, "Bearer") != 0) throw new Exception("App-Token invalid.", 403);

        $access_token_sec = Sec::decode($_COOKIE[Env::coo_name], Env::tkn_secret_sec);
        $access_token_app = Sec::decode($data, Env::tkn_secret_app);

        $phrase = $access_token_sec->data->phrase1 . $access_token_app->data->phrase2;

        if(!password_verify(Env::sec_phrase.$access_token_app->data->user->mail, $phrase)){
            throw new Exception("Token-Phrase validation failed", 403);
        }
        
        return $access_token_app->data->user;
        
    }

    public static function permit($userLevel, $allowedLevels){
        $found = array_search($userLevel, $allowedLevels, TRUE);
        if($found === FALSE) throw new Exception('insufficient_permission', 403);
        else return true;
    }
    
}