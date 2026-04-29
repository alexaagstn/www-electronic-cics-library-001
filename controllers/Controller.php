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

                $name = htmlspecialchars($firstName . " " . $lastName);
                $adminEmail = filter_var("cics.elibrary.ust@gmail.com", FILTER_VALIDATE_EMAIL);
                $message = htmlspecialchars("A new user has registered in the system.");

                if (!$adminEmail) {
                    die("Invalid email");
                }

            $body = "
            <div style='background:#f4f6f8; padding:30px; font-family:Tahoma, Arial, sans-serif;'>

                <div style='max-width:600px; margin:auto; background:#ffffff; border:1px solid #dcdcdc; border-bottom:4px solid #1a203b; border-radius:12px;'>

                    <!-- HEADER -->
                    <div style='padding:20px; border-bottom:1px solid #1a203b;'>
                        <h2 style='margin:0; font-size:20px; color:#333;'>UST CICS Electronic Library</h2>
                        <p style='margin:4px 0 0; font-size:12px; color:#888;'>System Notification</p>
                    </div>

                    <!-- BODY -->
                    <div style='padding:20px;'>

                        <h3 style='margin-top:0; font-size:16px; color:#333;'>New User Account Created</h3>

                        <p style='font-size:13px; color:#444;'><b>Name:</b> $name</p>
                        <p style='font-size:13px; color:#444;'><b>Email:</b> $email</p>
                        <p style='font-size:13px; color:#444;'><b>UST ID:</b> $ustID</p>
                        <p style='font-size:13px; color:#444;'><b>Username:</b> $username</p>
                        <p style='font-size:13px; color:#444;'><b>Role:</b> $role</p>
                        <p style='font-size:13px; color:#444;'><b>Course:</b> $course</p>
                        <p style='font-size:13px; color:#444;'><b>Year:</b> $year</p>

                        <p style='font-size:13px; color:#444; margin-top:10px;'>
                            <b>Date Registered:</b> " . date("F j, Y h:i A") . "
                        </p>

                        <br>

                        <p style='font-size:12px; color:#777; line-height:1.5;'>
                            A new user account has been successfully created in the system. 
                            You may review the account details or monitor activity through the admin dashboard.
                        </p>

                    </div>

                    <!-- FOOTER -->
                    <div style='padding:12px; background:#fafafa; text-align:center; font-size:11px; color:#1a203b;'>
                        CICS E-Library System
                    </div>
                </div>

            </div>
            ";

                $result = sendEmail(
                    "cics.elibrary.ust@gmail.com",
                    "Admin",
                    "New Registration",
                    $body
                );

                if ($result === true) {
                    echo "success";
                } else {
                    echo "Failed: " . $result;
                }
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