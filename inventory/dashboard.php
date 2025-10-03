<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only

include __DIR__ . '/../inc/header.php';
?>
<div class="container mt-4">
    <h1>Inventory Dashboard</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="list.php">View Inventory</a>
        <a class="list-group-item list-group-item-action" href="add_medicine.php">Add New Medicine</a>
        <a class="list-group-item list-group-item-action" href="receive.php">Record Incoming Stock</a>
        <a class="list-group-item list-group-item-action" href="sale.php">Record Outgoing Stock</a>
    </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>