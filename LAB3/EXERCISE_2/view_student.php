<?php
include 'db_conn.php';
$result = mysqli_query($conn, "SELECT * FROM students ORDER BY registration_date DESC, name ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Students</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #e8f0fe;
      margin: 0;
      padding: 40px;
    }

    .container {
      max-width: 850px;
      margin: auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #2a4d69;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
    }

    th {
      background-color: #2a9d8f;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f3f3f3;
    }

    tr:hover {
      background-color: #e0f7fa;
    }

    .action-buttons {
  display: flex;
  gap: 8px;
}

.action-buttons a {
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 5px;
  font-size: 13px;
  display: inline-block;
}

.edit-btn {
  background-color: #3498db;
  color: white;
}

.delete-btn {
  background-color: #e74c3c;
  color: white;
}


    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      text-decoration: none;
      color: #0077cc;
      font-weight: bold;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Student Records</h2>

  <table>
    <tr>

      <th style="text-align: center;">ID</th>
      <th style="text-align: center;">Name</th>
      <th style="text-align: center;">Email</th>
      <th style="text-align: center;">Phone</th>
      <th style="text-align: center;">Registered</th>
      <th style="text-align: center;">Actions</th>
    </tr>

    <?php $counter = 0;
    while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>
      <td style="text-align: center;"><?php echo ++$counter; ?></td>
      <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
      <td style="text-align: center;"><?php echo htmlspecialchars($row['email']); ?></td>
      <td style="text-align: center;"><?php echo htmlspecialchars($row['phone_number']); ?></td>
      <td style="text-align: center;"><?php echo htmlspecialchars($row['registration_date']); ?></td>

      <td style="text-align: center;" class="action-buttons">
        <a class="edit-btn" href="edit_student.php?id=<?php echo $row['student_id']; ?>">Edit</a>
        <a class="delete-btn" href="delete_student.php?id=<?php echo $row['student_id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div class="back-link">
    <p><a href="add_student.php">← Back to Add Student</a></p>
  </div>
</div>

</body>
</html>

