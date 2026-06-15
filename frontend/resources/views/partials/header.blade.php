<header class="erp-header">
    <div class="erp-header__inner">
        <div class="header-left">
            <button class="icon-btn" data-action="toggle-sidebar" title="Toggle sidebar"><i data-lucide="panel-left"></i></button>
            <a href="/dashboard" class="brand">
                <span class="brand-mark"><i data-lucide="warehouse"></i></span>
                <span class="brand-copy">
                    <strong>Timber Inventory</strong>
                </span>
            </a>
        </div>

        <div class="header-right">
            <button class="icon-btn" data-panel-trigger="notifications" title="Notifications">
                <i data-lucide="bell"></i>
                <span class="badge-dot" id="notification-count">5</span>
            </button>
            <div class="floating-panel" data-floating-panel="notifications">
                <div class="panel-row"><i data-lucide="triangle-alert"></i><span><strong>Low stock items</strong><span>12 SKUs below reorder level</span></span><span class="badge badge-warning">12</span></div>
                <div class="panel-row"><i data-lucide="shield-alert"></i><span><strong>Pending approvals</strong><span>4 role and dispatch approvals</span></span><span class="badge badge-primary">4</span></div>
                <div class="panel-row"><i data-lucide="truck"></i><span><strong>Pending dispatch</strong><span>8 challans need confirmation</span></span><span class="badge badge-danger">8</span></div>
                <div class="panel-row"><i data-lucide="database-zap"></i><span><strong>Stock variance</strong><span>Audit variance detected in bay 3</span></span><i data-lucide="chevron-right"></i></div>
            </div>
            <div class="dropdown">
                <button class="profile-trigger" data-dropdown-trigger type="button">
                    <span class="avatar" id="user-avatar">ST</span>
                    <span class="profile-meta">
                        <strong id="user-full-name">Suresh Timber Admin</strong>
                        <span id="user-role-display">Administrator</span>
                    </span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <div class="profile-card">
                        <span class="avatar avatar-lg" id="profile-menu-avatar">ST</span>
                        <span>
                            <strong id="profile-menu-name">Suresh Timber Admin</strong>
                            <span id="profile-menu-role">Administrator</span>
                        </span>
                    </div>
                    <div class="profile-facts">
                        <div><span>Role</span><strong id="profile-fact-role">Administrator</strong></div>
                        <div><span>Branch</span><strong id="profile-fact-branch">Main Branch</strong></div>
                        <div><span>Tenant</span><strong id="profile-fact-tenant">Suresh Timber</strong></div>
                    </div>
                    @foreach ([['My Profile','user-round','profile'], ['Change Password','key-round','password'], ['Activity Logs','history','activity']] as [$label, $icon, $action])
                        <a href="#" class="panel-row" data-profile-action="{{ $action }}">
                            <i data-lucide="{{ $icon }}"></i>
                            <span><strong>{{ $label }}</strong><span>Manage {{ strtolower($label) }}</span></span>
                            <i data-lucide="chevron-right"></i>
                        </a>
                    @endforeach
                    <a href="#" class="panel-row" data-action="theme-panel">
                        <i data-lucide="palette"></i>
                        <span><strong>Theme Settings</strong><span>Manage theme settings</span></span>
                        <i data-lucide="chevron-right"></i>
                    </a>
                    <a href="javascript:void(0);" class="panel-row" onclick="handleLogout();">
                        <i data-lucide="log-out"></i>
                        <span><strong>Logout</strong><span>End current session</span></span>
                        <i data-lucide="chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<aside class="theme-panel" aria-label="Theme settings">
    <div class="theme-panel__head">
        <h2>Theme Settings</h2>
        <button class="icon-btn" data-action="close-theme-panel" title="Close"><i data-lucide="x"></i></button>
    </div>
    @foreach ([
        'themePreset' => ['Timber Professional' => 'timber', 'Corporate' => 'corporate', 'Light' => 'light', 'Dark' => 'dark', 'Semi Dark' => 'semi-dark'],
        'themeColor' => ['Timber' => 'timber', 'Corporate Blue' => 'corporate', 'Green' => 'green', 'Teal' => 'teal', 'Purple' => 'purple', 'Orange' => 'orange', 'Red' => 'red', 'Indigo' => 'indigo'],
        'sidebarTheme' => ['Dark' => 'dark', 'Light' => 'light', 'Gradient' => 'gradient', 'Glass' => 'glass'],
        'headerTheme' => ['Light' => 'light', 'Dark' => 'dark', 'Transparent' => 'transparent'],
        'cardStyle' => ['Modern' => 'modern', 'Flat' => 'flat', 'Glassmorphism' => 'glassmorphism', 'Material' => 'material'],
        'darkMode' => ['Light' => 'light', 'Dark' => 'dark', 'Auto' => 'auto'],
        'borderRadius' => ['Compact' => 'compact', 'Comfortable' => 'comfortable', 'Rounded' => 'rounded'],
        'fontFamily' => ['Inter' => 'inter', 'Poppins' => 'poppins', 'Nunito' => 'nunito'],
    ] as $name => $options)
        <div class="theme-group">
            <label>{{ Str::headline($name) }}</label>
            <div class="segmented">
                @foreach ($options as $label => $value)
                    <button type="button" data-segment-name="{{ $name }}" data-segment-value="{{ $value }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    @endforeach
</aside>

<script>
    function applyHeaderUser(user) {
        const name = user.full_name || 'Suresh Timber Admin';
        const role = user.role_name || 'Administrator';
        const branch = user.branch_name || user.branch || 'Main Branch';
        const tenant = user.tenant_name || user.tenant || 'Suresh Timber';
        const initials = name.split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
        document.getElementById('user-full-name').textContent = name;
        document.getElementById('profile-menu-name').textContent = name;
        document.getElementById('user-role-display').textContent = role;
        document.getElementById('profile-menu-role').textContent = role;
        document.getElementById('profile-fact-role').textContent = role;
        document.getElementById('profile-fact-branch').textContent = branch;
        document.getElementById('profile-fact-tenant').textContent = tenant;
        document.getElementById('user-avatar').textContent = initials;
        document.getElementById('profile-menu-avatar').textContent = initials;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        applyHeaderUser(user);
        window.axios?.get('/v1/auth/me').then(response => {
            const freshUser = { ...user, ...(response.data.data || {}) };
            localStorage.setItem('user', JSON.stringify(freshUser));
            applyHeaderUser(freshUser);
        }).catch(() => {});
    });

    function handleLogout() {
        window.axios.post('/v1/auth/logout').finally(() => {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            localStorage.removeItem('timber-sidebar-navigation-v1');
            localStorage.removeItem('timber-sidebar-navigation-v2');
            localStorage.removeItem('timber-sidebar-navigation-v3');
            localStorage.removeItem('timber-sidebar-navigation-v4');
            window.location.href = '/login';
        });
    }

    function profileUser() {
        return JSON.parse(localStorage.getItem('user') || '{}');
    }

    function text(value, fallback = '-') {
        return String(value || fallback).replace(/[&<>"']/g, match => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match]));
    }

    function openMyProfile() {
        const user = profileUser();
        const name = user.full_name || 'Suresh Timber Admin';
        const initials = name.split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
        window.ErpModal.open({
            title: 'My Profile',
            subtitle: 'Signed-in account details',
            size: 'sm',
            body: `
                <div class="profile-modal">
                    <div class="profile-modal-head">
                        <span class="avatar avatar-lg">${text(initials)}</span>
                        <div><strong>${text(name)}</strong><span>${text(user.role_name || 'User')}</span></div>
                    </div>
                    <div class="profile-detail-grid">
                        <div><span>Login ID</span><strong>${text(user.login_id)}</strong></div>
                        <div><span>Employee Code</span><strong>${text(user.employee_code)}</strong></div>
                        <div><span>Mobile</span><strong>${text(user.mobile)}</strong></div>
                        <div><span>Email</span><strong>${text(user.email)}</strong></div>
                        <div><span>Role</span><strong>${text(user.role_name)}</strong></div>
                        <div><span>Branch</span><strong>${text(user.branch_name || user.branch)}</strong></div>
                    </div>
                </div>
            `,
            footer: '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>',
        });
    }

    function openChangePassword() {
        window.ErpModal.open({
            title: 'Change Password',
            subtitle: 'Update the password for this login',
            size: 'sm',
            body: `
                <form id="change-password-form" class="profile-form">
                    <div class="field"><i data-lucide="lock-keyhole"></i><label>Current Password</label><input id="current-password" type="password" autocomplete="current-password" required></div>
                    <div class="field"><i data-lucide="key-round"></i><label>New Password</label><input id="new-password" type="password" autocomplete="new-password" minlength="6" required></div>
                    <div class="field"><i data-lucide="badge-check"></i><label>Confirm Password</label><input id="confirm-password" type="password" autocomplete="new-password" minlength="6" required></div>
                    <div id="change-password-errors" class="form-errors" hidden></div>
                </form>
            `,
            footer: '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-save-password><i data-lucide="save"></i> Save Password</button>',
        });
    }

    async function savePassword() {
        const box = document.getElementById('change-password-errors');
        box.hidden = true;
        try {
            await window.axios.post('/v1/auth/change-password', {
                current_password: document.getElementById('current-password').value,
                password: document.getElementById('new-password').value,
                password_confirmation: document.getElementById('confirm-password').value,
            });
            window.ErpModal.close();
            window.ErpToast?.show('Password changed successfully.');
        } catch (error) {
            box.textContent = error.normalizedMessage || 'Unable to change password.';
            box.hidden = false;
        }
    }

    async function openActivityLogs() {
        window.ErpModal.loading('Activity Logs', 'Loading recent account activity...');
        try {
            const response = await window.axios.get('/v1/dashboard/recent');
            const rows = response.data.data || [];
            window.ErpModal.open({
                title: 'Activity Logs',
                subtitle: 'Recent stock ledger activity',
                size: 'lg',
                body: `<div class="profile-activity-list">${rows.length ? rows.map(row => `
                    <div class="profile-activity-item">
                        <i data-lucide="activity"></i>
                        <span><strong>${text(row.transaction_type || 'Activity')}</strong><span>${text(row.item_name || 'Item')} - In: ${text(row.qty_in || 0)}, Out: ${text(row.qty_out || 0)}</span></span>
                        <small>${row.transaction_date ? new Date(row.transaction_date).toLocaleString() : ''}</small>
                    </div>
                `).join('') : '<div class="profile-empty">No recent activity found.</div>'}</div>`,
                footer: '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>',
            });
        } catch (error) {
            window.ErpModal.open({
                title: 'Activity Logs',
                subtitle: 'Unable to load activity',
                size: 'sm',
                body: `<div class="form-errors">${text(error.normalizedMessage || 'Activity logs could not be loaded.')}</div>`,
                footer: '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>',
            });
        }
    }

    document.addEventListener('click', async function(event) {
        if (event.target.closest('[data-action="theme-panel"]')) {
            event.preventDefault();
            document.querySelector('.dropdown-menu.show')?.classList.remove('show');
        }
        const action = event.target.closest('[data-profile-action]')?.dataset.profileAction;
        if (!action) {
            if (event.target.closest('[data-save-password]')) await savePassword();
            return;
        }
        event.preventDefault();
        document.querySelector('.dropdown-menu.show')?.classList.remove('show');
        if (action === 'profile') openMyProfile();
        if (action === 'password') openChangePassword();
        if (action === 'activity') await openActivityLogs();
    });
</script>
