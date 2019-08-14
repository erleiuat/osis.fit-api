<?php

define('PROCESS', "App/Food/Favorite/DBList"); /* Name of this Process */
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

    $databases = [
        [
            "text" => "Schweizerische Nährwertdatenbank",
            "value" => [
                "title" => "sndb",
                "url" => "https://api.webapp.prod.blv.foodcase-services.com/BLV_WebApp_WS/webresources/BLV/foods",
                "searchParam" => "?component=25640&operator=%3E&amount=0&lang=de&search=",
                "location" => "switzerland",
                "language" => "de"
            ]
        ]
    ];

    $_REP->addData(count($databases), "total");
    $_REP->addData($databases, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();