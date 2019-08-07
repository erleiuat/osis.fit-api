<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Path to root */      
define('REC', "../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
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

    include_once LOCATION . 'src/Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $data->mail]);
    
    if($Auth->check()->status) throw new ApiException(403, "mail_in_use", ["entity"=>"mail"]);

    include_once REC . 'User.php';
    $User = new User($_DBC, [
        "mail" => $data->mail,
        "level" => "user"
    ]);
    
    $User->set([
        "firstname" => $data->firstname,
        "lastname" => $data->lastname
    ])->create();

    $_LOG->addInfo("User created");
    
    $Auth->setUser([
        "id" => $User->user->id,
        "mail" => $data->mail,
        "level" => "user"
    ])->set([
        "verify_code" => Core::randomString(10),
        "password" => $data->password
    ])->register();
    
    $_LOG->addInfo("User registered");

    include_once LOCATION . 'src/Mail.php';
    $Mailer = new Mailer(new defaultMail());
    $Mailer->addReceiver($User->user->mail, $User->firstname, $User->lastname);

    if ($data->language === "de") include_once 'mail/content_de.php';
    else include_once 'mail/content_en.php';
    $Mailer->prepare();
        
    if (Env_api::env === "prod") {
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