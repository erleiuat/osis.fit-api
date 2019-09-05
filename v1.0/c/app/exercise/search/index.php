<?php

define('PROCESS', "App/Exercise/Search"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $data = Core::getBody([
        'public' => ['boolean', false],
        'query' => ['string', false],
        'bodyparts' => ['array', false]
    ]);

    require_once REC . 'Exercise.php';
    $Exercise = new Exercise($_DBC, $sec);
    $items = [];    

    if (!$data->public) {

        $items = $Exercise->find($data->query, $data->bodyparts, $sec->id, false);
        
        foreach ($items as $key => $entry) {
            if ($entry['bodyparts']) $entry['bodyparts'] = explode(',', $entry['bodyparts']);
            $items[$key] = $Exercise->getSearchObject($entry);
        }
        
    } else if ($data->public) {

        $items = $Exercise->find($data->query, $data->bodyparts, $sec->id, true);
        require_once ROOT . 'Image.php';
        $Image = new Image($_DBC, $sec);
        foreach ($items as $key => $entry) {
            if ($entry['bodyparts']) $entry['bodyparts'] = explode(',', $entry['bodyparts']);
            $items[$key] = $Exercise->getSearchObject($entry, false, $Image);
        }

    }

    $_REP->addData($items, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();