/**
 * NEXORA - Admin Dashboard Frontend Script
 */

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardMetrics();
    loadAdminUsers();
    loadAdminChats();
    loadAdminLogs();
    loadAdminSettings();
    loadAdminReports();

    // User Filter Listener
    const userSearchForm = document.getElementById('userSearchForm');
    if (userSearchForm) {
        userSearchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loadAdminUsers();
        });
    }

    // System Settings Listener
    const adminSettingsForm = document.getElementById('adminSettingsForm');
    if (adminSettingsForm) {
        adminSettingsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msgEl = document.getElementById('settingsMsg');
            msgEl.innerText = 'Saving settings...';
            msgEl.className = 'mt-2 text-sm text-info';

            const payload = {
                platform_name: document.getElementById('setPlatformName').value,
                default_model: document.getElementById('setDefaultModel').value,
                registration_enabled: document.getElementById('setRegEnabled').checked ? '1' : '0',
                guest_chat_enabled: document.getElementById('setGuestEnabled').checked ? '1' : '0',
                maintenance_mode: document.getElementById('setMaintenance').checked ? '1' : '0',
                max_upload_size: document.getElementById('setMaxUpload').value
            };

            try {
                const res = await fetch('/api/admin/settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    msgEl.innerText = 'Settings saved successfully!';
                    msgEl.className = 'mt-2 text-sm text-success';
                } else {
                    msgEl.innerText = data.error || 'Failed to save settings.';
                    msgEl.className = 'mt-2 text-sm text-danger';
                }
            } catch (err) {
                msgEl.innerText = 'Error saving settings.';
                msgEl.className = 'mt-2 text-sm text-danger';
            }
        });
    }
});

async function loadDashboardMetrics() {
    const totalEl = document.getElementById('valTotalUsers');
    if (!totalEl) return;

    try {
        const res = await fetch('/api/admin/dashboard.php');
        const data = await res.json();

        if (data.success && data.metrics) {
            document.getElementById('valTotalUsers').innerText = data.metrics.total_users;
            document.getElementById('valActiveUsers').innerText = data.metrics.active_users;
            document.getElementById('valTotalChats').innerText = data.metrics.total_chats;
            document.getElementById('valTotalMessages').innerText = data.metrics.total_messages;

            // Render Recent Users
            const body = document.getElementById('recentUsersBody');
            if (body && data.recent_users) {
                body.innerHTML = '';
                data.recent_users.forEach(u => {
                    body.innerHTML += `
                        <tr>
                            <td><strong>${escapeHtml(u.first_name || '')} ${escapeHtml(u.last_name || '')}</strong></td>
                            <td>${escapeHtml(u.email)}</td>
                            <td><span class="badge badge-primary">${escapeHtml(u.role)}</span></td>
                            <td><span class="badge badge-${u.status === 'active' ? 'success' : 'danger'}">${escapeHtml(u.status)}</span></td>
                            <td>${escapeHtml(u.created_at)}</td>
                        </tr>
                    `;
                });
            }
        }
    } catch (e) {
        console.error('Error metrics:', e);
    }
}

async function loadAdminUsers() {
    const tableBody = document.getElementById('adminUsersTableBody');
    if (!tableBody) return;

    const search = document.getElementById('userSearchInput')?.value || '';
    const role = document.getElementById('userRoleFilter')?.value || '';
    const status = document.getElementById('userStatusFilter')?.value || '';

    try {
        const url = `/api/admin/users.php?search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}&status=${encodeURIComponent(status)}`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.success && data.users) {
            tableBody.innerHTML = '';
            if (data.users.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No user accounts found</td></tr>';
                return;
            }

            data.users.forEach(u => {
                tableBody.innerHTML += `
                    <tr>
                        <td>#${u.id}</td>
                        <td>${escapeHtml(u.first_name || '')} ${escapeHtml(u.last_name || '')}</td>
                        <td>${escapeHtml(u.email)}</td>
                        <td>
                            <select onchange="updateUserRole(${u.id}, this.value)" class="form-control form-control-sm">
                                <option value="user" ${u.role === 'user' ? 'selected' : ''}>User</option>
                                <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        </td>
                        <td>
                            <span class="badge badge-${u.status === 'active' ? 'success' : 'danger'}">${u.status}</span>
                        </td>
                        <td>${escapeHtml(u.created_at || 'N/A')}</td>
                        <td>${escapeHtml(u.last_login || 'Never')}</td>
                        <td>
                            <button onclick="toggleUserStatus(${u.id}, '${u.status === 'active' ? 'suspended' : 'active'}')" class="btn btn-sm btn-secondary">
                                ${u.status === 'active' ? 'Suspend' : 'Activate'}
                            </button>
                            <button onclick="deleteUserAccount(${u.id})" class="btn btn-sm btn-outline text-danger">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
    } catch (e) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load users</td></tr>';
    }
}

async function updateUserRole(userId, newRole) {
    try {
        const res = await fetch('/api/admin/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, action: 'change_role', role: newRole })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Role updated successfully', 'success');
        } else {
            alert(data.error || 'Failed to update role');
            loadAdminUsers();
        }
    } catch (e) {
        alert('Network error updating role');
    }
}

async function toggleUserStatus(userId, newStatus) {
    try {
        const res = await fetch('/api/admin/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, action: 'change_status', status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            showToast('User status updated', 'success');
            loadAdminUsers();
        } else {
            alert(data.error || 'Failed to update status');
        }
    } catch (e) {
        alert('Network error updating status');
    }
}

async function deleteUserAccount(userId) {
    if (!confirm('Are you sure you want to PERMANENTLY delete this user account?')) return;

    try {
        const res = await fetch('/api/admin/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, action: 'delete' })
        });
        const data = await res.json();
        if (data.success) {
            showToast('User deleted', 'success');
            loadAdminUsers();
        } else {
            alert(data.error || 'Failed to delete user');
        }
    } catch (e) {
        alert('Error deleting user');
    }
}

async function loadAdminChats() {
    const tableBody = document.getElementById('adminChatsTableBody');
    if (!tableBody) return;

    try {
        const res = await fetch('/api/admin/chats.php');
        const data = await res.json();

        if (data.success && data.chats) {
            tableBody.innerHTML = '';
            data.chats.forEach(c => {
                tableBody.innerHTML += `
                    <tr>
                        <td>#${c.id}</td>
                        <td><strong>${escapeHtml(c.title)}</strong></td>
                        <td>${escapeHtml(c.user_email || 'Guest')}</td>
                        <td><span class="badge badge-primary">${escapeHtml(c.model)}</span></td>
                        <td>${c.message_count} msgs</td>
                        <td>${escapeHtml(c.updated_at)}</td>
                    </tr>
                `;
            });
        }
    } catch (e) {}
}

async function loadAdminLogs() {
    const tableBody = document.getElementById('adminLogsTableBody');
    if (!tableBody) return;

    try {
        const res = await fetch('/api/admin/logs.php');
        const data = await res.json();

        if (data.success && data.logs) {
            tableBody.innerHTML = '';
            data.logs.forEach(l => {
                tableBody.innerHTML += `
                    <tr>
                        <td>#${l.id}</td>
                        <td>${escapeHtml(l.admin_email || 'System')}</td>
                        <td><span class="badge badge-primary">${escapeHtml(l.action)}</span></td>
                        <td>${escapeHtml(l.details || '')}</td>
                        <td>${escapeHtml(l.ip_address || '127.0.0.1')}</td>
                        <td>${escapeHtml(l.created_at)}</td>
                    </tr>
                `;
            });
        }
    } catch (e) {}
}

async function loadAdminSettings() {
    const nameEl = document.getElementById('setPlatformName');
    if (!nameEl) return;

    try {
        const res = await fetch('/api/admin/settings.php');
        const data = await res.json();

        if (data.success && data.settings) {
            if (data.settings.platform_name) nameEl.value = data.settings.platform_name;
            if (data.settings.default_model) document.getElementById('setDefaultModel').value = data.settings.default_model;
            if (data.settings.registration_enabled) document.getElementById('setRegEnabled').checked = (data.settings.registration_enabled === '1');
            if (data.settings.guest_chat_enabled) document.getElementById('setGuestEnabled').checked = (data.settings.guest_chat_enabled === '1');
            if (data.settings.maintenance_mode) document.getElementById('setMaintenance').checked = (data.settings.maintenance_mode === '1');
            if (data.settings.max_upload_size) document.getElementById('setMaxUpload').value = data.settings.max_upload_size;
        }
    } catch (e) {}
}

async function loadAdminReports() {
    const modelReport = document.getElementById('modelUsageReport');
    if (!modelReport) return;

    try {
        const res = await fetch('/api/admin/reports.php');
        const data = await res.json();

        if (data.success) {
            let html = '<ul class="list-group">';
            (data.model_usage || []).forEach(m => {
                html += `<li class="p-2 border-b flex justify-between"><span>${m.model || 'Default'}</span><strong>${m.total} responses</strong></li>`;
            });
            html += '</ul>';
            modelReport.innerHTML = html || 'No model usage data recorded yet.';

            let regHtml = '<ul class="list-group">';
            (data.daily_registrations || []).forEach(r => {
                regHtml += `<li class="p-2 border-b flex justify-between"><span>${r.date}</span><strong>${r.count} users</strong></li>`;
            });
            regHtml += '</ul>';
            document.getElementById('dailyRegsReport').innerHTML = regHtml || 'No recent signups.';
        }
    } catch (e) {}
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
