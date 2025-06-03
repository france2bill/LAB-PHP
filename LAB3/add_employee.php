<?php include 'db_conn.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Employee</title>
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
      margin-top: 25px;
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
      background-color: #2a9d8f;
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
      background-color: #21867a;
    }

    .view-link {
      text-align: center;
      margin-top: 50px;
    }

    .view-link a {
      text-decoration: none;
      color: #0077cc;
      font-weight: bold;
    }

    .view-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Add New Employee</h2>

  <form method="POST" action="process_employee.php">
    <label for="emp_name">Employee Name:</label>
    <input type="text" name="emp_name" required placeholder = "employee's name">

    <label for="emp_salary">Salary:</label>
    <input type="number" name="emp_salary" step="0.01" required placeholder = "$">

    <label for="emp_dept_id">Department:</label>
    <select name="emp_dept_id" required>
      <option value="">-- Select Department --</option>
      <?php
      $result = mysqli_query($conn, "SELECT * FROM Department");
      while ($row = mysqli_fetch_assoc($result)) {
          echo "<option value='{$row['emp_dept_id']}'>{$row['dept_name']}</option>";
      }
      ?>
    </select>

    <input type="submit" value="Add Employee">
  </form>

  <div class="view-link">
    <p><a href="view_employee.php">View All Employees</a></p>
  </div>
</div>

</body>
</html>
