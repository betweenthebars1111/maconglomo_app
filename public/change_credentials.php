<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/auth.php';
include __DIR__ . '/../inc/header.php';

require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $new_password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ?, must_change_password = 0 WHERE id = ?");
        $stmt->execute([$new_email, $new_password_hashed, $_SESSION['user_id']]);

        $_SESSION['must_change_password'] = 0;

        header("Location: /maconglomo_app/admin/dashboard.php");
        exit;
    }
}
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2 class="mb-4">Change Your Credentials</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="email" class="form-label">New Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update</button>
    </form>
</div>