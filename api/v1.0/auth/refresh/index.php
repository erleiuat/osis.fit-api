<?php

define('PROCESS', "Auth/Refresh"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody(
        ['token', 'string', true, ['min' => 1]]
    );

    $token = Sec::decode($data->token, Env::rtkn_secret);
    
    $_Auth->mail = $_LOG->identity = $token->data->mail;
    if($_Auth->checkState()->state === "verified"){
        
        if (!$_Auth->passwordLogin($data->password)) throw new ApiException(403, "password_wrong");            

        $_Auth->refresh_jti = Core::randomString(20);
        $_Auth->readToken()->updateStatus();
        $authInfo = Sec::getAuth($_Auth);
        $_REP->addData($authInfo, "auth");

    } else if ($_Auth->state === "locked") throw new ApiException(403, "account_locked");
    else if ($_Auth->state === "unverified") throw new ApiException(403, "account_not_verified");
    else throw new ApiException(401, "account_not_found");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();