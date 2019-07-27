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

    public $info;
    public $detail;
    public $data;

    /* ------------------ INIT ------------------ */
    public function __construct() {
        $this->data = [];
        $this->setStatus(204);
    }

    /* ----------------- METHODS ---------------- */

    public function setStatus($code, $info = null, $detail = null){
        $this->code = $code;
        $this->status = $this->status_options[$code];
        $this->info = $info;
        $this->detail = $detail;
    }

    public function addData($data, $name = false){
        if(!$name) array_push($this->data, $data);
        else $this->data[$name] = $data;
    }

    public function send(){

        if($this->code === 204 && $this->data) $this->setStatus(200);
        $response = ["status" => $this->status];
        if($this->info) $response["info"] = $this->info;
        if($this->detail) $response["detail"] = $this->detail;
        if($this->data) $response["data"] = $this->data;
    
        http_response_code($this->code);
        echo json_encode($response, JSON_NUMERIC_CHECK);

    }

}

