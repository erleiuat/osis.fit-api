<?php

define('PROCESS', "Log/Calorie/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/logs/CalorieLog.php';
$_cLog = new CalorieLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['date', 'time', 'calories']);

    $_cLog->user_id = $auth->id;

    $_cLog->title = Validate::string($data->title, 0, 60);
    $_cLog->calories = Validate::number($data->calories);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_cLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->addData("object", $_cLog->add());
    $_REP->addData("id", $_cLog->id);

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();