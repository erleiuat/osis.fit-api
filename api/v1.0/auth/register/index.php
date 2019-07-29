<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/class/User.php';
include_once LOCATION . 'src/class/Auth.php';
$Auth = new Auth($_DBC, new User($_DBC));

// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'firstname' => ['string', true, ['min' => 1, 'max' => 150]],
        'lastname' => ['string', true, ['min' => 1, 'max' => 150]],
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'password' => ['password', true, ['min' => 8, 'max' => 255]],
        'language' => ['string', false, ['max' => 5]]
    ]);
    
    $Auth->user->mail = $_LOG->identity = $data->mail;
    $Auth->user->firstname = $data->firstname;
    $Auth->user->lastname = $data->lastname;
    $Auth->user->level = "user";
    
    if($Auth->check()->status) {
        throw new ApiException(403, "mail_in_use", ["entity"=>"mail"]);
    }

    $Auth->user->create();
    $_LOG->addInfo("User created");

    $Auth->verify_code = Core::randomString(10); 
    $Auth->password = $data->password;
    $Auth->register();
    $_LOG->addInfo("User registered");

    include_once LOCATION . 'src/Mail.php';
    $Mailer = new Mailer(new defaultMail());
    $Mailer->addReceiver($Auth->user->mail, $Auth->user->firstname, $Auth->user->lastname);

    if ($data->language === "de") {
        include_once 'mail/content_de.php';
    } else {
        include_once 'mail/content_en.php';
    }
    $Mailer->prepare();
        
    if (Env::api_env === "prod") {
        $Mailer->send();
        $_LOG->addInfo('Verification-Mail sent');
    } else {
        //echo $Mailer->getHTML(); die();
        $_REP->addData($Auth->verify_code, "code");
        $_REP->addData($Mailer->getHTML(), "html");
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();