<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    /* ------------- PRIVATE PARAMS ------------- */
    private $mail;
    private $slot_options = [
        "SUBJECT" => "s",
        "Subject" => "s",
        "subject" => "s",
        "BODY" => "b",
        "Body" => "b",
        "body" => "b"
    ];

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    
    public $from_name;
    public $from_adress;
    public $receivers = [];
    
    public $template = false;
    public $subject = "";
    public $body = [];


    /* ------------------ INIT ------------------ */
    public function __construct($template = false) {
        
        $this->mail = new PHPMailer(true);
        $this->mail->isHTML(true);
        $this->mail->CharSet = "UTF-8";

        $this->from_name = Env::mail_from_name;
        $this->from_adress = Env::mail_from_adress;
        if($template) $this->template = $template;

    }

    /* ----------------- METHODS ---------------- */

    public function addReceiver($adress, $firstname = false, $lastname = false){
        if($lastname) $firstname .= " ".$lastname;
        array_push($this->receivers, [$adress, $firstname]);
        return $this;
    }

    public function setTemplate($templateClass){
        $this->template = $templateClass;
        return $this;
    }

    public function prepare() {

        $this->mail->setFrom($this->from_adress, $this->from_name);
        
        for ($i=0; $i < count($this->receivers); $i++) {
            $this->mail->addAddress($this->receivers[$i][0], $this->receivers[$i][1]);
        }
        for ($i=0; $i < count($this->template->images); $i++) {
            $this->mail->AddEmbeddedImage($this->template->images[$i][1], $this->template->images[$i][0]);
        }

        $this->mail->Subject = $this->subject;
        $this->mail->Body = $this->template->body;

        $bSearch = []; $bReplace = [];
        foreach ($this->template->slots as $key => $value) {
            array_push($bSearch, $value);
            if(isset($this->body[$key])) array_push($bReplace, $this->body[$key]);
            else if(isset($this->template->defaults[$key])) array_push($bReplace, $this->template->defaults[$key]);
            else array_push($bReplace, "");
        }
        
        $this->mail->Body = str_replace($bSearch, $bReplace, $this->mail->Body);
        return $this;

    }

    public function getHTML() {
        return $this->mail->Body;
    }

    public function send() {
        $this->mail->send();
        return $this;
    }

}