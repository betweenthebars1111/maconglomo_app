<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Require admin role
require_role(1);

// Extra safety: ensure admin is logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /maconglomo_app/public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($userId > 0 && in_array($action, ['APPROVE', 'REJECT'], true)) {

        // 1. Log the action first (while the user_id exists)
        $logStmt = $pdo->prepare(
            "INSERT INTO user_approvals (user_id, action, admin_id) VALUES (?, ?, ?)"
        );
        $logStmt->execute([$userId, $action, $_SESSION['user_id']]);

        // 2. Then process the action
        if ($action === 'APPROVE') {
            $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
        }

        // 3. Flash message
        $_SESSION['flash_message'] = "User has been successfully " . strtolower($action) . "d.";

        // 4. Redirect to prevent form resubmission
        header("Location: approve_users.php");
        exit;
    }
}

// Fetch pending users
$pendingUsers = $pdo->query("
    SELECT u.id, u.email, u.full_name, r.name AS role
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE is_approved = 0
")->fetchAll();

include __DIR__ . '/../inc/header.php';
?>

<h2>Approve Users</h2>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Email</th>
            <th>Full Name</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($pendingUsers)): ?>
            <?php foreach ($pendingUsers as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <form method="post" style="display:inline-block">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button name="action" value="APPROVE" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to reject and delete this user? This cannot be undone.');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button name="action" value="REJECT" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">No pending users</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../inc/footer.php'; ?>