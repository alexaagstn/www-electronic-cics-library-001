<?php
session_start();
require_once "../bl/UserManagement.php";

$usermanagement = new UserManagement();

if(isset($_POST["loginEmail"], $_POST["loginPassword"])) {
    $usermanagement->loginUserFunc($_POST["loginEmail"], $_POST["loginPassword"]);
    exit;
}
else if(isset($_POST["ustID"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["username"], $_POST["course"], $_POST["year_level"])) {
    $usermanagement->registerUserFunc($_POST["ustID"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"], $_POST["role"], $_POST["username"], $_POST["course"], $_POST["year_level"]);
    exit;
}

if(isset($_POST["updateProfile"])) {
    $usermanagement->updateProfileFunc(
        $_SESSION["user_id"],
        $_POST["firstName"],
        $_POST["lastName"],
        $_POST["email"],
        $_POST["password"]
    );
    exit;
}

?>