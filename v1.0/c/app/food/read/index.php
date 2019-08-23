<?php

define('PROCESS', "App/Food/Read"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody(['id' => ['number', false]]);
    
    require_once REC . 'Food.php';
    $Food = new Food($_DBC, $sec);
    require_once ROOT . 'Image.php';
    $Image = new Image($_DBC, $sec);

    if($data->id) {

        $obj = $Food->read($data->id)->getObject();
        if ($obj->image && $sec->premium) $obj->image = $Image->read($obj->image)->getObject();
        else $obj->image = false;
        $obj = Core::formResponse($obj);

        $_REP->addData(1, "total");
        $_REP->addData($obj, "item");

    } else {

        $arr = [];
        $entries = $Food->readAll();

        foreach ($entries as $entry) {
            $entry['image'] = $entry['image_id'];
            $obj = $Food->getObject($entry);
            if($obj->image && $sec->premium) $obj->image = $Image->read($obj->image)->getObject();
            else $obj->image = false;
            $obj = Core::formResponse($obj);
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