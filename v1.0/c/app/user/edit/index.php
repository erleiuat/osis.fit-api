<?php

define('PROCESS', "App/User/Edit"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'firstname' => ['string', false, ['max' => 150]],
        'lastname' => ['string', false, ['max' => 150]],
        'birthdate' => ['date', false],
        'height' => ['number', false],
        'gender' => ['string', false],
        'aims' => [
            'weight' => ['number', false],
            'date' => ['date', false]
        ]
    ]);
    
    $data->aim_weight = $data->aims->weight;
    $data->aim_date = $data->aims->date;

    require_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    
    $obj = $User->read()->set($data)->edit()->getObject();
    $_REP->addData($obj, "item");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();