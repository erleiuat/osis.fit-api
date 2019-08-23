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

        require_once REC . 'User.php';
        $User = new User($_DBC, $token_data->account);
        $obj = $User->read()->getObject();

        $premium = false;
        $sub = $token_data->subscription;
        if ($sub->id && !$sub->deleted) {
            if ($sub->status === 'active') $premium = true;
            else if ($sub->status === 'non_renewing') $premium = true;
            else if ($sub->status === 'in_trial') $premium = true;
        }

        if($obj->image && $premium) {
            require_once ROOT . 'Image.php';
            $Image = new Image($_DBC, $token_data->account);
            $obj->image = $Image->read($obj->image)->getObject();
        }
        else $obj->image = false;
    
        $_REP->addData($obj, "user");

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