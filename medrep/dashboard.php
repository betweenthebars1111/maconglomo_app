<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3); // MedRep only

include __DIR__ . '/../inc/header.php';
?>
<div class="container mt-4">
    <h1>MedRep Dashboard</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="client_coverage.php">
            View Client Coverage
        </a>
        <a class="list-group-item list-group-item-action" href="pre_post_calls.php">
            View Pre/Post Call Logs
        </a>
        <a class="list-group-item list-group-item-action" href="add_call.php">
            Add New Call (Pre/Post)
        </a>
        <a class="list-group-item list-group-item-action" href="add_client.php">
            Add New Client Coverage
        </a>
    </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>