<?php

define('PROCESS', "App/Food/Favorite/Read"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');
    
    require_once REC . 'FoodFavorite.php';
    $FoodFavorite = new FoodFavorite($_DBC, $sec);

    $arr = [];
    $entries = $FoodFavorite->readAll();

    foreach ($entries as $entry) {
        $obj = $FoodFavorite->getObject($entry);
        $obj = (object) Core::formResponse($obj);
        array_push($arr, $obj);
    }

    $_REP->addData(count($arr), "total");
    $_REP->addData($arr, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();