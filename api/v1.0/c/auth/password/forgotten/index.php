<?php

define('PROCESS', "Auth/Password/Forgotten"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Authentication.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['string', false],
        'language' => ['string', false],
        'code' => ['string', false],
        'password' => ['password', false]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $data->mail]);

    if ($Auth->check()->status === "verified") {

        if ($data->mail && !$data->code && !$data->password) {

            $Auth->password_code = Core::randomString(20);
            $Auth->passwordForgotten();
            $_LOG->addInfo("Code created");
            
            require_once REC . 'User.php';
            $User = new User($_DBC, $Auth->user);
            $User->read();
            
            require_once ROOT . 'Mail.php';
            $Mailer = new Mailer(new defaultMail());
            $Mailer->addReceiver($data->mail, $User->firstname, $User->lastname);

            if ($data->language === "de") require_once 'mail/content_de.php';
            else require_once 'mail/content_en.php';
            $Mailer->prepare();
                
            if (Env_api::env === "prod") {
                $Mailer->send();
                $_LOG->addInfo('Verification-Mail sent');
            } else {
                //echo $Mailer->getHTML(); die();
                $_REP->addData($Auth->password_code, "code");
                $_REP->addData($Mailer->getHTML(), "html");
            }

        } else if ($data->mail && $data->code && $data->password) {
            $Auth->passwordForgotten($data->code)->passwordChange($data->password);
        }

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