<?php

define('PROCESS', "Auth/Logout"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/Auth.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $token = Sec::decode(Core::getBody([
        'token' => ['string', true, ['min' => 1]]
    ])->token, Env::tkn_refresh_secret);
    
    $Auth->refresh_jti = $token->jti;
    $Auth->user->mail = $_LOG->identity = $token->data->mail;
    if($Auth->check()->status === "verified"){

        $Auth->removeRefresh();
        Sec::removeAuth();
        
    } else if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
    else if ($Auth->status === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();