<?php

function process_start($name) {

    Log::setProcess($name);

    ignore_user_abort(true);
    set_time_limit(0);
    ob_start();

}