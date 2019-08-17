<?php

define('PROCESS', "Subscription/Apply"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

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

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    if ($Auth->check($sec->mail)->status !== "verified") throw new ApiException(403, 'not_verified');

    ChargeBee_Environment::configure(Env_sec::sub_site, Env_sec::sub_tkn);
    $result = ChargeBee_HostedPage::retrieve($data->token);
    $hostedPage = $result->hostedPage()->getValues();
    $output = (object) $hostedPage['content']['subscription'];

    if($sec->id === $output->customer_id){
        $Auth->addSubscription($output->id);
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();