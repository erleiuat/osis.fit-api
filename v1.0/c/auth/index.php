<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */
define('ROOT', "../../../src/"); /* Path to root */      
define('REC', "../../src/class/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['string', true]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $data->mail]);

    if ($Auth->check()->status === "verified") {

        if(!$Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");

        require_once ROOT . 'Billing.php';
        $Billing = new Billing($_DBC, $Auth->user);
        $Auth->premium = $Billing->hasPremium();

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