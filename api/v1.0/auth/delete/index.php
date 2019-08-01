<?php

define('PROCESS', "Auth/Delete"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'mail' => ['mail', true],
        'password' => ['string', true]
    ]);
        
    if ($sec->mail !== $data->mail) throw new Exception("mail_wrong", 403);
    include_once LOCATION . 'src/class/Auth.php';
    $Auth = new Auth($_DBC, $sec);

    if ($Auth->check()->status === "verified") {

        if (!$Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");
        $Auth->disable();
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