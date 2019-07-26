<?php

class InfoMail {

    public $subject = [
        "en"=>"Thank you for your request!",
        "de"=>"Vielen dank für deine Anfrage!"
    ];

    public $body = [
        "en"=>"mail/templates/info_en.html",
        "de"=>"mail/templates/info_de.html"
    ];

    public $slots = [
        "subject" => [],
        "body" => [
            "firstname" => "{{firstname}}", 
            "lastname" => "{{lastname}}", 
            "mail" => "{{mail}}", 
            "subject" => "{{subject}}",
            "message" => "{{message}}"
        ]
    ];

    public $images = [
        ["bg_wave_1", "mail/images/bg_wave_1.png"],
        ["bg_wave_2", "mail/images/bg_wave_2.png"],
        ["appap_color", "mail/images/appap_color2x.png"]
    ];

    public $attachments = [];

}