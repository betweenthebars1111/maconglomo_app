<?php
// admin/medreps.php
require_once __DIR__ . '/../inc/auth.php';
require_role(1);
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email
        FROM users
        WHERE role_id = :role_id AND is_approved = 1
        ORDER BY full_name ASC
    ");
    $stmt->execute(['role_id' => 3]);
    $medreps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

include __DIR__ . '/../inc/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Medical Representatives</h1>

    <?php if (empty($medreps)): ?>
        <div class="alert alert-info">No approved medical representatives found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 30%;">Name</th>
                        <th style="width: 35%;">Email</th>
                        <th style="width: 35%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medreps as $m): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($m['full_name']) ?></td>
                            <td class="text-start"><?= htmlspecialchars($m['email']) ?></td>
                            <td>
                                <a href="view_medrep.php?id=<?= urlencode($m['id']) ?>&log=clients"
                                    class="btn btn-primary btn-sm me-2 mb-1">
                                    Client Coverage Logs
                                </a>
                                <a href="view_medrep.php?id=<?= urlencode($m['id']) ?>&log=calls"
                                    class="btn btn-secondary btn-sm mb-1">
                                    Pre/Post Call Logs
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>