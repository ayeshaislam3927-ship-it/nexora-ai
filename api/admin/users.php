<?php
/**
 * NEXORA ADMIN API - User Management
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDBConnection();

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $role = trim($_GET['role'] ?? '');
    $status = trim($_GET['status'] ?? '');

    $query = "SELECT id, first_name, last_name, email, role, status, email_verified, created_at, last_login FROM users WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (email LIKE :s OR first_name LIKE :s OR last_name LIKE :s)";
        $params[':s'] = "%{$search}%";
    }
    if (!empty($role)) {
        $query .= " AND role = :role";
        $params[':role'] = $role;
    }
    if (!empty($status)) {
        $query .= " AND status = :status";
        $params[':status'] = $status;
    }

    $query .= " ORDER BY id DESC LIMIT 100";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    json_response(['success' => true, 'users' => $users]);

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $targetUserId = (int)($input['user_id'] ?? 0);
    $action = trim($input['action'] ?? '');

    if (!$targetUserId || empty($action)) {
        json_response(['error' => 'Target user ID and action are required.'], 400);
    }

    $currentAdminId = $_SESSION['user_id'];

    if ($action === 'change_status') {
        $newStatus = in_array($input['status'] ?? '', ['active', 'suspended', 'inactive']) ? $input['status'] : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = :st, updated_at = NOW() WHERE id = :uid");
        $stmt->execute([':st' => $newStatus, ':uid' => $targetUserId]);

        // Log action
        $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (:aid, 'status_change', :d, :ip)");
        $log->execute([':aid' => $currentAdminId, ':d' => "Changed user #{$targetUserId} status to {$newStatus}", ':ip' => get_client_ip()]);

        json_response(['success' => true, 'message' => 'User status updated to ' . $newStatus]);

    } elseif ($action === 'change_role') {
        $newRole = in_array($input['role'] ?? '', ['user', 'admin']) ? $input['role'] : 'user';
        $stmt = $pdo->prepare("UPDATE users SET role = :r, updated_at = NOW() WHERE id = :uid");
        $stmt->execute([':r' => $newRole, ':uid' => $targetUserId]);

        $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (:aid, 'role_change', :d, :ip)");
        $log->execute([':aid' => $currentAdminId, ':d' => "Changed user #{$targetUserId} role to {$newRole}", ':ip' => get_client_ip()]);

        json_response(['success' => true, 'message' => 'User role updated to ' . $newRole]);

    } elseif ($action === 'delete') {
        // Prevent deleting self
        if ($targetUserId === $currentAdminId) {
            json_response(['error' => 'You cannot delete your own admin account.'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $targetUserId]);

        $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (:aid, 'delete_user', :d, :ip)");
        $log->execute([':aid' => $currentAdminId, ':d' => "Deleted user #{$targetUserId}", ':ip' => get_client_ip()]);

        json_response(['success' => true, 'message' => 'User account permanently deleted.']);
    } else {
        json_response(['error' => 'Invalid action.'], 400);
    }
}
