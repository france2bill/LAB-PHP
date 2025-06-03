<?php
include 'db_conn.php';
$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM Users WHERE id = $id");
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f1f6fb; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .form-container { background: #fff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); width: 400px; }
    h2 { text-align: center; color: #2a4d69; }
    label { display: block; margin-top: 15px; font-weight: bold; color: #333; }
    input[type="text"], input[type="email"], input[type="number"], input[type="submit"] { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
    input[type="submit"] { background-color: #f39c12; color: white; margin-top: 20px; border: none; cursor: pointer; }
  </style>
</head>
<body>
<div class="form-container">
  <h2>Edit User</h2>
  <form method="POST" action="update_user.php">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    <label>Age:</label>
    <input type="number" name="age" value="<?= $user['age'] ?>" required>
    <input type="submit" value="Update User">
  </form>
</div>
</body>
</html>
