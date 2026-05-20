<?php
require_once "../model/Database.php";

$db = new Database();
$conn = $db->connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"] ?? "";
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($token === "" || $password === "" || $confirmPassword === "") {
        echo "empty";
        exit;
    }

    if (strlen($password) < 8 || strlen($password) > 30) {
        echo "invalid_password";
        exit;
    }

    if ($password !== $confirmPassword) {
        echo "not_match";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT user_id
        FROM users_tbl
        WHERE reset_token = :reset_token
          AND reset_expires > NOW()
        LIMIT 1
    ");

    $stmt->execute([":reset_token" => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "invalid_token";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $update = $conn->prepare("
        UPDATE users_tbl
        SET password_hash = :password_hash,
            reset_token = NULL,
            reset_expires = NULL
        WHERE user_id = :user_id
    ");

    $update->execute([
        ":password_hash" => $passwordHash,
        ":user_id" => $user["user_id"]
    ]);

    echo "success";
}
?>