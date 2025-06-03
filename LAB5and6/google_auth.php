<?php
session_start();
require_once "vendor/autoload.php";
require_once "db.php";
require_once "config.php";

// Create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope("email");
$client->addScope("profile");

// Process OAuth 2.0 response
if (isset($_GET['code'])) {
    try {
        // Get token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        
        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        // Get user data
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;
        
        // Check if user exists in database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_method'] = 'google';
            
            // Redirect to dashboard
            header("Location: library_test.php");
            exit();
        } else {
            // Register new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, registration_date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
            $new_user_id = $pdo->lastInsertId();
            
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_method'] = 'google';
            
            // Redirect to dashboard
            header("Location: library_test.php");
            exit();
        }
    } catch (Exception $e) {
        echo "<h1>Authentication Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href=\"login.php\">Return to Login</a></p>";
        exit();
    }
} else {
    echo "<h1>No Authorization Code</h1>";
    echo "<p>No authorization code received</p>";
    echo "<p><a href=\"login.php\">Return to Login</a></p>";
    exit();
}













