<?php
session_start();
require_once "../model/Database.php";
require_once "../helper/sendEmail.php";

$db = new Database();
$conn = $db->connectDB();

if(isset($_POST["loginEmail"], $_POST["loginPassword"])) {
    loginUserFunc($_POST["loginEmail"], $_POST["loginPassword"]);
    exit;
}
else if(isset($_POST["ustID"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["username"], $_POST["course"], $_POST["year_level"])) {
    registerUserFunc($_POST["ustID"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["username"], $_POST["course"], $_POST["year_level"]);
    exit;
}

function loginUserFunc($email, $password) {
    global $conn;

    try {
        $query = "SELECT * FROM users_tbl WHERE email = :email";
        $response = $conn->prepare($query);
        $response->bindParam(":email", $email);
        $response->execute();

        $user = $response->fetch(PDO::FETCH_ASSOC);

        if($user) {
            if(password_verify($password, $user["password_hash"])) {

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["first_name"] = $user["first_name"];
                $_SESSION["last_name"] = $user["last_name"];
                $_SESSION["ust_id"] = $user["ust_id"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                addLogFunc($user["user_id"], $user["first_name"]);

                echo $user["role"];
            }
            else {
                echo "invalid";
            }
        }
        else {
            echo "invalid";
        }

    } catch(PDOException $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

function registerUserFunc($ustID, $firstName, $lastName, $email, $password, $role, $username, $course, $year) {
    global $conn;

    try {
        $checkQuery = "SELECT * FROM users_tbl WHERE email = :email OR ust_id = :ustID";
        $checkResponse = $conn->prepare($checkQuery);
        $checkResponse->bindParam(":email", $email);
        $checkResponse->bindParam(":ustID", $ustID);
        $checkResponse->execute();

        if($checkResponse->rowCount() > 0) {
            echo "exists";
        }
        else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertQuery = "INSERT INTO users_tbl (ust_id, first_name, last_name, email, password_hash, role, username, course, year_level)
                            VALUES (:ustID, :firstName, :lastName, :email, :passwordHash, :role, :username, :course, :year)";
            $response = $conn->prepare($insertQuery);
            $response->bindParam(":ustID", $ustID);
            $response->bindParam(":firstName", $firstName);
            $response->bindParam(":lastName", $lastName);
            $response->bindParam(":email", $email);
            $response->bindParam(":passwordHash", $hashedPassword);
            $response->bindParam(":role", $role);
            $response->bindParam(":username", $username);
            $response->bindParam(":course", $course);
            $response->bindParam(":year", $year);

            if($response->execute()) {
                echo "success";
            }
            else {
                echo "failed";
            }
        }

    } catch(PDOException $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

function addLogFunc($userID, $firstName) {
    global $conn;

    try {
        $actionType = "Login";
        $description = $firstName . " logged in successfully";

        $query = "INSERT INTO activity_logs_tbl (user_id, action_type, description)
                  VALUES (:userID, :actionType, :description)";
        $response = $conn->prepare($query);
        $response->bindParam(":userID", $userID);
        $response->bindParam(":actionType", $actionType);
        $response->bindParam(":description", $description);
        $response->execute();

    } catch(PDOException $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}
?>