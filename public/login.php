<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT u.*, r.name AS role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ((int)$user['is_approved'] !== 1) {
            $error = 'Account pending approval.';
        } elseif ((int)$user['is_active'] !== 1) {
            $error = 'Account inactive.';
        } else {
            // Successful login — set secure session data
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role_id']   = (int)$user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['must_change_password'] = (int)$user['must_change_password'];

            // Force password change if required
            if ($_SESSION['must_change_password'] === 1) {
                header("Location: /maconglomo_app/public/change_credentials.php");
                exit;
            }

            // Redirect based on role_id
            switch ($_SESSION['role_id']) {
                case 1: // Admin
                    header("Location: /maconglomo_app/admin/dashboard.php");
                    exit;
                case 2: // Inventory Keeper
                    header("Location: /maconglomo_app/inventory/dashboard.php");
                    exit;
                case 3: // MedRep
                    header("Location: /maconglomo_app/medrep/dashboard.php");
                    exit;
                default:
                    header("Location: /maconglomo_app/public/logout.php");
                    exit;
            }
        }
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<?php include __DIR__ . '/../inc/header.php'; ?>
<h2>Login</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post" class="mb-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100">Login</button>
</form>
<div class="text-center">
    <small>
        Don't have an account?
        <a href="/maconglomo_app/public/register.php">Register here</a>
    </small>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>