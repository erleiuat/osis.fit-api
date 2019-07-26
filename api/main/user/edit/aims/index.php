<?php

define('PROCESS', "User/Edit/Aims"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/User.php';
$_user = new User($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Security::auth();
    $_LOG->user_id = $authUser->id;
    $data = Core::getData(['weight', 'date']);

    $_user->id = $authUser->id;
    $_user->aim_weight = Validate::number($data->weight, 1, 400);
    $_user->aim_date = Validate::date($data->date, true);

    $_user->editAims();

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