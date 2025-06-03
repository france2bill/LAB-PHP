<?php
/**
 * Admin authentication check
 * Include this file at the top of all admin pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in as admin
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           !empty($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Check if this is an admin page (not login page)
$is_login_page = basename($_SERVER['SCRIPT_NAME']) === 'admin_login.php';

if (!$is_login_page) {
    // For all admin pages, verify admin is logged in
    if (!is_admin_logged_in()) {
        // Not logged in, redirect to login page
        header("Location: admin_login.php");
        exit();
    }
    
    // For POST requests to admin pages, verify CSRF token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            // Invalid CSRF token
            http_response_code(403);
            die("Security validation failed. Access denied.");
        }
    }
}