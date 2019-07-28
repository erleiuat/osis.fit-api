<?php

class defaultMail {

    public function __construct() {

        ob_start();
        require('default/mail.html');
        $mail_html = ob_get_clean();

        ob_start();
        require('default/mail.css');
        $mail_css = ob_get_clean();

        $template = str_replace(
            '<style type="text/css" name="MAIL_CSS"></style>', 
            '<style type="text/css" name="MAIL_CSS">'.$mail_css.'</style>', 
            $mail_html
        );

        $this->body = $template;
        $this->defaults["footer.inner"] = Env::mail_page_name." - ".Env::mail_slogan." <br/> <i>".Env::mail_creator."</i>";
        $this->defaults["header.image"] = Env::mail_logo_url;

    }

    public $body = "";
    public $subject = "";

    public $slots = [
        "main.title" => "{{main.title}}",
        "main.bgcolor" => "{{main.bgcolor}}",

        "header.image" => "{{header.image}}",
        "header.bgcolor" => "{{header.bgcolor}}",
        "header.heading" => "{{header.heading}}",
        "header.heading.color" => "{{header.heading.color}}",
        "header.subheading" => "{{header.subheading}}",
        "header.subheading.color" => "{{header.subheading.color}}",

        "content.bgcolor" => "{{content.bgcolor}}",
        "content.heading" => "{{content.heading}}",
        "content.heading.color" => "{{content.heading.color}}",
        "content.inner" => "{{content.inner}}",
        "content.inner.color" => "{{content.inner.color}}",
        "content.inner2" => "{{content.inner2}}",
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
        "main.bgcolor" => "#ffffff",

        "header.bgcolor" => "#444545",
        "header.heading.color" => "#2DC7FF",
        "header.subheading.color" => "#FFFFFF",

        "content.bgcolor" => "#FAFAFA",
        "content.heading.color" => "#202020",
        "content.inner.color" => "#494949",
        "content.inner2.color" => "#494949",

        "button.bgcolor" => "#2DC7FF",
        "button.inner.color" => "#FFFFFF",

        "footer.bgcolor" => "#F5F5F5",
        "footer.inner.color" => "#494949"

    ];

    public $images = [];
    public $attachments = [];

}