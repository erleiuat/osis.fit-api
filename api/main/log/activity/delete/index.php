<?php

define('PROCESS', "Log/Activity/Delete"); /* Name of this Process */
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
    $data = Core::getData(['id']);
    
    $_aLog->user_id = $authUser->id;

    $_aLog->id = Validate::number($data->id, 1);

    $_aLog->delete();

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