<?php

define('PROCESS', "Auth/Verify"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/class/Auth.php';
$Auth = new Auth($_DBC);

// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'code' => ['string', true, ['min' => 10, 'max' => 10]]
    ]);
    
    $Auth->user->mail = $_LOG->identity = $data->mail;
    if($Auth->check()->status === "unverified"){
        
        $Auth->verifyMail($data->code);
        if ($Auth->check()->status !== "verified") throw new ApiException(500, "account_verification_failed");

    } else if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
    else if ($Auth->status === "verified") throw new ApiException(403, "account_already_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();