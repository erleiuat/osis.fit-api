<?php

define('PROCESS', "Auth/Logout"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $token = Sec::decode(Core::getBody([
        'token' => ['string', true, ['min' => 1]]
    ])->token, Env_sec::t_refresh_secret);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $token->data->mail]);
    
    if ($Auth->check()->status === "verified") {

        $Auth->refresh_jti = $token->jti;
        $Auth->removeRefresh();
        Sec::removeAuth();

    } else {
        if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
        else if ($Auth->status === "unverified") throw new ApiException(403, "account_not_verified");
        else throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();