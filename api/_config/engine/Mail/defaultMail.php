<?php

class defaultMail {

    public function __construct() {

        ob_start();
        require('template/mail.html');
        $mail_html = ob_get_clean();

        ob_start();
        require('template/mail.css');
        $mail_css = ob_get_clean();

        $template = str_replace(
            '<style type="text/css" name="MAIL_CSS"></style>', 
            '<style type="text/css" name="MAIL_CSS">'.$mail_css.'</style>', 
            $mail_html
        );

        $this->body = $template;

    }

    public $body = "";
    public $subject = "";

    public $slots = [
        "main.title" => "{{main.title}}",
        "main.bgcolor" => "{{main.bgcolor}}",

        "header.heading1" => "{{header.heading1}}",
        "header.heading2" => "{{header.heading2}}",
        "header.image" => "{{header.image}}",
        "header.bgcolor" => "{{header.bgcolor}}",
        "header.heading1.color" => "{{header.heading1.color}}",
        "header.heading2.color" => "{{header.heading2.color}}",

        "content.heading" => "{{content.heading}}",
        "content.inner" => "{{content.inner}}",
        "content.inner2" => "{{content.inner2}}",
        "content.bgcolor" => "{{content.bgcolor}}",
        "content.heading.color" => "{{content.heading.color}}",
        "content.inner.color" => "{{content.inner.color}}",
        "content.inner2.color" => "{{content.inner2.color}}",

        "button.inner" => "{{button.inner}}",
        "button.href" => "{{button.href}}",
        "button.bgcolor" => "{{button.bgcolor}}",
        "button.inner.color" => "{{button.inner.color}}",

        "footer.inner" => "{{footer.inner}}",
        "footer.bgcolor" => "{{footer.bgcolor}}",
        "footer.inner.color" => "{{footer.inner.color}}"
    ];

    public $defaults = [
        "main.title" => "Default Mail Template",
        "main.bgcolor" => "#ffffff",

        "header.heading1" => "Make it easier",
        "header.heading2" => "Osis.fit Mail",
        "header.image" => "",
        "header.bgcolor" => "#444545",
        "header.heading1.color" => "#FFFFFF",
        "header.heading2.color" => "#2DC7FF",

        "content.heading" => "Willkommen!",
        "content.inner" => "Dies ist eine Vorlage",
        "content.inner2" => "Dies ist mittig",
        "content.bgcolor" => "#FAFAFA",
        "content.heading.color" => "#202020",
        "content.inner.color" => "#494949",
        "content.inner2.color" => "#494949",

        "button.href" => "#",
        "button.inner" => "",
        "button.bgcolor" => "#2DC7FF",
        "button.inner.color" => "#FFFFFF",

        "footer.inner" => "Osis.fit | Make it easier <br/> Basel, Switzerland",
        "footer.bgcolor" => "#F5F5F5",
        "footer.inner.color" => "#494949"
    ];

    public $images = [];
    public $attachments = [];

}