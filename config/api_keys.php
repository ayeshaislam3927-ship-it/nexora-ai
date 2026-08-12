<?php
/**
 * NEXORA - Central API Keys & Secrets Configuration
 * 
 * IMPORTANT SECURITY WARNING:
 * Never expose these keys to frontend JavaScript or HTML.
 * Never commit real secrets to public repositories.
 */

// Gemini API Key (Reads runtime environment variable if available)
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'PUT_YOUR_GEMINI_API_KEY_HERE');

// OpenAI API Key
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: 'PUT_YOUR_OPENAI_API_KEY_HERE');

// Google OAuth Credentials
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'PUT_YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'PUT_YOUR_GOOGLE_CLIENT_SECRET_HERE');

// SMTP Email Credentials
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.example.com');
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'your-email@example.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'PUT_YOUR_SMTP_PASSWORD_HERE');
define('SMTP_PORT', getenv('SMTP_PORT') ? (int)getenv('SMTP_PORT') : 587);
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'noreply@nexora.ai');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'NEXORA AI');
