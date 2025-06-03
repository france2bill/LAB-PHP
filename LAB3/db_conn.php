<?php
$servername = "localhost";
$username = "root";      // default XAMPP username
$password = "billmartial";          // default XAMPP password is empty
$database = "EmployeeDB";  // use the exact name of your DB

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
