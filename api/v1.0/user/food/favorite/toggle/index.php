<?php

define('PROCESS', "Template/User/Favorite/Toggle"); /* Name of this Process */
define('LOCATION', "../../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/class/FoodFavorite.php';
$_fFav = new FoodFavorite($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;    
    $data = Core::getData(['id']);

    $_fFav->user_id = $auth->id;

    $_fFav->id = Validate::number($data->id, 1);

    $_fFav->title = Validate::string($data->title, 1, 255);
    $_fFav->amount = Validate::number($data->amount);
    $_fFav->calories_per_100 = Validate::number($data->caloriesPer100);

    $_fFav->information = (isset($data->information) ? Validate::string($data->information) : null);
    $_fFav->source = (isset($data->source) ? Validate::string($data->source) : null);

    $_fFav->img_url = (isset($data->imgUrl) ? Validate::string($data->imgUrl) : null);
    $_fFav->img_lazy = (isset($data->imgLazy) ? Validate::string($data->imgLazy) : null);
    $_fFav->img_phrase = (isset($data->imgPhrase) ? Validate::string($data->imgPhrase) : null);

    $_REP->addData("added", $_fFav->toggle());

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();