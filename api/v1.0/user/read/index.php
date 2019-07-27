<?php

define('PROCESS', "User/Profile/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/User.php';
$_user = new User($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    
    $_user->id = $auth->id;

    $_REP->addData("user", $_user->read());

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();