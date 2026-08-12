<?php
/**
 * NEXORA ADMIN API - Dashboard Metrics
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

try {
    $pdo = getDBConnection();

    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $todayRegs = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $totalChats = (int)$pdo->query("SELECT COUNT(*) FROM chats")->fetchColumn();
    $totalMessages = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $verifiedUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn();

    // Recent registrations
    $recentUsers = $pdo->query("SELECT id, first_name, last_name, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 5")->fetchAll();

    json_response([
        'success' => true,
        'metrics' => [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'today_registrations' => $todayRegs,
            'verified_users' => $verifiedUsers,
            'total_chats' => $totalChats,
            'total_messages' => $totalMessages
        ],
        'recent_users' => $recentUsers
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error retrieving admin metrics: ' . $e->getMessage()], 500);
}
