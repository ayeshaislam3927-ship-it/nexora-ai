<?php
/**
 * NEXORA API - Secure File Upload Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!check_rate_limit('upload_' . get_client_ip(), 10, 60)) {
    json_response(['error' => 'Upload rate limit exceeded.'], 429);
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'No file uploaded or upload error occurred.'], 400);
}

$file = $_FILES['file'];

// Validate file size
if ($file['size'] > MAX_UPLOAD_SIZE) {
    json_response(['error' => 'File exceeds maximum allowed size of 10MB.'], 400);
}

// Extension check
$originalName = basename($file['name']);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$bannedExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'pl', 'py', 'sh', 'cgi', 'js', 'html', 'htm', 'htaccess'];
if (in_array($ext, $bannedExts) || !in_array($ext, ALLOWED_EXTENSIONS)) {
    json_response(['error' => 'Invalid file extension. Executable files are prohibited.'], 400);
}

// Ensure uploads directory exists
$uploadDir = ROOT_PATH . '/assets/uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Generate random safe filename
$randomFilename = bin2hex(random_bytes(16)) . '.' . $ext;
$targetPath = $uploadDir . '/' . $randomFilename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $fileUrl = '/assets/uploads/' . $randomFilename;
    json_response([
        'success' => true,
        'original_name' => sanitize($originalName),
        'file_name' => $randomFilename,
        'file_url' => $fileUrl,
        'extension' => $ext,
        'size' => $file['size']
    ]);
} else {
    json_response(['error' => 'Failed to save uploaded file.'], 500);
}
