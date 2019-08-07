<?php

define('PROCESS', "Billing/Premium/New"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    include_once LOCATION . 'src/Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $sec->mail]);

    if ($Auth->check()->status !== "verified") throw new ApiException(403, 'not_verified');

    include_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    $obj = $User->read()->getObject();

    include_once LOCATION . 'src/Billing.php';
    
    ChargeBee_Environment::configure(Env_bill::cb_site, Env_bill::cb_tkn);

    $result = ChargeBee_HostedPage::checkoutNew([
        "subscription" => [
            "planId" => "premium"
        ], 
        "customer" => [
            "email" => $Auth->user->mail, 
            "firstName" => $obj->firstname, 
            "lastName" => $obj->lastname,
            "id" => $Auth->user->id
        ],
        "billingAddress" => [
            "firstName" => $obj->firstname, 
            "lastName" => $obj->lastname
        ]
    ]);

    $hostedPage = $result->hostedPage();
    $output = $hostedPage->getValues();

    $_REP->addData($output, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();