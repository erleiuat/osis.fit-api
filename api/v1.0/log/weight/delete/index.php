<?php

define('PROCESS', "Log/Weight/Delete"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/log/WeightLog.php';
$_wLog = new WeightLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;        
    $data = Core::getData(['id']);

    $_wLog->user_id = $auth->id;
    $_wLog->id = Validate::number($data->id, 1);

    $_wLog->delete();

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();