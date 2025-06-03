<?php
// This file helps you set up Google OAuth correctly

echo "<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Setup Guide</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
    <style>
        body { padding: 20px; }
        .container { max-width: 800px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .step { margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Google OAuth Setup Guide</h1>
        <p class='lead'>Follow these steps to fix the redirect_uri_mismatch error</p>";

// Get the current URL and possible redirect URIs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$possible_uris = [
    $protocol . $host . '/LAB5/google_auth.php',
    $protocol . $host . '/google_auth.php',
    'https://' . $host . '/LAB5/google_auth.php',
    'http://' . $host . '/LAB5/google_auth.php'
];

echo "<div class='step'>
    <h2>Step 1: Go to Google Cloud Console</h2>
    <p>Visit the <a href='https://console.cloud.google.com' target='_blank'>Google Cloud Console</a>";
