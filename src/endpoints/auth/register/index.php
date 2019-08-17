<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'firstname' => ['string', true, ['min' => 1, 'max' => 150]],
        'lastname' => ['string', true, ['min' => 1, 'max' => 150]],
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['password', true, ['min' => 8, 'max' => 255]],
        'language' => ['string', false, ['max' => 5]]
    ]);


    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);
    if ($Auth->check($data->mail)->status) {
        throw new ApiException(403, "mail_in_use", ["entity"=>"mail"]);
    }

    $verify_code = Core::randomString(10);

    require_once ROOT . 'AccountPortal.php';
    require_once ROOT . 'Mail.php';
    require_once REC . 'User.php';


    $Account = new AccountPortal($_DBC);
    $Account->createAccount($data->mail);
    $_LOG->addInfo("Account created");
    $Account->createAuth($data->password, $verify_code);
    $_LOG->addInfo("Auth created");
    

    $User = new User($_DBC, $Account->getAccount());
    $User->create($data->firstname, $data->lastname);
    $_LOG->addInfo("User created");


    $Mailer = new Mailer(new defaultMail());
    $Mailer->addReceiver($data->mail, $data->firstname, $data->lastname);

    if ($data->language === "de") require_once 'mail/content_de.php';
    else require_once 'mail/content_en.php';
    $Mailer->prepare();
        
    if (Env_api::env === "prod") {
        $Mailer->send();
        $_LOG->addInfo('Verification-Mail sent');
    } else if (Env_api::env === "test") {
        $Mailer->send();
        $_REP->addData($verify_code, "code");
        $_LOG->addInfo('Verification-Mail sent');
    } else if (Env_api::env === "local") {
        //echo $Mailer->getHTML(); die();
        $_REP->addData($verify_code, "code");
        $_REP->addData($Mailer->getHTML(), "html");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();