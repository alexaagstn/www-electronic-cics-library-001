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

                if ($response->execute()) {

                    date_default_timezone_set("Asia/Manila");

                    $adminEmail = "cics.elibrary.ust@gmail.com";
                    $dateRegistered = date("F j, Y h:i A");

                    $nameSafe = htmlspecialchars($firstName . " " . $lastName);
                    $emailSafe = htmlspecialchars($email);
                    $ustIDSafe = htmlspecialchars($ustID);
                    $usernameSafe = htmlspecialchars($username);
                    $roleSafe = htmlspecialchars($role);
                    $courseSafe = htmlspecialchars($course);
                    $yearSafe = htmlspecialchars($year);

                    // ADMIN ITETCH
                    $adminBody = "
                    <div style='margin:0; padding:0; background:#F8F5E8; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Text&quot;, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; color:#1A1A1A;'>

                        <div style='width:100%; padding:36px 0;'>

                            <div style='max-width:640px; margin:0 auto; background:#FFFFFF; border:1px solid rgba(0,0,0,0.10); border-radius:22px; overflow:hidden; box-shadow:0 18px 45px rgba(0,0,0,0.10);'>

                                <div style='background:#111111; padding:30px 34px; border-bottom:4px solid #D4AF37;'>

                                    <div style='display:inline-block; margin-bottom:14px; padding:6px 11px; border-radius:999px; background:rgba(212,175,55,0.14); color:#D4AF37; font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase;'>
                                        Admin Notification
                                    </div>
            
                                    <h2 style='margin:0; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:25px; line-height:1.18; color:#F8F5E8; font-weight:750; letter-spacing:-0.45px;'>
                                        UST CICS Electronic Library
                                    </h2>

                                    <p style='margin:8px 0 0; font-size:13px; line-height:1.5; color:rgba(248,245,232,0.68); font-weight:400;'>
                                        A new user account has been created in the system.
                                    </p>
                                </div>

                                <div style='padding:34px;'>

                                    <div style='display:inline-block; background:rgba(212,175,55,0.12); color:#9B7A14; border:1px solid rgba(212,175,55,0.28); border-radius:999px; padding:7px 13px; font-size:11px; font-weight:700; letter-spacing:0.9px; text-transform:uppercase; margin-bottom:18px;'>
                                        New Registration
                                    </div>

                                    <h3 style='margin:0 0 10px; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:23px; line-height:1.22; color:#1A1A1A; font-weight:750; letter-spacing:-0.45px;'>
                                        New User Account Created
                                    </h3>

                                    <p style='margin:0 0 26px; font-size:14px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                                        A new user account has been successfully created in the system. You may review the account details below or monitor activity through the admin dashboard.
                                    </p>

                                    <div style='background:#FFFDF8; border:1px solid rgba(0,0,0,0.10); border-radius:18px; overflow:hidden;'>

                                        <div style='background:#F0EBD8; padding:15px 20px; border-bottom:1px solid rgba(0,0,0,0.10);'>
                                            <p style='margin:0; font-size:11px; color:#6B6B6B; letter-spacing:1.3px; text-transform:uppercase; font-weight:750;'>
                                                Account Information
                                            </p>
                                        </div>

                                        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                                            <tr>
                                                <td style='padding:14px 20px; width:160px; font-size:13px; color:#6B6B6B; font-weight:650;'>Name</td>
                                                <td style='padding:14px 20px; font-size:14px; color:#1A1A1A; font-weight:650;'>$nameSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Email</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px;'>
                                                    <a href='mailto:$emailSafe' style='color:#9B7A14; text-decoration:none; font-weight:650;'>$emailSafe</a>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>UST ID</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px; color:#1A1A1A;'>$ustIDSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Username</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px; color:#1A1A1A;'>$usernameSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Role</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08);'>
                                                    <span style='display:inline-block; background:rgba(15,122,53,0.12); color:#0F7A35; padding:6px 11px; border-radius:999px; font-size:12px; font-weight:750; text-transform:capitalize;'>
                                                        $roleSafe
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Course</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px; color:#1A1A1A;'>$courseSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Year Level</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px; color:#1A1A1A;'>$yearSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Date Registered</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px; color:#1A1A1A;'>$dateRegistered</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div style='margin-top:24px; padding:17px 19px; background:#F8F5E8; border-left:4px solid #D4AF37; border-radius:14px;'>
                                        <p style='margin:0; font-size:13px; color:#6B6B6B; line-height:1.65; font-weight:400;'>
                                            This notification was automatically generated by the CICS E-Library System.
                                        </p>
                                    </div>

                                </div>

                                <div style='background:#2F2F2F; padding:18px 30px; text-align:center; border-top:4px solid #D4AF37;'>
                                    <p style='margin:0; color:#F8F5E8; font-size:12px; font-weight:750;'>CICS E-Library System</p>
                                    <p style='margin:5px 0 0; color:rgba(248,245,232,0.62); font-size:11px;'>University of Santo Tomas</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    ";

                    // USER
                    $userBody = "
                    <div style='margin:0; padding:0; background:#F8F5E8; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Text&quot;, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; color:#1A1A1A;'>

                        <div style='width:100%; padding:36px 0;'>

                            <div style='max-width:640px; margin:0 auto; background:#FFFFFF; border:1px solid rgba(0,0,0,0.10); border-radius:22px; overflow:hidden; box-shadow:0 18px 45px rgba(0,0,0,0.10);'>

                                <div style='background:#111111; padding:30px 34px; border-bottom:4px solid #D4AF37;'>

                                    <div style='display:inline-block; margin-bottom:14px; padding:6px 11px; border-radius:999px; background:rgba(212,175,55,0.14); color:#D4AF37; font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase;'>
                                        Welcome Message
                                    </div>

                                    <h2 style='margin:0; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:25px; line-height:1.18; color:#F8F5E8; font-weight:750; letter-spacing:-0.45px;'>
                                        Welcome to UST CICS E-Library
                                    </h2>

                                    <p style='margin:8px 0 0; font-size:13px; line-height:1.5; color:rgba(248,245,232,0.68); font-weight:400;'>
                                        Your account has been successfully created.
                                    </p>
                                </div>

                                <div style='padding:34px;'>

                                    <h3 style='margin:0 0 10px; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:23px; line-height:1.22; color:#1A1A1A; font-weight:750; letter-spacing:-0.45px;'>
                                        Hello, $nameSafe!
                                    </h3>

                                    <p style='margin:0 0 26px; font-size:14px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                                        Your UST CICS E-Library account has been registered successfully. You may now sign in using your registered username or email address.
                                    </p>

                                    <div style='background:#FFFDF8; border:1px solid rgba(0,0,0,0.10); border-radius:18px; overflow:hidden;'>

                                        <div style='background:#F0EBD8; padding:15px 20px; border-bottom:1px solid rgba(0,0,0,0.10);'>
                                            <p style='margin:0; font-size:11px; color:#6B6B6B; letter-spacing:1.3px; text-transform:uppercase; font-weight:750;'>
                                                Your Account Details
                                            </p>
                                        </div>

                                        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                                            <tr>
                                                <td style='padding:14px 20px; width:160px; font-size:13px; color:#6B6B6B; font-weight:650;'>Name</td>
                                                <td style='padding:14px 20px; font-size:14px; color:#1A1A1A; font-weight:650;'>$nameSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Email</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px;'>$emailSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Username</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px;'>$usernameSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Course</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px;'>$courseSafe</td>
                                            </tr>

                                            <tr>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:13px; color:#6B6B6B; font-weight:650;'>Year Level</td>
                                                <td style='padding:14px 20px; border-top:1px solid rgba(0,0,0,0.08); font-size:14px;'>$yearSafe</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div style='margin-top:24px; padding:17px 19px; background:#F8F5E8; border-left:4px solid #D4AF37; border-radius:14px;'>
                                        <p style='margin:0; font-size:13px; color:#6B6B6B; line-height:1.65; font-weight:400;'>
                                            Please keep your login information secure. If you did not create this account, contact the CICS E-Library administrator immediately.
                                        </p>
                                    </div>

                                </div>

                                <div style='background:#2F2F2F; padding:18px 30px; text-align:center; border-top:4px solid #D4AF37;'>
                                    <p style='margin:0; color:#F8F5E8; font-size:12px; font-weight:750;'>CICS E-Library System</p>
                                    <p style='margin:5px 0 0; color:rgba(248,245,232,0.62); font-size:11px;'>University of Santo Tomas</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    ";

                    $adminResult = sendEmail(
                        $adminEmail,
                        "Admin",
                        "New Registration",
                        $adminBody
                    );

                    $userResult = sendEmail(
                        $email,
                        $firstName,
                        "Welcome to UST CICS E-Library",
                        $userBody
                    );

                    if ($adminResult === true && $userResult === true) {
                        echo "success";
                    } 
                    else {
                        echo "Failed: Admin Email = " . $adminResult . " | User Email = " . $userResult;
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

    // LOG & DASHBOARD FUNCTIONS

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

    public function getEResourcesFunc() {
    $sql = "SELECT * FROM eresources_tbl ORDER BY resource_id DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function updateProfileFunc($userID, $firstName, $lastName, $email, $password) {

    if($password != "") {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $sql = "UPDATE users_tbl 
                SET first_name = ?, last_name = ?, email = ?, password_hash = ?
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $userID]);
    }
    else {
        $sql = "UPDATE users_tbl 
                SET first_name = ?, last_name = ?, email = ?
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $userID]);
    }

    $_SESSION["first_name"] = $firstName;
    $_SESSION["last_name"] = $lastName;
    $_SESSION["email"] = $email;

    echo "1";
}

public function logActionFunc($user_id, $action_type, $description) {
    try {
        $query = "INSERT INTO activity_logs_tbl (user_id, action_type, description, created_at)
                  VALUES (:user_id, :action_type, :description, NOW())";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":user_id", $user_id);
        $statement->bindParam(":action_type", $action_type);
        $statement->bindParam(":description", $description);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function updateUserRoleFunc($user_id, $role) {
    try {
        $query = "UPDATE users_tbl 
                  SET role = :role, updated_at = NOW()
                  WHERE user_id = :user_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":role", $role);
        $statement->bindParam(":user_id", $user_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function toggleUserStatusFunc($user_id) {
    try {
        $query = "UPDATE users_tbl 
                  SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                      updated_at = NOW()
                  WHERE user_id = :user_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":user_id", $user_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function deactivateUserFunc($user_id) {
    try {
        $query = "UPDATE users_tbl
                  SET is_active = 0, updated_at = NOW()
                  WHERE user_id = :user_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":user_id", $user_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function updateMaterialCopiesFunc($material_id, $total_copies, $available_copies) {
    try {
        $query = "UPDATE materials_tbl
                  SET total_copies = :total_copies,
                      available_copies = :available_copies,
                      updated_at = NOW()
                  WHERE material_id = :material_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":total_copies", $total_copies);
        $statement->bindParam(":available_copies", $available_copies);
        $statement->bindParam(":material_id", $material_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function toggleMaterialStatusFunc($material_id) {
    try {
        $query = "UPDATE materials_tbl
                  SET status = CASE WHEN status = 'Active' THEN 'Inactive' ELSE 'Active' END,
                      updated_at = NOW()
                  WHERE material_id = :material_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":material_id", $material_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function deactivateMaterialFunc($material_id) {
    try {
        $query = "UPDATE materials_tbl
                  SET status = 'Inactive',
                      updated_at = NOW()
                  WHERE material_id = :material_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":material_id", $material_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function getBorrowRecordsFunc() {
    try {
        $query = "SELECT borrow_records_tbl.*, 
                         materials_tbl.title,
                         materials_tbl.material_id,
                         users_tbl.first_name,
                         users_tbl.last_name
                  FROM borrow_records_tbl
                  INNER JOIN materials_tbl ON borrow_records_tbl.material_id = materials_tbl.material_id
                  INNER JOIN users_tbl ON borrow_records_tbl.user_id = users_tbl.user_id
                  ORDER BY borrow_records_tbl.borrow_id DESC";

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

public function getReservationRecordsFunc() {
    try {
        $query = "SELECT reservations_tbl.*, 
                         materials_tbl.title,
                         users_tbl.first_name,
                         users_tbl.last_name
                  FROM reservations_tbl
                  INNER JOIN materials_tbl ON reservations_tbl.material_id = materials_tbl.material_id
                  INNER JOIN users_tbl ON reservations_tbl.user_id = users_tbl.user_id
                  ORDER BY reservations_tbl.reservation_id DESC";

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

public function approveReservationFunc($reservation_id) {
    try {
        $query = "UPDATE reservations_tbl
                  SET status = 'Approved'
                  WHERE reservation_id = :reservation_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":reservation_id", $reservation_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function cancelReservationFunc($reservation_id) {
    try {
        $query = "UPDATE reservations_tbl
                  SET status = 'Cancelled'
                  WHERE reservation_id = :reservation_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":reservation_id", $reservation_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function addSupportMessageFunc($sender_id, $sender_role, $message_type, $message) {
    try {
        $query = "INSERT INTO support_messages_tbl 
                  (sender_id, sender_role, message_type, message, created_at)
                  VALUES 
                  (:sender_id, :sender_role, :message_type, :message, NOW())";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":sender_id", $sender_id);
        $statement->bindParam(":sender_role", $sender_role);
        $statement->bindParam(":message_type", $message_type);
        $statement->bindParam(":message", $message);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function getSupportMessagesFunc() {
    try {
        $query = "SELECT support_messages_tbl.*, users_tbl.first_name, users_tbl.last_name
                  FROM support_messages_tbl
                  LEFT JOIN users_tbl ON support_messages_tbl.sender_id = users_tbl.user_id
                  ORDER BY support_messages_tbl.message_id ASC";

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

public function updateUserStatusFunc($user_id, $is_active) {
    try {
        $query = "UPDATE users_tbl
                  SET is_active = :is_active
                  WHERE user_id = :user_id";

        $statement = $this->conn->prepare($query);
        $statement->bindParam(":is_active", $is_active);
        $statement->bindParam(":user_id", $user_id);

        return $statement->execute();
    }
    catch(Exception $ex) {
        http_response_code(500);
        echo $ex->getMessage();
        exit;
    }
}

public function downloadMaterialFunc($userID, $materialID) {
    $stmt = $this->conn->prepare("
        INSERT INTO download_logs_tbl (user_id, material_id, downloaded_at)
        VALUES (:user_id, :material_id, :downloaded_at)
    ");

    $dateNow = date("Y-m-d H:i:s");

    $stmt->bindParam(":user_id", $userID);
    $stmt->bindParam(":material_id", $materialID);
    $stmt->bindParam(":downloaded_at", $dateNow);

    return $stmt->execute();
}

public function addNotificationFunc($user_id, $target_role, $title, $message, $notification_type) {
    $db = new Database();
    $conn = $db->connectDB();

    $stmt = $conn->prepare("
        INSERT INTO notifications_tbl
        (user_id, target_role, title, message, notification_type, is_read, created_at)
        VALUES
        (:user_id, :target_role, :title, :message, :notification_type, 0, NOW())
    ");

    return $stmt->execute([
        ":user_id" => $user_id,
        ":target_role" => $target_role,
        ":title" => $title,
        ":message" => $message,
        ":notification_type" => $notification_type
    ]);
}

}
?>