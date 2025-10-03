<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // only admin can delete
require_once __DIR__ . '/../config/database.php';

// Check if batch_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_id'])) {
    $batch_id = (int)$_POST['batch_id'];

    // If password not yet submitted → show password form
    if (!isset($_POST['password'])) {
        include __DIR__ . '/../inc/header.php'; ?>
        <div class="container mt-4">
            <h3>Confirm Deletion</h3>
            <p>Deleting this batch will also remove all related stock transactions. Please confirm your password to continue.</p>
            <form method="post">
                <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">Your Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    <?php include __DIR__ . '/../inc/footer.php';
        exit;
    }

    // If password was submitted → verify
    $enteredPassword = trim($_POST['password']);

    // Get current user from session
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header("Location: login.php");
        exit;
    }

    // Fetch password hash for this user
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($enteredPassword, $row['password_hash'])) {
        include __DIR__ . '/../inc/header.php'; ?>
        <div class="container mt-4">
            <div class="alert alert-danger">Password incorrect. Deletion not allowed.</div>
            <a href="list.php" class="btn btn-primary">Back to Inventory</a>
        </div>
<?php include __DIR__ . '/../inc/footer.php';
        exit;
    }

    // Password correct → delete batch + related transactions
    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM stock_transactions WHERE batch_id = ?")->execute([$batch_id]);
        $pdo->prepare("DELETE FROM medicine_batches WHERE id = ?")->execute([$batch_id]);
        $pdo->commit();

        header("Location: list.php?deleted=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error deleting batch: " . $e->getMessage());
    }
}

// If accessed directly with no POST
header("Location: list.php");
exit;
