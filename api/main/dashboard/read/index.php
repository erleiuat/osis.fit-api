<?php

define('PROCESS', "Dashboard/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */

include_once LOCATION.'_objects/User.php';
$_user = new User($_DBC);
include_once LOCATION.'_objects/logs/WeightLog.php';
$_wLog = new WeightLog($_DBC);
include_once LOCATION.'_objects/logs/ActivityLog.php';
$_aLog = new ActivityLog($_DBC);
include_once LOCATION.'_objects/logs/CalorieLog.php';
$_cLog = new CalorieLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth();
    $_LOG->user_id = $authUser->id;

    $_user->id = $authUser->id;
    $_wLog->user_id = $authUser->id;
    $_cLog->user_id = $authUser->id;
    $_aLog->user_id = $authUser->id;

    $from = date('Y-m-d', time());
    $to = date('Y-m-d', strtotime($from.' +1 day'));
    
    $_REP->addContent("user", $_user->read());
    $_REP->addContent("weights", $_wLog->read());
    $_REP->addContent("calories", $_cLog->read($from, $to));
    $_REP->addContent("activity", $_aLog->read($from, $to));

} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync(); /* End Async-Request */

// -------------- AFTER RESPONSE -------------
$_LOG->write();