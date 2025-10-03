<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalize casing before saving
    $generic = strtoupper(trim($_POST['generic_name'] ?? '')); // ALL CAPS
    $brand   = ucwords(strtolower(trim($_POST['brand_name'] ?? ''))); // Title Case
    $unit    = strtoupper(trim($_POST['unit'] ?? '')); // ALL CAPS

    if ($generic && $brand && $unit) {
        try {
            // Check if exact combination already exists
            $checkSql = "SELECT COUNT(*) FROM medicines WHERE generic_name = ? AND brand_name = ? AND unit = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$generic, $brand, $unit]);
            $exists = $checkStmt->fetchColumn();

            if ($exists > 0) {
                $error = "⚠️ Medicine with Generic: <strong>$generic</strong>, Brand: <strong>$brand</strong>, and Unit: <strong>$unit</strong> already exists.";
            } else {
                // Insert only if unique combination
                $sql = "INSERT INTO medicines (generic_name, brand_name, unit) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$generic, $brand, $unit]);

                $success = "✅ Medicine <strong>$generic ($brand, $unit)</strong> added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<div class="container mt-4">
    <h1>Add New Medicine</h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Generic Name</label>
            <input type="text" name="generic_name" class="form-control text-uppercase" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Brand Name</label>
            <input type="text" name="brand_name" class="form-control brand-case" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Unit</label>
            <input type="text" name="unit" class="form-control text-uppercase" placeholder="e.g., TABLET, CAPSULE, BOTTLE" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Medicine</button>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
</div>

<script>
    // Force uppercase for generic name & unit as user types
    document.querySelectorAll('.text-uppercase').forEach(el => {
        el.addEventListener('input', () => {
            el.value = el.value.toUpperCase();
        });
    });

    // Auto Title Case for brand name
    document.querySelectorAll('.brand-case').forEach(el => {
        el.addEventListener('input', () => {
            el.value = el.value
                .toLowerCase()
                .replace(/\b\w/g, char => char.toUpperCase());
        });
    });
</script>

<?php include __DIR__ . '/../inc/footer.php'; ?>