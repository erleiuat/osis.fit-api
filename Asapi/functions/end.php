<?php

function process_end() {
    
    Reply::send();
    header('Content-Length: ' . ob_get_length());
    ob_end_flush();
    ob_flush();
    flush();

}