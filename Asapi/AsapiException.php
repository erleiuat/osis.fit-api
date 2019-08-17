<?php

class AsapiException extends Exception {

    protected $info;
    protected $dev_info;

    public function __construct($code, $kill = true, $message, $info = false, $dev_info = false) {

        if ($info) {
            $this->info = $info;
        }

        if ($dev_info) {
            $this->dev_info = $dev_info;
        }

        parent::__construct($message, $code);

    }

    function getInfo() {
        return $this->info;
    }

    function getDevInfo() {
        return $this->dev_info;
    }

}