<?php
/**
 * NEXORA ADMIN - Audit Logs View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Audit Logs — NEXORA Admin';
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
            <a href="/admin/logs" class="admin-nav-item active">Logs</a>
            <a href="/admin/reports" class="admin-nav-item">Reports</a>
            <hr class="admin-divider">
            <a href="/chat" class="admin-nav-item text-primary">Back to App</a>
            <a href="/logout" class="admin-nav-item text-danger">Sign Out</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1 class="admin-title">Security & Audit Logs</h1>
                <p class="admin-subtitle">Track admin actions and system security events.</p>
            </div>
        </header>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Admin Email</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="adminLogsTableBody">
                        <tr><td colspan="6" class="text-center">Loading audit events...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
