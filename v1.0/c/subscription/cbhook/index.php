<?php

define('PROCESS', "Subscription/CBHook"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    if (!isset(getallheaders()['Authorization'])) throw new ApiException(403, "auth_missing", "basic");
    list($type, $authData) = explode(" ", getallheaders()['Authorization'], 2);
    if (strcasecmp($type, "Basic") != 0) throw new ApiException(403, "token_invalid", "not_basic");

    list($user, $pass) = explode(":", base64_decode($authData));
    $data = json_decode(file_get_contents("php://input"));


    if (isset($data->event_type)) {
        if($data->event_type === "subscription_created") {
            $_REP->addData('subscription_created', "happening");
        }
    }


} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();