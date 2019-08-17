<?php

try {

    use_env();
    use_env("Database");
    
    import('@/plugins/Database');
    import("@/plugins/Reply");
    import("@/plugins/Log");
    
    import('@/router/');
    
    router::view();

    //echo router::sayHello();


} catch (\Exception $e) {

    
    if(ENV === 'local') {
        print_r($e);
    } else {
        print_r('not local');
    }


}

/*

require './src/router/';

$current = Router::route;

print_r($current);

$url = $_SERVER['REQUEST_URI'];
$path = $_SERVER['QUERY_STRING'];

*/