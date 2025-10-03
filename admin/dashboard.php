<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Only admin

include __DIR__ . '/../inc/header.php';
?>
<div class="container mt-4">
    <h1>Admin Dashboard</h1>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="approve_users.php">Approve / Reject Users</a>
        <a class="list-group-item list-group-item-action" href="list.php">Inventory Dashboard</a>
        <a class="list-group-item list-group-item-action" href="medreps.php">MedRep Dashboard</a>
    </div>



</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>