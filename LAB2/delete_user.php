<?php
include 'db_conn.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $conn->prepare("DELETE FROM Users WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
}

header("Location: view_user.php");
?>
