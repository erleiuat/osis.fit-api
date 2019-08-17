<?php

define('PROCESS', "App/Food/Favorite/Delete"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/class/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    if(!$sec->premium) throw new ApiException(401, 'premium_required');
    
    $data = Core::getBody(['id' => ['number', true]]);
    
    require_once REC . 'FoodFavorite.php';
    $FoodFavorite = new FoodFavorite($_DBC, $sec);

    $FoodFavorite->delete($data->id);

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();