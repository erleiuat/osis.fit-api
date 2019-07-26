<?php

class MailTemplate {

    public $subject = [
        "en"=>"Activate your Osis.fit Account!",
        "de"=>"Aktiviere dein Konto bei Osis.fit!"
    ];

    public $body = [
        "en"=>"mail/templates/en.html",
        "de"=>"mail/templates/de.html"
    ];

    public $slots = [
        "subject" => [],
        "body" => [
            "name" => "{{name}}", 
            "mail" => "{{mail}}", 
            "url" => "{{url}}", 
            "code" => "{{code}}"
        ]
    ];

    public $images = [
        ["bg_wave_1", "mail/images/bg_wave_1.png"],
        ["bg_wave_2", "mail/images/bg_wave_2.png"],
        ["appap_color", "mail/images/appap_color2x.png"]
    ];

    public $attachments = [];

}