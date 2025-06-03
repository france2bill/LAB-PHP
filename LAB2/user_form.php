<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f1f6fb;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background-color: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
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
      color: #333;
    }

    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    input[type="submit"] {
      background-color: #2a9d8f;
      color: white;
      border: none;
      margin-top: 20px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #21867a;
    }

    .view-link {
      text-align: center;
      margin-top: 15px;
    }

    .view-link a {
      color: #0077cc;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Add User</h2>
  <form method="POST" action="process_form.php">
    <label for="name">Name:</label>
    <input type="text" name="name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" required>

    <label for="age">Age:</label>
    <input type="number" name="age" required>

    <input type="submit" value="Add User">
  </form>

  <div class="view-link">
    <p><a href="view_user.php">View All Users</a></p>
  </div>
</div>

</body>
</html>
