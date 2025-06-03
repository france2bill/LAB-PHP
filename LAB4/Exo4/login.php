<?php
session_start();
require_once 'db.php';


$message = '';

// Redirect already authenticated users to the home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: index.php"); // Redirect to your main page
            exit();
        } else {
            $message = '<div class="alert alert-danger">Invalid email or password.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-box {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 32px 28px;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2 class="mb-4">Login</h2>
    <?php echo $message; ?>
    <form method="post">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
    <div class="mt-3 text-center">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>
</body>
</html>