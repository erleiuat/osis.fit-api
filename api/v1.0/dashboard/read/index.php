<?php

define('PROCESS', "Dashboard/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

include_once LOCATION . 'src/class/User.php';
$_user = new User($_DBC);
include_once LOCATION . 'src/class/log/WeightLog.php';
$_wLog = new WeightLog($_DBC);
include_once LOCATION . 'src/class/log/ActivityLog.php';
$_aLog = new ActivityLog($_DBC);
include_once LOCATION . 'src/class/log/CalorieLog.php';
$_cLog = new CalorieLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;

    $_user->id = $auth->id;
    $_wLog->user_id = $auth->id;
    $_cLog->user_id = $auth->id;
    $_aLog->user_id = $auth->id;

    $from = date('Y-m-d', time());
    $to = date('Y-m-d', strtotime($from . ' +1 day'));
    
    $_REP->addData("user", $_user->read());
    $_REP->addData("weights", $_wLog->read());
    $_REP->addData("calories", $_cLog->read($from, $to));
    $_REP->addData("activity", $_aLog->read($from, $to));

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();