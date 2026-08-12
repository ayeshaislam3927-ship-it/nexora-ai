<?php
/**
 * NEXORA - User Settings Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = get_logged_in_user();

$pageTitle = 'Settings — NEXORA';
$extraCss = ['/assets/css/auth.css'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card max-w-lg">
        <div class="auth-header">
            <h1 class="auth-title">User Account Settings</h1>
            <p class="auth-subtitle">Manage preferences and profile details</p>
        </div>

        <div class="user-profile-summary mb-4">
            <div class="profile-avatar-lg">
                <?= strtoupper(substr($user['first_name'] ?? $user['email'], 0, 1)) ?>
            </div>
            <div>
                <h3><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h3>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge badge-primary"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
            </div>
        </div>

        <form class="auth-form" onsubmit="alert('Settings saved successfully!'); return false;">
            <div class="form-group">
                <label>Preferred Color Theme</label>
                <select id="userThemeSetting" class="form-control" onchange="localStorage.setItem('nexora_theme', this.value); document.documentElement.setAttribute('data-theme', this.value);">
                    <option value="dark">Dark Theme (Default)</option>
                    <option value="light">Light Theme</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Save Preferences</button>
            <a href="/assistant" class="btn btn-outline btn-block mt-2">Back to Chat</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
