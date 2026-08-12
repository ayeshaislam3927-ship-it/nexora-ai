<?php
/**
 * NEXORA ADMIN API - Analytics & Usage Reports
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

try {
    $pdo = getDBConnection();

    // Model breakdown
    $modelStats = $pdo->query("SELECT model, COUNT(*) as total FROM messages WHERE role = 'assistant' GROUP BY model ORDER BY total DESC")->fetchAll();

    // Daily registrations last 7 days
    $dailyRegs = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7")->fetchAll();

    json_response([
        'success' => true,
        'model_usage' => $modelStats,
        'daily_registrations' => $dailyRegs
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error generating analytics report.'], 500);
}
