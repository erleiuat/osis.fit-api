<?php

class Asapi {

    private static $imported_names = [];
    private static $imported_paths = [];
    private static $imported = [];

    private static $registered_envs = [];

    public static function get_env($name) {

        if (!in_array($name, self::$registered_envs)) {

            try {

                include ENVPATH . "/" .$name. ".php";

                array_push(self::$registered_envs, $name);

            } catch (ErrorException $ex) {

                throw new AsapiException(500, true, 'Import failed', 'import_failed', [
                    "message" => $ex->getMessage(),
                    "file" => $ex->getFile(),
                    "line" => $ex->getLine()
                ]);

            }

        }

    }

    public static function exists($name, $path) {

        $name_exists = false;
        $path_exists = false;
        $default_name = false;

        if ($name && in_array($name, self::$imported_names)) {
            $name_exists = $name;
        }

        if (isset(self::$imported_paths[$path])) {
            $path_exists = $path;
            $default_name = self::$imported[self::$imported_paths[$path]][2];
        }

        return (object) [
            "name" => $name_exists,
            "path" => $path_exists,
            "default" => $default_name
        ];

    }

    public static function register_import($name, $path, $default) {

        array_push(self::$imported, [$name, $path, $default]);

        $key = max(array_keys(self::$imported));

        self::$imported_paths[$path] = $key;
        array_push(self::$imported_names, $name);

    }

}