<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
include __DIR__ . '/../inc/header.php';

$errors = [];
$success_message = '';
$old = [
    'date' => date('Y-m-d'),
    'client_name' => '',
    'precall_notes' => '',
    'postcall_notes' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $date        = trim($_POST['date'] ?? '');
        $client_name = trim($_POST['client_name'] ?? '');
        $pre_notes   = trim($_POST['precall_notes'] ?? '');
        $post_notes  = trim($_POST['postcall_notes'] ?? '');

        $old = [
            'date' => $date,
            'client_name' => $client_name,
            'precall_notes' => $pre_notes,
            'postcall_notes' => $post_notes
        ];

        if ($date === '')         $errors[] = 'Date is required.';
        if ($client_name === '')  $errors[] = 'Client name is required.';

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO call_logs (medrep_id, date, client_name, precall_notes, postcall_notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $date, $client_name, $pre_notes, $post_notes]);

            $success_message = "✅ Call log added successfully.";
            // Reset form fields
            $old = [
                'date' => date('Y-m-d'),
                'client_name' => '',
                'precall_notes' => '',
                'postcall_notes' => ''
            ];
        }
    }
}
?>

<div class="container">
    <h1>Add New Call</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($old['date']) ?>">
        </div>

        <div class="mb-3">
            <label>Client Name</label>
            <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($old['client_name']) ?>">
        </div>

        <div class="mb-3">
            <label>Pre-call Notes</label>
            <textarea name="precall_notes" class="form-control"><?= htmlspecialchars($old['precall_notes']) ?></textarea>
        </div>

        <div class="mb-3">
            <label>Post-call Notes</label>
            <textarea name="postcall_notes" class="form-control"><?= htmlspecialchars($old['postcall_notes']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Log Call</button>
    </form>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>