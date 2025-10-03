<?php
require_once __DIR__ . '/../config/session.php';

// Start session if not started (optional here, but fine to keep)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to their dashboards
if (!empty($_SESSION['role_id'])) {
    switch ((int)$_SESSION['role_id']) {
        case 1:
            header("Location: /maconglomo_app/admin/dashboard.php");
            exit;
        case 2:
            header("Location: /maconglomo_app/inventory/dashboard.php");
            exit;
        case 3:
            header("Location: /maconglomo_app/medrep/dashboard.php");
            exit;
    }
}

// Flash message via cookie
$successMessage = '';
if (!empty($_COOKIE['flash_success'])) {
    $successMessage = $_COOKIE['flash_success'];

    // Delete the cookie so it behaves like a one-time flash
    $cookiePath = '/maconglomo_app';
    setcookie('flash_success', '', time() - 3600, $cookiePath, '', !empty($_SERVER['HTTPS']), true);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Maconglomo App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <!-- Simple Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MAConglomo</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/maconglomo_app/public/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 text-center">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <h1 class="mb-4">Welcome to Maconglomo App</h1>
        <p class="lead">Manage your medicine inventory and representatives with ease.</p>

        <div class="mt-4">
            <a href="login.php" class="btn btn-primary btn-lg me-2">Login</a>
            <a href="register.php" class="btn btn-outline-secondary btn-lg">Register</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Fade-out script for alerts -->
    <script>
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000); // 3 seconds before starting fade
    </script>
</body>

</html>