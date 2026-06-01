<?php
session_start();
require_once "../model/Database.php";

$db = new Database();
$conn = $db->connectDB();

$userId = $_SESSION["user_id"] ?? 0;
$role = $_SESSION["role"] ?? "";

$stmt = $conn->prepare("
    UPDATE notifications_tbl
    SET is_read = 1
    WHERE user_id = :user_id
       OR target_role = :role
       OR target_role = 'all'
");

$stmt->execute([
    ":user_id" => $userId,
    ":role" => $role
]);

echo "success";
?>