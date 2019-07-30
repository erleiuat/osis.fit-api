<?php

define('PROCESS', "User/Read"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $sec = Sec::auth();
    
    include_once LOCATION . 'src/class/User.php';

    $User = new User($_DBC, $sec->id);
    $_REP->addData($User->getObject(), "user");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();