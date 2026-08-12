<?php
/**
 * NEXORA - Global Application Configuration
 */

if (!defined('NEXORA_INIT')) {
    define('NEXORA_INIT', true);
}

// App Information
define('APP_NAME', 'NEXORA');
define('APP_VERSION', '1.0.0');
define('APP_DESC', 'Next-Generation Full-Stack AI Platform');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOADS_PATH', ROOT_PATH . '/assets/uploads');

// Protocol and Host detection
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:3000';
define('SITE_URL', $protocol . '://' . $host);

// Session Config
define('SESSION_LIFETIME', 86400 * 7); // 7 days

// Default AI Model
define('DEFAULT_AI_MODEL', 'gemini-2.5-flash');

// Allowed Upload Configs
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'txt', 'csv', 'json', 'md', 'doc', 'docx']);

// Timezone
date_default_timezone_set('UTC');

// Load API keys
require_once CONFIG_PATH . '/api_keys.php';
