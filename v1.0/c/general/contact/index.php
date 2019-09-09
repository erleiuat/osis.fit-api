<?php

define('PROCESS', "General/Contact"); /* Name of this Process */
define('ROOT', "../../../../src/"); /* Path to root */      
define('REC', "../../../src/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------


// ------------------ SCRIPT -----------------
try {

    $data = Core::getBody([
        'firstname' => ['string', true, ['min' => 1, 'max' => 150]],
        'lastname' => ['string', true, ['min' => 1, 'max' => 150]],
        'mail' => ['mail', true, ['min' => 1, 'max' => 90]],
        'subject' => ['string', true],
        'message' => ['string', true],
        'language' => ['string', false, ['max' => 5]]
    ]);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC);

    if ($Auth->check($data->mail)->status) $uDetail = $Auth->token();
    else $uDetail = (object) [
        "level" => "Not Set",
        "account" => (object) [
            "id" => "Not Set",
            "username" => "Not Set"
        ],
        "subscription" => (object) [
            "id" => "Not Set",
            "status" => "Not Set",
            "deleted" => "Not Set",
            "plan" => "Not Set"
        ]
    ];

    require_once ROOT . 'Mail.php';

    $Mailer1 = new Mailer(new defaultMail());    
    $Mailer2 = new Mailer(new defaultMail());

    $Mailer1->addReceiver(ENV_mail::support_adress, "Osis.Fit", "Support");
    $Mailer2->addReceiver($data->mail, $data->firstname, $data->lastname);

    require_once 'mailToSupport/content.php';
    if ($data->language === "de") require_once 'mailToUser/content_de.php';
    else require_once 'mailToUser/content_en.php';

    $Mailer1->prepare();
    $Mailer2->prepare();
        
    if (Env_api::env === "prod") {
        $Mailer1->send();
        $Mailer2->send();
    } else if (Env_api::env === "test") {
        $Mailer1->send();
        $Mailer2->send();
    } else if (Env_api::env === "local") {
        //echo $Mailer1->getHTML();
        //echo $Mailer2->getHTML();
        die();
    }

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();