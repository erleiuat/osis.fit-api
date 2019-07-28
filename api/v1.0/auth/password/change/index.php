<?php

define('PROCESS', "Auth/Password/Change"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $data = Core::getBody([
        'current' => ['string', true],
        'new' => ['password', true]
    ]);
    
    $_Auth->user_id = $_LOG->user_id = $auth->id;
    if($_Auth->checkStatus()->state === "verified"){
        
        if (!$_Auth->passwordLogin($data->current)) throw new ApiException(403, "password_wrong");
        $_Auth->passwordChange($data->new);

    } else if ($_Auth->state === "locked") throw new ApiException(403, "account_locked");
    else if ($_Auth->state === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();