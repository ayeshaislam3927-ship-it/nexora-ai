<?php
/**
 * NEXORA ADMIN - Analytics & Usage Reports View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Usage Reports — NEXORA Admin';
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
            <a href="/admin/settings" class="admin-nav-item">Settings</a>
            <a href="/admin/logs" class="admin-nav-item">Logs</a>
            <a href="/admin/reports" class="admin-nav-item active">Reports</a>
            <hr class="admin-divider">
            <a href="/chat" class="admin-nav-item text-primary">Back to App</a>
            <a href="/logout" class="admin-nav-item text-danger">Sign Out</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1 class="admin-title">Analytics & Usage Reports</h1>
                <p class="admin-subtitle">AI model usage metrics and registration trends.</p>
            </div>
        </header>

        <div class="grid grid-cols-2 gap-4">
            <section class="admin-card">
                <h3>AI Model Usage Distribution</h3>
                <div id="modelUsageReport" class="mt-4">Loading stats...</div>
            </section>

            <section class="admin-card">
                <h3>Daily User Signups</h3>
                <div id="dailyRegsReport" class="mt-4">Loading stats...</div>
            </section>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
