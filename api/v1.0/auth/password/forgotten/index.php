<?php

define('PROCESS', "Auth/Password/Forgotten"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/class/Auth.php';
$Auth = new Auth($_DBC);


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'mail' => ['string', false],
        'language' => ['string', false],
        'code' => ['string', false],
        'password' => ['password', false]
    ]);

    if ($data->mail && !$data->code && !$data->password) {

        $Auth->user->mail = $_LOG->identity = $data->mail;
        if ($Auth->check()->status === "verified") {

            $Auth->password_code = Core::randomString(20);
            $Auth->passwordForgotten();
            $_LOG->addInfo("Code created");

            include_once LOCATION . 'src/Mail.php';
            include_once LOCATION . 'src/class/User.php';
            $User = new User($_DBC, $Auth->user->id);
            $Mailer = new Mailer(new defaultMail());
            $Mailer->addReceiver($User->mail, $User->firstname, $User->lastname);

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
                $_REP->addData($Auth->password_code, "code");
                $_REP->addData($Mailer->getHTML(), "html");
            }

        }

    } else if ($data->mail && $data->code && $data->password) {

        $Auth->user->mail = $_LOG->identity = $data->mail;
        if ($Auth->check()->status === "verified") {

            $Auth->passwordForgotten($data->code)->passwordChange($data->password);

        } else if ($Auth->status === "locked") {
            throw new ApiException(403, "account_locked");
        } else if ($Auth->status === "unverified") {
            throw new ApiException(403, "account_not_verified");
        } else {
            throw new ApiException(401, "account_not_found");
        }

    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();