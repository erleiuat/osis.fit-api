<?php

// App-Params
error_reporting(Setup::api_error_reports);
date_default_timezone_set(Setup::api_timezone);

// Headers
header("Access-Control-Allow-Origin: ".Setup::sec_cors);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");

// Return if Options-Method
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    die();
}
