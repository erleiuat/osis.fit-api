<?php

define('PROCESS', "Auth/Login"); /* Name of this Process */
define('ROOT', "../../../src/"); /* Path to root */      
define('REC', "../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getBody([
        'identifier' => ['string', true, ['min' => 1, 'max' => 250]],
        'password' => ['string', true]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    $_LOG->addInfo($data->identifier);

    if ($Auth->check($data->identifier)->status === "verified") {

        if (!$Auth->pass($data->password)) throw new ApiException(403, "password_wrong");

        $jti = Core::randomString(20);
        $phrase = Core::randomString(20);
        $Auth->initRefresh($jti, $phrase);

        $token_data = $Auth->token();
        $sec = Sec::placeAuth($token_data);
        $_REP->addData($sec, "tokens");

        $premium = false;
        $sub = $token_data->subscription;
        if ($sub->id && !$sub->deleted) {
            if ($sub->status === 'active') $premium = true;
            else if ($sub->status === 'non_renewing') $premium = true;
            else if ($sub->status === 'in_trial') $premium = true;
        }



        $actArr = [];
        require_once REC . 'Activity.php';
        $Activity = new Activity($_DBC, $token_data->account);
        $entries = $Activity->readByDate();
        foreach ($entries as $entry) array_push($actArr, $Activity->getObject($entry));
        $_REP->addData($actArr, "activity");

        $calArr = [];
        require_once REC . 'Calories.php';
        $Calories = new Calories($_DBC, $token_data->account);
        $entries = $Calories->readByDate();
        foreach ($entries as $entry) array_push($calArr, $Calories->getObject($entry));
        $_REP->addData($calArr, "calories");

        $weiArr = [];
        require_once REC . 'Weight.php';
        $Weight = new Weight($_DBC, $token_data->account);
        $entries = $Weight->readByDate();
        foreach ($entries as $entry) array_push($weiArr, $Weight->getObject($entry));
        $_REP->addData($weiArr, "weight");

        require_once REC . 'User.php';
        $User = new User($_DBC, $token_data->account);
        $usr = $User->read()->getObject();
        if ($usr->image && $premium) {
            require_once ROOT . 'Image.php';
            $Image = new Image($_DBC, $token_data->account);
            $usr->image = $Image->read($usr->image)->getObject();
        } else $usr->image = false;
        $_REP->addData($usr, "user");



    } else if ($Auth->status === "locked") {
        throw new ApiException(403, "account_locked");
    } else if ($Auth->status === "unverified") {
        throw new ApiException(403, "account_not_verified");
    } else {
        throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) {
    Core::processException($_REP, $_LOG, $e);
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();