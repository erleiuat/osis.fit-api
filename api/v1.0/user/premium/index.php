<?php

define('PROCESS', "User/Premium"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

require_once LOCATION . 'src/api/libs/chargebee-php/test/ChargeBee.php';

ChargeBee_Environment::configure("osis-fit","test_bEyMPSgEjzZx4cn7q1avZiPwp6XtPOSx");

$result = ChargeBee_HostedPage::checkoutNew(array(

"subscription" => array(
    "planId" => "your_plan_name"
), 
"customer" => array(
    "email" => "john@user.com", 
    "firstName" => "John", 
    "lastName" => "Doe", 
    "locale" => "fr-CA", 
    "phone" => "+1-949-999-9999"
), 
"billingAddress" => array(
    "firstName" => "John", 
    "lastName" => "Doe", 
    "line1" => "PO Box 9999", 
    "city" => "Walnut", 
    "state" => "California", 
    "zip" => "91789", 
    "country" => "US"
)));

$hostedPage = $result->hostedPage();
$output = $hostedPage->getValues();

Core::endAsync();