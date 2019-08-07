<?php

define('PROCESS', "App/Calories/Read"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */        

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
    
    include_once REC . 'Calories.php';
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