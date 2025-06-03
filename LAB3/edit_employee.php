<?php
include 'db_conn.php';

// Check if ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $emp_id = $_GET['id'];

    // Fetch employee details
    $emp_result = mysqli_query($conn, "SELECT * FROM Employee WHERE emp_id = $emp_id");
    $employee = mysqli_fetch_assoc($emp_result);

    // Fetch departments for dropdown
    $dept_result = mysqli_query($conn, "SELECT * FROM Department");
} else {
    echo "❌ Invalid employee ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Employee</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f1f6fb;
      margin: 0;
      padding: 40px;
    }

    .form-container {
      max-width: 500px;
      margin: auto;
      background-color: #ffffff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #2a4d69;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #333;
    }

    input[type="text"],
    input[type="number"],
    select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    input[type="submit"] {
      background-color: #f39c12;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      margin-top: 25px;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #d4890d;
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

<div class="form-container">
  <h2>Edit Employee</h2>

  <form method="POST" action="update_employee.php">
    <input type="hidden" name="emp_id" value="<?php echo $employee['emp_id']; ?>">

    <label for="emp_name">Employee Name:</label>
    <input type="text" name="emp_name" value="<?php echo htmlspecialchars($employee['emp_name']); ?>" required>

    <label for="emp_salary">Salary:</label>
    <input type="number" name="emp_salary" step="0.01" value="<?php echo $employee['emp_salary']; ?>" required>

    <label for="emp_dept_id">Department:</label>
    <select name="emp_dept_id" required>
      <?php while ($dept = mysqli_fetch_assoc($dept_result)) : ?>
        <option value="<?php echo $dept['emp_dept_id']; ?>"
          <?php if ($dept['emp_dept_id'] == $employee['emp_dept_id']) echo 'selected'; ?>>
          <?php echo $dept['dept_name']; ?>
        </option>
      <?php endwhile; ?>
    </select>

    <input type="submit" value="Update Employee">
  </form>

  <div class="back-link">
    <p><a href="view_employee.php">← Back to Employee List</a></p>
  </div>
</div>

</body>
</html>
