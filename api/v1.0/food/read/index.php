<?php

define('PROCESS', "Food/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'id' => ['number', false]
    ]);
    
    include_once LOCATION . 'src/class/Food.php';
    $Food = new Food($_DBC, $sec);
    include_once LOCATION . 'src/class/Image.php';
    $Image = new Image($_DBC, $sec);

    if($data->id) {

        $obj = $Food->read($data->id)->getObject();
        if($obj->image) $obj->image = $Image->read($obj->image)->getObject();
        $_REP->addData(1, "total");
        $_REP->addData($obj, "item");

    } else {

        $arr = [];
        $entries = $Food->readAll();
        foreach ($entries as $entry) {
            $entry['image'] = $entry['image_id'];
            $obj = $Food->getObject($entry);
            if($obj->image) $obj->image = $Image->read($obj->image)->getObject();
            array_push($arr, $obj);
        }

        $_REP->addData(count($arr), "total");
        $_REP->addData($arr, "items");

    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();