<?php
/**
 * NEXORA - Mail and OTP Delivery Utility
 */

require_once __DIR__ . '/config.php';

function sendEmailOTP(string $toEmail, string $otpCode, string $purpose = 'registration'): bool {
    $subject = "Your NEXORA Verification Code: {$otpCode}";
    $message = "Hello,\n\nYour NEXORA verification code is: {$otpCode}\n\nThis code will expire in 10 minutes.\nIf you did not request this code, please ignore this email.\n\nBest regards,\nNEXORA AI Team";

    // Always log OTP locally for development/testing visibility
    $logFile = STORAGE_PATH . '/logs/otp.log';
    $logEntry = "[" . date('Y-m-d H:i:s') . "] OTP for {$toEmail} ({$purpose}): {$otpCode}\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Attempt PHP mail or SMTP if configured
    if (defined('SMTP_HOST') && SMTP_HOST !== 'smtp.example.com') {
        $headers = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . ">\r\n" .
                   'Reply-To: ' . SMTP_FROM_EMAIL . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        return @mail($toEmail, $subject, $message, $headers);
    }

    return true;
}
