<?php
/**
 * NEXORA API - Central AI Chat Gateway
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!check_rate_limit('chat_' . get_client_ip(), 30, 60)) {
    json_response(['error' => 'Rate limit exceeded. Please slow down your requests.'], 429);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$prompt = trim($input['prompt'] ?? '');
$chatId = !empty($input['chat_id']) ? (int)$input['chat_id'] : null;
$selectedModel = trim($input['model'] ?? DEFAULT_AI_MODEL);
$attachmentUrl = trim($input['attachment_url'] ?? '');

if (empty($prompt)) {
    json_response(['error' => 'Message prompt cannot be empty.'], 400);
}

$isGuest = !is_logged_in();
$userId = $isGuest ? null : $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Prepare message contents for Gemini API
    $apiKey = GEMINI_API_KEY;
    if (empty($apiKey) || $apiKey === 'PUT_YOUR_GEMINI_API_KEY_HERE') {
        // Fallback response if API key is not configured
        $assistantText = "Welcome to **NEXORA**! The platform is working properly.\n\nTo enable live AI generation responses, please update `config/api_keys.php` with your valid `GEMINI_API_KEY`. You can set it in environment variables or directly inside `config/api_keys.php`.\n\n*Your prompt received:* \"{$prompt}\"";
    } else {
        // Call Gemini 2.5 API
        $geminiEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($selectedModel) . ":generateContent?key=" . urlencode($apiKey);

        $contents = [];

        // Add System instruction
        $systemInstruction = [
            "parts" => [
                ["text" => "You are NEXORA, a highly capable, articulate, intelligent, and helpful full-stack AI assistant created by NEXORA AI. Provide insightful, clear, precise answers with well-formatted markdown, syntax-highlighted code blocks, tables, and structured lists where appropriate."]
            ]
        ];

        // Fetch prior message context
        $priorHistory = [];
        if (!$isGuest && $chatId) {
            $histStmt = $pdo->prepare("SELECT role, content FROM messages WHERE chat_id = :cid ORDER BY id ASC LIMIT 20");
            $histStmt->execute([':cid' => $chatId]);
            $rows = $histStmt->fetchAll();
            foreach ($rows as $r) {
                $role = ($r['role'] === 'assistant') ? 'model' : 'user';
                $priorHistory[] = [
                    'role' => $role,
                    'parts' => [['text' => $r['content']]]
                ];
            }
        } elseif ($isGuest && isset($_SESSION['guest_chat'])) {
            foreach (array_slice($_SESSION['guest_chat'], -10) as $msg) {
                $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
                $priorHistory[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        // Add new user prompt
        $userParts = [["text" => $prompt]];

        // Handle attachment image or text if provided
        if (!empty($attachmentUrl)) {
            $userParts[] = ["text" => "\n\n[Attached file reference: " . basename($attachmentUrl) . "]"];
        }

        $priorHistory[] = [
            'role' => 'user',
            'parts' => $userParts
        ];

        $payload = [
            'system_instruction' => $systemInstruction,
            'contents' => $priorHistory,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4096
            ]
        ];

        $ch = curl_init($geminiEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30
        ]);

        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            $respDec = json_decode($responseRaw, true);
            $errMsg = $respDec['error']['message'] ?? $curlErr ?? "API request failed with HTTP {$httpCode}";
            $assistantText = "I encountered an error communicating with the AI model: {$errMsg}. Please check your API key and connection.";
        } else {
            $respDec = json_decode($responseRaw, true);
            $assistantText = $respDec['candidates'][0]['content']['parts'][0]['text'] ?? "I received an empty response. Please try asking again.";
        }
    }

    // Process persistence
    if (!$isGuest) {
        // Logged-in user: save to MySQL
        if (!$chatId) {
            // Generate title from prompt
            $title = mb_substr($prompt, 0, 40) . (mb_strlen($prompt) > 40 ? '...' : '');
            $cStmt = $pdo->prepare("INSERT INTO chats (user_id, title, model, created_at, updated_at) VALUES (:uid, :title, :model, NOW(), NOW())");
            $cStmt->execute([':uid' => $userId, ':title' => $title, ':model' => $selectedModel]);
            $chatId = (int)$pdo->lastInsertId();
        } else {
            // Verify ownership
            $vStmt = $pdo->prepare("SELECT id FROM chats WHERE id = :cid AND user_id = :uid LIMIT 1");
            $vStmt->execute([':cid' => $chatId, ':uid' => $userId]);
            if (!$vStmt->fetch()) {
                json_response(['error' => 'Unauthorized chat session'], 403);
            }

            // Update timestamp
            $uStmt = $pdo->prepare("UPDATE chats SET updated_at = NOW() WHERE id = :cid");
            $uStmt->execute([':cid' => $chatId]);
        }

        // Save User Message
        $m1 = $pdo->prepare("INSERT INTO messages (chat_id, role, content, model, created_at) VALUES (:cid, 'user', :content, :model, NOW())");
        $m1->execute([':cid' => $chatId, ':content' => $prompt, ':model' => $selectedModel]);

        // Save Assistant Message
        $m2 = $pdo->prepare("INSERT INTO messages (chat_id, role, content, model, created_at) VALUES (:cid, 'assistant', :content, :model, NOW())");
        $m2->execute([':cid' => $chatId, ':content' => $assistantText, ':model' => $selectedModel]);

        // Get updated chat title
        $tStmt = $pdo->prepare("SELECT title FROM chats WHERE id = :cid LIMIT 1");
        $tStmt->execute([':cid' => $chatId]);
        $chatTitle = $tStmt->fetchColumn() ?: 'New Chat';

    } else {
        // Guest user: save to session temporary storage only (NOT database)
        if (!isset($_SESSION['guest_chat'])) {
            $_SESSION['guest_chat'] = [];
        }
        $_SESSION['guest_chat'][] = ['role' => 'user', 'content' => $prompt, 'time' => date('H:i')];
        $_SESSION['guest_chat'][] = ['role' => 'assistant', 'content' => $assistantText, 'time' => date('H:i')];

        $chatId = 0;
        $chatTitle = 'Temporary Guest Conversation';
    }

    json_response([
        'success' => true,
        'chat_id' => $chatId,
        'chat_title' => $chatTitle,
        'prompt' => $prompt,
        'response' => $assistantText,
        'formatted_response' => parse_markdown($assistantText),
        'model' => $selectedModel,
        'is_guest' => $isGuest,
        'created_at' => date('M j, Y - g:i A')
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Server error processing request: ' . $e->getMessage()], 500);
}
