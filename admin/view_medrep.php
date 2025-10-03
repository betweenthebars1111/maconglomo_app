<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Only admin
require_once __DIR__ . '/../config/database.php';

$medrep_id = $_GET['id'] ?? null;
$log_type = $_GET['log'] ?? 'clients';
$filterDate = $_GET['date'] ?? null; // <-- added filter

if (!$medrep_id) {
    die("MedRep not specified.");
}

// Fetch MedRep info
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? AND role_id = 3");
$stmt->execute([$medrep_id]);
$medrep = $stmt->fetch();

if (!$medrep) {
    die("MedRep not found.");
}

// Fetch logs
if ($log_type === "clients") {
    $sql = "
        SELECT c.id, c.client_name, c.hospital_clinic,
               c.products_covered, c.proof_image, c.created_at, c.date
        FROM client_logs c
        WHERE c.medrep_id = ?
    ";
} else {
    $sql = "
        SELECT p.id, p.date, p.client_name, p.precall_notes, 
               p.postcall_notes, p.created_at
        FROM call_logs p
        WHERE p.medrep_id = ?
    ";
}

$params = [$medrep_id];

if ($filterDate) {
    $sql .= " AND date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY created_at DESC";
$logs = $pdo->prepare($sql);
$logs->execute($params);

include __DIR__ . '/../inc/header.php';
?>
<div class="container mt-4">
    <h2><?= htmlspecialchars($medrep['full_name']) ?> — <?= ucfirst($log_type) ?> Logs</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($medrep['email']) ?></p>
    <a href="medreps.php" class="btn btn-secondary mb-3">← Back to MedReps</a>

    <!-- Date Filter -->
    <form method="get" class="row g-3 mb-4" id="filterForm">
        <input type="hidden" name="id" value="<?= htmlspecialchars($medrep_id) ?>">
        <input type="hidden" name="log" value="<?= htmlspecialchars($log_type) ?>">
        <div class="col-auto">
            <input type="date" name="date" class="form-control"
                value="<?= htmlspecialchars($filterDate ?? '') ?>"
                onchange="document.getElementById('filterForm').submit();">
        </div>
        <div class="col-auto">
            <a href="?id=<?= htmlspecialchars($medrep_id) ?>&log=<?= htmlspecialchars($log_type) ?>"
                class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mb-3">
        <?php if ($log_type === "clients"): ?>
            <a href="client_coverage_pdf.php?id=<?= $medrep_id ?>&date=<?= urlencode($filterDate ?? '') ?>"
                target="_blank" class="btn btn-success">
                Export Client Coverage PDF
            </a>
        <?php else: ?>
            <a href="pre_post_calls_pdf.php?id=<?= $medrep_id ?>&date=<?= urlencode($filterDate ?? '') ?>"
                target="_blank" class="btn btn-success">
                Export Pre/Post Call PDF
            </a>
        <?php endif; ?>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <?php if ($log_type === "clients"): ?>
                    <th>Client Name</th>
                    <th>Hospital/Clinic</th>
                    <th>Products Covered</th>
                    <th>Proof</th>
                    <th>Date</th>
                <?php else: ?>
                    <th>Client/Doctor</th>
                    <th>Pre-call Notes</th>
                    <th>Post-call Notes</th>
                    <th>Date</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <?php if ($log_type === "clients"): ?>
                        <td><?= htmlspecialchars($log['client_name']) ?></td>
                        <td><?= htmlspecialchars($log['hospital_clinic']) ?></td>
                        <td><?= nl2br(htmlspecialchars($log['products_covered'])) ?></td>
                        <td>
                            <?php if (!empty($log['proof_image'])): ?>
                                <!-- Thumbnail -->
                                <img src="../<?= htmlspecialchars($log['proof_image']) ?>"
                                    alt="Proof Image"
                                    class="img-thumbnail"
                                    style="max-width: 100px; cursor:pointer;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#imageModal<?= $log['id'] ?>">

                                <!-- Modal -->
                                <div class="modal fade" id="imageModal<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Proof Image</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="../<?= htmlspecialchars($log['proof_image']) ?>"
                                                    class="img-fluid rounded shadow" alt="Proof Image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No Proof</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <?php else: ?>
                        <td><?= htmlspecialchars($log['client_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($log['precall_notes'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($log['postcall_notes'])) ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>