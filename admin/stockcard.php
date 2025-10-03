<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1);
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

$batch_id = isset($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
if ($batch_id <= 0) {
    $_SESSION['error'] = "Batch ID is required.";
    header("Location: list.php");
    exit;
}

// fetch batch + medicine info
$stmt = $pdo->prepare("
    SELECT b.*, m.generic_name, m.brand_name, m.unit
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.id = ?
");
$stmt->execute([$batch_id]);
$batch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$batch) {
    $_SESSION['error'] = "Batch not found.";
    header("Location: list.php");
    exit;
}

// detect transaction column name
$ttCol = (function ($pdo) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'transaction_type'");
    $stmt->execute();
    if ($stmt->fetch()) return 'transaction_type';
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'type'");
    $stmt->execute();
    return $stmt->fetch() ? 'type' : 'transaction_type';
})($pdo);

// fetch transactions (ascending to compute running stock)
$sql = "SELECT * FROM stock_transactions WHERE batch_id = ? ORDER BY date ASC, id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$batch_id]);
$txs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// compute running stock
$running = 0;
$rows = [];
foreach ($txs as $t) {
    $col = $ttCol;
    $tt = $t[$col] ?? ($t['type'] ?? ($t['transaction_type'] ?? null));
    if ($tt === 'IN') {
        $running += (int)$t['quantity'];
    } else {
        $running -= (int)$t['quantity'];
    }
    $t['running'] = $running;
    $rows[] = $t;
}

// sort param: default DESC
$sort = $_GET['sort'] ?? 'desc';
if ($sort === 'desc') {
    $rows = array_reverse($rows);
}

// current stock
$currentStock = null;
if (isset($batch['stock_on_hand'])) $currentStock = (int)$batch['stock_on_hand'];
elseif (isset($batch['quantity'])) $currentStock = (int)$batch['quantity'];
else $currentStock = end($rows)['running'] ?? 0;
?>

<div class="container mt-4">
    <h1>Stock Card</h1>
    <div class="mb-3">
        <a href="export_stockcard.php?batch_id=<?= $batch_id ?>"
            class="btn btn-danger" target="_blank">
            Export PDF
        </a>
    </div>
    <h3>PRODUCT: <?= htmlspecialchars($batch['generic_name']) ?> (<?= htmlspecialchars($batch['brand_name']) ?>)</h3>
    <p>
        Unit: <?= htmlspecialchars($batch['unit'] ?? '') ?> |
        Batch #: <?= htmlspecialchars($batch['batch_no'] ?? '') ?> |
        Expiry: <?= htmlspecialchars($batch['expiry_date'] ?? '') ?> |
        Current Stock: <strong><?= $currentStock ?></strong> |
        Cost/Unit: <strong>₱<?= number_format($batch['cost_per_unit'] ?? 0, 2) ?></strong>
    </p>

    <!-- Sorting Toggle -->
    <form method="get" class="mb-3">
        <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
        <label for="sort">Sort by Date:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Ascending (oldest first)</option>
            <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Descending (latest first)</option>
        </select>
    </form>

    <table class="table table-bordered table-sm mt-3">
        <thead>
            <tr>
                <th>Date</th>
                <th>Received (IN)</th>
                <th>Sale (OUT)</th>
                <th>Stock on Hand</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="4" class="text-center">No transactions for this batch.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['date'] ?? $t['created_at'] ?? '') ?></td>
                    <td>
                        <?php if (($t['transaction_type'] ?? ($t['type'] ?? '')) === 'IN'): ?>
                            Qty: <?= (int)$t['quantity'] ?><br>
                            <?= isset($t['cost']) ? 'Cost: ' . number_format($t['cost'], 2) : '' ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($t['transaction_type'] ?? ($t['type'] ?? '')) === 'OUT'): ?>
                            <?= isset($t['invoice_no']) ? 'Invoice: ' . htmlspecialchars($t['invoice_no']) . '<br>' : '' ?>
                            <?= isset($t['customer']) ? 'Customer: ' . htmlspecialchars($t['customer']) . '<br>' : '' ?>
                            Qty: <?= (int)$t['quantity'] ?><br>
                            <?= isset($t['price']) ? 'Price: ' . number_format($t['price'], 2) . '<br>' : '' ?>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$t['running'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="list.php" class="btn btn-secondary">Back to Inventory</a>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>