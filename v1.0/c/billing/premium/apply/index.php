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
        'token' => ['string', false]
    ]);    

    require_once ROOT . 'Billing.php';
    $Billing = new Billing($_DBC, $sec);
    $user = $Billing->readUser();

    $subscription = false;
    $plan = false;
    $active = false;

    if ($data->token) {

        $info = (object) $Billing->cbCheckout($data->token)->subscription;

        if ($info->customer_id !== $Billing->user->id) throw new ApiException(500, 'user_dont_match', 'billing');
        if ($info->status === 'active') {
            $subscription = $info->id;
            $plan = $info->plan_id;
            $active = true;
        }
        
    } else if ($user->subscription) {

        $info = $Billing->cbSubscription($user->subscription);
        if ($info->status === 'active') {
            $subscription = $info->id;
            $plan = $info->plan;
            $active = true;
        }

    }

    $details = (object) [
        "subscription" => $subscription,
        "plan" => $plan,
        "active" => $active
    ];

    $Billing->setSub($details);

    $_REP->addData((bool) $active, "active");
    $_REP->addData($subscription, "subscription");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();