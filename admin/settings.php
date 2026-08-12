<?php
/**
 * NEXORA ADMIN - System Settings View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Platform Settings — NEXORA Admin';
$extraCss = ['/assets/css/admin.css'];
$extraJs = ['/assets/js/admin.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="/admin/dashboard" class="brand-link">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="admin-logo">
                <span class="brand-name">NEXORA Admin</span>
            </a>
        </div>
        <nav class="admin-nav">
            <a href="/admin/dashboard" class="admin-nav-item">Overview</a>
            <a href="/admin/users" class="admin-nav-item">Users</a>
            <a href="/admin/chats" class="admin-nav-item">Chats</a>
            <a href="/admin/settings" class="admin-nav-item active">Settings</a>
            <a href="/admin/logs" class="admin-nav-item">Logs</a>
            <a href="/admin/reports" class="admin-nav-item">Reports</a>
            <hr class="admin-divider">
            <a href="/chat" class="admin-nav-item text-primary">Back to App</a>
            <a href="/logout" class="admin-nav-item text-danger">Sign Out</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1 class="admin-title">System Settings</h1>
                <p class="admin-subtitle">Configure application options, models, and platform controls.</p>
            </div>
        </header>

        <section class="admin-card max-w-2xl">
            <form id="adminSettingsForm" class="settings-form">
                <div class="form-group">
                    <label>Platform Name</label>
                    <input type="text" name="platform_name" id="setPlatformName" class="form-control" value="NEXORA">
                </div>

                <div class="form-group">
                    <label>Default AI Model</label>
                    <select name="default_model" id="setDefaultModel" class="form-control">
                        <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                        <option value="gemini-2.5-pro">Gemini 2.5 Pro</option>
                        <option value="gpt-4o">GPT-4o</option>
                        <option value="claude-3-5-sonnet">Claude 3.5 Sonnet</option>
                    </select>
                </div>

                <div class="form-group flex-checkbox">
                    <label><input type="checkbox" name="registration_enabled" id="setRegEnabled" value="1"> Allow Public User Signups</label>
                </div>

                <div class="form-group flex-checkbox">
                    <label><input type="checkbox" name="guest_chat_enabled" id="setGuestEnabled" value="1"> Enable Temporary Guest Chat</label>
                </div>

                <div class="form-group flex-checkbox">
                    <label><input type="checkbox" name="maintenance_mode" id="setMaintenance" value="1"> Maintenance Mode</label>
                </div>

                <div class="form-group">
                    <label>Max Upload Size (Bytes)</label>
                    <input type="number" name="max_upload_size" id="setMaxUpload" class="form-control" value="10485760">
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
                <div id="settingsMsg" class="mt-2 text-sm"></div>
            </form>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
