<?php

define('PROCESS', "App/Calories/Read"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'from' => ['date', false],
        'to' => ['date', false]
    ]);
    
    require_once REC . 'Activity.php';
    $Activity = new Activity($_DBC, $sec);

    $arr = [];
    $entries = $Activity->readByDate($data->from, $data->to);
    foreach ($entries as $entry) array_push($arr, $Activity->getObject($entry));

    $_REP->addData(count($arr), "total");
    $_REP->addData($arr, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();