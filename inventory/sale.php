<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_id   = $_POST['batch_id'] ?? null;
    $quantity   = $_POST['quantity'] ?? null;
    $price      = $_POST['price'] ?? null;
    $invoice_no = $_POST['invoice_no'] ?? null;
    $customer   = trim($_POST['customer'] ?? '');
    $date       = $_POST['date'] ?? date('Y-m-d'); // default to today

    // ✅ Only check required fields (price not included)
    if ($batch_id && $quantity && $invoice_no && $customer) {
        // Check available stock
        $stmt = $pdo->prepare("SELECT stock_on_hand, medicine_id, cost_per_unit FROM medicine_batches WHERE id = ?");
        $stmt->execute([$batch_id]);
        $batch = $stmt->fetch();

        if ($batch && $batch['stock_on_hand'] >= $quantity) {
            // Deduct stock
            $update = $pdo->prepare("UPDATE medicine_batches SET stock_on_hand = stock_on_hand - ? WHERE id = ?");
            $update->execute([$quantity, $batch_id]);

            // Normalize price → NULL if empty
            $price = ($price !== null && $price !== '') ? $price : null;

            // Insert transaction (use provided date)
            $sql = "INSERT INTO stock_transactions 
                    (medicine_id, batch_id, transaction_type, date, quantity, cost, price, invoice_no, customer) 
                    VALUES (?, ?, 'OUT', ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $batch['medicine_id'],
                $batch_id,
                $date,
                $quantity,
                $batch['cost_per_unit'],
                $price,   // ✅ can be NULL
                $invoice_no,
                $customer
            ]);

            $_SESSION['success'] = "Sale recorded successfully.";
        } else {
            $_SESSION['error'] = "Not enough stock available in this batch.";
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }
}


// Fetch batches (only ones with stock left)
$sql = "SELECT b.id AS batch_id, m.generic_name, m.brand_name, m.unit, 
               b.batch_no, b.expiry_date, b.stock_on_hand, b.cost_per_unit
        FROM medicine_batches b
        JOIN medicines m ON b.medicine_id = m.id
        WHERE b.stock_on_hand > 0
        ORDER BY m.generic_name ASC, b.expiry_date ASC";
$batches = $pdo->query($sql)->fetchAll();
?>

<div class="container mt-4">
    <h1>Record Outgoing Stock</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div id="successAlert" class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post" id="saleForm">
        <div class="mb-3">
            <label class="form-label">Select Batch</label>
            <select name="batch_id" id="batch_id" class="form-control" required>
                <option value="">-- Select Medicine Batch --</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['batch_id'] ?>"
                        data-cost="<?= htmlspecialchars($b['cost_per_unit']) ?>">
                        <?= htmlspecialchars($b['generic_name']) ?> (<?= htmlspecialchars($b['brand_name']) ?>)
                        | Unit: <?= htmlspecialchars($b['unit']) ?>
                        | Batch: <?= htmlspecialchars($b['batch_no']) ?>
                        | Exp: <?= htmlspecialchars($b['expiry_date']) ?>
                        | Stock: <?= htmlspecialchars($b['stock_on_hand']) ?>
                        | Cost/Unit: ₱<?= $b['cost_per_unit'] !== null ? number_format($b['cost_per_unit'], 2) : 'N/A' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Invoice No.</label>
            <input type="text" name="invoice_no" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Customer</label>
            <input type="text" name="customer" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control"
                value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price per Unit</label>
            <input type="number" step="0.01" id="price" name="price" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const batchSelect = document.getElementById("batch_id");
            const priceInput = document.getElementById("price");

            batchSelect.addEventListener("change", function() {
                const selected = batchSelect.options[batchSelect.selectedIndex];
                const cost = selected.getAttribute("data-cost");

                if (cost) {
                    priceInput.value = parseFloat(cost).toFixed(2); // set default price
                }
            });

            // Auto-hide success alert after 3 seconds
            const successAlert = document.getElementById("successAlert");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = "opacity 1s";
                    successAlert.style.opacity = 0;
                    setTimeout(() => successAlert.remove(), 1000);
                }, 3000);
            }
        });
    </script>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>