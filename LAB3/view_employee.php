<?php
include 'db_conn.php';
$result = mysqli_query($conn, "SELECT e.emp_id, e.emp_name, e.emp_salary, d.dept_name
                               FROM Employee e
                               JOIN Department d ON e.emp_dept_id = d.emp_dept_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Employees</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #e8f0fe;
      margin: 0;
      padding: 40px;
    }

    .container {
      max-width: 900px;
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
      background-color: #f9f9f9;
    }

    tr:hover {
      background-color: #e0f7fa;
    }

    .action-buttons a {
      margin-right: 8px;
      text-decoration: none;
      padding: 6px 10px;
      border-radius: 5px;
      font-size: 13px;
    }

    .edit-btn {
      background-color: #3498db;
      color: white;
    }

    .delete-btn {
      background-color: #e74c3c;
      color: white;
    }

    .action-buttons a:hover {
      opacity: 0.9;
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
  <h2>Employee Records</h2>

  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Salary</th>
      <th>Department</th>
      <th>Actions</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>
      <td><?php echo htmlspecialchars($row['emp_id']); ?></td>
      <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
      <td><?php echo htmlspecialchars($row['emp_salary']); ?></td>
      <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
      <td class="action-buttons">
        <a class="edit-btn" href="edit_employee.php?id=<?php echo $row['emp_id']; ?>">Edit</a>
       <a class="delete-btn"
   href="delete_student.php?id=<?= $row['student_id']; ?>"
   onclick="return doubleConfirm('<?= addslashes($row['name']) ?>');">
   Delete
</a>

      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div class="back-link">
    <p><a href="add_employee.php">← Back to Add Employee</a></p>
  </div>
</div>
<script>
function doubleConfirm(studentName) {
  const first = window.confirm(`🧠 Heads Up!\n\nYou're about to DELETE "${studentName}".\n\nThis will permanently remove the student from the database.\n\nClick OK to continue, or Cancel to abort.`);

  if (!first) return false;

  const second = window.confirm(`⚠️ Final Confirmation\n\nAre you absolutely sure you want to permanently delete "${studentName}"?\n\nOnce deleted, there's no way back.`);

  return second; // only proceeds if both prompts are confirmed
}
</script>


</body>
</html>
