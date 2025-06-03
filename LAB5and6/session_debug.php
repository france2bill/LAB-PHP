<?php
session_start();
echo "<h1>Session Debug Information</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "</pre>";

echo "<p><a href='login.php'>Return to Login</a></p>";
?>