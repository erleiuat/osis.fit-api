<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Mail.php'; 
include_once LOCATION.'_objects/Auth.php';
$_Mail = new Mail();
$_Auth = new Auth($_DBC);

// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody(
        ['firstname', 'string', true, ['min' => 1, 'max' => 150]],
        ['lastname', 'string', true, ['min' => 1, 'max' => 150]],
        ['mail', 'mail', true, ['min' => 1, 'max' => 90]],
        ['password', 'password', true, ['min' => 8, 'max' => 255]],
        ['language', 'string', false, ['max' => 5]]
    );
    
    $_LOG->identity = $data->mail;
    $_Auth->mail = $data->mail;
    $_Auth->firstname = $data->firstname;
    $_Auth->lastname = $data->lastname;
    
    if($_Auth->check_state()){
        $_REP->setStatus(422, 'mail_in_use', ["entity"=>"mail"]);
        $_LOG->setStatus('info', 'mail_in_use');
    } else {
        
        $_Auth->verify_code = Core::randomString(10);     
        $_Auth->password = $data->password;

        $_Auth->register();
        $_LOG->addInfo("User registered");

        include_once 'MailTemplate.php';
        $_mTemplate = new MailTemplate();
        $_Mail->setLanguage($data->language ? $data->language : "en")
        ->addReceiver($_Auth->mail, $_Auth->firstname, $_Auth->lastname)
        ->setTemplate($_mTemplate)
        ->setContent("body", [
            "name" => $_Auth->firstname, 
            "mail" => $_Auth->mail, 
            "url" => "osis.fit/auth/verify", 
            "code" => $_Auth->verify_code
        ])
        ->prepare();
            
        if (Env::api_env === "prod") {
            $_Mail->send();
            $_LOG->addInfo('Verification-Mail sent');
        } else {
            $_REP->addData($_Auth->verify_code, "code");
            $_REP->addData($_Mail->getHTML(), "html");
        }

    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();