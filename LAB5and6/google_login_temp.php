<?php
session_start();
require_once "vendor/autoload.php";

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
$client->setPrompt("select_account");
$client->setAccessType("offline");

// Create Google OAuth URL
$authUrl = $client->createAuthUrl();

// Add debugging
error_log("Google Auth URL: " . $authUrl);
error_log("Redirect URI: " . $redirectUri);

// Redirect to Google OAuth URL
header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>