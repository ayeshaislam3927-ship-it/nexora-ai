<?php
/**
 * NEXORA ADMIN API - Security & Action Audit Logs
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT l.id, l.admin_id, l.action, l.details, l.ip_address, l.created_at,
               u.email as admin_email, u.first_name, u.last_name
        FROM admin_logs l
        LEFT JOIN users u ON l.admin_id = u.id
        ORDER BY l.id DESC
        LIMIT 100
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll();

    json_response(['success' => true, 'logs' => $logs]);

} catch (Exception $e) {
    json_response(['error' => 'Error retrieving audit logs.'], 500);
}
