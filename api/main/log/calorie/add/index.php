<?php

define('PROCESS', "Log/Calorie/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/logs/CalorieLog.php';
$_cLog = new CalorieLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth();
    $_LOG->user_id = $authUser->id;
    $data = Core::getData(['date', 'time', 'calories']);

    $_cLog->user_id = $authUser->id;

    $_cLog->title = Validate::string($data->title, 0, 60);
    $_cLog->calories = Validate::number($data->calories);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_cLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->addContent("object", $_cLog->add());
    $_REP->addContent("id", $_cLog->id);

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