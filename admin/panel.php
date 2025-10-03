<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Admin only

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $user_id = (int)$_POST['user_id'];
    $action  = $_POST['action'] ?? '';

    if (in_array($action, ['APPROVE', 'REJECT'])) {
        $pdo->beginTransaction();

        if ($action === 'APPROVE') {
            $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET is_approved = 0, is_active = 0 WHERE id = ?");
            $stmt->execute([$user_id]);
        }

        // Log action
        $stmt = $pdo->prepare("
            INSERT INTO user_approvals (user_id, action, admin_id, reason) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $action, $_SESSION['user_id'], $_POST['reason'] ?? null]);

        $pdo->commit();

        header("Location: panel.php?success=1");
        exit;
    }
}

// Fetch pending accounts
$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.email, r.name AS role_name, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.is_approved = 0
    ORDER BY u.created_at ASC
");
$pending_users = $stmt->fetchAll();

include __DIR__ . '/../inc/header.php';
?>
<div class="container mt-4">
    <h1>Admin Panel</h1>
    <p>Approve or reject pending accounts.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Action completed successfully.</div>
    <?php endif; ?>

    <?php if (empty($pending_users)): ?>
        <div class="alert alert-info">No pending accounts.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role_name']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="action" value="APPROVE">
                                <button class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="action" value="REJECT">
                                <input type="text" name="reason" placeholder="Reason" class="form-control form-control-sm d-inline-block" style="width:120px;">
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>