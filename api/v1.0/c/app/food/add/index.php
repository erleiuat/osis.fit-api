<?php

define('PROCESS', "App/Food/Add"); /* Name of this Process */
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
        'title' => ['string', true, ['max' => 150]],
        'amount' => ['number', true],
        'caloriesPer100' => ['number', true],
        'imageID' => ['number', false]
    ]);
    $data->calories_per_100 = $data->caloriesPer100;
    
    if($data->imageID) {
        include_once REC . 'Image.php';
        $Image = new Image($_DBC, $sec);
        $data->image = $Image->set(['id'=>$data->imageID])->read()->getObject();
    }

    include_once REC . 'Food.php';
    $Food = new Food($_DBC, $sec);
    
    $obj = $Food->set($data)->create()->getObject();
    $obj = Core::formResponse($obj);

    $_REP->addData((int) $obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();