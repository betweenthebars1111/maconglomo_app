<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(3);

$file = $_GET['file'] ?? '';
$uploadDir = __DIR__ . '/../uploads/medrep_logs/';

// Make sure filename is safe (no directory traversal)
$basename = basename($file);
$filePath = $uploadDir . $basename;

if (!file_exists($filePath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// Detect MIME type (so browser knows it's an image)
$mime = mime_content_type($filePath);

// Only allow image types
$allowed = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime, $allowed)) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// Set correct headers and output the image directly
header("Content-Type: $mime");
readfile($filePath);
exit;
