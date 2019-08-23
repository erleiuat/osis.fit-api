<?php

define('PROCESS', "Auth/Verify"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'code' => ['string', true]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);
    
    if ($Auth->check($data->mail)->status === "unverified") {

        require_once ROOT . 'AccountPortal.php';
        $Account = new AccountPortal($_DBC, $Auth->getAccount());

        if (!$Account->verify($Auth->id, $data->code)) {
            throw new ApiException(500, "code_wrong");
        }

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