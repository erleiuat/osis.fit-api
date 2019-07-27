<?php

define('PROCESS', "General/Blog/Read/Preview"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Article.php';
$_Article = new Article($_DBC);

// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth(false);
    $_LOG->user_id = ($auth ? $auth->id : 'none');

    $_Article->publication_date = date('Y-m-d', time());
    $_REP->addData("preview", $_Article->readPreview());
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();