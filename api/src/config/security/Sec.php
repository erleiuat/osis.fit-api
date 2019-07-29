<?php

use \Firebase\JWT\JWT;

class Sec {

    public static function auth(){

        if (!isset(getallheaders()['Authorization'])) throw new ApiException(403, "token_missing", "app");
        else if (!isset($_COOKIE[Env::coo_name])) throw new ApiException(403, "token_missing", "secure");

        list($type, $data) = explode(" ", getallheaders()['Authorization'], 2);
        if (strcasecmp($type, "Bearer") != 0) throw new ApiException(403, "token_invalid", "not_bearer");

        $access_token_sec = Sec::decode($_COOKIE[Env::coo_name], Env::tkn_access_secret_sec);
        $access_token_app = Sec::decode($data, Env::tkn_access_secret_app);
        $phrase = $access_token_sec->data->phrase . $access_token_app->data->phrase;

        if(!password_verify(Env::sec_phrase.$access_token_app->data->user->mail, $phrase)){
            throw new ApiException(403, "token_invalid", "phrase_wrong");
        }
        
        return $access_token_app->data->user;
        
    }

    public static function getAuth($Auth) {

        try {

            $now = time();
            $phrase = password_hash(Env::sec_phrase.$Auth->user->mail, Env::sec_encryption);
            $half = (int) ( (strlen($phrase) / 2) );

            $def = [
                "iss" => Env::tkn_issuer,
                "iat" => $now,
                "nbf" => $now,
            ];

            $jwt_sec = JWT::encode($def + [
                "exp" => $now + Env::tkn_access_lifetime,
                "data" => [
                    "phrase" => substr($phrase, 0, $half)
                ]
            ], Env::tkn_access_secret_sec);

            $jwt_app = JWT::encode($def + [
                "exp" => $now + Env::tkn_access_lifetime,
                "data" => [
                    "phrase" => substr($phrase, $half),
                    "user" => [
                        "id" => (int) $Auth->user->id,
                        "mail" => $Auth->user->mail,
                        "level" => $Auth->user->level
                    ]
                ]
            ], Env::tkn_access_secret_app);

            $jwt_refresh = JWT::encode($def + [
                "exp" => $now + Env::tkn_refresh_lifetime,
                "jti" => $Auth->refresh_jti,
                "data" => [
                    "mail" => $Auth->user->mail,
                    "phrase" => $Auth->refresh_phrase,
                    "password_stamp" => $Auth->password_stamp
                ]
            ], Env::tkn_refresh_secret);
            
            $c = [
                "data" => $jwt_sec,
                "exp" => $now + Env::coo_lifetime
            ];

            $cookie = setcookie(Env::coo_name, $c["data"], $c["exp"], Env::coo_path, Env::coo_domain, Env::coo_secure, true);

            if($cookie) return (object) [
                "access" => [
                    "expire" => $now + Env::tkn_access_lifetime,
                    "token" => $jwt_app
                ],
                "refresh" => [
                    "expire" => $now + Env::tkn_refresh_lifetime,
                    "token" => $jwt_refresh
                ]
            ];

            throw new Exception("cookie_error", 500);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

    }

    public static function removeAuth(){

        $c = [
            "name" => Env::coo_name,
            "data" => false,
            "expire" => time() - 3600,
            "path" => Env::coo_path,
            "domain" => Env::coo_domain,
            "secure" => Env::coo_secure,
            "httponly" => true
        ];

        $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);
        if(!$cookie) throw new Exception("cookie_remove_error", 500);

    }

    public static function permit($userLevel, $allowedLevels){
        $found = array_search($userLevel, $allowedLevels, TRUE);
        if($found === FALSE) throw new Exception('insufficient_permission', 403);
        else return true;
    }

    public static function decode($token, $secret, $alg = Env::tkn_algorithm){
        return JWT::decode($token, $secret, $alg);
    }
    
}