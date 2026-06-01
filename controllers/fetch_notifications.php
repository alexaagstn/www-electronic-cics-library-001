<?php
session_start();
require_once "../model/Database.php";

$db = new Database();
$conn = $db->connectDB();

$userId = $_SESSION["user_id"] ?? 0;
$role = $_SESSION["role"] ?? "";

$stmt = $conn->prepare("
    SELECT notification_id, title, message, notification_type, is_read, created_at
    FROM notifications_tbl
    WHERE user_id = :user_id
       OR target_role = :role
       OR target_role = 'all'
    ORDER BY created_at DESC
    LIMIT 20
");

$stmt->execute([
    ":user_id" => $userId,
    ":role" => $role
]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = 0;

foreach ($notifications as $notif) {
    if ($notif["is_read"] == 0) {
        $unreadCount++;
    }
}

echo json_encode([
    "unread" => $unreadCount,
    "notifications" => $notifications
]);
?>