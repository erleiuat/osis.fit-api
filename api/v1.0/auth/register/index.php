<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/class/Auth.php';
$_Auth = new Auth($_DBC);

// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'firstname' => ['string', true, ['min' => 1, 'max' => 150]],
        'lastname' => ['string', true, ['min' => 1, 'max' => 150]],
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['password', true, ['min' => 8, 'max' => 255]],
        'language' => ['string', false, ['max' => 5]]
    ]);
    
    $_Auth->mail = $_LOG->identity = $data->mail;
    $_Auth->firstname = $data->firstname;
    $_Auth->lastname = $data->lastname;
    
    if($_Auth->checkStatus()->state) throw new ApiException(403, "mail_in_use", ["entity"=>"mail"]);

    $_Auth->verify_code = Core::randomString(10); 
    $_Auth->password = $data->password;

    $_Auth->register();
    $_LOG->addInfo("User registered");

    include_once LOCATION.'src/Mail.php';
    $_Mailer = new Mailer(new defaultMail());
    $_Mailer->addReceiver($_Auth->mail, $_Auth->firstname, $_Auth->lastname);

    if ($data->language === "de") include_once 'mail/content_de.php';
    else include_once 'mail/content_en.php';
    $_Mailer->prepare();
        
    if (Env::api_env === "prod") {
        $_Mailer->send();
        $_LOG->addInfo('Verification-Mail sent');
    } else {
        //echo $_Mailer->getHTML(); die();
        $_REP->addData($_Auth->verify_code, "code");
        $_REP->addData($_Mailer->getHTML(), "html");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();