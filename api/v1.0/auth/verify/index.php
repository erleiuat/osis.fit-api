<?php

define('PROCESS', "Auth/Verify"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getData(["mail", "code"]);
    $_Auth->mail = Validate::mail($data->mail, 1, 90);
    $_LOG->identity = $_Auth->mail;

    if($_Auth->check_state() && $_Auth->state === "unverified"){

        $_LOG->user_id = $_Auth->id;
        $code = Validate::string($data->code, 1, 20);
        $_Auth->verify_mail($code);

        $_Auth->check_state();
        if ($_Auth->state === "verified") {
            $_LOG->addInfo('mail_successful_verified');
        } else {
            $_REP->setStatus(500, 'verification_failed');
            $_LOG->setStatus('error', 'verification_failed');
        }

    } else if($_Auth->state === "verified"){
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'mail_already_verified');
        $_LOG->setStatus('info', 'mail_already_verified');
    } else if($_Auth->state === "locked"){
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'account_locked');
        $_LOG->setStatus('warn', 'account_locked');
    } else {
        $_REP->setStatus(403, 'mail_not_found');
        $_LOG->setStatus('info', 'mail_not_found');
    }

} catch (\Exception $e) {
    $_REP->setStatus(500, $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync();

// -------------- AFTER RESPONSE -------------
$_LOG->write();