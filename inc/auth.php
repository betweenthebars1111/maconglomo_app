<?php
// inc/auth.php
date_default_timezone_set('Asia/Manila'); // or your correct timezone


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching for all pages that require auth
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header("Location: /maconglomo_app/public/login.php");
        exit;
    }

    // Force password change check
    if (!empty($_SESSION['must_change_password']) && basename($_SERVER['PHP_SELF']) !== 'change_credentials.php') {
        header("Location: /maconglomo_app/public/change_credentials.php");
        exit;
    }
}

function require_role($role_id)
{
    require_login();

    /* Admins (role_id = 1) can access all pages (Don't forget to comment out soon)
    if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
        return;
    }*/

    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != $role_id) {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>";
        exit;
    }
}
