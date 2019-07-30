<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */
include_once LOCATION . 'src/class/Auth.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['string', true]
    ]);

    $Auth->user->mail = $_LOG->identity = $data->mail;
    if ($Auth->check()->status === "verified") {

        if(!$Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");

        $Auth->refresh_jti = Core::randomString(20);
        $Auth->setRefreshAuth();
        
        $authData = Sec::getAuth($Auth);
        $_REP->addData($authData->access, "access");
        $_REP->addData($authData->refresh, "refresh");

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