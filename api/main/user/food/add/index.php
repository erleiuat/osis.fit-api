<?php

define('PROCESS', "User/Food/Add"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/Food.php';
$_Food = new Food($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth();
    $_LOG->user_id = $authUser->id;
    $data = Core::getData(['title', 'amount', 'caloriesPer100']);
    
    $_Food->user_id = $authUser->id;
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