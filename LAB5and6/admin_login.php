<?php
// Start session
session_start();

// Initialize error message
$error = "";

// Function to verify if the request is coming from a valid source
function is_valid_request() {
    // Check if referer exists and is from the same host
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $request_host = $_SERVER['HTTP_HOST'];
        
        if ($referer_host === $request_host) {
            return true;
        }
    }
    
    // If it's the first visit (GET request with no referer), it's valid
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SERVER['HTTP_REFERER'])) {
        return true;
    }
    
    return false;
}

// For all POST requests, strictly enforce CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the attempt for debugging
    error_log('Admin login attempt - POST data: ' . print_r($_POST, true));
    error_log('Session data: ' . print_r($_SESSION, true));
    error_log('CSRF token in session: ' . (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'Not set'));
    error_log('CSRF token in POST: ' . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'Not set'));
    
    // Check CSRF token - strict validation
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        error_log('CSRF token validation failed');
        // Return HTTP 403 Forbidden status
        http_response_code(403);
        die("Security validation failed. Access denied.");
    }
    
    // Process credentials
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Basic authentication
        if ($_POST['username'] === 'admin' && $_POST['password'] === 'password') {
            // Set admin session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = 1;
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Generate a new CSRF token after login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Redirect to dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials";
        }
    }
}

// Always generate a new CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf_token'];

// Log the generated token for debugging
error_log('Generated new CSRF token: ' . $csrf_token);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-container { max-width: 400px; margin: 60px auto; }
    </style>
    <!-- Add JavaScript to prevent iframe embedding -->
    <script>
        if (window.top !== window.self) {
            window.top.location.href = window.self.location.href;
        }
    </script>
</head>
<body>
<div class="login-container bg-white shadow rounded p-4">
    <h2 class="mb-4 text-center">Admin Login</h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <!-- Add CSRF token field -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
