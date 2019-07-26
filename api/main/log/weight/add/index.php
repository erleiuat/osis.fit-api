<?php

define('PROCESS', "Log/Weight/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/logs/WeightLog.php';
$_wLog = new WeightLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Sec::auth();
    $_LOG->user_id = $authUser->id;    
    $data = Core::getData(['date', 'time', 'weight']);

    $_wLog->user_id = $authUser->id;
    $_wLog->weight = Validate::number($data->weight, 1);

    $date = Validate::date($data->date, true);
    $time = Validate::time($data->time, true);
    $_wLog->stamp = date('Y-m-d H:i:s', strtotime($date." ".$time));

    $_REP->content = array(
        "object"=>$_wLog->add(),
        "id"=>$_wLog->id
    );

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