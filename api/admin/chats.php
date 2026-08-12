<?php
/**
 * NEXORA ADMIN API - Platform Chats Metadata View
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
        SELECT c.id, c.user_id, c.title, c.model, c.created_at, c.updated_at,
               u.email as user_email, u.first_name, u.last_name,
               (SELECT COUNT(*) FROM messages m WHERE m.chat_id = c.id) as message_count
        FROM chats c
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY c.updated_at DESC
        LIMIT 100
    ");
    $stmt->execute();
    $chats = $stmt->fetchAll();

    json_response(['success' => true, 'chats' => $chats]);

} catch (Exception $e) {
    json_response(['error' => 'Error retrieving chats metadata.'], 500);
}
