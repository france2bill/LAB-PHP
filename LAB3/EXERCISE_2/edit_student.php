<?php
include 'db_conn.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid ID.");
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM students WHERE student_id = $id");
$student = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Student</title>
  <style>
    body {
      background: #fcf5e5;
      font-family: 'Segoe UI', sans-serif;
      padding: 60px;
    }
    .edit-box {
      background: #fff5cc;
      max-width: 500px;
      margin: auto;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 10px 15px rgba(0,0,0,0.15);
    }
    h2 {
      text-align: center;
      color: #c77d02;
      margin-bottom: 30px;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #333;
    }
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    input[type="submit"] {
      background-color: #c77d02;
      color: white;
      border: none;
      margin-top: 25px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
    }
    input[type="submit"]:hover {
      background-color: #a66801;
    }
  </style>
</head>
<body>

<div class="edit-box">
  <h2>Edit Student</h2>
  <form method="POST" action="update_student.php">

    <input type="hidden" name="id" value="<?= $student['student_id'] ?>">


    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required required placeholder="edit name">

    <label>Email:</label>
    <input type="email" name="email" required placeholder="edit email" value="<?= htmlspecialchars($student['email']) ?>" required>

    <label>Phone Number:</label>
    <input type="text" name="phone" pattern="^6\d{8}$" maxlength="9" required placeholder="edit phone number e.g., 6XXXXXXXX"
           value="<?= htmlspecialchars($student['phone_number']) ?>" required>

    <input type="submit" value="Update Student">
  </form>
</div>

</body>
</html>
