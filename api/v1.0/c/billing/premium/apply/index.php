<?php

define('PROCESS', "Billing/Premium/Apply"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'token' => ['string', true]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, $sec);
    if ($Auth->check()->status !== "verified") throw new ApiException(500, 'not_verified', 'billing');
    

    require_once ROOT . 'Billing.php';
    $Billing = new Billing($_DBC, $sec);

    $info = $Billing->cbCheckout($data->token);
    $sub = (object) $info->subscription;
    if ($sub->status !== 'active') throw new ApiException(500, 'sub_not_active', 'billing');
    if ($sub->customer_id !== $Auth->user->id) throw new ApiException(500, 'user_dont_match', 'billing');


    $user = $Billing->readUser();
    if($user->subscription){
        $check = $Billing->cbSubscription($user->subscription);
        if ($check->status === 'active') throw new ApiException(500, 'already_active', 'billing');
        else $Billing->unSub();
    }

    $details = (object) [
        "subscription" => $sub->id,
        "plan" => $sub->plan_id,
        "active" => true
    ];

    $Billing->setSub($details);

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();