<?php
$token = $_GET["token"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">

<div class="forgot-box" style="margin: 80px auto;">
    <h3>Reset Password</h3>

    <input type="hidden" id="reset-token" value="<?php echo htmlspecialchars($token); ?>">

    <input 
        type="password" 
        id="new-password" 
        placeholder="New password"
        minlength="8"
        maxlength="30"
        required
    >

    <input 
        type="password" 
        id="confirm-new-password" 
        placeholder="Confirm new password"
        minlength="8"
        maxlength="30"
        required
    >

    <button class="forgot-send" onclick="resetPasswordFunc()">
        Update Password
    </button>
</div>

<script src="../scripts/service.js"></script>

</body>
</html>