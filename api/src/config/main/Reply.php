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

    public $condition;
    public $info;
    public $data;

    /* ------------------ INIT ------------------ */
    public function __construct() {
        $this->data = [];
        $this->setStatus(204, "success");
    }

    /* ----------------- METHODS ---------------- */

    public function setStatus($code, $condition = false, $info = false) {
        $this->code = $code;
        $this->status = $this->status_options[$code];
        if ($condition) $this->condition = $condition;
        if ($info) $this->info = $info;
    }

    public function addData($data, $name = false) {
        if (!$name) array_push($this->data, $data);
        else $this->data[$name] = $data;
    }

    public function send() {

        if ($this->code === 204 && $this->data) $this->setStatus(200);

        $response = [
            "status" => $this->code,            
            "statusMessage" => $this->status
        ];

        if ($this->condition) $response["condition"] = $this->condition;
        if ($this->info) $response["info"] = $this->info;
        if ($this->data) $response["data"] = $this->data;
    
        http_response_code($this->code === 204 ? 200 : $this->code);
        echo json_encode($response);

    }

}

