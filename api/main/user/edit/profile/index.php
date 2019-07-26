<?php

define('PROCESS', "User/Edit/Profile"); /* Name of this Process */
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
    $data = Core::getData(['firstname', 'lastname', 'birthdate', 'gender', 'height']);

    $_user->id = $authUser->id;
    $_user->firstname = Validate::string($data->firstname, 1, 150);
    $_user->lastname = Validate::string($data->lastname, 1, 150);
    $_user->birth = Validate::date($data->birthdate, true);
    $_user->gender = Validate::string($data->gender, 1, 10);
    $_user->height = Validate::number($data->height, 1, 900);

    $_user->editProfile();

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