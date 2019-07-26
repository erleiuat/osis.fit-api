<?php

define('PROCESS', "Auth"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getData(['mail', 'password']);
    $_Auth->mail = Validate::mail($data->mail, 1, 90);
    $_LOG->identity = $_Auth->mail;
    
    if($_Auth->check_state() && $_Auth->state === "verified"){

        $_LOG->user_id = $_Auth->id;
        $password = Validate::string($data->password, 8, 255);

        if ($_Auth->password_login($password)) {

            $_Auth->read_token();
            $authInfo = Sec::doAuthToken($_Auth);
            $_REP->addContent("auth", $authInfo);

        } else {
            $_REP->setStatus(403, 'password_incorrect');
            $_LOG->setStatus('warn', 'password_incorrect');
        }

    } else if ($_Auth->state === "locked"){
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'account_locked');
        $_LOG->setStatus('warn', 'account_locked');
    } else if ($_Auth->state === "unverified") {
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'mail_not_verified');
        $_LOG->setStatus('info', 'mail_not_verified');
    } else {
        $_REP->setStatus(500, 'mail_not_found');
        $_LOG->setStatus('info', 'mail_not_found');
    }

} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync(); /* End Async-Request */

// -------------- AFTER RESPONSE -------------
$_LOG->write();