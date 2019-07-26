<?php

class Database {
    
    private $host;
    private $db_name;
    private $username;
    private $password;
    
    public $conn;
    
    public function __construct() {

        $this->conn = null;
        $this->host = Setup::db_host;
        $this->db_name = Setup::db_database;
        $this->username = Setup::db_user;
        $this->password = Setup::db_password;

        try {

            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";".
                "dbname=" . $this->db_name, 
                $this->username, $this->password
            );
            $this->conn->exec("SET NAMES utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }

    }

    public function stmtExecute($stmt){
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function stmtBind($stmt, $params, $values){
        try {
            $numParams = count($params);
            for ($i = 0; $i < $numParams; $i++) {
                $stmt->bindParam(':'.$params[$i], $values[$i]);
            }
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

}
