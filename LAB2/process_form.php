<?php
include 'db_conn.php';

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$age = $_POST['age'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Invalid email format.");
}

if (!is_numeric($age) || $age <= 0) {
    die("❌ Invalid age.");
}

$stmt = $conn->prepare("INSERT INTO Users (name, email, age) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $name, $email, $age);
$stmt->execute();

header("Location: view_user.php");
?>
