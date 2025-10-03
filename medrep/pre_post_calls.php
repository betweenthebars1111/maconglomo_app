<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3);

require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

$medrepId = (int)$_SESSION['user_id'];

// Handle date filter
$filterDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : null;

// Call logs query
$callSql = "
    SELECT id, date, client_name, precall_notes, postcall_notes, created_at
    FROM call_logs
    WHERE medrep_id = ?
";
$params = [$medrepId];

if ($filterDate) {
    $callSql .= " AND date = ?";
    $params[] = $filterDate;
}

$callSql .= " ORDER BY date DESC, id DESC LIMIT 30";
$callStmt = $pdo->prepare($callSql);
$callStmt->execute($params);
$callLogs = $callStmt->fetchAll();
?>

<div class="container mt-4">
    <h1>Pre/Post Call Logs (latest 30)</h1>

    <!-- Date Filter and PDF Button -->
    <form method="get" class="row g-3 mb-4 align-items-center">
        <div class="col-auto">
            <input type="date" name="date" class="form-control"
                value="<?= htmlspecialchars($filterDate ?? '') ?>"
                onchange="this.form.submit();">
        </div>
        <div class="col-auto">
            <a href="pre_post_calls.php" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-auto">
            <!-- PDF Export Button -->
            <a href="pre_post_calls_pdf.php?<?= $filterDate ? 'date=' . urlencode($filterDate) : '' ?>"
                target="_blank"
                class="btn btn-danger">
                Export PDF
            </a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle" style="table-layout: auto; word-wrap: break-word;">
            <thead>
                <tr>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 15%;">Client</th>
                    <th style="width: 35%;">Pre-call Notes</th>
                    <th style="width: 35%;">Post-call Notes</th>
                    <th style="width: 15%;">Logged At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$callLogs): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No entries yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($callLogs as $row): ?>
                        <tr>
                            <!-- Keep full date for the Date column -->
                            <td><?= date('M j, Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td style="white-space: normal;"><?= nl2br(htmlspecialchars($row['precall_notes'])) ?></td>
                            <td style="white-space: normal;"><?= nl2br(htmlspecialchars($row['postcall_notes'])) ?></td>
                            <!-- Show only time for Logged At -->
                            <td><?= date('g:i a', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <?php include __DIR__ . '/../inc/footer.php'; ?>