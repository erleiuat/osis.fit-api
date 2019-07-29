<?php

class ApiException extends Exception {

    protected $detail;

    public function __construct($code = 0, $message = "", $detail = null, Exception $previous = null) {
        if (!is_null($detail)) {
            $this->detail = $detail;
        }
        parent::__construct($message, $code, $previous);
    }

    function getDetail() {
        return $this->detail;
    }

}