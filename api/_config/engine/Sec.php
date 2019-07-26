<?php

use \Firebase\JWT\JWT;

class Sec {

    public static function doAuthToken($user) {

        try {

            $now = time();
            $phrase = Env::sec_phrase.$user->mail;
            $phrase_hash = password_hash($phrase, PASSWORD_BCRYPT);
            $half = (int) ( (strlen($phrase) / 2) );

            $t_sec = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Env::tkn_lifetime,
                "nbf" => $now,
                "data" => [
                    "phrase1" => substr($phrase_hash, 0, $half)
                ]
            ];
        
            $t_app = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Env::tkn_lifetime,
                "nbf" => $now,
                "data" => [
                    "phrase2" => substr($phrase_hash, $half),
                    "user" => [
                        "id" => (int) $user->id,
                        "mail" => $user->mail,
                        "level" => $user->level
                    ]
                ]
            ];

            $sec_jwt = JWT::encode($t_sec, Env::tkn_secret_sec);
            $app_jwt = JWT::encode($t_app, Env::tkn_secret_app);
            
            $c = [
                "name" => Env::coo_name,
                "data" => $sec_jwt,
                "expire" => $now + Env::coo_lifetime,
                "path" => Env::coo_path,
                "domain" => Env::coo_domain,
                "secure" => Env::coo_secure,
                "httponly" => true
            ];

            $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);

            if($cookie) return [
                "token" => $app_jwt,
                "expire" => $now + Env::coo_lifetime
            ];

            throw new Exception("cookie_error", 500);

        } catch (\Exception $e) {
            throw new Exception("makeAuth_error", 500);
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
            else return false;
        }

        list($type, $data) = explode(" ", getallheaders()['Authorization'], 2);
        if (strcasecmp($type, "Bearer") != 0) {
            throw new Exception("App-Token invalid.", 403);
        }

        $token_sec = JWT::decode($_COOKIE[Env::coo_name], Env::tkn_secret_sec, Env::tkn_algorithm);
        $token_app = JWT::decode($data, Env::tkn_secret_app, Env::tkn_algorithm);

        $phrase = $token_sec->data->phrase1 . $token_app->data->phrase2;

        if(!password_verify(Env::sec_phrase.$token_app->data->user->mail, $phrase)){
            throw new Exception("Token-Phrase validation failed", 403);
        }
        
        return $token_app->data->user;
        
    }

    public static function permit($userLevel, $allowedLevels){

        $found = array_search($userLevel, $allowedLevels, TRUE);
        if($found === FALSE) throw new Exception('insufficient_permission', 403);
        else return true;

    }
    
}