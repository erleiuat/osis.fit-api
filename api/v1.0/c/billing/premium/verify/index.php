<?php

define('PROCESS', "Billing/Premium/Verify"); /* Name of this Process */
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

    require_once ROOT . 'Billing.php';

    ChargeBee_Environment::configure(Env_bill::cb_site, Env_bill::cb_tkn);
    $result = ChargeBee_HostedPage::retrieve($data->token);
    $hostedPage = $result->hostedPage()->getValues();
    $info = (object) $hostedPage['content']['subscription'];

    if($info->customer_id !== $sec->id) throw new ApiException(500, 'user_no_match', 'billing');

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, $sec);

    if ($Auth->check()->status !== "verified") throw new ApiException(500, 'not_verified', 'billing');
    
    $active = false;
    if($info->status === 'active'){
        $active = true;
    }

    $details = (object) [
        "subscription" => $info->id,
        "plan" => $info->plan_id,
        "active" => $active
    ];

    $Auth->setSubscription($details);

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();