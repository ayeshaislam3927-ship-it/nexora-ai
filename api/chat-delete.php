<?php
/**
 * NEXORA API - Delete or Rename Chat Session Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!is_logged_in()) {
    json_response(['error' => 'Authentication required'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$chatId = (int)($input['chat_id'] ?? 0);
$action = trim($input['action'] ?? 'delete');
$newTitle = sanitize($input['title'] ?? '');

if (!$chatId) {
    json_response(['error' => 'Valid chat ID required.'], 400);
}

$userId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = :cid AND user_id = :uid LIMIT 1");
    $stmt->execute([':cid' => $chatId, ':uid' => $userId]);
    if (!$stmt->fetch()) {
        json_response(['error' => 'Chat not found or access denied.'], 404);
    }

    if ($action === 'rename') {
        if (empty($newTitle)) {
            json_response(['error' => 'New title cannot be empty.'], 400);
        }
        $uStmt = $pdo->prepare("UPDATE chats SET title = :t, updated_at = NOW() WHERE id = :cid AND user_id = :uid");
        $uStmt->execute([':t' => $newTitle, ':cid' => $chatId, ':uid' => $userId]);

        json_response(['success' => true, 'message' => 'Chat title updated successfully.']);
    } else {
        // Delete chat and cascade messages
        $dStmt = $pdo->prepare("DELETE FROM chats WHERE id = :cid AND user_id = :uid");
        $dStmt->execute([':cid' => $chatId, ':uid' => $userId]);

        json_response(['success' => true, 'message' => 'Chat deleted successfully.']);
    }

} catch (Exception $e) {
    json_response(['error' => 'Error modifying chat session.'], 500);
}
