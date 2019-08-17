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
            "path" => "auth/register",
            "location" => "@/endpoints/auth/register"
        ],
        [
            "path" => "auth",
            "location" => "@/endpoints/auth/",
            "children" => [

            ]
        ],
        [
            "path" => "auth/login",
            "location" => "@/endpoints/auth/"
        ]

    ];

    public static function view() {

        $url = $_SERVER['REQUEST_URI'];
        $path = $_SERVER['QUERY_STRING'];

        $params = explode("/", $url);

        print_r($params);

        $version = $params[2];
        $root = $params[3];

        Log::addInfo("Root: ".$root);
        
        $key = array_search($root, array_column(self::$routes, 'path'));

        if($key !== false) {

            for($i=4; $i<=count($params);$i++) {

                $key = array_search($root, array_column(self::$routes[$key], 'path'));
                $cKey = self::$routes[$key][$i];

            }

            die();

            import(self::$routes[$key]["location"]);

        }

    }

}