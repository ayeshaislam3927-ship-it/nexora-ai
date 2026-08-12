<?php
/**
 * NEXORA ADMIN - Dashboard View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'NEXORA Admin Dashboard';
$extraCss = ['/assets/css/admin.css'];
$extraJs = ['/assets/js/admin.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="/admin/dashboard" class="brand-link">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="admin-logo">
                <span class="brand-name">NEXORA Admin</span>
            </a>
        </div>
        <nav class="admin-nav">
            <a href="/admin/dashboard" class="admin-nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Overview</span>
            </a>
            <a href="/admin/users" class="admin-nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Users</span>
            </a>
            <a href="/admin/chats" class="admin-nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Chats</span>
            </a>
            <a href="/admin/settings" class="admin-nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Settings</span>
            </a>
            <a href="/admin/logs" class="admin-nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Logs</span>
            </a>
            <a href="/admin/reports" class="admin-nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Reports</span>
            </a>
            <hr class="admin-divider">
            <a href="/chat" class="admin-nav-item text-primary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                <span>Back to App</span>
            </a>
            <a href="/logout" class="admin-nav-item text-danger">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Sign Out</span>
            </a>
        </nav>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1 class="admin-title">System Overview</h1>
                <p class="admin-subtitle">Welcome back, <?= htmlspecialchars($currentUser['first_name'] ?? 'Admin') ?></p>
            </div>
            <div class="admin-user-badge">
                <span class="status-dot online"></span>
                <span>Administrator Mode</span>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="metrics-grid" id="metricsGrid">
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-label">Total Registered Users</span>
                    <div class="metric-icon bg-blue"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                </div>
                <div class="metric-value" id="valTotalUsers">--</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-label">Active Users</span>
                    <div class="metric-icon bg-green"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
                <div class="metric-value" id="valActiveUsers">--</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-label">Total Chat Sessions</span>
                    <div class="metric-icon bg-purple"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
                </div>
                <div class="metric-value" id="valTotalChats">--</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-label">AI Messages Generated</span>
                    <div class="metric-icon bg-orange"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                </div>
                <div class="metric-value" id="valTotalMessages">--</div>
            </div>
        </div>

        <!-- Recent Registrations -->
        <section class="admin-card">
            <div class="card-header">
                <h3>Recent User Registrations</h3>
                <a href="/admin/users" class="btn btn-sm btn-secondary">View All Users</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody id="recentUsersBody">
                        <tr><td colspan="5" class="text-center">Loading user records...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
