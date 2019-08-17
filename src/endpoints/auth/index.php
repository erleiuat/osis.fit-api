<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */

import('@/components/Engine'); /* Load API-Engine */


Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
import('@/components/Security'); /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['string', true]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    $_LOG->addInfo($data->mail);
    if ($Auth->check($data->mail)->status === "verified") {

        if (!$Auth->pass($data->password)) throw new ApiException(403, "password_wrong");

        $jti = Core::randomString(20);
        $phrase = Core::randomString(20);
        $Auth->initRefresh($jti, $phrase);

        $token_data = $Auth->token();        
        $_REP->addData(Sec::placeAuth($token_data), "tokens");

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