<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['string', true]
    ]);

    include_once LOCATION . 'src/class/Auth.php';
    $Auth = new Auth($_DBC, ["mail" => $data->mail]);

    if ($Auth->check()->status === "verified") {

        if(!$Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");

        $Auth->refresh_jti = Core::randomString(20);
        $Auth->setRefreshAuth();
        
        $_REP->addData(Sec::getAuth($Auth), "tokens");

    } else if ($Auth->status === "locked") {
        throw new ApiException(403, "account_locked");
    } else if ($Auth->status === "unverified") {
        throw new ApiException(403, "account_not_verified");
    } else {
        throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) {
    Core::processException($_REP, $_LOG, $e);
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();