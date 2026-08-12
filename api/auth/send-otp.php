<?php
/**
 * NEXORA API - Send Verification Email OTP
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!check_rate_limit('send_otp_' . get_client_ip(), 5, 300)) {
    json_response(['error' => 'Too many requests. Please wait a few minutes before requesting another code.'], 429);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = strtolower(trim($input['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Please provide a valid email address.'], 400);
}

try {
    $pdo = getDBConnection();

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        json_response(['error' => 'An account with this email already exists. Please sign in.'], 400);
    }

    $otp = generate_otp(6);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Insert OTP record
    $insertStmt = $pdo->prepare("INSERT INTO email_otps (email, otp_code, purpose, expires_at) VALUES (:email, :otp, 'registration', :expires)");
    $insertStmt->execute([
        ':email' => $email,
        ':otp' => $otp,
        ':expires' => $expiresAt
    ]);

    // Send Email
    sendEmailOTP($email, $otp, 'registration');

    json_response([
        'success' => true,
        'message' => 'Verification code sent to your email address.',
        'email' => $email
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Server error sending verification code. ' . $e->getMessage()], 500);
}
