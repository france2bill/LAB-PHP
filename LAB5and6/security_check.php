<?php
/**
 * Security check functions
 */

/**
 * Check if the request is potentially a CSRF attack
 * 
 * @return bool True if the request is suspicious
 */
function is_potential_csrf() {
    // Check if referer is missing or from a different domain
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return true;
    }
    
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $request_host = $_SERVER['HTTP_HOST'];
    
    if ($referer_host !== $request_host) {
        error_log("CSRF check failed: Referer host ($referer_host) doesn't match request host ($request_host)");
        return true;
    }
    
    return false;
}

/**
 * Block the request if it appears to be a CSRF attack
 */
function block_csrf_attacks() {
    if (is_potential_csrf()) {
        // Log the attempt
        error_log('Blocked potential CSRF attack: ' . $_SERVER['REQUEST_URI']);
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'None'));
        
        // Clear the session
        session_destroy();
        
        // Return an error
        header('HTTP/1.1 403 Forbidden');
        echo 'Security check failed. Please try again from the application.';
        exit();
    }
}