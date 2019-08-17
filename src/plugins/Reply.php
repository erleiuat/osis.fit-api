<?php

class Reply {

    /* ------------- PRIVATE PARAMS ------------- */
    private static $status_options = [
        200 => "ok",
        204 => "no_content",
        400 => "bad_request",
        401 => "unauthorized",
        403 => "forbidden",
        404 => "not_found",
        422 => "unprocessable_entity",
        500 => "internal_server_error"
    ];
    
    /* ----------- PUBLIC BASIC PARAMS ---------- */
    private static $code = 204;
    private static $status = "success";

    private static $condition;
    private static $info;

    private static $data = [];

    /* ----------------- METHODS ---------------- */

    public static function setStatus($code, $condition = false) {

        self::$code = $code;
        self::$status = self::$status_options[$code];
        if ($condition) self::$condition = $condition;

    }

    public static function addData($data, $name = false) {
        if (!$name) array_push(self::$data, $data);
        else self::$data[$name] = $data;
    }

    public static function resetData(){
        self::$data = [];
    }

    public static function send() {

        if (self::$code === 204 && self::$data) self::setStatus(200);

        $response = [
            "status" => self::$code,            
            "statusMessage" => self::$status
        ];

        if (self::$condition) $response["condition"] = self::$condition;
        if (self::$info) $response["info"] = self::$info;
        if (self::$data) $response["data"] = self::$data;
    
        http_response_code(self::$code === 204 ? 200 : self::$code);
        echo json_encode($response);

    }

}

