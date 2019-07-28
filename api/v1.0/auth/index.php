<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */
define('LOCATION', "../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['string', true]
    ]);

    $_Auth->mail = $_LOG->identity = $data->mail;    
    if($_Auth->checkStatus()->state === "verified"){

        if (!$_Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");            

        $_Auth->refresh_jti = Core::randomString(20);
        $_Auth->readToken()->setRefreshAuth();
        
        $authData = Sec::getAuth($_Auth);
        $_REP->addData($authData->access, "access");
        $_REP->addData($authData->refresh, "refresh");

    } else if ($_Auth->state === "locked") throw new ApiException(403, "account_locked");
    else if ($_Auth->state === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();