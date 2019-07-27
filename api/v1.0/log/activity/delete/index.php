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

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['id']);
    
    $_aLog->user_id = $auth->id;

    $_aLog->id = Validate::number($data->id, 1);

    $_aLog->delete();

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();