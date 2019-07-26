<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {

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

    private $subjectContent = [];
    private $bodyContent = [];

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    
    public $from_name;
    public $from_adress;
    public $receivers = [];

    public $language = "en";
    public $template;


    /* ------------------ INIT ------------------ */
    public function __construct() {
        
        $this->mail = new PHPMailer(true);
        $this->mail->isHTML(true);
        $this->mail->CharSet = "UTF-8";

        $this->from_name = Env::mail_from_name;
        $this->from_adress = Env::mail_from_adress;

    }

    /* ----------------- METHODS ---------------- */

    public function addReceiver($adress, $firstname, $lastname = false){
        if($lastname) $firstname .= " ".$lastname; 
        array_push($this->receivers, [$adress, $firstname]);
    }

    public function setLanguage($language){
        $this->language = $language;
    }

    public function setTemplate($templateClass){
        $this->template = $templateClass;
    }

    public function setContent($slot, $contents){
        $where = $this->slot_options[$slot];
        if($where === "s"){
            $this->subjectContent = $contents;
        } else if($where === "b"){
            $this->bodyContent = $contents;
        }
    }

    public function send() {

        $this->mail->setFrom($this->from_adress, $this->from_name);
        $this->mail->setFrom($this->from_adress, $this->from_name);

        for ($i=0; $i < count($this->receivers); $i++) {
            $this->mail->addAddress($this->receivers[$i][0], $this->receivers[$i][1]);
        }

        for ($i=0; $i < count($this->template->images); $i++) {
            $this->mail->AddEmbeddedImage($this->template->images[$i][1], $this->template->images[$i][0]);
        }

        $this->mail->Subject = $this->template->subject[$this->language];
        $this->mail->Body = Core::includeToVar($this->template->body[$this->language]);

        $rSubjectSearch = [];
        $rSubjectReplace = [];
        foreach ($this->template->slots["subject"] as $key => $value) {
            array_push($rSubjectSearch, $value);
            array_push($rSubjectReplace, $this->subjectContent[$key]);
        }

        $rBodySearch = [];
        $rBodyReplace = [];
        foreach ($this->template->slots["body"] as $key => $value) {
            array_push($rBodySearch, $value);
            array_push($rBodyReplace, $this->bodyContent[$key]);
        }
        
        $this->mail->Subject = str_replace($rSubjectSearch, $rSubjectReplace, $this->mail->Subject);
        $this->mail->Body = str_replace($rBodySearch, $rBodyReplace, $this->mail->Body);

        $this->mail->send();

    }

}