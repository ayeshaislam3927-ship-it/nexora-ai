<?php
/**
 * NEXORA ADMIN - User Management View
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Manage Users — NEXORA Admin';
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
            <a href="/admin/dashboard" class="admin-nav-item">Overview</a>
            <a href="/admin/users" class="admin-nav-item active">Users</a>
            <a href="/admin/chats" class="admin-nav-item">Chats</a>
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
                <h1 class="admin-title">User Management</h1>
                <p class="admin-subtitle">Inspect user accounts, manage roles, and enforce platform security.</p>
            </div>
        </header>

        <!-- Search & Filters -->
        <div class="admin-card mb-4">
            <form id="userSearchForm" class="filter-bar">
                <input type="text" id="userSearchInput" class="form-control" placeholder="Search by name or email...">
                <select id="userRoleFilter" class="form-control">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <select id="userStatusFilter" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <!-- Users Table -->
        <section class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminUsersTableBody">
                        <tr><td colspan="8" class="text-center">Loading users...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
