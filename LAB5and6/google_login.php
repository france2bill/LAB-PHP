<?php
session_start();
require_once "vendor/autoload.php";
require_once "config.php";

// Create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope("email");
$client->addScope("profile");
$client->setPrompt("select_account");
$client->setAccessType("offline");

// Create Google OAuth URL
$authUrl = $client->createAuthUrl();

// Redirect to Google OAuth URL
header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();


