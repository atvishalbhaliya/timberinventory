@extends('layouts.app')

@section('title', 'Roles & Permissions - Timber Inventory')

@section('content')
    <div class="erp-page-title">
        <div>
            <h1>Roles & Permissions</h1>
            <p>Manage role access, navigation visibility, and API permissions.</p>
        </div>
        <div class="module-actions">
            <button class="btn-erp" id="export-permissions"><i data-lucide="download"></i> Permissions</button>
            <button class="btn-erp" id="export-roles"><i data-lucide="download"></i> Roles</button>
        </div>
    </div>

    <div class="alert d-none" id="module-alert" role="alert"></div>

    <section class="role-admin-shell">
        <aside class="role-list-panel">
            <div class="erp-card role-list-card">
                <div class="erp-card-header">
                    <div>
                        <h2 class="erp-card-title">Role List</h2>
                        <p class="text-muted small mb-0" id="role-count">Loading roles...</p>
                    </div>
                    <button class="btn-erp" type="button" id="add-role"><i data-lucide="plus"></i> Add Role</button>
                </div>

                <div class="module-search role-search">
                    <i data-lucide="search"></i>
                    <input id="role-search" type="search" placeholder="Search roles">
                    <button class="search-clear" data-action="clear-role-search" type="button"><i data-lucide="x"></i></button>
                </div>

                <div class="role-list" id="roles-body">
                    <div class="sidebar-loading">Loading roles...</div>
                </div>

                <div class="pagination-row role-pager">
                    <button class="btn-erp" id="prev-role-page"><i data-lucide="chevron-left"></i></button>
                    <span id="role-page-status" class="text-muted small"></span>
                    <button class="btn-erp" id="next-role-page"><i data-lucide="chevron-right"></i></button>
                </div>
            </div>
        </aside>

        <main class="role-editor-panel">
            <section class="erp-card role-editor-card">
                <div class="erp-card-header">
                    <div>
                        <h2 class="erp-card-title" id="role-form-title">New Role</h2>
                        <p class="text-muted small mb-0" id="selected-role-summary">Create a role and assign permissions by module.</p>
                    </div>
                    <div class="module-actions">
                        <button class="btn-erp" type="button" id="clone-role" disabled><i data-lucide="copy"></i> Clone</button>
                        <button class="btn-erp" type="button" id="reset-role-form"><i data-lucide="rotate-ccw"></i> Reset</button>
                    </div>
                </div>

                <form id="role-form" class="role-detail-grid">
                    <input type="hidden" id="role-id">
                    <label>
                        <span>Role Name</span>
                        <input id="role-name" maxlength="100" required>
                    </label>
                    <label>
                        <span>Description</span>
                        <input id="role-description" maxlength="255" placeholder="Optional role purpose">
                    </label>
                    <label>
                        <span>Status</span>
                        <select id="role-status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </label>
                    <label>
                        <span>Guard</span>
                        <input id="role-guard" value="api" maxlength="50">
                    </label>
                </form>
            </section>

            <section class="erp-card permission-matrix-card">
                <div class="erp-card-header matrix-header">
                    <div>
                        <h2 class="erp-card-title">Permission Matrix</h2>
                        <p class="text-muted small mb-0" id="permission-counter">0 selected</p>
                    </div>
                    <div class="module-actions matrix-actions">
                        <button class="btn-erp" type="button" id="select-all-permissions"><i data-lucide="check-check"></i> Select All</button>
                        <button class="btn-erp" type="button" id="clear-all-permissions"><i data-lucide="x"></i> Clear</button>
                        <button class="btn-erp" type="button" id="expand-all-permissions"><i data-lucide="chevrons-down"></i></button>
                        <button class="btn-erp" type="button" id="collapse-all-permissions"><i data-lucide="chevrons-up"></i></button>
                    </div>
                </div>

                <div class="module-search permission-search">
                    <i data-lucide="search"></i>
                    <input id="permission-search" type="search" placeholder="Search permissions">
                    <button class="search-clear" data-action="clear-permission-search" type="button"><i data-lucide="x"></i></button>
                </div>

                <div class="permission-modules" id="permission-modules">
                    <div class="sidebar-loading">Loading permissions...</div>
                </div>
            </section>

            <section class="role-save-bar">
                <div>
                    <strong id="save-role-name">No role selected</strong>
                    <span class="text-muted small" id="save-role-detail">Unsaved changes will be applied to the selected role.</span>
                </div>
                <button class="btn-erp btn-primary" id="save-role" type="button"><i data-lucide="save"></i> Save Role</button>
            </section>
        </main>
    </section>

    <style>
        .role-admin-shell { display: grid; grid-template-columns: minmax(280px, 380px) minmax(0, 1fr); gap: 16px; align-items: start; }
        .role-list-card, .role-editor-card, .permission-matrix-card { overflow: hidden; }
        .role-list-panel { position: sticky; top: 88px; }
        .role-search, .permission-search { margin: 0 16px 14px; }
        .role-list { display: grid; gap: 8px; padding: 0 16px 16px; max-height: 62vh; overflow: auto; }
        .role-row { width: 100%; display: grid; gap: 8px; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface); text-align: left; color: var(--text); }
        .role-row.is-selected { border-color: var(--primary); background: rgba(var(--primary-rgb), .08); }
        .role-row-main { display: flex; justify-content: space-between; gap: 10px; align-items: start; }
        .role-row-name { font-weight: 900; }
        .role-row-meta { display: flex; flex-wrap: wrap; gap: 6px; font-size: 12px; color: var(--muted); }
        .role-actions { display: flex; gap: 6px; justify-content: flex-end; }
        .role-actions .icon-btn { width: 32px; height: 32px; }
        .role-pager { padding: 12px 16px 16px; }
        .role-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 0 16px 16px; }
        .role-detail-grid label { display: grid; gap: 6px; font-weight: 800; font-size: 12px; color: var(--muted); }
        .role-detail-grid input, .role-detail-grid select { width: 100%; border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; background: var(--surface); color: var(--text); }
        .permission-modules { display: grid; gap: 10px; padding: 0 16px 18px; }
        .permission-module { border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--surface); }
        .permission-module-header { width: 100%; border: 0; background: var(--surface-soft); color: var(--text); padding: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px; text-align: left; }
        .permission-module-title { display: flex; align-items: center; gap: 8px; font-weight: 900; }
        .permission-module-tools { display: flex; align-items: center; gap: 8px; }
        .permission-module-body { display: none; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; padding: 12px; }
        .permission-module.is-open .permission-module-body { display: grid; }
        .permission-option { display: flex; gap: 8px; align-items: start; border: 1px solid var(--border); border-radius: var(--radius); padding: 9px; min-height: 58px; cursor: pointer; }
        .permission-option input { margin-top: 3px; }
        .permission-option strong { display: block; font-size: 13px; color: var(--text); }
        .permission-option span { display: block; font-size: 11px; color: var(--muted); word-break: break-word; }
        .role-save-bar { position: sticky; bottom: 16px; z-index: 5; display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 16px; padding: 14px 16px; border: 1px solid var(--border); border-radius: var(--radius); background: color-mix(in srgb, var(--surface) 92%, transparent); box-shadow: 0 14px 30px rgba(15, 23, 42, .14); backdrop-filter: blur(12px); }
        .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
        @media (max-width: 1024px) { .role-admin-shell { grid-template-columns: 1fr; } .role-list-panel { position: static; } .role-list { max-height: 420px; } }
        @media (max-width: 720px) { .role-detail-grid, .permission-module-body { grid-template-columns: 1fr; } .role-save-bar, .matrix-header { align-items: stretch; flex-direction: column; } .matrix-actions { width: 100%; flex-wrap: wrap; } }
    </style>
@endsection

@push('scripts')
<script>
let roles = [];
let permissions = [];
let selectedRole = null;
let selectedPermissionIds = new Set();
let rolePage = 1;
let roleLastPage = 1;
let roleSearchTimer = null;

const systemRoles = new Set(['Super Admin', 'Admin', 'Manager', 'Store', 'Production', 'Accounts']);
const permissionLabels = {
    view: 'View',
    create: 'Create',
    update: 'Update',
    edit: 'Edit',
    delete: 'Delete',
    manage: 'Manage',
    submit: 'Submit',
    approve: 'Approve',
    cancel: 'Cancel',
    export: 'Export',
};
const moduleLabels = {
    'stock-ledger': 'Inventory',
    'stock-summary': 'Inventory',
    'stock-verification': 'Inventory',
    'purchase-grn': 'GRN',
    grn: 'GRN',
    bom: 'Production',
    production: 'Production',
    dispatch: 'Dispatch',
    accounts: 'Accounts',
    reports: 'Reports',
    roles: 'Administration',
    permissions: 'Administration',
    'role-permissions': 'Administration',
    users: 'Administration',
    dashboard: 'Dashboard',
    masters: 'Master Data',
    inventory: 'Inventory',
    audit: 'Audit',
};

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
}

function showAlert(message, type = 'success') {
    const alert = document.getElementById('module-alert');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.classList.remove('d-none');
    window.ErpToast?.show(message, type === 'danger' ? 'danger' : 'success');
    window.setTimeout(() => alert.classList.add('d-none'), 3200);
}

function badge(status) {
    return `<span class="badge ${status === 'Active' ? 'badge-success' : 'badge-warning'}">${escapeHtml(status || 'Active')}</span>`;
}

function moduleName(permission) {
    return permission.main_module || moduleLabels[permission.module] || permission.module?.replaceAll('-', ' ').replace(/\b\w/g, char => char.toUpperCase()) || 'Other';
}

function actionName(permission) {
    return permission.action || permissionLabels[permission.action_key] || permission.action_key?.replaceAll('-', ' ') || permission.name;
}

function openRoleModal(role = null) {
    window.ErpModal.open({
        title: role ? `Edit ${role.name}` : 'Add Role',
        subtitle: 'Role details are saved without leaving this page.',
        body: `
            <div class="role-detail-grid" style="padding:0;">
                <input type="hidden" id="modal-role-id" value="${role?.id || ''}">
                <label><span>Role Name *</span><input id="modal-role-name" maxlength="100" required value="${escapeHtml(role?.name || '')}"></label>
                <label><span>Description</span><input id="modal-role-description" maxlength="255" value="${escapeHtml(role?.description || '')}"></label>
            </div>
            <div id="modal-validation" class="form-errors" hidden></div>
        `,
        footer: '<button class="btn-erp" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" data-action="save-role-details"><i data-lucide="save"></i> Save</button>',
        size: 'lg',
    });
}

function rolePayload() {
    return {
        name: document.getElementById('role-name').value.trim(),
        description: document.getElementById('role-description').value.trim(),
        status: document.getElementById('role-status').value,
        guard_name: document.getElementById('role-guard').value.trim() || 'api',
    };
}

function setRoleForm(role = null) {
    selectedRole = role;
    selectedPermissionIds = new Set(role?.permission_ids || []);
    document.getElementById('role-id').value = role?.id || '';
    document.getElementById('role-name').value = role?.name || '';
    document.getElementById('role-description').value = role?.description || '';
    document.getElementById('role-status').value = role?.status || 'Active';
    document.getElementById('role-guard').value = role?.guard_name || 'api';
    document.getElementById('role-form-title').textContent = role ? `Edit ${role.name}` : 'New Role';
    document.getElementById('selected-role-summary').textContent = role ? `${role.user_count || 0} user(s), ${selectedPermissionIds.size} permission(s)` : 'Create a role and assign permissions by module.';
    document.getElementById('clone-role').disabled = !role;
    renderRoles();
    renderPermissionMatrix();
    updateCounters();
}

function renderRoles() {
    const body = document.getElementById('roles-body');
    document.getElementById('role-count').textContent = `${roles.length} role(s) on this page`;
    document.getElementById('role-page-status').textContent = `Page ${rolePage} of ${roleLastPage}`;

    body.innerHTML = roles.length ? roles.map(role => `
        <article class="role-row ${selectedRole?.id === role.id ? 'is-selected' : ''}" data-role-select="${role.id}">
            <div class="role-row-main">
                <div>
                    <div class="role-row-name">${escapeHtml(role.name)}</div>
                    <div class="small text-muted">${escapeHtml(role.description || 'No description')}</div>
                </div>
                ${badge(role.status)}
            </div>
            <div class="role-row-meta">
                <span>${role.user_count || 0} users</span>
                <span>${(role.permissions || []).length} permissions</span>
                <span>${role.created_at ? new Date(role.created_at).toLocaleDateString() : ''}</span>
            </div>
            <div class="role-actions">
                <button class="icon-btn" type="button" data-role-view="${role.id}" title="View"><i data-lucide="eye"></i></button>
                <button class="icon-btn" type="button" data-role-edit="${role.id}" title="Edit"><i data-lucide="pencil"></i></button>
                <button class="icon-btn" type="button" data-role-clone="${role.id}" title="Clone Role"><i data-lucide="copy"></i></button>
                <button class="icon-btn" type="button" data-role-delete="${role.id}" title="Delete" ${role.is_system || systemRoles.has(role.name) ? 'disabled' : ''}><i data-lucide="trash-2"></i></button>
            </div>
        </article>
    `).join('') : '<div class="sidebar-loading">No roles found.</div>';
    window.lucide?.createIcons();
}

function groupedPermissions() {
    const search = document.getElementById('permission-search').value.trim().toLowerCase();
    return permissions
        .filter(permission => !search || permission.name.toLowerCase().includes(search) || moduleName(permission).toLowerCase().includes(search) || actionName(permission).toLowerCase().includes(search))
        .reduce((groups, permission) => {
            const group = moduleName(permission);
            groups[group] = groups[group] || [];
            groups[group].push(permission);
            return groups;
        }, {});
}

function renderPermissionMatrix() {
    const root = document.getElementById('permission-modules');
    const groups = groupedPermissions();
    const html = Object.entries(groups).map(([group, items]) => {
        const selectedCount = items.filter(permission => selectedPermissionIds.has(permission.id)).length;
        const moduleKey = group.toLowerCase().replaceAll(' ', '-');

        return `
            <section class="permission-module is-open" data-permission-module="${moduleKey}">
                <button class="permission-module-header" type="button" data-module-toggle="${moduleKey}">
                    <span class="permission-module-title"><i data-lucide="folder-key"></i>${escapeHtml(group)} <span class="badge badge-primary">${selectedCount}/${items.length}</span></span>
                    <span class="permission-module-tools">
                        <span class="small text-muted">Select Module</span>
                        <input type="checkbox" data-module-select="${moduleKey}" ${selectedCount === items.length && items.length ? 'checked' : ''}>
                        <i data-lucide="chevron-down"></i>
                    </span>
                </button>
                <div class="permission-module-body">
                    ${items.map(permission => `
                        <label class="permission-option">
                            <input type="checkbox" value="${permission.id}" data-permission-check ${selectedPermissionIds.has(permission.id) ? 'checked' : ''}>
                            <span>
                                <strong>${escapeHtml(actionName(permission))}</strong>
                                <span>${escapeHtml(permission.name)}</span>
                            </span>
                        </label>
                    `).join('')}
                </div>
            </section>
        `;
    }).join('');

    root.innerHTML = html || '<div class="sidebar-loading">No permissions found.</div>';
    window.lucide?.createIcons();
}

function updateCounters() {
    document.getElementById('permission-counter').textContent = `${selectedPermissionIds.size} of ${permissions.length} selected`;
    document.getElementById('save-role-name').textContent = selectedRole?.name || (document.getElementById('role-name').value.trim() || 'New role');
    document.getElementById('save-role-detail').textContent = `${selectedPermissionIds.size} permission(s) selected`;
}

async function loadRoles(page = rolePage) {
    const response = await window.axios.get('/v1/admin/roles', {
        params: { search: document.getElementById('role-search').value, page, per_page: 10 },
    });
    const payload = response.data.data;
    roles = payload.data || [];
    rolePage = payload.current_page || 1;
    roleLastPage = payload.last_page || 1;
    if (selectedRole) selectedRole = roles.find(role => role.id === selectedRole.id) || selectedRole;
    renderRoles();
}

async function loadPermissions() {
    const response = await window.axios.get('/v1/admin/permissions', { params: { per_page: 500 } });
    permissions = response.data.data.data || [];
    renderPermissionMatrix();
    updateCounters();
}

async function saveRole() {
    const id = document.getElementById('role-id').value;
    const payload = rolePayload();

    if (!payload.name) {
        showAlert('Role name is required.', 'danger');
        return;
    }

    try {
        const roleResponse = id
            ? await window.axios.put(`/v1/admin/roles/${id}`, payload)
            : await window.axios.post('/v1/admin/roles', payload);

        const savedRole = roleResponse.data.data;
        await window.axios.put(`/v1/admin/roles/${savedRole.id}/permissions`, {
            permission_ids: Array.from(selectedPermissionIds),
        });

        localStorage.removeItem('timber-sidebar-navigation-v1');
        localStorage.removeItem('timber-sidebar-navigation-v2');
        localStorage.removeItem('timber-sidebar-navigation-v3');
        localStorage.removeItem('timber-sidebar-navigation-v4');
        localStorage.removeItem('timber-sidebar-navigation-v5');
        localStorage.removeItem('timber-sidebar-navigation-v6');
        localStorage.removeItem('timber-sidebar-navigation-v7');
        await loadRoles(rolePage);
        const freshRole = roles.find(role => role.id === savedRole.id) || savedRole;
        setRoleForm(freshRole);
        showAlert('Role saved.');
    } catch (error) {
        showAlert(error.normalizedMessage || error.response?.data?.message || 'Role save failed.', 'danger');
    }
}

async function cloneRole(role) {
    const cloneName = `${role.name} Copy`;
    try {
        const response = await window.axios.post('/v1/admin/roles', {
            name: cloneName,
            description: role.description || `Cloned from ${role.name}`,
            status: 'Active',
            guard_name: role.guard_name || 'api',
        });
        const clone = response.data.data;
        await window.axios.put(`/v1/admin/roles/${clone.id}/permissions`, {
            permission_ids: role.permission_ids || [],
        });
        await loadRoles(1);
        setRoleForm(roles.find(item => item.id === clone.id) || clone);
        showAlert('Role cloned.');
    } catch (error) {
        showAlert(error.normalizedMessage || error.response?.data?.message || 'Role clone failed.', 'danger');
    }
}

async function downloadCsv(url, filename) {
    const response = await window.axios.get(url, { responseType: 'blob' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(response.data);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}

document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([loadRoles(), loadPermissions()]);

    document.getElementById('add-role').addEventListener('click', () => openRoleModal());
    document.getElementById('reset-role-form').addEventListener('click', () => setRoleForm(null));
    document.getElementById('save-role').addEventListener('click', saveRole);
    document.getElementById('clone-role').addEventListener('click', () => selectedRole && cloneRole(selectedRole));
    document.getElementById('export-roles').addEventListener('click', () => downloadCsv('/v1/admin/roles/export', 'roles.csv'));
    document.getElementById('export-permissions').addEventListener('click', () => downloadCsv('/v1/admin/permissions/export', 'permissions.csv'));
    document.getElementById('prev-role-page').addEventListener('click', () => rolePage > 1 && loadRoles(rolePage - 1));
    document.getElementById('next-role-page').addEventListener('click', () => rolePage < roleLastPage && loadRoles(rolePage + 1));

    document.getElementById('role-search').addEventListener('input', () => {
        clearTimeout(roleSearchTimer);
        roleSearchTimer = setTimeout(() => loadRoles(1).catch(error => showAlert(error.normalizedMessage || 'Role search failed.', 'danger')), 250);
    });

    document.getElementById('permission-search').addEventListener('input', () => {
        renderPermissionMatrix();
        updateCounters();
    });

    document.getElementById('role-form').addEventListener('input', updateCounters);

    document.addEventListener('click', async event => {
        if (event.target.closest('[data-action="clear-role-search"]')) {
            document.getElementById('role-search').value = '';
            await loadRoles(1);
        }
        if (event.target.closest('[data-action="clear-permission-search"]')) {
            document.getElementById('permission-search').value = '';
            renderPermissionMatrix();
        }

        const roleElement = event.target.closest('[data-role-select], [data-role-view], [data-role-edit]');
        if (roleElement && !event.target.closest('.role-actions')) {
            const id = Number(roleElement.dataset.roleSelect || roleElement.dataset.roleView || roleElement.dataset.roleEdit);
            setRoleForm(roles.find(role => role.id === id));
        }

        const editId = event.target.closest('[data-role-edit]')?.dataset.roleEdit;
        if (editId) openRoleModal(roles.find(role => role.id === Number(editId)));

        const viewId = event.target.closest('[data-role-view]')?.dataset.roleView;
        if (viewId) setRoleForm(roles.find(role => role.id === Number(viewId)));

        const cloneId = event.target.closest('[data-role-clone]')?.dataset.roleClone;
        if (cloneId) await cloneRole(roles.find(role => role.id === Number(cloneId)));

        const deleteId = event.target.closest('[data-role-delete]')?.dataset.roleDelete;
        if (deleteId) {
            const role = roles.find(item => item.id === Number(deleteId));
            window.ErpModal.open({
                title: 'Delete Role',
                subtitle: role?.name || '',
                body: `<p>Delete ${escapeHtml(role?.name || 'this role')}?</p>`,
                footer: `<button class="btn-erp" data-modal-close>Cancel</button><button class="btn-erp btn-danger" data-confirm-role-delete="${deleteId}"><i data-lucide="trash-2"></i> Delete</button>`,
                size: 'sm',
            });
        }

        const moduleToggle = event.target.closest('[data-module-toggle]');
        if (moduleToggle && !event.target.closest('[data-module-select]')) {
            moduleToggle.closest('.permission-module')?.classList.toggle('is-open');
        }

        if (event.target.closest('#select-all-permissions')) {
            selectedPermissionIds = new Set(permissions.map(permission => permission.id));
            renderPermissionMatrix();
            updateCounters();
        }
        if (event.target.closest('#clear-all-permissions')) {
            selectedPermissionIds.clear();
            renderPermissionMatrix();
            updateCounters();
        }
        if (event.target.closest('#expand-all-permissions')) {
            document.querySelectorAll('.permission-module').forEach(module => module.classList.add('is-open'));
        }
        if (event.target.closest('#collapse-all-permissions')) {
            document.querySelectorAll('.permission-module').forEach(module => module.classList.remove('is-open'));
        }

        if (event.target.closest('[data-action="save-role-details"]')) {
            const id = document.getElementById('modal-role-id').value;
            const currentRole = roles.find(role => role.id === Number(id));
            const payload = {
                name: document.getElementById('modal-role-name').value.trim(),
                description: document.getElementById('modal-role-description').value.trim(),
                status: currentRole?.status || 'Active',
                guard_name: currentRole?.guard_name || 'api',
            };
            try {
                const response = id ? await window.axios.put(`/v1/admin/roles/${id}`, payload) : await window.axios.post('/v1/admin/roles', payload);
                window.ErpModal.close();
                await loadRoles(rolePage);
                setRoleForm(roles.find(role => role.id === response.data.data.id) || response.data.data);
                showAlert('Role saved.');
            } catch (error) {
                const box = document.getElementById('modal-validation');
                box.textContent = error.normalizedMessage || 'Role save failed.';
                box.hidden = false;
            }
        }

        if (event.target.closest('[data-confirm-role-delete]')) {
            const id = event.target.closest('[data-confirm-role-delete]').dataset.confirmRoleDelete;
            try {
                await window.axios.delete(`/v1/admin/roles/${id}`);
                if (selectedRole?.id === Number(id)) setRoleForm(null);
                window.ErpModal.close();
                await loadRoles(rolePage);
                showAlert('Role deleted.');
            } catch (error) {
                showAlert(error.normalizedMessage || error.response?.data?.message || 'Role delete failed.', 'danger');
            }
        }

    });

    document.getElementById('permission-modules').addEventListener('change', event => {
        const permissionCheck = event.target.closest('[data-permission-check]');
        if (permissionCheck) {
            const id = Number(permissionCheck.value);
            if (permissionCheck.checked) selectedPermissionIds.add(id);
            else selectedPermissionIds.delete(id);
            renderPermissionMatrix();
            updateCounters();
            return;
        }

        const moduleSelect = event.target.closest('[data-module-select]');
        if (moduleSelect) {
            const module = moduleSelect.closest('.permission-module');
            module.querySelectorAll('[data-permission-check]').forEach(input => {
                const id = Number(input.value);
                if (moduleSelect.checked) selectedPermissionIds.add(id);
                else selectedPermissionIds.delete(id);
            });
            renderPermissionMatrix();
            updateCounters();
        }
    });
});
</script>
@endpush
