<?php

define('PROCESS', "Calories/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'from' => ['date', false],
        'to' => ['date', false]
    ]);
    
    include_once LOCATION . 'src/class/Calories.php';
    $Calories = new Calories($_DBC, $sec);

    $arr = [];
    $entries = $Calories->readByDate($data->from, $data->to);
    foreach ($entries as $entry) array_push($arr, $Calories->getObject($entry));

    $_REP->addData(count($arr), "total");
    $_REP->addData($arr, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();