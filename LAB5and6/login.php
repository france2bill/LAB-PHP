<?php
session_start();
require_once 'db.php';

$error = "";
$info = "";

// Check if redirected from Google login with an error
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'no_auth_code') {
        $error = "Google authentication failed: No authorization code received.";
    } else {
        $error = "Authentication error: " . $_GET['error'];
    }
}

// Check if redirected from Google login
if (isset($_GET['from']) && $_GET['from'] == 'google') {
    $info = "Google login is not available yet. Please use email login.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_method'] = 'standard'; // Add this line to track login method
        
        // Check if there's a redirect parameter
        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
            header("Location: " . $_GET['redirect']);
        } else {
            header("Location: library_test.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f8f9fa; }
        .login-container { 
            max-width: 400px; 
            margin: 60px auto; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header img {
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
<div class="login-container">
    <div class="login-header">
        <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="Library Icon">
        <h2 class="mb-4">User Login</h2>
    </div>
    
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($info) echo "<div class='alert alert-info'>$info</div>"; ?>
    
    <a href="google_login.php" class="btn btn-google w-100" style="padding: 12px; font-size: 16px;">
        <i class="fab fa-google google-icon"></i> Sign in with Google
    </a>
    
    <div class="divider">
        <span>OR SIGN IN WITH EMAIL</span>
    </div>
    
    <form method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="mt-3 text-center text-muted">
        Don't have an account? <a href="signup.php">Sign up here</a>
    </p>
</div>
</body>
</html>
