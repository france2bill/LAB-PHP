<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = '<div class="alert alert-danger">Email already registered.</div>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed])) {
                $message = '<div class="alert alert-success">Registration successful! <a href="login.php">Login here</a>.</div>';
            } else {
                $message = '<div class="alert alert-danger">Registration failed. Please try again.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .register-box {
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
<div class="register-box">
    <h2 class="mb-4">Register</h2>
    <?php echo $message; ?>
    <form method="post">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Register</button>
    </form>
    <div class="mt-3 text-center">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>