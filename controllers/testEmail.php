<?php
/*
require_once "../helper/sendEmail.php";

$name = htmlspecialchars("Alexa Agustin");
$email = filter_var("cics.elibrary.ust@gmail.com", FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars("Hello World!");

if (!$email) {
    die("Invalid email");
}

$body = "
<div style='background:#f4f6f8; padding:30px; font-family:Tahoma, Arial, sans-serif;'>

    <div style='max-width:600px; margin:auto; background:#ffffff; border:1px solid #dcdcdc; border-bottom:4px solid #1a203b; border-radius:12px;'>

        <!-- HEADER -->
        <div style='padding:20px; border-bottom:1px solid #1a203b;'>
            <h2 style='margin:0; font-size:20px; color:#333;'>UST CICS E-Library</h2>
            <p style='margin:4px 0 0; font-size:12px; color:#888;'>System Notification</p>
        </div>

        <!-- BODY -->
        <div style='padding:20px;'>

            <h3 style='margin-top:0; font-size:16px; color:#333;'>New User Account Created</h3>

            <p style='font-size:13px; color:#444;'><b>Name:</b> $name</p>
            <p style='font-size:13px; color:#444;'><b>Email:</b> $email</p>
            <p style='font-size:13px; color:#444;'><b>UST ID:</b> $ustID</p>
            <p style='font-size:13px; color:#444;'><b>Role:</b> $username</p>
            <p style='font-size:13px; color:#444;'><b>Role:</b> $role</p>
            <p style='font-size:13px; color:#444;'><b>Role:</b> $course</p>
            <p style='font-size:13px; color:#444;'><b>Role:</b> $year</p>

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
            UST CICS E-Library System
        </div>

    </div>

</div>
";

$result = sendEmail(
    "cics.elibrary.ust@gmail.com",
    "Admin",
    "Contact Form Submission",
    $body
);

if ($result === true) {
    echo "Email sent successfully!";
} else {
    echo "Failed: $result";
}

exit;

?> */