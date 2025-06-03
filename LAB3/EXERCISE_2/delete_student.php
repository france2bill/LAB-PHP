<?php
include 'db_conn.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    // Styled redirect
    echo "
    <html>
    <head>
      <meta http-equiv='refresh' content='2;url=view_student.php' />
      <style>
        body {
          background: #ffe6e6;
          font-family: Arial;
          text-align: center;
          padding-top: 100px;
        }
        .message {
          background: #fff0f0;
          color: #cc0000;
          padding: 30px 50px;
          border-radius: 12px;
          display: inline-block;
          box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
      </style>
    </head>
    <body>
      <div class='message'>
        <h2>🗑️ Student deleted successfully</h2>
        <p>Redirecting to student list...</p>
      </div>
    </body>
    </html>
    ";
    exit;
  } else {
    echo "❌ Deletion failed: " . $stmt->error;
  }
} else {
  echo "❌ Invalid ID.";
}
?>
