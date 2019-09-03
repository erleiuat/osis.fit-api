<?php

define('PROCESS', "App/Food/Favorite/Add"); /* Name of this Process */
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

    $data = Core::getBody([
        'id' => ['number', true],
        'image' => ['string', false],
        'title' => ['string', true, ['max' => 150]],
        'amount' => ['number', false],
        'caloriesPer100' => ['number', false],
        'total' => ['number', false]
    ]);
    $data->calories_per_100 = $data->caloriesPer100;

    require_once REC . 'FoodFavorite.php';
    $FoodFavorite = new FoodFavorite($_DBC, $sec);
    
    $obj = $FoodFavorite->set($data)->create()->getObject();
    $obj = (object) Core::formResponse($obj);

    $_REP->addData($obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();