<?php

define('PROCESS', "User/Food/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/Security.php'; /* Load Security-Methods */
include_once LOCATION.'src/_objects/Food.php';
$_Food = new Food($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['title', 'amount', 'caloriesPer100']);
    
    $_Food->user_id = $auth->id;
    $_Food->title = Validate::string($data->title, 1, 30);
    $_Food->amount = Validate::number($data->amount, 1);
    $_Food->calories_per_100 = Validate::number($data->caloriesPer100, 1);

    $_Food->img_url = (isset($data->imgUrl) ? Validate::string($data->imgUrl) : null);
    $_Food->img_lazy = (isset($data->imgLazy) ? Validate::string($data->imgLazy) : null);
    $_Food->img_phrase = (isset($data->imgPhrase) ? Validate::string($data->imgPhrase) : null);
    
    $_REP->content = array(
        "object"=>$_Food->add(),
        "id"=>$_Food->id
    );

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();