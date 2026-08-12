<?php
/**
 * NEXORA - Router for PHP Built-in Server & Local Preview Environment
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Block access to sensitive directories
if (preg_match('#^/(config|includes|storage|database)#', $uri)) {
    http_response_code(403);
    echo "403 Forbidden - Access Denied";
    exit;
}

$filePath = __DIR__ . $uri;

// 1. Serve static assets directly if file exists
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath) && !str_ends_with($uri, '.php')) {
    return false; // Serve static file directly
}

// 2. Clean URL Mappings
$routes = [
    '/' => '/index.php',
    '/login' => '/login.php',
    '/signup' => '/register.php',
    '/register' => '/register.php',
    '/assistant' => '/assistant.php',
    '/logout' => '/logout.php',
    '/settings' => '/settings.php',
    '/admin/dashboard' => '/admin/dashboard.php',
    '/admin/users' => '/admin/users.php',
    '/admin/chats' => '/admin/chats.php',
    '/admin/settings' => '/admin/settings.php',
    '/admin/logs' => '/admin/logs.php',
    '/admin/reports' => '/admin/reports.php',
];

if (isset($routes[$uri])) {
    require __DIR__ . $routes[$uri];
    exit;
}

// 3. Fallback: check if $uri + '.php' exists
if (file_exists(__DIR__ . $uri . '.php')) {
    require __DIR__ . $uri . '.php';
    exit;
}

// 4. Fallback if request is to existing .php file
if (file_exists($filePath) && is_file($filePath) && str_ends_with($uri, '.php')) {
    require $filePath;
    exit;
}

// 404 Fallback
http_response_code(404);
require __DIR__ . '/index.php';
