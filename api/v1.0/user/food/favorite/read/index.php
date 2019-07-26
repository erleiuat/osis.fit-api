<?php

define('PROCESS', "User/Food/Favorite/Read"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/FoodFavorite.php';
$_fFav = new FoodFavorite($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Sec::auth();
    $_LOG->user_id = $authUser->id;    

    $_fFav->user_id = $authUser->id;

    $_REP->addContent("foodFavorite", $_fFav->read());

} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync(); /* End Async-Request */

// -------------- AFTER RESPONSE -------------
$_LOG->write();