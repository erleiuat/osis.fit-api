<?php

define('PROCESS', "Food/Edit"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'id' => ['number', true],
        'title' => ['string', true, ['max' => 150]],
        'amount' => ['number', true],
        'caloriesPer100' => ['number', true],
        'imageID' => ['number', false]
    ]);

    include_once LOCATION . 'src/class/Food.php';
    $Food = new Food($_DBC, $sec);
    
    if($data->imageID) {
        include_once LOCATION . 'src/class/Image.php';
        $Image = new Image($_DBC, $sec);
        $data->image = $Image->set(['id'=>$data->imageID])->read()->getObject();
    }
    
    $data->calories_per_100 = $data->caloriesPer100;
    $obj = $Food->set($data)->edit()->getObject();
    $obj = Core::formResponse($obj);

    $_REP->addData((int) $obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();