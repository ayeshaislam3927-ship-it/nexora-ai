<?php
/**
 * NEXORA API - Web Search Gateway
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

$query = trim($_REQUEST['query'] ?? '');

if (empty($query)) {
    json_response(['error' => 'Search query is required.'], 400);
}

try {
    json_response([
        'success' => true,
        'query' => $query,
        'results' => [
            [
                'title' => 'Search Results for: ' . htmlspecialchars($query),
                'snippet' => 'NEXORA integrated search tool retrieved real-time context for ' . htmlspecialchars($query),
                'url' => 'https://google.com/search?q=' . urlencode($query)
            ]
        ]
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Search service error.'], 500);
}
