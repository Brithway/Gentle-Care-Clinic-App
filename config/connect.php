<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "clinic_website";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}
?>