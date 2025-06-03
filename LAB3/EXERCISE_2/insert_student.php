<?php
include 'db_conn.php';

$name  = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Validate formats
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo "<script>alert('Invalid email format.'); window.history.back();</script>";
  exit();
}
if (!preg_match('/^6\d{8}$/', $phone)) {
  echo "<script>alert('Phone number must be 9 digits and start with 6.'); window.history.back();</script>";
  exit();
}

// Check for duplicate
$check = $conn->prepare("SELECT * FROM students WHERE name = ? AND email = ? AND phone_number = ?");
$check->bind_param("sss", $name, $email, $phone);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
  echo "<script>alert('This student is already registered.'); window.history.back();</script>";
  exit();
}

// Insert new student
$stmt = $conn->prepare("INSERT INTO students (name, email, phone_number) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $phone);

if ($stmt->execute()) {
  header("Location: view_student.php");
  exit();




  // Show redirect options after successful insert
  echo "
    <!DOCTYPE html>
    <html>
    <head>
      <title>Student Added</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          text-align: center;
          background-color: #f4f9ff;
          padding-top: 100px;
        }
        .box {
          background: #fff;
          margin: auto;
          width: 400px;
          padding: 40px;
          border-radius: 10px;
          box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        a.button {
          display: inline-block;
          margin: 15px 10px 0;
          padding: 10px 20px;
          border-radius: 6px;
          background-color: #2a9d8f;
          color: white;
          text-decoration: none;
          transition: background 0.3s ease;
        }
        a.button:hover {
          background-color: #21867a;
        }
      </style>
    </head>
    <body>
      <div class='box'>
        <h2>✅ Student added successfully!</h2>
        <p>What would you like to do next?</p>
        <a class='button' href='add_student.php'>Add Another Student</a>
        <a class='button' href='view_student.php'>View All Students</a>
      </div>
    </body>
    </html>
  ";
} else {
  echo "❌ Error inserting student: " . $stmt->error;
}
?>
