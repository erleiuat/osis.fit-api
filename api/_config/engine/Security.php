<?php

use \Firebase\JWT\JWT;

class Security {

    public static function doAuthToken($user) {

        try {

            $now = time();
            $phrase = Setup::sec_phrase.$user->mail;
            $phrase_hash = password_hash($phrase, PASSWORD_BCRYPT);
            $half = (int) ( (strlen($phrase) / 2) );

            $t_sec = [
                "iss" => Setup::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Setup::tkn_lifetime,
                "nbf" => $now,
                "data" => [
                    "phrase1" => substr($phrase_hash, 0, $half)
                ]
            ];
        
            $t_app = [
                "iss" => Setup::tkn_issuer,
                "iat" => $now,
                "exp" => $now + Setup::tkn_lifetime,
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

            $sec_jwt = JWT::encode($t_sec, Setup::tkn_secret_sec);
            $app_jwt = JWT::encode($t_app, Setup::tkn_secret_app);
            
            $c = [
                "name" => Setup::coo_name,
                "data" => $sec_jwt,
                "expire" => $now + Setup::coo_lifetime,
                "path" => Setup::coo_path,
                "domain" => Setup::coo_domain,
                "secure" => Setup::coo_secure,
                "httponly" => true
            ];

            $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);

            if($cookie) return [
                "token" => $app_jwt,
                "expire" => $now + Setup::coo_lifetime
            ];

            throw new Exception("cookie_error", 500);

        } catch (\Exception $e) {
            throw new Exception("makeAuth_error", 500);
        }

    }

    public static function removeAuth(){

        $now = time();

        $c = [
            "name" => Setup::coo_name,
            "data" => false,
            "expire" => $now - 3600,
            "path" => Setup::coo_path,
            "domain" => Setup::coo_domain,
            "secure" => Setup::coo_secure,
            "httponly" => true
        ];

        $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);

        if(!$cookie){
            throw new Exception("cookie_remove_error", 500);
        }

    }

    public static function auth($required = true){

        if (!isset($_COOKIE[Setup::coo_name]) || !isset(getallheaders()['Authorization'])) {
            if($required) throw new Exception("Required Tokens not found.", 403);
            else return false;
        }

        list($type, $data) = explode(" ", getallheaders()['Authorization'], 2);
        if (strcasecmp($type, "Bearer") != 0) {
            throw new Exception("App-Token invalid.", 403);
        }

        $token_sec = JWT::decode($_COOKIE[Setup::coo_name], Setup::tkn_secret_sec, Setup::tkn_algorithm);
        $token_app = JWT::decode($data, Setup::tkn_secret_app, Setup::tkn_algorithm);

        $phrase = $token_sec->data->phrase1 . $token_app->data->phrase2;

        if(!password_verify(Setup::sec_phrase.$token_app->data->user->mail, $phrase)){
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