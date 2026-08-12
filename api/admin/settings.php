<?php
/**
 * NEXORA ADMIN API - System Settings
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $raw = $stmt->fetchAll();
    $settings = [];
    foreach ($raw as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }

    json_response(['success' => true, 'settings' => $settings]);

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $validKeys = [
        'platform_name',
        'default_model',
        'registration_enabled',
        'guest_chat_enabled',
        'maintenance_mode',
        'max_upload_size'
    ];

    foreach ($validKeys as $key) {
        if (isset($input[$key])) {
            $val = sanitize((string)$input[$key]);
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v, updated_at = NOW()");
            // SQLite compatibility check
            try {
                $stmt->execute([':k' => $key, ':v' => $val]);
            } catch (Exception $ex) {
                $up = $pdo->prepare("UPDATE system_settings SET setting_value = :v, updated_at = NOW() WHERE setting_key = :k");
                $up->execute([':v' => $val, ':k' => $key]);
            }
        }
    }

    $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (:aid, 'update_settings', 'Updated system settings', :ip)");
    $log->execute([':aid' => $_SESSION['user_id'], ':ip' => get_client_ip()]);

    json_response(['success' => true, 'message' => 'System settings saved successfully.']);
}
