<?php
/**
 * NEXORA API - Google OAuth Login & Onboarding Endpoint
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
$credential = $input['credential'] ?? $input['id_token'] ?? '';
$email = strtolower(trim($input['email'] ?? ''));
$firstName = sanitize($input['first_name'] ?? 'Google');
$lastName = sanitize($input['last_name'] ?? 'User');
$googleId = sanitize($input['google_id'] ?? '');

if (empty($email)) {
    json_response(['error' => 'Google account email not received.'], 400);
}

try {
    $pdo = getDBConnection();

    // Check if user exists by email or google_id
    $stmt = $pdo->prepare("SELECT id, email, role, status FROM users WHERE email = :email OR google_id = :gid LIMIT 1");
    $stmt->execute([':email' => $email, ':gid' => $googleId]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['status'] === 'suspended') {
            json_response(['error' => 'Account suspended.'], 403);
        }

        // Update google_id and last_login
        $update = $pdo->prepare("UPDATE users SET google_id = :gid, last_login = NOW() WHERE id = :id");
        $update->execute([':gid' => $googleId, ':id' => $user['id']]);

        $userId = $user['id'];
        $role = $user['role'];
    } else {
        // Register new user via Google
        $ins = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, google_id, email_verified, role, status, created_at, last_login)
            VALUES (:fn, :ln, :email, :gid, 1, 'user', 'active', NOW(), NOW())
        ");
        $ins->execute([
            ':fn' => $firstName,
            ':ln' => $lastName,
            ':email' => $email,
            ':gid' => $googleId
        ]);

        $userId = $pdo->lastInsertId();
        $role = 'user';

        // Settings
        $sett = $pdo->prepare("INSERT INTO user_settings (user_id, theme, language, selected_model) VALUES (:uid, 'dark', 'en', 'gemini-2.5-flash')");
        $sett->execute([':uid' => $userId]);
    }

    regenerate_session_id();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $role;

    $redirectUrl = ($role === 'admin') ? '/admin/dashboard' : '/chat';

    $authToken = session_id();

    json_response([
        'success' => true,
        'message' => 'Signed in with Google successfully!',
        'token' => $authToken,
        'token_type' => 'Bearer',
        'redirect' => $redirectUrl
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Google authentication error: ' . $e->getMessage()], 500);
}
