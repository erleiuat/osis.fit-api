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

    $data = Core::getBody(
        ['mail', 'mail', true, ['min' => 1, 'max' => 90]],
        ['password', 'string', true]
    );

    $_Auth->mail = $data->mail;
    $_LOG->identity = $_Auth->mail;
    
    if($_Auth->check_state() && $_Auth->state === "verified"){
        $_LOG->user_id = $_Auth->id;

        if ($_Auth->password_login($data->password)) {
            $_Auth->read_token();
            $authInfo = Sec::doAuthToken($_Auth);
            $_REP->addData($authInfo, "auth");
        } else {
            $_REP->setStatus(403, 'password_wrong');
            $_LOG->setStatus('warn', 'password_wrong');
        }

    } else if ($_Auth->state === "locked"){
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'account_locked');
        $_LOG->setStatus('warn', 'account_locked');
    } else if ($_Auth->state === "unverified") {
        $_LOG->user_id = $_Auth->id;
        $_REP->setStatus(403, 'account_not_verified');
        $_LOG->setStatus('info', 'account_not_verified');
    } else {
        $_REP->setStatus(401, 'account_not_found', ["entity"=>"mail"]);
        $_LOG->setStatus('info', 'account_not_found');
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();