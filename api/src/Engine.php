<?php

include_once LOCATION . 'env.php';

include_once LOCATION . 'src/api/main/headers.php';
include_once LOCATION . 'src/api/main/Core.php';
include_once LOCATION . 'src/api/main/Validate.php';
include_once LOCATION . 'src/api/main/ApiException.php';
include_once LOCATION . 'src/api/main/ApiObject.php';

include_once LOCATION . 'src/api/main/Reply.php';
include_once LOCATION . 'src/api/main/Database.php';
include_once LOCATION . 'src/api/main/Log.php';

$_REP = new Reply();
$_DBC = new Database();
$_LOG = new Log($_DBC, PROCESS);