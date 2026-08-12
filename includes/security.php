<?php
/**
 * NEXORA - Security Headers & Rate Limiting
 */

function set_security_headers(): void {
    if (headers_sent()) return;

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

function get_client_ip(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Basic in-memory / session rate limiter
 */
function check_rate_limit(string $key, int $maxRequests = 30, int $timeWindowSeconds = 60): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $now = time();
    $rateKey = "rate_limit_" . md5($key);

    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = [
            'count' => 1,
            'start_time' => $now
        ];
        return true;
    }

    $data = $_SESSION[$rateKey];
    if ($now - $data['start_time'] > $timeWindowSeconds) {
        $_SESSION[$rateKey] = [
            'count' => 1,
            'start_time' => $now
        ];
        return true;
    }

    if ($data['count'] >= $maxRequests) {
        return false;
    }

    $_SESSION[$rateKey]['count']++;
    return true;
}

set_security_headers();
