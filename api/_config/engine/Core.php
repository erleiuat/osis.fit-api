<?php

class Core {

    public static function startAsync(){
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();
    }

    public static function endAsync(){
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();
    }

    public static function includeToVar($file){
        ob_start();
        require($file);
        return ob_get_clean();
    }

    public static function getData($reqEntities = false){

        $data = json_decode(file_get_contents("php://input"));

        if($reqEntities) for($i=0;$i<count($reqEntities);$i++){
            $test = $reqEntities[$i];
            if(!isset($data->$test)){
                throw new Exception("Required Entities: ".implode(", ", $reqEntities), 400);
            }
        }

        return $data;

    }

}