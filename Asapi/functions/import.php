<?php

function import($name, $path = false) {

    if (!$path) {
        $path = $name;
        $name = false;
    }

    if (substr($path, 0, 1) === "@") {
        $path = str_replace("@", "./src", $path);
    }

    if (substr($path, -1) === "/") {
        $path .= "index.php";
    } else if (substr($path, -4) !== ".php") {
        $path .= ".php";
    }

    $exists = Asapi::exists($name, $path);

    if ($exists->name) {

        print_r('Import: Name "'.$name.'" already exists.');

    } else {

        if (!$exists->path) {
            
            try {

                include $path;

            } catch (ErrorException $ex) {

                throw new AsapiException(500, true, 'Import failed', 'import_failed', [
                    "message" => $ex->getMessage(),
                    "file" => $ex->getFile(),
                    "line" => $ex->getLine()
                ]);

            }

            $exists->default = $path;
            if (!isset($export_default) && $name) {
                print_r('Import: Code at "'.$path.'" does not define any export_default variable.');
            } else if (isset($export_default)) {
                $exists->default = $export_default;   
                unset($export_default);
            }
                
        }

        if ($name && strcasecmp($name, $exists->default) !== 0) {
            class_alias($exists->default, $name);
        }

        Asapi::register_import($name, $path, $exists->default);

    }

}