<?php
include 'db_conn.php';
$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$age = $_POST['age'];

$stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, age = ? WHERE id = ?");
$stmt->bind_param("ssii", $name, $email, $age, $id);
$stmt->execute();

header("Location: view_user.php");
?>
