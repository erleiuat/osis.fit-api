<?php
if (!isset($_COOKIE["iosCookieApprove"])) {
    setcookie("iosCookieApprove", "yes");
}

error_reporting(Env_api::error_reports);

set_error_handler(function($errno, $errstr, $errfile, $errline ){
    if(!error_reporting()) return;
    $code = ($errno ? $errno : 'NOT_SET');
    throw new ErrorException('internal_root_exception:code='.$code.':msg='.$errstr.':file='.$errfile.':line='.$errline, 500);
});

date_default_timezone_set(Env_api::timezone);

/* TODO
if(isset($_SERVER['HTTP_REFERER'])){
    print_r($_SERVER['HTTP_REFERER'].'<br/>');
}
if(isset($_SERVER['HTTP_HOST'])){
    print_r($_SERVER['HTTP_HOST'].'<br/>');
}
if(isset($_SERVER['HTTP_ORIGIN'])){
    print_r($_SERVER['HTTP_ORIGIN'].'<br/>');
}
die();
*/
header("Access-Control-Allow-Origin: " . Env_api::cors);

header("Content-Type: " . (defined("CTYPE") ? CTYPE : "application/json; charset=UTF-8"));
header("Access-Control-Allow-Methods: " . (defined("AMETHS") ? AMETHS : "POST, GET"));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    exit();
}
