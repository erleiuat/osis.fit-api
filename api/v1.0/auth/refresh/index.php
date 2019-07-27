<?php

define('PROCESS', "Auth/Refresh"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody(
        ['token', 'string', true, ['min' => 1]]
    );

    $token = Sec::decode($data->token, Env::rtkn_secret);

    $_Auth->mail = $token->data->mail;
    $_LOG->identity = $_Auth->mail;
    
    if($_Auth->check_state() && $_Auth->state === "verified"){
        $_LOG->user_id = $_Auth->user_id;

        if(password_verify($token->jti, $_Auth->refresh_jti)){

            $_Auth->refresh_jti = Core::randomString(20);
            $_Auth->read_token();
            $authInfo = Sec::getAuth($_Auth);
            $_Auth->updateStatus();

            $_REP->addData($authInfo, "auth");

        } else throw new Exception("jti_invalid", 403);


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