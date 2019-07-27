<?php

define('PROCESS', "Log/Weight/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/logs/WeightLog.php';
$_wLog = new WeightLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;    
    $data = Core::getData(['date', 'time', 'weight']);

    $_wLog->user_id = $auth->id;
    $_wLog->weight = Validate::number($data->weight, 1);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_wLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->content = array(
        "object"=>$_wLog->add(),
        "id"=>$_wLog->id
    );

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();