<?php

define('PROCESS', "Auth/Logout"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $token = Sec::decode(Core::getBody(['token', 'string', true, ['min' => 1]])->token, Env::rtkn_secret);
    
    $_Auth->refresh_jti = $token->jti;
    $_Auth->mail = $_LOG->identity = $token->data->mail;
    if($_Auth->checkStatus()->state === "verified"){

        if (!$_Auth->verifyRefresh($token->data->phrase)) throw new ApiException(403, "phrase_wrong");
        $_Auth->removeRefresh();
        Sec::removeAuth();

    } else if ($_Auth->state === "locked") throw new ApiException(403, "account_locked");
    else if ($_Auth->state === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();