<?php

class Database {

    private $host = "localhost";
    private $dbName = "agustin_prelims_db";
    private $username = "root";
    private $password = "";

    public function connectDB() {
        try {

            $connect = new PDO(
                "mysql:host=$this->host;dbname=$this->dbName",
                $this->username,
                $this->password
            );

            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $connect;

        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
        }
    }
}
?>