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
    <h1>My Logbook</h1>

    <!-- Date Filter -->
    <form method="get" class="row g-3 mb-4" id="filterForm">
        <div class="col-auto">
            <input type="date" name="date" class="form-control"
                value="<?= htmlspecialchars($filterDate ?? '') ?>"
                onchange="document.getElementById('filterForm').submit();">
        </div>
        <div class="col-auto">
            <a href="logbook.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>


    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h3 class="mt-4">Client Coverage (latest 30)</h3>
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
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= htmlspecialchars($row['hospital_clinic']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['products_covered'])) ?></td>
                            <td>
                                <?php if (!empty($row['proof_image'])): ?>
                                    <img src="../<?= htmlspecialchars($row['proof_image']) ?>"
                                        alt="Proof Image"
                                        class="img-thumbnail"
                                        style="max-width: 100px; cursor: pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal<?= $row['id'] ?>">

                                    <!-- Modal -->
                                    <div class="modal fade" id="imageModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Proof Image</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="../<?= htmlspecialchars($row['proof_image']) ?>"
                                                        class="img-fluid rounded shadow" alt="Proof Image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>

    <h3 class="mt-5">Pre/Post Call (latest 30)</h3>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Pre-call Notes</th>
                    <th>Post-call Notes</th>
                    <th>Logged At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$callLogs): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No entries yet.</td>
                    </tr>
                    <?php else: foreach ($callLogs as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['precall_notes'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['postcall_notes'])) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Proof Image" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        const modalImage = document.getElementById('modalImage');

        document.querySelectorAll('.view-image-btn').forEach(button => {
            button.addEventListener('click', function() {
                const imagePath = this.getAttribute('data-image');
                modalImage.src = imagePath;
                modal.show();
            });
        });
    });
</script>