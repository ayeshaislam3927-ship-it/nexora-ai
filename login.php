<?php
/**
 * NEXORA - Authentication / Sign In Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . (is_admin() ? '/admin/dashboard' : '/assistant'));
    exit;
}

$pageTitle = 'Sign In — NEXORA';
$extraCss = ['/assets/css/auth.css'];
$extraJs = ['/assets/js/auth.js'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo-link">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="auth-logo">
            </a>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to your NEXORA account</p>
        </div>

        <div id="authAlert" class="alert alert-danger hidden"></div>

        <!-- Google OAuth Button Container -->
        <div class="google-auth-wrapper">
            <div id="g_id_onload"
                data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
                data-callback="handleGoogleSignIn"
                data-auto_prompt="false">
            </div>
            <div class="g_id_signin" data-type="standard" data-size="large" data-theme="dark" data-text="continue_with" data-shape="rectangular" data-logo_alignment="left" data-width="100%"></div>
        </div>

        <div class="auth-divider">
            <span>OR</span>
        </div>

        <form id="loginForm" class="auth-form">
            <?= csrf_input_field() ?>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <div class="flex-between">
                    <label for="password">Password</label>
                </div>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" id="btnLogin" class="btn btn-primary btn-block btn-lg">
                <span>Continue</span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="/signup" class="auth-link">Sign up</a></p>
        </div>
    </div>
</div>

<!-- Google Identity Services SDK -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
