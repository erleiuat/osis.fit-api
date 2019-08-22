<?php

define('PROCESS', "Auth/Delete"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'username' => ['username', true],
        'password' => ['string', true]
    ]);
    
    if ($sec->username !== $data->username) throw new Exception("username_wrong", 403);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);
    
    if ($Auth->check($sec->mail)->status === "verified") {
        
        if (!$Auth->pass($data->password)) throw new ApiException(403, "password_wrong");

        require_once ROOT . 'AccountPortal.php';
        $Account = new AccountPortal($_DBC, $Auth->getAccount());
        $Account->disable($Auth->id);
        
        Sec::removeAuth();

    } else {
        if ($Auth->status === "locked") throw new ApiException(403, "account_locked");
        else if ($Auth->status === "unverified") throw new ApiException(403, "account_not_verified");
        else throw new ApiException(401, "account_not_found");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();