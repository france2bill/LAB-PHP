<?php
/**
 * Security functions for the application
 */

/**
 * Verify that the user is logged in as an admin
 * 
 * @return bool True if the user is logged in as an admin
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check if the request is potentially a CSRF attack
 * 
 * @return bool True if the request is suspicious
 */
function is_potential_csrf() {
    // For POST requests, check if CSRF token is valid
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            error_log('CSRF token validation failed');
            return true;
        }
    }
    
    // Check if referer is missing or from a different domain
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return true;
    }
    
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $request_host = $_SERVER['HTTP_HOST'];
    
    if ($referer_host !== $request_host) {
        error_log("Suspicious request: Referer host ($referer_host) doesn't match request host ($request_host)");
        return true;
    }
    
    return false;
}

/**
 * Generate a CSRF token and store it in the session
 * 
 * @return string The generated token
 */
function generate_csrf_token() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Output a hidden input field with the CSRF token
 */
function csrf_token_field() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}