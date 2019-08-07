<?php

require ROOT . '../env/Api.php';

require ROOT . 'api/main/headers.php';
require ROOT . 'api/main/Core.php';
require ROOT . 'api/main/Validate.php';
require ROOT . 'api/main/ApiException.php';
require ROOT . 'api/main/ApiObject.php';

require ROOT . '../env/Database.php';

require ROOT . 'api/engine/Database.php';
require ROOT . 'api/engine/Reply.php';
require ROOT . 'api/engine/Log.php';

$_REP = new Reply();
$_DBC = new Database();
$_LOG = new Log($_DBC, PROCESS);