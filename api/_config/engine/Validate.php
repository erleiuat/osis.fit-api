<?php

class Validate {

    public static function string($val, $min = false, $max = false, $entities = true) {

        $val = trim($val);

        if ($min && strlen($val) < $min) throw new Exception("validation_string_lenght_min_".$min, 422);
        if ($max && strlen($val) > $max) throw new Exception("validation_string_lenght_max_".$max, 422);

        if ($entities) {
            return filter_var($val, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        }
        
        return $val;
        
    }
    
    public static function number($val, $min = false, $max = false) {
    
        $val = trim($val);
        $val = htmlspecialchars($val);
        $val = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (strlen($val) && !is_numeric($val)) throw new Exception("validation_number_numeric", 422);

        if ($min && $val < $min) throw new Exception("validation_number_size_min_".$min, 422);
        if ($max && $val > $max) throw new Exception("validation_number_size_max_".$max, 422);
        
        if ($val > 0 && !filter_var($val, FILTER_VALIDATE_FLOAT)) throw new Exception("validation_number_filter", 422);
        
        if (strlen($val)) return (float) $val;
        return null;
        
    }
    
    public static function bool($val){

        $val = trim($val);
        return (boolval($val) ? true : false);

    }

    public static function mail($val, $min = false, $max = 90) {
    
        $val = trim($val);
        $val = htmlspecialchars($val);
        $val = filter_var($val, FILTER_SANITIZE_EMAIL);

        if ($min && strlen($val) < $min) throw new Exception("validation_mail_lenght_min_".$min, 422);
        if ($max && strlen($val) > $max) throw new Exception("validation_mail_lenght_max_".$max, 422);
        
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) throw new Exception("validation_mail_filter", 422);

        return $val;
            
    }

    public static function password($val, $min = 8, $max = 255) {
    
        $val = trim($val);

        if ($min && strlen($val) < $min) throw new Exception("validation_pw_lenght_min_".$min, 422);
        if ($max && strlen($val) > $max) throw new Exception("validation_pw_lenght_max_".$max, 422);
        
        if (!preg_match("#[0-9]+#",$val)) throw new Exception("validation_pw_number", 422);
        if (!preg_match("#[A-Z]+#",$val)) throw new Exception("validation_pw_capital", 422);
        if (!preg_match("#[a-z]+#",$val)) throw new Exception("validation_pw_lowercase", 422);
        
        return $val;
            
    }

    public static function date($val, $required = false){

        // YYYY-MM-DD
        $val = trim($val);
        $val_arr  = explode('-', $val);

        if (!$required && strlen($val) === 0) return null;

        if (count($val_arr) !== 3) throw new Exception("validation_date_format", 422);
        if (!checkdate($val_arr[1], $val_arr[2], $val_arr[0])) throw new Exception("validation_date_valid", 422);

        return $val_arr[0]."-".$val_arr[1]."-".$val_arr[2];

    }

    public static function time($val, $required = false){
        
        $val = trim($val);

        if (!$required && strlen($val) === 0) return null;

        if(!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $val)) throw new Exception("validation_time_format", 422);

        return $val;

    }

}