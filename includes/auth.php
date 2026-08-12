<?php
/**
 * NEXORA - Authentication & Authorization Layer
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Extract Authorization Header from incoming HTTP request
 */
function get_authorization_header(): ?string {
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['Authorization'])) {
        return trim($_SERVER['Authorization']);
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $headers = array_combine(array_map('ucwords', array_keys($headers)), array_values($headers));
        if (isset($headers['Authorization'])) {
            return trim($headers['Authorization']);
        }
    }
    return null;
}

function is_logged_in(): bool {
    if (!empty($_SESSION['user_id'])) {
        return true;
    }

    // Check Authorization Bearer Header
    $authHeader = get_authorization_header();
    if ($authHeader && preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
        $token = $matches[1];
        if (!empty($token)) {
            if (session_id() === $token && !empty($_SESSION['user_id'])) {
                return true;
            }
            if (preg_match('/^[a-zA-Z0-9,-]{1,128}$/', $token)) {
                if (session_status() === PHP_SESSION_ACTIVE && session_id() !== $token) {
                    session_write_close();
                }
                session_id($token);
                @session_start();
                if (!empty($_SESSION['user_id'])) {
                    return true;
                }
            }
        }
    }

    return false;
}

function get_logged_in_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, status, profile_image, created_at FROM users WHERE id = :id AND status = 'active' LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (Exception $e) {
        return null;
    }
}

function is_admin(): bool {
    $user = get_logged_in_user();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/') ||
                 (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
                 (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isApi) {
            $authHeader = get_authorization_header();
            if (empty($authHeader)) {
                if (!headers_sent()) http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Unauthorized: Missing Authorization header'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (!headers_sent()) http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized: Invalid Authorization token', 'redirect' => '/login'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header("Location: /login");
        exit;
    }
}

/**
 * Strict API Route Authorization
 */
function require_api_auth(): void {
    $authHeader = get_authorization_header();

    if (empty($authHeader) && empty($_SESSION['user_id'])) {
        if (!headers_sent()) http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized: Missing Authorization header'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!is_logged_in()) {
        if (!headers_sent()) http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized: Invalid Authorization token'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Strict Server-Side Admin Authorization
 * Returns 404 or redirects to /chat if unauthorized, hiding admin routes completely.
 */
function require_admin(): void {
    if (!is_admin()) {
        if (!headers_sent()) {
            http_response_code(404);
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['error' => '404 Not Found']);
            exit;
        }
        header("Location: /chat");
        exit;
    }
}

