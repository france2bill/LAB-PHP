<?php include 'db_conn.php';
$result = mysqli_query($conn, "SELECT * FROM Users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Users</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #e8f0fe;
      padding: 40px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 12px;
      text-align: left;
    }
    th {
      background-color: #2a9d8f;
      color: white;
    }
    tr:nth-child(even) { background-color: #f9f9f9; }
    tr:hover { background-color: #e0f7fa; }

    .action-buttons a {
      text-decoration: none;
      padding: 6px 10px;
      border-radius: 5px;
      font-size: 13px;
      margin-right: 6px;
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
      color: #0077cc;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>User Records</h2>
    <table>
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Age</th><th>Actions</th>
      </tr>
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= $row['age'] ?></td>
          <td class="action-buttons">
            <a class="edit-btn" href="edit_user.php?id=<?= $row['id'] ?>">Edit</a>
            <a class="delete-btn" href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?');">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
    <div class="back-link">
      <p><a href="user_form.php">← Back to Add User</a></p>
    </div>
  </div>
</body>
</html>
