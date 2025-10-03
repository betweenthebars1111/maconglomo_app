<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1);
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $table = str_replace("`", "``", $table);
    $column = str_replace("`", "``", $column);
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $stmt = $pdo->query($sql);
    return (bool)$stmt->fetch();
}

// always compute stock from transactions
$ttCol = columnExists($pdo, 'stock_transactions', 'transaction_type') ? 'transaction_type' : 'type';

// General inventory (not expired, not near expiry — > 1 year)
$sql = "
    SELECT
        b.id AS batch_id,
        m.id AS medicine_id,
        m.generic_name,
        m.brand_name,
        m.unit,
        b.batch_no,
        b.expiry_date,
        (
            SELECT COALESCE(SUM(CASE WHEN t.`$ttCol` = 'IN' THEN t.quantity ELSE -t.quantity END), 0)
            FROM stock_transactions t
            WHERE t.batch_id = b.id
        ) AS stock
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.expiry_date > DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
    ORDER BY m.generic_name ASC, b.expiry_date ASC
";

// Near expiry (within 1 year but not expired)
$nearSql = "
    SELECT
        b.id AS batch_id,
        m.generic_name, m.brand_name, m.unit,
        b.batch_no, b.expiry_date,
        (
            SELECT COALESCE(SUM(CASE WHEN t.`$ttCol` = 'IN' THEN t.quantity ELSE -t.quantity END), 0)
            FROM stock_transactions t
            WHERE t.batch_id = b.id
        ) AS stock
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.expiry_date > CURDATE()
      AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
    HAVING stock > 0
    ORDER BY b.expiry_date ASC
";

// Expired (today or earlier)
$expiredSql = "
    SELECT
        b.id AS batch_id,
        m.generic_name, m.brand_name, m.unit,
        b.batch_no, b.expiry_date,
        (
            SELECT COALESCE(SUM(CASE WHEN t.`$ttCol` = 'IN' THEN t.quantity ELSE -t.quantity END), 0)
            FROM stock_transactions t
            WHERE t.batch_id = b.id
        ) AS stock
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.expiry_date <= CURDATE()
    HAVING stock > 0
    ORDER BY b.expiry_date ASC
";

$batches    = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$nearExpiry = $pdo->query($nearSql)->fetchAll(PDO::FETCH_ASSOC);
$expired    = $pdo->query($expiredSql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1>Inventory</h1>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Batch deleted successfully.</div>
    <?php endif; ?>

    <h3>General Inventory</h3>
    <?php if (empty($batches)): ?>
        <div class="alert alert-info">No valid batches found.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Generic Name</th>
                    <th>Brand Name</th>
                    <th>Unit</th>
                    <th>Batch #</th>
                    <th>EXP. DATE</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                    <tr onclick="window.location='stockcard.php?batch_id=<?= $b['batch_id'] ?>'" style="cursor:pointer;">
                        <td><?= htmlspecialchars($b['generic_name']) ?></td>
                        <td><?= htmlspecialchars($b['brand_name']) ?></td>
                        <td><?= htmlspecialchars($b['unit'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['batch_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['expiry_date'] ?? '') ?></td>
                        <td><?= (int)$b['stock'] ?></td>
                        <td>
                            <form method="post" action="delete_batch.php"
                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this batch? This will also remove its stock transactions.');">
                                <input type="hidden" name="batch_id" value="<?= (int)$b['batch_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="event.stopPropagation();">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3 class="mt-5">NEAR EXPIRY (within 1 year)</h3>
    <?php if (empty($nearExpiry)): ?>
        <div class="alert alert-info">No near-expiry batches found.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Generic Name</th>
                    <th>Brand Name</th>
                    <th>Unit</th>
                    <th>Batch #</th>
                    <th>EXP. DATE</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nearExpiry as $b): ?>
                    <tr onclick="window.location='stockcard.php?batch_id=<?= $b['batch_id'] ?>'" style="cursor:pointer;">
                        <td><?= htmlspecialchars($b['generic_name']) ?></td>
                        <td><?= htmlspecialchars($b['brand_name']) ?></td>
                        <td><?= htmlspecialchars($b['unit'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['batch_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['expiry_date'] ?? '') ?></td>
                        <td><?= (int)$b['stock'] ?></td>
                        <td>
                            <form method="post" action="delete_batch.php"
                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this batch? This will also remove its stock transactions.');">
                                <input type="hidden" name="batch_id" value="<?= (int)$b['batch_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="event.stopPropagation();">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3 class="mt-5 text-danger">EXPIRED</h3>
    <?php if (empty($expired)): ?>
        <div class="alert alert-info">No expired batches found.</div>
    <?php else: ?>
        <table class="table table-bordered table-danger">
            <thead>
                <tr>
                    <th>Generic Name</th>
                    <th>Brand Name</th>
                    <th>Unit</th>
                    <th>Batch #</th>
                    <th>EXP. DATE</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expired as $b): ?>
                    <tr onclick="window.location='stockcard.php?batch_id=<?= $b['batch_id'] ?>'" style="cursor:pointer;">
                        <td><?= htmlspecialchars($b['generic_name']) ?></td>
                        <td><?= htmlspecialchars($b['brand_name']) ?></td>
                        <td><?= htmlspecialchars($b['unit'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['batch_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['expiry_date'] ?? '') ?></td>
                        <td><?= (int)$b['stock'] ?></td>
                        <td>
                            <form method="post" action="delete_batch.php"
                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this batch? This will also remove its stock transactions.');">
                                <input type="hidden" name="batch_id" value="<?= (int)$b['batch_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="event.stopPropagation();">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="container mt-4">
    <a href="export_inventory.php"
        class="btn btn-danger"
        target="_blank"
        style="display: inline-flex; align-items: center; justify-content: center;">
        <i class="bi bi-file-earmark-pdf-fill me-2"></i>
        <span>Download PDF</span>
    </a>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>