<?php

define('PROCESS', "App/"); /* Name of this Process */
define('ROOT', "../../../src/"); /* Path to root */      
define('REC', "../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    
    $actArr = [];
    require_once REC . 'Activity.php';
    $Activity = new Activity($_DBC, $sec);
    $entries = $Activity->readByDate();
    foreach ($entries as $entry) array_push($actArr, $Activity->getObject($entry));
    $_REP->addData($actArr, "activity");

    $calArr = [];
    require_once REC . 'Calories.php';
    $Calories = new Calories($_DBC, $sec);
    $entries = $Calories->readByDate();
    foreach ($entries as $entry) array_push($calArr, $Calories->getObject($entry));
    $_REP->addData($calArr, "calories");

    $weiArr = [];
    require_once REC . 'Weight.php';
    $Weight = new Weight($_DBC, $sec);
    $entries = $Weight->readByDate();
    foreach ($entries as $entry) array_push($weiArr, $Weight->getObject($entry));
    $_REP->addData($weiArr, "weight");

    require_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    $usr = $User->read()->getObject();
    if ($usr->image && $sec->premium) {
        require_once ROOT . 'Image.php';
        $Image = new Image($_DBC, $sec);
        $usr->image = $Image->read($usr->image)->getObject();
    } else $usr->image = false;
    $_REP->addData($usr, "user");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();