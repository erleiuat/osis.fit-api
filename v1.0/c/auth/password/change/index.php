<?php

define('PROCESS', "Auth/Password/Change"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'current' => ['string', true],
        'new' => ['password', true]
    ]);
    
    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, $sec);

    if ($Auth->check($sec->mail)->status === "verified") {
        
        if (!$Auth->pass($data->current)) throw new ApiException(403, "password_wrong");

        require_once ROOT . 'AccountPortal.php';
        $Account = new AccountPortal($_DBC, $Auth->getAccount());
        $Account->passChange($Auth->id, $data->new);

        $Auth->removeRefresh();

    } else {
        if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
        else if ($Auth->status === "unverified") throw new ApiException(403, "account_not_verified");
        else throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();