<?php
/**
 * Session configuration file
 * 
 * This file sets up secure session configuration to protect against various attacks
 */

// Ensure session cookies are not accessible via JavaScript
ini_set('session.cookie_httponly', 1);

// Force sessions to only use cookies, not URL parameters
ini_set('session.use_only_cookies', 1);

// Set secure flag on cookies when using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Set SameSite attribute to Lax (prevents CSRF while allowing normal navigation)
ini_set('session.cookie_samesite', 'Lax');

// Set session lifetime to 30 minutes
ini_set('session.gc_maxlifetime', 1800);

// Set session name to something unique
session_name('SecureLibrarySession');

// Start the session
session_start();

// Regenerate session ID periodically (every 5 minutes)
if (!isset($_SESSION['last_regeneration']) || 
    (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

