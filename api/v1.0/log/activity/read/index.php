<?php

define('PROCESS', "Log/Activity/Read"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/log/ActivityLog.php';
$_aLog = new ActivityLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['from']);

    $_aLog->user_id = $auth->id;

    $from = Validate::date($data->from);
    $to = (isset($data->to) ? Validate::date($data->to) : date('Y-m-d', strtotime($from.' +1 day')));
    
    $_REP->addData("activity", $_aLog->read($from, $to));

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();