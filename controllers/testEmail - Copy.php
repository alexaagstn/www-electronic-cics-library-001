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
    <h3>New Message</h3>
    <p><strong>Name:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Message:</strong><br>$message</p>
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

?>
