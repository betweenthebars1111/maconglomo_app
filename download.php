<?php
require_once __DIR__ . '/inc/auth.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$baseDir = __DIR__ . '/uploads/medrep_logs/';
$file = basename($_GET['file'] ?? ''); // prevent directory traversal
$path = realpath($baseDir . $file);

// Validate path
if (!$file || !$path || strpos($path, realpath($baseDir)) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('File not found');
}

// Force download or inline display
header('Content-Type: application/octet-stream');
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
