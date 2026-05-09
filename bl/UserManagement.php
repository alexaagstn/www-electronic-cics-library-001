<?php
require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../helper/sendEmail.php";

class UserManagement {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connectDB();
    }

    // USER FUNCTIONS

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

    public function loginUserFunc($email, $password) {
    try {
        $query = "SELECT * FROM users_tbl WHERE email = :email";
        $response = $this->conn->prepare($query);
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

                $this->addLogFunc($user["user_id"], $user["first_name"]);

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

    public function registerUserFunc($ustID, $firstName, $lastName, $email, $password, $role, $username, $course, $year) {
        try {

        if (!str_ends_with(strtolower($email), "@ust.edu.ph")) {
                echo "invalid_email";
                return;
            }

            $checkQuery = "SELECT * FROM users_tbl WHERE email = :email OR ust_id = :ustID";
            $checkResponse = $this->conn->prepare($checkQuery);
            $checkResponse->bindParam(":email", $email);
            $checkResponse->bindParam(":ustID", $ustID);
            $checkResponse->execute();

            if($checkResponse->rowCount() > 0) {
                echo "exists";
            }
            else {
                $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

                $insertQuery = "INSERT INTO users_tbl (ust_id, first_name, last_name, email, password_hash, role, username, course, year_level)
                                VALUES (:ustID, :firstName, :lastName, :email, :passwordHash, :role, :username, :course, :year)";
                $response = $this->conn->prepare($insertQuery);
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

    public function addLogFunc($userID, $firstName) {
        try {
            $actionType = "Login";
            $description = $firstName . " logged in successfully";

            $query = "INSERT INTO activity_logs_tbl (user_id, action_type, description)
                    VALUES (:userID, :actionType, :description)";
            $response = $this->conn->prepare($query);
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
    
    // MATERIAL FUNCTIONS

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

    public function totalMaterialsFunc() {
        try {
            $query = "SELECT COUNT(*) AS totalMaterials 
                      FROM materials_tbl 
                      WHERE status = 'Active'";

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

    public function materialBreakdownFunc() {
        try {
            $query = "SELECT material_type, COUNT(*) AS total 
                      FROM materials_tbl 
                      WHERE status = 'Active'
                      GROUP BY material_type";

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

    public function addMaterialFunc($title, $author, $category, $material_type, $total_copies, $available_copies) {
        try {
            $query = "INSERT INTO materials_tbl
                      (title, author, category, material_type, total_copies, available_copies, status, created_at, updated_at)
                      VALUES
                      (:title, :author, :category, :material_type, :total_copies, :available_copies, 'Active', NOW(), NOW())";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":title", $title);
            $statement->bindParam(":author", $author);
            $statement->bindParam(":category", $category);
            $statement->bindParam(":material_type", $material_type);
            $statement->bindParam(":total_copies", $total_copies);
            $statement->bindParam(":available_copies", $available_copies);

            return $statement->execute();
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    // BORROW FUNCTIONS

    public function activeBorrowsFunc() {
        try {
            $query = "SELECT COUNT(*) AS activeBorrows 
                      FROM borrow_records_tbl 
                      WHERE status = 'Borrowed'
                      AND returned_at IS NULL";

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

    public function overdueItemsFunc() {
        try {
            $query = "SELECT COUNT(*) AS overdueItems 
                      FROM borrow_records_tbl 
                      WHERE due_date < CURDATE() 
                      AND status = 'Borrowed'
                      AND returned_at IS NULL";

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

    public function borrowMaterialFunc($user_id, $material_id) {
        try {
            $checkQuery = "SELECT available_copies FROM materials_tbl WHERE material_id = :material_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":material_id", $material_id);
            $checkStmt->execute();

            $material = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$material || $material["available_copies"] <= 0) {
                return false;
            }

            $due_date = date("Y-m-d", strtotime("+7 days"));

            $query = "INSERT INTO borrow_records_tbl
                      (user_id, material_id, due_date, status)
                      VALUES
                      (:user_id, :material_id, :due_date, 'Borrowed')";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":user_id", $user_id);
            $statement->bindParam(":material_id", $material_id);
            $statement->bindParam(":due_date", $due_date);
            $statement->execute();

            $logQuery = "INSERT INTO usage_logs_tbl (user_id, material_id, action_type)
             VALUES (:user_id, :material_id, 'Borrowed')";

            $logStmt = $this->conn->prepare($logQuery);
            $logStmt->bindParam(":user_id", $user_id);
            $logStmt->bindParam(":material_id", $material_id);
            $logStmt->execute();

            $updateQuery = "UPDATE materials_tbl
                            SET available_copies = available_copies - 1
                            WHERE material_id = :material_id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":material_id", $material_id);
            $updateStmt->execute();

            return true;
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    public function getMyLoansFunc($user_id) {
        try {
            $query = "SELECT borrow_records_tbl.*, materials_tbl.title, materials_tbl.author, materials_tbl.material_type
                      FROM borrow_records_tbl
                      INNER JOIN materials_tbl ON borrow_records_tbl.material_id = materials_tbl.material_id
                      WHERE borrow_records_tbl.user_id = :user_id
                      AND borrow_records_tbl.status = 'Borrowed'
                      ORDER BY borrow_records_tbl.borrow_id DESC";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":user_id", $user_id);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    public function returnedItemsFunc(){
        $query = "SELECT COUNT(*) as returnedItems 
                FROM borrow_records_tbl 
                WHERE status = 'Returned'";

        $statement = $this->conn->prepare($query);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function returnMaterialFunc($borrow_id, $material_id){
        try {
            $query = "UPDATE borrow_records_tbl
                    SET status = 'Returned',
                        returned_at = NOW()
                    WHERE borrow_id = :borrow_id";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":borrow_id", $borrow_id);
            $statement->execute();

            $updateQuery = "UPDATE materials_tbl
                            SET available_copies = available_copies + 1
                            WHERE material_id = :material_id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":material_id", $material_id);
            $updateStmt->execute();

            return true;
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    // RESERVATION FUNCTIONS

    public function reservedItemsFunc() {
        try {
            $query = "SELECT COUNT(*) AS reservedItems 
                      FROM reservations_tbl 
                      WHERE status = 'Pending'";

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

    public function reserveMaterialFunc($user_id, $material_id) {
        try {
            $expiry_date = date("Y-m-d", strtotime("+3 days"));

            $query = "INSERT INTO reservations_tbl
                    (user_id, material_id, expiry_date, status)
                    VALUES
                    (:user_id, :material_id, :expiry_date, 'Pending')";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":user_id", $user_id);
            $statement->bindParam(":material_id", $material_id);
            $statement->bindParam(":expiry_date", $expiry_date);

            $success = $statement->execute();

            if ($success) {
                $logQuery = "INSERT INTO usage_logs_tbl (user_id, material_id, action_type)
                            VALUES (:user_id, :material_id, 'Reserved')";

                $logStmt = $this->conn->prepare($logQuery);
                $logStmt->bindParam(":user_id", $user_id);
                $logStmt->bindParam(":material_id", $material_id);
                $logStmt->execute();
            }

            return $success;

    } catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }

    }

    public function getMyReservationsFunc($user_id) {
        try {
            $query = "SELECT reservations_tbl.*, materials_tbl.title, materials_tbl.author, materials_tbl.material_type
                      FROM reservations_tbl
                      INNER JOIN materials_tbl ON reservations_tbl.material_id = materials_tbl.material_id
                      WHERE reservations_tbl.user_id = :user_id
                      AND reservations_tbl.status = 'Pending'
                      ORDER BY reservations_tbl.reservation_id DESC";

            $statement = $this->conn->prepare($query);
            $statement->bindParam(":user_id", $user_id);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }
    }

    // LOG / DASHBOARD FUNCTIONS

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

    public function weeklyUsageFunc() {
        try {
            $query = "SELECT DAYNAME(created_at) AS dayName, COUNT(*) AS totalUsage
                      FROM usage_logs_tbl
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DAYNAME(created_at)";

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