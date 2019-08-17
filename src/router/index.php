<?php

$export_default = "Router";

class Router {

    public static $route;

    private static $routes = [
        [
            "path" => "test",
            "location" => "@/endpoints/test"
        ],
        [
            "path" => "auth",
            "location" => "@/endpoints/auth/"
        ]

    ];

    private function __construct() {
        
        print_r('here');
        die();
        print_r($_SERVER);
        $this->route = 'blabla';

    }

    public static function view() {

        $url = $_SERVER['REQUEST_URI'];
        $path = $_SERVER['QUERY_STRING'];

        $params = explode("/", $url);

        $version = $params[2];

        $route = $params[3];
        
        $key = array_search($route, array_column(self::$routes, 'path'));

        if($key !== false) {

            import(self::$routes[$key]["location"]);

        }

    }

    public static function sayHello() {
        return 'Hello from router';
    }

    public static function push($path){



    }


}