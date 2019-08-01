<?php

define('PROCESS', "Weight/Read"); /* Name of this Process */
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
    
    include_once LOCATION . 'src/class/Weight.php';
    $Weight = new Weight($_DBC, $sec);

    $arr = [];
    $entries = $Weight->get($data->from, $data->to);
    foreach ($entries as $entry) array_push($arr, $Weight->getObject($entry));

    $_REP->addData(count($arr), "total");
    $_REP->addData($arr, "weight");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();