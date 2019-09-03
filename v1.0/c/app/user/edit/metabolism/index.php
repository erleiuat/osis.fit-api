<?php

define('PROCESS', "App/User/Edit/Metabolism"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'birthdate' => ['date', false],
        'height' => ['number', false],
        'gender' => ['string', false],
        'pal' => ['number', false]
    ]);

    require_once REC . 'User.php';
    $User = new User($_DBC, $sec);
    
    $obj = $User->read()->set($data)->editMetabolism()->read()->getObject();

    if($obj->image && $sec->premium) {
        require_once ROOT . 'Image.php';
        $Image = new Image($_DBC, $sec);
        $obj->image = $Image->read($obj->image)->getObject();
    } else $obj->image = false;

    $_REP->addData($obj, "item");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();