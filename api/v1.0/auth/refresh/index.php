<?php

define('PROCESS', "Auth/Refresh"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */
include_once LOCATION . 'src/class/Auth.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $token = Sec::decode(Core::getBody([
        'token' => ['string', true, ['min' => 1]]
    ])->token, Env::tkn_refresh_secret);
    
    $Auth->user->mail = $_LOG->identity = $token->data->mail;
    if ($Auth->check()->status === "verified") {
        
        $Auth->refresh_jti = $token->jti;
        if (!$Auth->verifyRefresh($token->data->phrase)) {
            throw new ApiException(403, "token_invalid", "phrase_error");
        }
        if ($Auth->password_stamp !== $token->data->password_stamp) {
            throw new ApiException(403, "token_invalid", "password_stamp_error");
        }
        
        $Auth->refresh_jti = Core::randomString(20);
        $Auth->refresh_phrase = Core::randomString(20);
        $Auth->setRefreshAuth($token->jti);

        $_REP->addData(Sec::getAuth($Auth), "tokens");

    } else if ($Auth->status === "locked") {
        throw new ApiException(403, "account_locked");
    } else if ($Auth->status === "unverified") {
        throw new ApiException(403, "account_not_verified");
    } else {
        throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();