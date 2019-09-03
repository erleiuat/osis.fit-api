<?php

define('PROCESS', "App/Training/Add"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if (!$sec->premium) throw new ApiException(401, 'premium_required');

    $data = Core::getBody([
        'public' => ['boolean', false],
        'title' => ['string', true, ['max' => 150]],
        'description' => ['string', false],
        'exercises' => ['array', false]
    ]);

    if (!Sec::permit($sec->level, ['moderator', 'admin'])) $data->public = false;

    require_once REC . 'Training.php';
    $Training = new Training($_DBC, $sec);
    
    $Training->set($data)->create();

    foreach ($data->exercises as $exercise) {
        $Training->addExercise($exercise->id, $exercise->repetitions);
    }

    $obj = $Training->read()->getObject();
    $obj = (object) Core::formResponse($obj);

    $obj->exercises = [];

    if ($Training->exercises) {

        require_once REC . 'Exercise.php';
        $Exercise = new Exercise($_DBC);

        foreach ($Training->exercises as $exercise) {

            $tmp = $Exercise->read($exercise['id'])->getObject();
            $tmp->repetitions = $exercise['repetitions'];
            array_push($obj->exercises, $tmp);

        }

    }

    $_REP->addData($obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();