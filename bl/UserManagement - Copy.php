<?php
require_once __DIR__ . "/../model/Database.php";

class UserManagement {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connectDB();
    }

    public function getUserFunc() {
        try {
            $query = "SELECT * FROM users_tbl ORDER BY user_id DESC";
            $statement = $this->conn->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    public function totalUsersFunc() {
        try {
            $query = "SELECT COUNT(*) AS totalUsers FROM users_tbl";
            $statement = $this->conn->prepare($query);
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    public function getLogsFunc() {
        try {
            $query = "SELECT activity_logs_tbl.*, users_tbl.first_name, users_tbl.last_name
                      FROM activity_logs_tbl
                      INNER JOIN users_tbl ON activity_logs_tbl.user_id = users_tbl.user_id
                      ORDER BY activity_logs_tbl.log_id DESC";

            $statement = $this->conn->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    public function getMaterialsFunc() {
        try {
            $query = "SELECT * FROM materials_tbl ORDER BY material_id DESC";
            $statement = $this->conn->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }
}
?>