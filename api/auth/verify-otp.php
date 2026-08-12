<?php
/**
 * NEXORA API - Verify Email OTP
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = strtolower(trim($input['email'] ?? ''));
$otpCode = trim($input['otp_code'] ?? '');

if (empty($email) || empty($otpCode)) {
    json_response(['error' => 'Email and verification code are required.'], 400);
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id FROM email_otps WHERE email = :email AND otp_code = :code AND verified = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute([':email' => $email, ':code' => $otpCode]);
    $record = $stmt->fetch();

    if (!$record) {
        json_response(['error' => 'Invalid or expired verification code.'], 400);
    }

    // Mark verified
    $updateStmt = $pdo->prepare("UPDATE email_otps SET verified = 1 WHERE id = :id");
    $updateStmt->execute([':id' => $record['id']]);

    $_SESSION['verified_registration_email'] = $email;

    json_response([
        'success' => true,
        'message' => 'Email verified successfully.',
        'email' => $email
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Server error verifying code.'], 500);
}
