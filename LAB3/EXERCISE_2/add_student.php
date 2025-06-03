<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Student</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f6fc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: #fff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 400px;
    }

    h2 {
      text-align: center;
      color: #2a4d69;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #444;
    }

    input[type="text"],
    input[type="email"],
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    input[type="submit"] {
      background: #2a9d8f;
      color: white;
      border: none;
      margin-top: 20px;
      cursor: pointer;
      transition: background 0.3s;
    }

    input[type="submit"]:hover {
      background: #21867a;
    }

    .view-link {
      text-align: center;
      margin-top: 15px;
    }

    .view-link a {
      color: #0077cc;
      text-decoration: none;
    }

    .view-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Add New Student</h2>
  <form method="POST" action="insert_student.php">
    <label for="name">Name:</label>
    <input type="text" name="name" required placeholder="student's name">

    <label for="email">Email:</label>
    <input type="email" name="email" required placeholder="add email">

    <label for="phone">Phone Number:</label>
<input type="text" name="phone" pattern="^6\d{8}$" maxlength="9" required placeholder="e.g., 6XXXXXXXX"
       title="Phone number must be 9 digits and start with 6">



    <input type="submit" value="Add Student">
  </form>

  <div class="view-link">
    <p><a href="view_student.php">View All Students</a></p>
  </div>
</div>

</body>
</html>
