<?php

define('PROCESS', "Auth/Register"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */           

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Mail.php'; 
$_Mail = new Mail();

include_once LOCATION.'_objects/Auth.php';
$_Auth = new Auth($_DBC);

// ------------------ SCRIPT -----------------
try {
    
    $data = Core::getData(['firstname', 'lastname', 'mail', 'password', 'language']);
    $_Auth->mail = Validate::mail($data->mail, 1, 90);
    $_LOG->identity = $_Auth->mail;
    
    if($_Auth->check_state()){

        $_REP->setStatus(422, 'mail_in_use');
        $_LOG->setStatus('info', 'mail_in_use');

    } else {
        
        // Create verification code
        $code = '';
        $chars = '123456789';
        for ($i = 0; $i < 10; $i++) $code .= $chars[rand(0, strlen($chars) - 1)];
        
        $_LOG->addInfo("Code created");

        $language = Validate::string($data->language, 0, 5);
        $password = Validate::password($data->password, 8, 255);

        $_Auth->code = password_hash($code, PASSWORD_BCRYPT);
        $_Auth->password = password_hash($password, PASSWORD_BCRYPT);
        $_Auth->firstname = Validate::string($data->firstname, 1, 150);
        $_Auth->lastname = Validate::string($data->lastname, 1, 150);

        $_LOG->addInfo("Required Params registered");

        $_Auth->register();

        $_LOG->addInfo("User registration successful");

        include_once 'MailTemplate.php';
        $_mTemplate = new MailTemplate();
        $_Mail->setLanguage("de");
        $_Mail->addReceiver($_Auth->mail, $_Auth->firstname, $_Auth->lastname);
        $_Mail->setTemplate($_mTemplate);
        $_Mail->setContent("body", [
            "name" => $_Auth->firstname, 
            "mail" => $_Auth->mail, 
            "url" => "osis.fit/auth/verify", 
            "code" => $code
        ]);
        $_Mail->send();

        $_LOG->addInfo('Mail has been sent.');        

    }

} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync();

// -------------- AFTER RESPONSE -------------
$_LOG->write();