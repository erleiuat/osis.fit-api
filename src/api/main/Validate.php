<?php

class Validate {

    public static function username($val, $options) {
        $c = array_merge(["min"=>false, "max"=>250], $options);
        $val = trim($val);

        if ($c['max'] && strlen($val) > $c['max']) throw new Exception("lenght_max:" . $c['max'], 422);
        if (preg_match("/\s/", $val)) throw new Exception("invalid_whitespace", 422);
        return $val;
        
    }

    public static function string($val, $options) {
        $c = array_merge(["min"=>false, "max"=>false, "encode_entities"=>false], $options);
        $val = trim($val);

        if ($c['min'] && strlen($val) < $c['min']) throw new Exception("lenght_min:" . $c['min'], 422);
        if ($c['max'] && strlen($val) > $c['max']) throw new Exception("lenght_max:" . $c['max'], 422);
        if ($c['encode_entities']) return filter_var($val, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        return $val;
        
    }
    
    public static function number($val, $min = false, $max = false) {
    
        $val = htmlspecialchars(trim($val));        
        $val = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


        if (!filter_var($val, FILTER_VALIDATE_FLOAT)) {
            if ($min && $val < $min) throw new Exception("wrong_format:(00.000)", 422);
        }
        if ($min && $val < $min) throw new Exception("size_min:" . $min, 422);
        if ($max && $val > $max) throw new Exception("size_max:" . $max, 422);
        
        return (float) $val;
        
    }
    
    public static function bool($val, $options = []) {
        $c = array_merge(["null"=>true], $options);

        if ($val === "false" || $val === false || $val === 0) return false;
        else if ($val === "true" || $val === true || $val === 1) return true;
        else if ($c["null"] === false) throw new Exception("not_null", 422);
        else return null;
    }

    public static function mail($val, $options = []) {
        $c = array_merge(["min"=>false, "max"=>90], $options);
        
        $val = htmlspecialchars(trim($val));
        $val = filter_var($val, FILTER_SANITIZE_EMAIL);

        if ($c['min'] && strlen($val) < $c['min']) throw new Exception("lenght_min:" . $c['min'], 422);
        if ($c['max'] && strlen($val) > $c['max']) throw new Exception("lenght_max:" . $c['max'], 422);
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) throw new Exception("wrong_format:(xyz@abc.domain)", 422);

        return $val;
            
    }

    public static function password($val, $options = []) {
        $c = array_merge(["min"=>8, "max"=>255], $options);
    
        $val = trim($val);
        if ($c['min'] && strlen($val) < $c['min']) throw new Exception("lenght_min:" . $c['min'], 422);
        if ($c['max'] && strlen($val) > $c['max']) throw new Exception("lenght_max:" . $c['max'], 422);
        
        if (!preg_match("#[0-9]+#", $val)) throw new Exception("number_required", 422);
        if (!preg_match("#[A-Z]+#", $val)) throw new Exception("capital_required", 422);
        if (!preg_match("#[a-z]+#", $val)) throw new Exception("lowercase_required", 422);
        
        return $val;
            
    }

    public static function date($val, $required = false) {

        $val = trim($val);
        $val_arr = explode('-', $val);

        if (!$required && strlen($val) === 0) {
            return null;
        }
        if (count($val_arr) !== 3) {
            throw new Exception("wrong_format", 422);
        }
        if (!checkdate($val_arr[1], $val_arr[2], $val_arr[0])) {
            throw new Exception("wrong_format:(YYYY-MM-DD)", 422);
        }

        return $val_arr[0] . "-" . $val_arr[1] . "-" . $val_arr[2];

    }

    public static function time($val, $required = false) {
        
        $val = trim($val);
        if (!$required && strlen($val) === 0) return null;
        if (!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $val)) throw new Exception("wrong_format:(HH:MM)", 422);
        return $val;

    }

}