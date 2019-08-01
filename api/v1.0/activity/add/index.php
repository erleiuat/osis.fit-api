<?php

define('PROCESS', "Activity/Add"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'title' => ['string', false, ['max' => 150]],
        'duration' => ['time', false, ['max' => 150]],
        'calories' => ['number', true],
        'date' => ['date', true],
        'time' => ['time', true]
    ]);
    
    include_once LOCATION . 'src/class/Activity.php';
    $Activity = new Activity($_DBC, $sec);

    $obj = $Activity->set($data)->create()->getObject();

    $_REP->addData($obj->id, "id");
    $_REP->addData($obj, "object");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();