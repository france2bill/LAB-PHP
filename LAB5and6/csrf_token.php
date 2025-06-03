<?php
/**
 * CSRF Protection Functions
 * 
 * This file contains functions for generating and validating CSRF tokens
 * to protect against Cross-Site Request Forgery attacks.
 */

/**
 * Generates a new CSRF token and stores it in the session
 * 
 * @return string The generated token
 */
function generate_csrf_token() {
    // Only generate a new token if one doesn't exist
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        error_log('Generated new CSRF token: ' . $_SESSION['csrf_token']);
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a CSRF token against the one stored in the session
 * 
 * @param string $token The token to validate
 * @return bool True if the token is valid, false otherwise
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        error_log('CSRF validation failed: No token in session');
        return false;
    }
    
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    error_log('CSRF validation: ' . ($valid ? 'Success' : 'Failed') . 
              ' (Session: ' . $_SESSION['csrf_token'] . ', Submitted: ' . $token . ')');
    return $valid;
}

/**
 * Refreshes the CSRF token by generating a new one
 * 
 * @return string The new token
 */
function refresh_csrf_token() {
    unset($_SESSION['csrf_token']);
    return generate_csrf_token();
}

/**
 * Outputs a hidden input field containing the CSRF token
 * 
 * @return void
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}




