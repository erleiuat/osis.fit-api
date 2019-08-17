<?php

class Core {

    public static function startAsync() {
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();
    }

    public static function endAsync($reply = false) {
        if ($reply) $reply->send();
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();
    }

    public static function mergeAssign($arr1, $arr2){
        return array_intersect_key($arr2, $arr1) + $arr1;
    }

    public static function formResponse($obj){
        if(!is_array($obj)) $obj = (array) $obj;

        $response = [];
        foreach ($obj as $key => $value){

            $keyParts = explode("_", $key);  

            if(count($keyParts) > 1){
                $key = [];
                foreach($keyParts as $keyPart){
                    array_push($key, ucfirst($keyPart));
                }
                $key = lcfirst(implode("", $key));
            }

            if(is_array($value) || is_object($value)){
                $value = Core::formResponse($value);
            }
            
            $response[$key] = $value;
        }

        return (object) $response;

    }

    public static function includeToVar($file) {
        ob_start();
        require($file);
        return ob_get_clean();
    }

    public static function randomString($length = 10) {
        $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function processException($rep, $log, $e) {
        if (get_class($e) === 'ApiException') {
            $rep->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage(), $e->getDetail());
            $log->setStatus('error', "(" . (($e->getCode()) ? $e->getCode() : 500) . ") Catched: | " . $e->getMessage() . " | ");
        } else {
            $rep->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
            $log->setStatus('fatal', "(" . (($e->getCode()) ? $e->getCode() : 500) . ") Catched: | " . $e->getMessage() . " | ");
        }
    }


    public static function getBody($pattern) {
        $data = json_decode(file_get_contents("php://input"));
        if (json_last_error() !== 0) throw new ApiException(400, "invalid_json");
        return Core::processGet($pattern, $data, 'raw');
    }

    public static function getPost($pattern) {
        $data = (object) $_POST;
        return Core::processGet($pattern, $data, 'x-www-form-urlencoded');
    }

    public static function processGet($pattern, $rec, $type = null, $level = []) {

        $lstr = "";
        $pl = $pattern; 
        foreach ($level as $down) {
            $lstr .= (strlen($lstr) > 0 ? "." : "") . $down;
            $pl = $pl[$down];
        }

        $numReq = (count($pl));
        $numRec = (count((array) $rec));
        if ($numRec > $numReq) throw new ApiException(400, "too_many_entities", ["entity" => $lstr, "received" => $numRec, "required" => $numReq, "syntax" => Core::formatPattern($pattern)]);
        else if (strlen($lstr) > 0) $lstr .= ".";

        $data = new stdClass();
        foreach ($pl as $key => $unit) {

            if (!array_key_exists($key, $rec)) throw new ApiException(400, "missing_entity", ["entity" => $lstr . $key, "syntax" => Core::formatPattern($pattern), "requestType"=> $type]);
            else if ($unit[0] === "array") $data->$key = $rec->$key;
            else if (strlen(trim($rec->$key)) <= 0 && $unit[0] !== "bool" && $unit[1]) throw new ApiException(400, "empty_value", ["entity" => $lstr . $key, "syntax" => Core::formatPattern($pattern), "requestType"=> $type]);
            else if (strlen(trim($rec->$key)) <= 0 && $unit[0] !== "bool" && !$unit[1]) $data->$key = NULL;
            else if (strlen(trim($rec->$key)) > 0 || $unit[0] === "bool") try {
                $data->$key = Core::validateVar($rec->$key, $unit[0], (isset($unit[2]) ? $unit[2] : []));
            } catch (Exception $e) { 
                throw new ApiException($e->getCode(), "value_invalid", ["entity" => $lstr . $key, "error"=>$e->getMessage(), "syntax" => Core::formatPattern($pattern), "requestType"=> $type]);
            }
            else throw new ApiException(500, "entity_processing_error", ["entity" => $lstr . $key]);
        }
        
        return $data;

    }

    public static function validateVar($value, $type, $reqs) {

        switch ($type) {
            case "string":
                return Validate::string($value, $reqs);
            case "number":
                return Validate::number($value, $reqs);
            case "bool":
                return Validate::bool($value, $reqs);
            case "mail":
                return Validate::mail($value, $reqs);
            case "password":
                return Validate::password($value, $reqs);
            case "date":
                return Validate::date($value, $reqs);
            case "time":
                return Validate::time($value, $reqs);
            default:
                return $value;
        }

    }

    public static function formatPattern($pattern) {
        $all = [];
        foreach ($pattern as $key => $unit) {
            if (gettype(array_values($unit)[0]) === "array") {
                $val = ["name"=>$key, "syntax"=> Core::formatPattern($unit)];
            } else {
                $val = ["name"=>$key, "type"=>$unit[0], "required"=>$unit[1]];
            }
            array_push($all, $val);
        }
        return $all;
    }

    public static function getData() {
        throw new Exception("getData() no longer supported", 400);
    }

}