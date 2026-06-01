<?php
require_once "../model/Database.php";
require_once "../helper/sendEmail.php";

$db = new Database();
$conn = $db->connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if ($email === "") {
        echo "empty";
        exit;
    }

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$/", $email)) {
        echo "invalid_email";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT user_id, first_name, email 
        FROM users_tbl 
        WHERE email = :email 
        LIMIT 1
    ");

    $stmt->execute([
        ":email" => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Do not reveal if the email exists or not
    if (!$user) {
        echo "success";
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    $update = $conn->prepare("
        UPDATE users_tbl
        SET reset_token = :reset_token,
            reset_expires = :reset_expires
        WHERE user_id = :user_id
    ");

    $update->execute([
        ":reset_token" => $token,
        ":reset_expires" => $expires,
        ":user_id" => $user["user_id"]
    ]);

    $resetLink = "http://localhost/AGUSTIN_2ITF_prelims/views/ResetPasswordPage.php?token=" . $token;

    $subject = "UST CICS E-Library Password Reset";

$body = "
<div style='margin:0; padding:0; background:#F8F5E8; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Text&quot;, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; color:#1A1A1A;'>

    <div style='width:100%; padding:36px 0;'>

        <div style='max-width:640px; margin:0 auto; background:#FFFFFF; border:1px solid rgba(0,0,0,0.10); border-radius:22px; overflow:hidden; box-shadow:0 18px 45px rgba(0,0,0,0.10);'>

            <div style='background:#111111; padding:30px 34px; border-bottom:4px solid #D4AF37;'>

                <div style='display:inline-block; margin-bottom:14px; padding:6px 11px; border-radius:999px; background:rgba(212,175,55,0.14); color:#D4AF37; font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase;'>
                    Account Security
                </div>

                <h2 style='margin:0; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:25px; line-height:1.18; color:#F8F5E8; font-weight:750; letter-spacing:-0.45px;'>
                    UST CICS Electronic Library
                </h2>

                <p style='margin:8px 0 0; font-size:13px; line-height:1.5; color:rgba(248,245,232,0.68); font-weight:400;'>
                    Password reset request for your E-Library account.
                </p>
            </div>

            <div style='padding:34px;'>

                <div style='display:inline-block; background:rgba(212,175,55,0.12); color:#9B7A14; border:1px solid rgba(212,175,55,0.28); border-radius:999px; padding:7px 13px; font-size:11px; font-weight:700; letter-spacing:0.9px; text-transform:uppercase; margin-bottom:18px;'>
                    Password Reset
                </div>

                <h3 style='margin:0 0 10px; font-family:-apple-system, BlinkMacSystemFont, &quot;SF Pro Display&quot;, &quot;Segoe UI&quot;, system-ui, sans-serif; font-size:23px; line-height:1.22; color:#1A1A1A; font-weight:750; letter-spacing:-0.45px;'>
                    Reset Your Password
                </h3>

                <p style='margin:0 0 18px; font-size:14px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                    Hello <strong style='color:#1A1A1A;'>" . htmlspecialchars($user["first_name"]) . "</strong>,
                </p>

                <p style='margin:0 0 24px; font-size:14px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                    We received a request to reset the password for your UST CICS E-Library account. To continue, click the button below and create a new password.
                </p>

                <div style='text-align:center; margin:30px 0 28px;'>

                    <a href='" . $resetLink . "'
                       style='display:inline-block; background:#D4AF37; color:#111111; text-decoration:none; padding:13px 24px; border-radius:14px; font-size:14px; font-weight:800; letter-spacing:-0.1px; box-shadow:0 12px 24px rgba(212,175,55,0.26);'>
                        Reset Password
                    </a>

                </div>

                <div style='background:#FFFDF8; border:1px solid rgba(0,0,0,0.10); border-radius:18px; overflow:hidden; margin-top:24px;'>

                    <div style='background:#F0EBD8; padding:15px 20px; border-bottom:1px solid rgba(0,0,0,0.10);'>
                        <p style='margin:0; font-size:11px; color:#6B6B6B; letter-spacing:1.3px; text-transform:uppercase; font-weight:750;'>
                            Security Notice
                        </p>
                    </div>

                    <div style='padding:18px 20px;'>

                        <p style='margin:0 0 12px; font-size:13px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                            This password reset link will expire in <strong style='color:#1A1A1A;'>30 minutes</strong>.
                        </p>

                        <p style='margin:0; font-size:13px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                            If you did not request this password reset, you may safely ignore this email. Your current password will remain unchanged.
                        </p>

                    </div>
                </div>

                <div style='margin-top:24px; padding:17px 19px; background:#F8F5E8; border-left:4px solid #D4AF37; border-radius:14px;'>
                    <p style='margin:0; font-size:13px; color:#6B6B6B; line-height:1.65; font-weight:400;'>
                        For your security, please do not share this email or reset link with anyone.
                    </p>
                </div>

                <p style='margin:24px 0 0; font-size:14px; line-height:1.65; color:#6B6B6B; font-weight:400;'>
                    Thank you,<br>
                    <strong style='color:#1A1A1A;'>UST CICS E-Library Team</strong>
                </p>

            </div>

            <div style='background:#2F2F2F; padding:18px 30px; text-align:center; border-top:4px solid #D4AF37;'>
                <p style='margin:0; color:#F8F5E8; font-size:12px; font-weight:750;'>CICS E-Library System</p>
                <p style='margin:5px 0 0; color:rgba(248,245,232,0.62); font-size:11px;'>University of Santo Tomas</p>
            </div>

        </div>
    </div>
</div>
";

    $toEmail = $user["email"];
    $toName = $user["first_name"];

    $sent = sendEmail($toEmail, $toName, $subject, $body);

    if ($sent) {
        echo "success";
    } else {
        echo "email_failed";
    }

    exit;
}
?>