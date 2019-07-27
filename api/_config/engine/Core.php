<?php

class Core {

    public static function startAsync(){
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();
    }

    public static function endAsync($reply = false){
        if($reply) $reply->send();
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

    public static function processException($rep, $log, $e){
        if(get_class($e) === 'ApiException'){
            $rep->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage(), $e->getDetail());
            $log->setStatus('error', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
        } else {
            $rep->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
            $log->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
        }
    }


    public static function getBody(...$structure){
        $data = json_decode(file_get_contents("php://input"));
        return Core::processGet($structure, $data, 'raw');
    }

    public static function getFile(...$structure){
        $data = (object) $_FILES;
        return Core::processGet($structure, $data, 'form-data');
    }

    public static function getPost(...$structure){
        $data = (object) $_POST;
        return Core::processGet($structure, $data, 'x-www-form-urlencoded');
    }

    public static function processGet($dataStructure, $receivedData, $type = null){
        
        $data = new stdClass();
        foreach ($dataStructure as $structure) {
            
            $key = $structure[0];
            $data->$key = NULL;

            if ($structure[2] && !isset($receivedData->$key)) throw new ApiException(400, "missing_entity", [
                "type"=> $type,
                "entity" => $key,
                "syntax" => Core::formatStructure($dataStructure)
            ]);
            
            try {
                if (isset($structure[3]) && isset($receivedData->$key)) {
                    $data->$key = Core::validateVar($receivedData->$key, $structure[1], $structure[3]);
                } else if (isset($receivedData->$key)){
                    $data->$key = Core::validateVar($receivedData->$key, $structure[1], false);
                }
            } catch(Exception $e){
                throw new ApiException($e->getCode(), "entity_invalid", [
                    "type"=> $type,
                    "entity"=>$key,
                    "error"=>$e->getMessage(),
                    "syntax" => Core::formatStructure([$structure])
                ]);
            }

            if ($structure[2] && $structure[1] !== "bool" && $structure[1] !== "array" && strlen($data->$key) < 1) {
                throw new ApiException(422, "entity_processing_failed", ["entity"=>$key]);
            }
            
        }

        $numReceived = (count((array)$receivedData));
        $numRequired = (count($dataStructure));
        if($numReceived > $numRequired) throw new ApiException(400, "too_many_entities", [
            "received" => $numReceived,
            "required" => $numRequired,
            "syntax" => Core::formatStructure($dataStructure)
        ]);
        
        return $data;

    }

    public static function validateVar($value, $type, $reqs){

        $min = (isset($reqs["min"]) ? $reqs["min"] : NULL);
        $max = (isset($reqs["max"]) ? $reqs["max"] : NULL);
        $encode = (($type === "string" && isset($reqs["encode"])) ? true : NULL);

        switch ($type) {
            case "string":
                return Validate::string($value, $min, $max, $encode);
            case "number":
                return Validate::number($value, $min, $max);
            case "bool":
                return Validate::bool($value);
            case "mail":
                return Validate::mail($value, $min, $max);
            case "password":
                return Validate::password($value, $min, $max);
            case "date":
                return Validate::date($value);
            case "time":
                return Validate::time($value);
            default:
                return $value;
        }

    }

    public static function formatStructure($structure){
        $all = [];
        foreach ($structure as $item) {
            array_push($all, (["name"=>$item[0], "type"=>$item[1], "required"=>$item[2]]) );
        }
        return $all;
    }

    public static function getData($reqEntities = false){
        throw new Exception("getData() no longer supported", 400);
    }

}