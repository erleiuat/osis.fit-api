<?php

class ContactMail {

    public $subject = [
        "en"=>"Osis.fit Contact Request: {{subject}}"
    ];

    public $body = [
        "en"=>"mail/templates/contact.html"
    ];

    public $slots = [
        "subject" => ["subject"=>"{{subject}}"],
        "body" => [
            "firstname" => "{{firstname}}", 
            "lastname" => "{{lastname}}", 
            "mail" => "{{mail}}", 
            "subject" => "{{subject}}",
            "message" => "{{message}}",
            "language" => "{{language}}",
            "userid" => "{{userid}}",
        ]
    ];

    public $images = [
        ["bg_wave_1", "mail/images/bg_wave_1.png"],
        ["bg_wave_2", "mail/images/bg_wave_2.png"],
        ["appap_color", "mail/images/appap_color2x.png"]
    ];

    public $attachments = [];

}