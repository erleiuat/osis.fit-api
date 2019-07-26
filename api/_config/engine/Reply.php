<?php

class Reply {

    /* ------------- PRIVATE PARAMS ------------- */
    private $status_options = [
        200 => "ok",
        204 => "no_content",
        400 => "bad_request",
        401 => "unauthorized",
        403 => "forbidden",
        404 => "not_found",
        422 => "unprocessable_entity",
        500 => "internal_server_error"
    ];
    
    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $code;
    public $status;

    public $message;
    public $detail;

    public $content;

    /* ------------------ INIT ------------------ */
    public function __construct() {
        $this->content = false;
        $this->setStatus(204);
    }

    /* ----------------- METHODS ---------------- */

    public function setStatus($code, $message = null, $detail = null){

        $this->code = $code;
        $this->status = $this->status_options[$code];

        $this->message = $message;
        $this->detail = $detail;

    }

    public function addContent($name = false, $values){
        if(!$name) $this->content["content"] = $values;
        else $this->content[$name] = $values;
    }

    public function send(){

        if($this->code === 204 && $this->content){
            $this->setStatus(200);
        }

        http_response_code($this->code);

        $response = [
            "_status" => $this->status,
            "_info" => $this->message
        ];

        if($this->content) {
            $response += $this->content;
        }

        echo json_encode($response, JSON_NUMERIC_CHECK);

    }

}

