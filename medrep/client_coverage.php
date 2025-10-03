<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3);

require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header.php';

$medrepId = (int)$_SESSION['user_id'];

// Handle date filter
$filterDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : null;

// Client logs query
$clientSql = "
    SELECT id, date, client_name, hospital_clinic, products_covered, proof_image, created_at
    FROM client_logs
    WHERE medrep_id = ?
";
$params = [$medrepId];

if ($filterDate) {
    $clientSql .= " AND date = ?";
    $params[] = $filterDate;
}

$clientSql .= " ORDER BY date DESC, id DESC LIMIT 30";
$clientStmt = $pdo->prepare($clientSql);
$clientStmt->execute($params);
$clientLogs = $clientStmt->fetchAll();
?>

<div class="container mt-4">
    <h1>Client Coverage (latest 30)</h1>


    <!-- PDF button: open in new tab -->
    <a href="client_coverage_pdf.php<?= $filterDate ? '?date=' . urlencode($filterDate) : '' ?>"
        class="btn btn-danger mb-3"
        target="_blank">
        <i class="bi bi-file-earmark-pdf-fill me-2"></i>
        <span>Download PDF</span>
    </a>
    <!-- mb-3 adds bottom margin -->

    <!-- Date Filter -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="date" name="date" class="form-control"
                value="<?= htmlspecialchars($filterDate ?? '') ?>"
                onchange="this.form.submit();">
        </div>
        <div class="col-auto">
            <a href="client_coverage.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>



    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Hospital/Clinic</th>
                    <th>Products Covered</th>
                    <th>Photo</th>
                    <th>Logged At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$clientLogs): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No entries yet.</td>
                    </tr>
                    <?php else: foreach ($clientLogs as $row): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= htmlspecialchars($row['hospital_clinic']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['products_covered'])) ?></td>
                            <td>
                                <?php if (!empty($row['proof_image'])): ?>
                                    <img src="../<?= htmlspecialchars($row['proof_image']) ?>" alt="Proof"
                                        class="img-thumbnail" style="max-width:100px;"
                                        data-bs-toggle="modal" data-bs-target="#imageModal<?= $row['id'] ?>">

                                    <!-- Modal -->
                                    <div class="modal fade" id="imageModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Proof Image</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="../<?= htmlspecialchars($row['proof_image']) ?>"
                                                        class="img-fluid rounded" alt="Proof Image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y, g:i a', strtotime($row['created_at'])) ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>