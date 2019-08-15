<?php

define('PROCESS', "Subscription/Checkout"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    if ($Auth->check($sec->mail)->status !== "verified") throw new ApiException(403, 'not_verified');

    require_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    $obj = $User->read()->getObject();

    ChargeBee_Environment::configure(Env_sec::sub_site, Env_sec::sub_tkn);

    $result = ChargeBee_HostedPage::checkoutNew([
        "subscription" => [
            "planId" => Env_sec::sub_plan
        ], 
        "customer" => [
            "email" => $sec->mail, 
            "firstName" => $obj->firstname, 
            "lastName" => $obj->lastname,
            "id" => $sec->id
        ],
        "billingAddress" => [
            "firstName" => $obj->firstname, 
            "lastName" => $obj->lastname
        ]
    ]);

    $output = $result->hostedPage()->getValues();

    $_REP->addData($output, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();