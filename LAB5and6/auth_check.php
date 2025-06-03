<?php
/**
 * Authentication check utility
 * 
 * This file provides functions to verify user authentication status
 * and redirect unauthorized users to the login page.
 */

/**
 * Checks if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verifies user authentication and redirects to login page if not authenticated
 * 
 * @param string $redirect_url Optional URL to redirect after successful login
 * @return void
 */
function auth_check($redirect_url = '') {
    if (!is_logged_in()) {
        // User is not logged in, redirect to login page
        $redirect_param = $redirect_url ? "?redirect=" . urlencode($redirect_url) : '';
        header("Location: login.php" . $redirect_param);
        exit();
    }
}

/**
 * Gets the current user's display name
 * 
 * @return string User's username or email if username is not available
 */
function get_user_display_name() {
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        return $_SESSION['username'];
    } elseif (isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])) {
        return $_SESSION['user_email'];
    } else {
        return "User";
    }
}

/**
 * Checks if the user logged in via Google
 * 
 * @return bool True if user logged in via Google, false otherwise
 */
function is_google_login() {
    return isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'google';
}
?>