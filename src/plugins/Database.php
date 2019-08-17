<?php

class Database {
    
    private static $db;
    private $conn;
    
    public function __construct() {

        try {

            $this->conn = new PDO(
                "mysql:host=" . ENV_db::host . ";" .
                "dbname=" . ENV_db::database, 
                ENV_db::user, 
                ENV_db::password
            );

            $this->conn->exec("SET NAMES utf8");

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            throw new ApiException(500, "db_connect_error", $e->getMessage());

        }

    }

    public static function select ($table, $whr) {

        if (self::$db == null) self::$db = new Database();

        $whr = self::formParams($whr);

        $where = " WHERE " .join(' AND ', array_map(
            function ($v1, $v2) { return $v1." = ".$v2; },
            $whr->e, $whr->k
        ));

        $stmt = self::prepare("SELECT * FROM ".$table.$where);
        self::bind($stmt, $whr->k, $whr->v)->execute($stmt);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function formParams($arr){

        $entys = [];
        $keys = [];
        $vals = [];

        foreach ($arr as $key => $value){
            array_push($entys, "`".$key."`");
            array_push($keys, ":".$key);
            array_push($vals, $value);
        }

        return (object) [
            "e" => $entys,
            "k" => $keys,
            "v" => $vals,
        ];

    }

    public function makeReplace ($table, $par) {

        $par = $this->formParams($par);

        $stmt = $this->prepare("
            REPLACE INTO ".$table." 
            (".implode(",", $par->e).") VALUES 
            (".implode(",", $par->k).");
        ");

        $this->bind($stmt, $par->k, $par->v)->execute($stmt);
        return $stmt->rowCount();

    }

    public function makeInsert ($table, $par) {

        $par = $this->formParams($par);

        $stmt = $this->prepare("
            INSERT INTO ".$table." 
            (".implode(",", $par->e).") VALUES 
            (".implode(",", $par->k).");
        ");

        $this->bind($stmt, $par->k, $par->v)->execute($stmt);
        return $stmt->rowCount();

    }

    public function makeSelect ($table, $whr) {

        $whr = $this->formParams($whr);

        $where = " WHERE " .join(' AND ', array_map(
            function ($v1, $v2) { return $v1." = ".$v2; },
            $whr->e, $whr->k
        ));

        $stmt = $this->prepare("SELECT * FROM ".$table.$where);
        $this->bind($stmt, $whr->k, $whr->v)->execute($stmt);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function makeUpdate ($table, $par, $whr) {

        $par = $this->formParams($par);
        $set = " SET " .implode(', ', array_map(
            function ($v1, $v2) { return $v1." = ".$v2; },
            $par->e, $par->k
        ));
        
        $whr = $this->formParams($whr);
        $where = " WHERE " .join(' AND ', array_map(
            function ($v1, $v2) { return $v1." = ".$v2; },
            $whr->e, $whr->k
        ));

        $stmt = $this->prepare("UPDATE ".$table.$set.$where);

        $this->bind($stmt, 
            array_merge($par->k, $whr->k), 
            array_merge($par->v, $whr->v)
        )->execute($stmt);
        
        return $stmt->rowCount();

    }

    public function makeDelete ($table, $whr) {

        $whr = $this->formParams($whr);

        $where = " WHERE " .join(' AND ', array_map(
            function ($v1, $v2) { return $v1." = ".$v2; },
            $whr->e, $whr->k
        ));

        $stmt = $this->prepare("DELETE FROM ".$table.$where);
        $this->bind($stmt, $whr->k, $whr->v)->execute($stmt);

        return $stmt->rowCount();

    }

    public function prepare($query) {
        try {
            return $this->conn->prepare($query);
        } catch (PDOException $e) {
            throw new ApiException(500, "db_prepare_error", $e->getMessage());
        }
    }

    public function bind($stmt, $params, $values) {
        try {
            $num = count($params);
            for ($i = 0; $i < $num; $i++) $stmt->bindParam($params[$i], $values[$i]);
            return $this;
        } catch (PDOException $e) {
            throw new ApiException(500, "db_bind_error", $e->getMessage());
        }
    }

    public function execute($stmt) {
        try {
            $stmt->execute();
            return $this;
        } catch (PDOException $e) {
            throw new ApiException(500, "db_execute_error", $e->getMessage());
        }
    }

}
