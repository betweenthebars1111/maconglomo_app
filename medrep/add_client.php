<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3); // Medrep only

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
include __DIR__ . '/../inc/header.php';

$errors = [];
$success = '';
$old = [
    'date' => date('Y-m-d'),
    'client_name' => '',
    'hospital_clinic' => '',
    'products_covered' => '',
];

// Upload directory
$uploadDir = __DIR__ . '/../uploads/medrep_logs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $date             = trim($_POST['date'] ?? '');
        $client_name      = trim($_POST['client_name'] ?? '');
        $hospital_clinic  = trim($_POST['hospital_clinic'] ?? '');
        $products_covered = trim($_POST['products_covered'] ?? '');
        $image_path       = null;

        $old = [
            'date' => $date,
            'client_name' => $client_name,
            'hospital_clinic' => $hospital_clinic,
            'products_covered' => $products_covered,
        ];

        // Validation
        if ($date === '')             $errors[] = 'Date is required.';
        if ($client_name === '')      $errors[] = 'Client name is required.';
        if ($hospital_clinic === '')  $errors[] = 'Hospital/Clinic is required.';
        if ($products_covered === '') $errors[] = 'Products covered are required.';

        // File upload validation
        if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Proof image is required.';
        } else {
            $fileTmp  = $_FILES['proof_image']['tmp_name'];
            $fileSize = $_FILES['proof_image']['size'];
            $fileType = mime_content_type($fileTmp);

            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!array_key_exists($fileType, $allowedTypes)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, and WebP allowed.';
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $errors[] = 'File is too large. Max 5MB.';
            }

            if (!$errors) {
                $newName = bin2hex(random_bytes(8)) . '.' . $allowedTypes[$fileType];
                $destPath = $uploadDir . $newName;

                if (move_uploaded_file($fileTmp, $destPath)) {
                    $image_path = 'uploads/medrep_logs/' . $newName;
                } else {
                    $errors[] = 'Failed to save uploaded image.';
                }
            }
        }

        // Save if no errors
        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO client_logs 
                (medrep_id, date, client_name, hospital_clinic, products_covered, proof_image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $date,
                $client_name,
                $hospital_clinic,
                $products_covered,
                $image_path
            ]);

            $success = '✅ Client log saved successfully.';
            $old = [
                'date' => date('Y-m-d'),
                'client_name' => '',
                'hospital_clinic' => '',
                'products_covered' => '',
            ];
        }
    }
}
?>

<div class="container mt-4">
    <h2>Add Client Log</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($old['date']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Client Name</label>
            <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($old['client_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Hospital/Clinic</label>
            <input type="text" name="hospital_clinic" class="form-control" value="<?= htmlspecialchars($old['hospital_clinic']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Products Covered</label>
            <textarea name="products_covered" class="form-control" rows="2" required><?= htmlspecialchars($old['products_covered']) ?></textarea>
        </div>

        <div class="mb-3">
            <label>Proof Image (JPG, PNG, WebP, max 5MB)</label>
            <input type="file" name="proof_image" id="proofImage" class="form-control"
                accept="image/*" capture="environment" required>

            <!-- Live Preview -->
            <div class="mt-2">
                <img id="previewImage" src="" alt="Preview"
                    style="max-width: 200px; display: none;" class="img-thumbnail">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Log</button>
    </form>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>

<script>
    document.getElementById('proofImage').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const preview = document.getElementById('previewImage');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>