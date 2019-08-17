<?php

class Log {

    /* ------------- PRIVATE PARAMS ------------- */
    private $level_options = [
        'trace', 'degub', 'info', 
        'warn', 'error', 'fatal'
    ];

    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private static $t_main = "log";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    private static $trace;
    private static $level = "trace";
    private static $write = true;
    
    private static $start_stamp;
    private static $end_stamp;
    private static $process = "not set";

    private static $account_id;
    private static $identifier;

    private static $information = "";


    /* ----------------- METHODS ---------------- */

    public static function start() {

        self::$start_stamp = date('Y-m-d G:i:s');

        self::$trace = (
            "USER_AGENT|" . $_SERVER['HTTP_USER_AGENT'] . "|;" .
            "REMOTE_ADDRESS|" . $_SERVER['REMOTE_ADDR'] . "|;" .
            "REMOTE_PORT|" . $_SERVER['REMOTE_PORT'] . "|;" .
            "PHP_SELF|" . $_SERVER['PHP_SELF'] . "|;"
        );

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            self::$trace .= "X_FORWARDED_FOR|" . $_SERVER['HTTP_X_FORWARDED_FOR'] . "|;";
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            self::$trace .= "REFERER|" . $_SERVER['HTTP_REFERER'] . "|;";
        }

    }

    public static function setProcess($name) {

        self::$process = $name;

    }

    public static function setAccount($account_id) {

        self::$account_id = $account_id;

    }

    public static function setIdentifier($value) {

        self::$identifier = $value;

    }
    
    public static function setLevel($level) {

        self::$level = $level;

    }

    public static function addInfo($info) {

        self::$information .= $info . "; ";

    }

    public static function disable() {
        self::$write = false;
    }

    public static function end() {

        self::$end_stamp = date('Y-m-d G:i:s');

        Database::insert(self::$t_main, [
            "account_id" => self::$account_id,
            "level" => self::$level,
            "process" => self::$process,
            "information" => self::$information,
            "identity" => self::$identifier,
            "trace" => self::$trace,
            "stamp" => self::$end_stamp
        ]);

    }
    
}