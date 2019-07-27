<?php

error_reporting(Env::api_error_reports);
date_default_timezone_set(Env::api_timezone);

header("Content-Type: ".(defined("CTYPE") ? CTYPE : "application/json; charset=UTF-8"));
header("Access-Control-Allow-Origin: ".Env::sec_cors);
header("Access-Control-Allow-Methods: ".(defined("AMETHS") ? AMETHS : "POST, GET"));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") exit();
