<?php
session_start();
require_once "vendor/autoload.php";
require_once "db.php";

// Google API configuration
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
// Use the exact URI you're currently accessing from
$redirectUri = "http://localhost/LAB5/google_auth.php";

// Create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Process OAuth callback
if (isset($_GET["code"])) {
    try {
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
        
        if (isset($token["access_token"])) {
            $client->setAccessToken($token);
            
            // Get user profile data from Google
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            
            // Extract user data
            $email = $google_account_info->email;
            $name = $google_account_info->name;
            $google_id = $google_account_info->id;
            
            echo "<h1>Authentication Successful!</h1>";
            echo "<p>Email: " . htmlspecialchars($email) . "</p>";
            echo "<p>Name: " . htmlspecialchars($name) . "</p>";
            echo "<p><a href=\"login.php\">Return to Login</a></p>";
            exit();
        } else {
            echo "<h1>Authentication Failed</h1>";
            echo "<p>No access token received</p>";
            echo "<p><a href=\"login.php\">Return to Login</a></p>";
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
?>