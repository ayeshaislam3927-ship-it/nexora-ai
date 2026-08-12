<?php
/**
 * NEXORA API - Chat History & Conversations List Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    // Return temporary session chat for guests if available
    $guestMessages = $_SESSION['guest_chat'] ?? [];
    $formattedGuestMsgs = [];
    foreach ($guestMessages as $index => $msg) {
        $formattedGuestMsgs[] = [
            'id' => $index + 1,
            'role' => $msg['role'],
            'content' => $msg['content'],
            'formatted_content' => parse_markdown($msg['content']),
            'created_at' => $msg['time'] ?? 'Just now'
        ];
    }

    json_response([
        'success' => true,
        'is_guest' => true,
        'chats' => [],
        'active_chat' => null,
        'messages' => $formattedGuestMsgs
    ]);
}

$userId = $_SESSION['user_id'];
$chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;
$search = trim($_GET['search'] ?? '');

try {
    $pdo = getDBConnection();

    // Fetch user chats list
    if (!empty($search)) {
        $chatsStmt = $pdo->prepare("
            SELECT id, title, model, created_at, updated_at 
            FROM chats 
            WHERE user_id = :uid AND title LIKE :q 
            ORDER BY updated_at DESC LIMIT 50
        ");
        $chatsStmt->execute([':uid' => $userId, ':q' => "%{$search}%"]);
    } else {
        $chatsStmt = $pdo->prepare("
            SELECT id, title, model, created_at, updated_at 
            FROM chats 
            WHERE user_id = :uid 
            ORDER BY updated_at DESC LIMIT 50
        ");
        $chatsStmt->execute([':uid' => $userId]);
    }
    $chats = $chatsStmt->fetchAll();

    $messages = [];
    $activeChat = null;

    if ($chatId) {
        // Verify chat ownership
        $chatCheck = $pdo->prepare("SELECT id, title, model, created_at FROM chats WHERE id = :cid AND user_id = :uid LIMIT 1");
        $chatCheck->execute([':cid' => $chatId, ':uid' => $userId]);
        $activeChat = $chatCheck->fetch();

        if ($activeChat) {
            $msgStmt = $pdo->prepare("SELECT id, role, content, model, created_at FROM messages WHERE chat_id = :cid ORDER BY id ASC");
            $msgStmt->execute([':cid' => $chatId]);
            $rawMsgs = $msgStmt->fetchAll();

            foreach ($rawMsgs as $m) {
                $messages[] = [
                    'id' => $m['id'],
                    'role' => $m['role'],
                    'content' => $m['content'],
                    'formatted_content' => parse_markdown($m['content']),
                    'model' => $m['model'],
                    'created_at' => format_datetime($m['created_at'])
                ];
            }
        }
    }

    json_response([
        'success' => true,
        'is_guest' => false,
        'chats' => $chats,
        'active_chat' => $activeChat,
        'messages' => $messages
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error retrieving chat history.'], 500);
}
