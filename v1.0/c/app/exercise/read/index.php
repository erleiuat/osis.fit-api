<?php

define('PROCESS', "App/Exercise/Read"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $sec = Sec::auth($_LOG);
    if(!$sec->premium) throw new ApiException(401, 'premium_required');
    
    $data = Core::getBody(['id' => ['array', false]]);
    
    require_once REC . 'Exercise.php';
    $Exercise = new Exercise($_DBC, $sec);
    require_once ROOT . 'Image.php';
    $Image = new Image($_DBC, $sec);


    if (gettype($data->id) === 'integer') {

        $obj = $Exercise->read($data->id)->getObject();
        if ($obj->image && $sec->premium) {
            $obj->image = $Image->read($obj->image)->getObject();
        } else $obj->image = false;
        $obj = (object) Core::formResponse($obj);
        $_REP->addData($obj, "item");

    } else if (gettype($data->id) === 'array') {

        $obj = $Exercise->readMultiple($data->id);
        $obj = Core::formResponse($obj);

        $_REP->addData($obj, "items");

    } else if (gettype($data->id) === 'string') {

        $id = Validate::number($data->id);
        $obj = $Exercise->read($id)->getObject();
        if ($obj->image && $sec->premium) {
            $obj->image = $Image->read($obj->image)->getObject();
        } else $obj->image = false;
        $obj = (object) Core::formResponse($obj);
        $_REP->addData($obj, "item");

    } else {
        throw new ApiException(500, "entity_processing_error");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();