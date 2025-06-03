<?php
// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Optional: Set defaults if environment variables are not set
if (!isset($_ENV['GOOGLE_CLIENT_ID'])) {
    $_ENV['GOOGLE_CLIENT_ID'] = '';
}
if (!isset($_ENV['GOOGLE_CLIENT_SECRET'])) {
    $_ENV['GOOGLE_CLIENT_SECRET'] = '';
}
if (!isset($_ENV['GOOGLE_REDIRECT_URI'])) {
    $_ENV['GOOGLE_REDIRECT_URI'] = 'http://localhost/LAB5/google_auth.php';
}
?>