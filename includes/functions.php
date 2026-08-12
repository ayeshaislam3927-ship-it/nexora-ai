<?php
/**
 * NEXORA - Utility Functions
 */

function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $statusCode = 200): void {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function generate_otp(int $length = 6): string {
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[random_int(0, 9)];
    }
    return $otp;
}

function format_datetime(?string $datetime): string {
    if (!$datetime) return 'N/A';
    $ts = strtotime($datetime);
    if (!$ts) return 'N/A';
    return date('M j, Y - g:i A', $ts);
}

function format_time_ago(?string $datetime): string {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    if (!$ts) return '';
    $diff = time() - $ts;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $ts);
}

/**
 * Basic Markdown to HTML converter
 */
function parse_markdown(string $text): string {
    // Sanitize input
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Code blocks with copy button placeholder
    $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n(.*?)```/s', function($matches) {
        $lang = $matches[1] ?: 'text';
        $code = trim($matches[2]);
        return '<div class="code-block-container">' .
               '<div class="code-header"><span class="code-lang">' . htmlspecialchars($lang) . '</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>' .
               '<pre><code class="language-' . htmlspecialchars($lang) . '">' . $code . '</code></pre>' .
               '</div>';
    }, $text);

    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $text);

    // Bold & Italics
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);

    // Headings
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);

    // Convert newlines to breaks
    $text = nl2br($text);

    return $text;
}
