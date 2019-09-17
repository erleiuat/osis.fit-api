<?php

define('PROCESS', "App/Exercise/Add"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */
require_once ROOT . 'Image.php';

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $data = Core::getBody([
        'public' => ['boolean', false],
        'title' => ['string', true, ['max' => 150]],
        'imageID' => ['number', false],
        'description' => ['string', false],
        'content' => ['string', false],
        'type' => ['string', true],
        'calories' => ['number', false],
        'bodyparts' => ['array', false]
    ]);

    if ($data->imageID && $sec->premium) {
        $Image = new Image($_DBC, $sec);
        //$data->image = $Image->set(['id'=>$data->imageID])->read()->getObject();
        $data->image = (object) ["id" => $data->imageID];
    }

    if (!Sec::permit($sec->level, ['moderator', 'admin'])) $data->public = false;

    require_once REC . 'Exercise.php';
    $Exercise = new Exercise($_DBC, $sec);
    
    $obj = $Exercise->set($data)->create()->read()->getObject();
    if ($obj->image && $sec->premium) {
        $obj->image = $Image->read($obj->image)->getObject();
    } else $obj->image = false;
    
    $obj = (object) Core::formResponse($obj);

    $_REP->addData($obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();