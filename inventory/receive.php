<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicine_id   = $_POST['medicine_id'] ?? null;
    $batch_no      = trim($_POST['batch_no'] ?? '');
    $expiry_date   = $_POST['expiry_date'] ?? null;
    $quantity      = (int) ($_POST['quantity'] ?? 0);
    $cost_per_unit = (float) ($_POST['cost'] ?? 0);
    $received_at   = $_POST['received_at'] ?? date('Y-m-d');  // ✅ Add this

    if ($medicine_id && $batch_no && $expiry_date && $quantity > 0) {
        $total_cost = $cost_per_unit > 0 ? $cost_per_unit * $quantity : null;
        try {
            $total_cost = $cost_per_unit * $quantity;

            // ✅ Check if batch already exists
            $sql = "SELECT id FROM medicine_batches 
                    WHERE medicine_id = ? AND batch_no = ? AND expiry_date = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$medicine_id, $batch_no, $expiry_date]);
            $existingBatch = $stmt->fetch();

            if ($existingBatch) {
                // ✅ Batch exists → update stock
                $batch_id = $existingBatch['id'];

                $sql = "UPDATE medicine_batches 
        SET quantity = quantity + ?, 
            stock_on_hand = stock_on_hand + ?, 
            cost_per_unit = ?, 
            cost = cost + ?
        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $quantity,
                    $quantity,
                    $cost_per_unit > 0 ? $cost_per_unit : null,
                    $total_cost ?? 0,
                    $batch_id
                ]);
            } else {
                // ✅ New batch → insert
                $sql = "INSERT INTO medicine_batches 
(medicine_id, batch_no, expiry_date, quantity, stock_on_hand, cost_per_unit, cost, received_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $medicine_id,
                    $batch_no,
                    $expiry_date,
                    $quantity,
                    $quantity,
                    $cost_per_unit > 0 ? $cost_per_unit : null,
                    $total_cost ?? 0,
                    $received_at
                ]);
                $batch_id = $pdo->lastInsertId();
            }

            // ✅ Insert transaction log (always record)
            $sql = "INSERT INTO stock_transactions 
(medicine_id, batch_id, transaction_type, date, quantity, cost) 
VALUES (?, ?, 'IN', ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $medicine_id,
                $batch_id,
                $received_at,
                $quantity,
                $total_cost ?? 0
            ]);


            // ✅ Success message
            $_SESSION['success'] = "Incoming stock recorded successfully.";

            // ✅ Clear form inputs after successful save
            $_POST = [];
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }
}

// Fetch medicines for dropdown
$meds = $pdo->query("SELECT id, generic_name, brand_name, unit FROM medicines ORDER BY generic_name ASC")->fetchAll();
?>

<div class="container mt-4">
    <h1>Record Incoming Stock</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="post" id="stockForm">
        <div class="mb-3">
            <label class="form-label">Medicine</label>
            <select name="medicine_id" class="form-control" required>
                <option value="">-- Select Medicine --</option>
                <?php foreach ($meds as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= (($_POST['medicine_id'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['generic_name']) ?> (<?= htmlspecialchars($m['brand_name']) ?> - <?= htmlspecialchars($m['unit']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Batch #</label>
            <input type="text" name="batch_no" class="form-control"
                value="<?= htmlspecialchars($_POST['batch_no'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Received At</label>
            <input type="date" name="received_at"
                class="form-control"
                value="<?= htmlspecialchars($_POST['received_at'] ?? date('Y-m-d')) ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control"
                value="<?= htmlspecialchars($_POST['expiry_date'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="text" name="quantity"
                class="form-control number-input"
                value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cost per Unit (₱)</label>
            <input type="text" name="cost"
                class="form-control currency-input"
                value="<?= htmlspecialchars($_POST['cost'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>

    <script>
        // Format Quantity (no decimals, with commas)
        document.querySelector('.number-input').addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                e.target.value = parseInt(value, 10).toLocaleString();
            }
        });

        // Format Price (with commas and 2 decimals on blur)
        document.querySelector('.currency-input').addEventListener('blur', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                e.target.value = parseFloat(value).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });

        // Before submitting → remove commas so DB only gets raw numbers
        document.getElementById('stockForm').addEventListener('submit', function() {
            let qtyInput = document.querySelector('.number-input');
            let costInput = document.querySelector('.currency-input');

            qtyInput.value = qtyInput.value.replace(/,/g, '');
            costInput.value = costInput.value.replace(/,/g, '');
        });
    </script>



    <?php include __DIR__ . '/../inc/footer.php'; ?>