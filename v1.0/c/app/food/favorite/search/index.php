<?php

define('PROCESS', "App/Food/Favorite/Search"); /* Name of this Process */
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

    $items = [
        [
            "title" => "Blabla",
            "image" => false,
            "caloriesPer100" => 55,
            "calories" => 123,
        ]
    ];

    $_REP->addData(count($items), "total");
    $_REP->addData($items, "items");

} catch (\Exception $e) { 
    Core::processException($_REP, $_LOG, $e); 
    $_LOG->write();
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------