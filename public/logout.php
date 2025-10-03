<?php
require_once __DIR__ . '/../config/session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Put the flash message in a short-lived cookie so we can safely destroy the session.
 * Adjust the path to your app root so the cookie is visible on index.php.
 */
$cookiePath = '/maconglomo_app';
setcookie('flash_success', 'You have successfully logged out.', [
    'expires'  => time() + 10,                      // 10 seconds is enough for the redirect
    'path'     => $cookiePath,
    'secure'   => !empty($_SERVER['HTTPS']),        // true if using HTTPS
    'httponly' => true,                              // PHP can read it; JS can’t
    'samesite' => 'Lax',
]);

// Clear all session vars
$_SESSION = [];
session_unset();

// Destroy session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to public index
header('Location: /maconglomo_app/public/index.php');
exit;
