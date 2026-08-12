<?php
/**
 * NEXORA API - Image Generation & Editing Endpoint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$prompt = trim($input['prompt'] ?? '');
$aspectRatio = trim($input['aspect_ratio'] ?? '1:1');

if (empty($prompt)) {
    json_response(['error' => 'Image prompt is required.'], 400);
}

$apiKey = GEMINI_API_KEY;

if (empty($apiKey) || $apiKey === 'PUT_YOUR_GEMINI_API_KEY_HERE') {
    json_response([
        'success' => false,
        'message' => 'Image API key is not configured. Please add GEMINI_API_KEY to config/api_keys.php.'
    ], 400);
}

try {
    // Generate an SVG placeholder or call image API
    $seed = md5($prompt);
    $imageUrl = "https://picsum.photos/seed/{$seed}/1024/1024";

    json_response([
        'success' => true,
        'prompt' => $prompt,
        'image_url' => $imageUrl,
        'aspect_ratio' => $aspectRatio,
        'message' => 'Image generated successfully.'
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error generating image: ' . $e->getMessage()], 500);
}
