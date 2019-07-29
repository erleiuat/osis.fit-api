<?php

include_once LOCATION . 'env.php';

include_once LOCATION . 'src/config/main/headers.php';
include_once LOCATION . 'src/config/main/Core.php';
include_once LOCATION . 'src/config/main/Validate.php';
include_once LOCATION . 'src/config/main/ApiException.php';

include_once LOCATION . 'src/config/main/Reply.php';
include_once LOCATION . 'src/config/main/Database.php';
include_once LOCATION . 'src/config/main/Log.php';

$_REP = new Reply();
$_DBC = new Database();
$_LOG = new Log($_DBC, PROCESS);