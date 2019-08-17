<?php

process_start("Auth/Login");

// --------------- DEPENDENCIES --------------
import('@/components/Security'); /* Load Security-Methods */
import('@/plugins/Core');


// ------------------ SCRIPT -----------------

$data = Core::getBody([
    'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
    'password' => ['string', true]
]);

Log::setIdentifier($data->mail);

import('@/components/Authentication');
$Auth = new Auth();

if ($Auth->check($data->mail)->status === "verified") {

    if (!$Auth->pass($data->password)) throw new ApiException(403, "password_wrong");

    $jti = Core::randomString(20);
    $phrase = Core::randomString(20);
    $Auth->initRefresh($jti, $phrase);

    $token_data = $Auth->token();       

    Reply::addData(Sec::placeAuth($token_data), "tokens");

} else if ($Auth->status === "locked") {
    throw new ApiException(403, "account_locked");
} else if ($Auth->status === "unverified") {
    throw new ApiException(403, "account_not_verified");
} else {
    throw new ApiException(401, "account_not_found");
}

// -------------------------------------------

process_end();
