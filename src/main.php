<?php

try {

    use_env();
    use_env("Api");
    use_env("Database");
    
    import("@/plugins/main/headers");

    import('@/plugins/Database');
    import("@/plugins/Reply");
    import("@/plugins/Log");
    Log::start();

    import('@/router/');
    router::view();


} catch (\Exception $e) {

    Reply::resetData();
    Reply::setStatus($e->getCode(), $e->getMessage());
    Reply::addData($e->getInfo(), "info");

    if (ENV === 'local' || ENV === 'test') {
        Reply::addData($e->getDevInfo(), "devInfo");
    }

    Reply::send();

    Log::setLevel('fatal');
    Log::addInfo("Message: ".$e->getMessage());
    Log::addInfo("Info: ".json_encode($e->getInfo()));
    Log::addInfo("Dev-Info: ".json_encode($e->getDevInfo()));

}

Log::end();
