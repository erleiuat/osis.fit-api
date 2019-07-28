<?php

define('PROCESS', "Auth/Password/Forgotten"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'src/_objects/Auth.php';
$_Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['string', false],
        'language' => ['string', false],
        'code' => ['string', false],
        'password' => ['password', false]
    ]);

    if ($data->mail && !$data->code && !$data->password) {

        $_Auth->mail = $_LOG->identity = $data->mail;
        if($_Auth->checkStatus()->state === "verified"){

            $_Auth->pw_code = Core::randomString(20);
            $_Auth->passwordForgotten();
            $_LOG->addInfo("Code created");

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
                $_REP->addData($_Auth->pw_code, "code");
                $_REP->addData($_Mailer->getHTML(), "html");
            }

        }

    } else if ($data->mail && $data->code && $data->password) {

        $_Auth->mail = $_LOG->identity = $data->mail;
        if($_Auth->checkStatus()->state === "verified"){
            $_Auth->passwordForgotten($data->code)->passwordChange($data->password);
        }

    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();