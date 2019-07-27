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

    $data = Core::getBody(
        ['mail', 'mail', true, ['min' => 1, 'max' => 90]],
        ['code', 'string', true, ['min' => 10, 'max' => 10]]
    );
    
    $_Auth->mail = $_LOG->identity = $data->mail;

    if($_Auth->check_state() && $_Auth->state === "unverified"){
        $_LOG->user_id = $_Auth->user_id;

        $_Auth->verify_mail($data->code);

        $_Auth->check_state();
        if ($_Auth->state !== "verified") {
            $_REP->setStatus(500, 'account_verification_failed');
            $_LOG->setStatus('error', 'account_verification_failed');
        }

    } else if($_Auth->state === "verified"){
        $_LOG->user_id = $_Auth->user_id;
        $_REP->setStatus(403, 'account_already_verified');
        $_LOG->setStatus('info', 'account_already_verified');
    } else if($_Auth->state === "locked"){
        $_LOG->user_id = $_Auth->user_id;
        $_REP->setStatus(403, 'account_locked');
        $_LOG->setStatus('warn', 'account_locked');
    } else {
        $_REP->setStatus(403, 'account_not_found');
        $_LOG->setStatus('info', 'account_not_found');
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();