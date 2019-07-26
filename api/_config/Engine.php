<?php

include_once LOCATION.'env.php';

include_once LOCATION.'_config/engine/headers.php';
include_once LOCATION.'_config/engine/Core.php';
include_once LOCATION.'_config/engine/Validate.php';

include_once LOCATION.'_config/engine/Reply.php';
include_once LOCATION.'_config/engine/Database.php';
include_once LOCATION.'_config/engine/Log.php';

$_REP = new Reply();
$_DBC = new Database();
$_LOG = new Log($_DBC, PROCESS);