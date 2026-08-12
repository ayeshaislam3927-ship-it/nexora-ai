<?php
/**
 * NEXORA ADMIN - Platform Chats View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Chats Metadata — NEXORA Admin';
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
            <a href="/admin/chats" class="admin-nav-item active">Chats</a>
            <a href="/admin/settings" class="admin-nav-item">Settings</a>
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
                <h1 class="admin-title">Chat Sessions Metadata</h1>
                <p class="admin-subtitle">Monitor platform conversation volume and AI model distribution.</p>
            </div>
        </header>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Chat ID</th>
                            <th>Title</th>
                            <th>User Email</th>
                            <th>AI Model</th>
                            <th>Messages</th>
                            <th>Last Activity</th>
                        </tr>
                    </thead>
                    <tbody id="adminChatsTableBody">
                        <tr><td colspan="6" class="text-center">Loading chat records...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
