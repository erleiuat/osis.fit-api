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
    if ($user !== ENV_sec::sub_hook_user) throw new ApiException(403, "wrong_user", "basic_auth");
    if (!password_verify($pass, ENV_sec::sub_hook_pass)) throw new ApiException(403, "wrong_pass", "basic_auth");
    
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->event_type)) throw new ApiException(500, "missing", "event_type");

    $_LOG->addInfo("EVENT:".$data->event_type);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    if ($data->event_type === "subscription_created") {

        $info = $data->content->subscription;
        $aInfo = $Auth->getAccountInfo($info->customer_id);

        $_LOG->addInfo("USER:".$info->customer_id);
        $_LOG->addInfo("SUB_ID:".$info->id);

        if ($aInfo['auth_subscription']) {

            if ($aInfo['auth_subscription'] !== $info->id) {
                $Auth->addSubscription($info->id, $info->customer_id);
            }

        } else {
            $Auth->addSubscription($info->id, $info->customer_id);
        }

    }

    //$_REP->addData('subscription_created', "happening");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();