<?php

define('PROCESS', "Log/Activity/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/logs/ActivityLog.php';
$_aLog = new ActivityLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;    
    $data = Core::getData(['date', 'time']);

    $_aLog->user_id = $auth->id;


    $_aLog->title = (isset($data->title) ? Validate::string($data->title) : null);
    $_aLog->duration = (isset($data->calories) ? Validate::time($data->duration) : null);
    $_aLog->calories = (isset($data->calories) ? Validate::number($data->calories) : null);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_aLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->addData("object", $_aLog->add());
    $_REP->addData("id", $_aLog->id);

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();