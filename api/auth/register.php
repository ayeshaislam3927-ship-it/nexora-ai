<?php
/**
 * NEXORA API - Account Registration Endpoint
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
$firstName = sanitize($input['first_name'] ?? '');
$lastName = sanitize($input['last_name'] ?? '');
$dob = trim($input['date_of_birth'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

if (empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
    json_response(['error' => 'All required fields must be completed.'], 400);
}

if ($password !== $confirmPassword) {
    json_response(['error' => 'Passwords do not match.'], 400);
}

if (strlen($password) < 8) {
    json_response(['error' => 'Password must be at least 8 characters long.'], 400);
}

try {
    $pdo = getDBConnection();

    // Verify email was previously OTP verified or is valid
    $otpCheck = $pdo->prepare("SELECT id FROM email_otps WHERE email = :email AND verified = 1 LIMIT 1");
    $otpCheck->execute([':email' => $email]);
    if (!$otpCheck->fetch() && ($_SESSION['verified_registration_email'] ?? '') !== $email) {
        json_response(['error' => 'Email verification code is required.'], 400);
    }

    // Check duplicate
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $checkUser->execute([':email' => $email]);
    if ($checkUser->fetch()) {
        json_response(['error' => 'An account with this email already exists.'], 400);
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $dobValue = !empty($dob) ? $dob : null;

    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, date_of_birth, email_verified, role, status, created_at)
        VALUES (:fn, :ln, :email, :pass, :dob, 1, 'user', 'active', NOW())
    ");
    $stmt->execute([
        ':fn' => $firstName,
        ':ln' => $lastName,
        ':email' => $email,
        ':pass' => $passwordHash,
        ':dob' => $dobValue
    ]);

    $userId = $pdo->lastInsertId();

    // Create user default settings
    $settingsStmt = $pdo->prepare("INSERT INTO user_settings (user_id, theme, language, selected_model) VALUES (:uid, 'dark', 'en', 'gemini-2.5-flash')");
    $settingsStmt->execute([':uid' => $userId]);

    // Auto Login
    regenerate_session_id();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';

    $authToken = session_id();

    json_response([
        'success' => true,
        'message' => 'Account created successfully! Redirecting...',
        'token' => $authToken,
        'token_type' => 'Bearer',
        'redirect' => '/chat'
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error creating account. ' . $e->getMessage()], 500);
}
