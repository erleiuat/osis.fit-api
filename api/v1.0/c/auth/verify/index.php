<?php

define('PROCESS', "Auth/Verify"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'code' => ['string', true]
    ]);

    include_once LOCATION . 'src/Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $data->mail]);
    
    if ($Auth->check()->status === "unverified") {
        
        if (!$Auth->verifyMail($data->code)) throw new ApiException(500, "code_wrong");

    } else {
        if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
        else if ($Auth->status === "verified") throw new ApiException(403, "account_already_verified");
        else throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();