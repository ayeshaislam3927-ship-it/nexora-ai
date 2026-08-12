<?php
/**
 * NEXORA API - Login Endpoint
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!check_rate_limit('login_' . get_client_ip(), 10, 300)) {
    json_response(['error' => 'Too many failed login attempts. Please try again later.'], 429);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(['error' => 'Please enter both email and password.'], 400);
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        json_response(['error' => 'Invalid email or password.'], 401);
    }

    if ($user['status'] === 'suspended') {
        json_response(['error' => 'Your account has been suspended. Please contact support.'], 403);
    }

    // Update last login timestamp
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);

    // Set Session
    regenerate_session_id();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    $redirectUrl = ($user['role'] === 'admin') ? '/admin/dashboard' : '/chat';

    $authToken = session_id();

    json_response([
        'success' => true,
        'message' => 'Login successful!',
        'token' => $authToken,
        'token_type' => 'Bearer',
        'role' => $user['role'],
        'redirect' => $redirectUrl
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Server error during login.'], 500);
}
