<?php
session_start();
require_once 'db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Email already registered!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed]);
        // Auto-login after signup
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;
        header("Location: library_test.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .signup-container { 
            max-width: 400px; 
            margin: 60px auto; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .signup-header img {
            width: 70px;
            margin-bottom: 15px;
        }
        .btn-google {
            background-color: #fff;
            color: #757575;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .btn-google:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-google img {
            width: 18px;
            margin-right: 10px;
        }
        .google-icon {
            color: #4285F4;
            font-size: 18px;
            margin-right: 10px;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #757575;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .divider span {
            padding: 0 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="signup-container">
    <div class="signup-header">
        <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="Library Icon">
        <h2 class="mb-4">User Signup</h2>
    </div>
    
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    
    <a href="google_login.php" class="btn btn-google w-100">
        <i class="fab fa-google google-icon"></i> Sign up with Google
    </a>
    
    <div class="divider">
        <span>OR SIGN UP WITH EMAIL</span>
    </div>
    
    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Sign Up</button>
    </form>
    <p class="mt-3 text-center text-muted">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
</body>
</html>
