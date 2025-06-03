<?php
include 'db_conn.php';

if (isset($_POST['id'], $_POST['name'], $_POST['email'], $_POST['phone'])) {
  $id    = $_POST['id'];
  $name  = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Invalid email format.'); window.history.back();</script>";
    exit();
  }

  // Validate phone
  if (!preg_match('/^6\d{8}$/', $phone)) {
    echo "<script>alert('Phone number must start with 6 and be 9 digits long.'); window.history.back();</script>";
    exit();
  }

  // Check for duplicate info (exclude current ID)
  $check = $conn->prepare("SELECT * FROM students WHERE name=? AND email=? AND phone_number=? AND student_id != ?");
  $check->bind_param("sssi", $name, $email, $phone, $id);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    echo "<script>alert('Another student with this info already exists.'); window.history.back();</script>";
    exit();
  }

  // ✅ UPDATE the student (not INSERT)
  $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone_number = ? WHERE student_id = ?");
  $stmt->bind_param("sssi", $name, $email, $phone, $id);

  if ($stmt->execute()) {
    header("Location: view_student.php");
    exit();
  } else {
    echo "❌ Update failed: " . $stmt->error;
  }
} else {
  echo "❌ Missing form data.";
}
?>
