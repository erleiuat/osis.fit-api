<?php

define('PROCESS', "Auth/Password/Change"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/Auth.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth();
    $data = Core::getBody([
        'current' => ['string', true],
        'new' => ['password', true]
    ]);
    
    $Auth->user->id = $_LOG->user_id = $sec->id;
    if($Auth->check()->status === "verified"){
        
        if (!$Auth->passwordLogin($data->current)) throw new ApiException(403, "password_wrong");
        $Auth->passwordChange($data->new);

    } else if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
    else if ($Auth->status === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();