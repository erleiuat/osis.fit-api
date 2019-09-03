<?php

define('PROCESS', "App/User/Edit/Profile"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */
require_once ROOT . 'Image.php';

// ------------------ SCRIPT -----------------
try {
    
    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'firstname' => ['string', false, ['max' => 150]],
        'lastname' => ['string', false, ['max' => 150]],
        'imageID' => ['number', false]
    ]);

    if ($data->imageID && $sec->premium) {
        $Image = new Image($_DBC, $sec);
        //$data->image = $Image->set(['id'=>$data->imageID])->read()->getObject();
        $data->image = (object) ["id" => $data->imageID];
    }

    require_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    
    $obj = $User->read()->set($data)->editProfile()->getObject();

    if ($obj->image && $sec->premium) {
        $obj->image = $Image->read($obj->image->id)->getObject();
    } else $obj->image = false;

    $_REP->addData($obj, "item");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();
