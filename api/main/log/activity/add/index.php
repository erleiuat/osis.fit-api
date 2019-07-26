<?php

define('PROCESS', "Log/Activity/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/logs/ActivityLog.php';
$_aLog = new ActivityLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth();
    $_LOG->user_id = $authUser->id;    
    $data = Core::getData(['date', 'time']);

    $_aLog->user_id = $authUser->id;


    $_aLog->title = (isset($data->title) ? Validate::string($data->title) : null);
    $_aLog->duration = (isset($data->calories) ? Validate::time($data->duration) : null);
    $_aLog->calories = (isset($data->calories) ? Validate::number($data->calories) : null);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_aLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->addContent("object", $_aLog->add());
    $_REP->addContent("id", $_aLog->id);

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