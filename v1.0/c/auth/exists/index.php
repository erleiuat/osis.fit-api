<?php

define('PROCESS', "Auth/Exists"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'mail' => ['mail', false, ['min' => 1, 'max' => 90]],
        'username' => ['username', false, ['min' => 1, 'max' => 250]]
    ]);

    $_LOG->addInfo($data->mail);
    $_LOG->addInfo($data->username);
    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    $check = (object) [
        "username" => false,
        "mail" => false
    ];

    if ($Auth->check($data->username)->status) $check->username = true;
    if ($Auth->check($data->mail)->status) $check->mail = true;

    $_REP->addData($check, "exists");

} catch (\Exception $e) {
    Core::processException($_REP, $_LOG, $e);
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();