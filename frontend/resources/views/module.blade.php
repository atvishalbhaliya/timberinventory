@extends('layouts.app')

@section('title', $title.' - Timber Inventory')

@section('content')
    <div class="alert-panel" id="module-alert" hidden></div>

    @if ($isCrud)
        <section class="erp-card standard-list-card">
            <div class="standard-card-head">
                <div>
                    <h1>{{ $title }}</h1>
                    <p class="text-muted">API connected master workspace.</p>
                </div>
                <div class="module-actions">
                    <button class="btn-erp" type="button" data-action="reload-records"><i data-lucide="refresh-cw"></i> Refresh</button>
                    <button class="btn-erp btn-primary" data-action="new-record"><i data-lucide="plus"></i> New</button>
                </div>
            </div>
            <div class="standard-filter-row" id="standard-filter-row" hidden>
                <div class="standard-filter-group">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
            <div class="table-toolbar standard-table-toolbar">
                <div class="data-grid-controls">
                    <select id="per-page">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="column-picker">
                        <button class="btn-erp btn-column-picker" type="button" data-action="columns-menu" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                        <div class="column-menu column-picker-menu" id="column-menu"></div>
                    </div>
                    <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="standard-filter-row standard-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
                </div>
                <div class="module-search">
                    <i data-lucide="search"></i>
                    <input id="module-search" type="search" placeholder="Search {{ strtolower($title) }}">
                    <button class="search-clear" type="button" data-action="clear-search" title="Clear search"><i data-lucide="x"></i></button>
                </div>
            </div>
            <div class="table-responsive standard-data-table-wrap">
                <table class="table standard-data-table">
                    <thead id="module-head"></thead>
                    <tbody id="module-body">
                        <tr class="skeleton-row"><td></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-row">
                <div class="pager-actions">
                    <button class="btn-erp btn-page-nav" data-action="prev-page" title="Previous"><i data-lucide="chevron-left"></i></button>
                    <span class="page-number-list" id="page-links"></span>
                    <button class="btn-erp btn-page-nav" data-action="next-page" title="Next"><i data-lucide="chevron-right"></i></button>
                    <span id="page-status" class="text-muted page-record-status"></span>
                </div>
                <div class="module-actions">
                    <button class="btn-erp" type="button" data-action="export-csv"><i data-lucide="file-spreadsheet"></i> Export CSV</button>
                    <button class="btn-erp" type="button" data-action="print-grid"><i data-lucide="printer"></i> Print</button>
                </div>
            </div>
        </section>
    @else
        <section class="erp-card empty-module">
            <i data-lucide="workflow"></i>
            <h3>{{ $title }}</h3>
            <p class="text-muted">This page is intentionally not using demo content. Connect this workflow to its backend API before enabling transactions.</p>
        </section>
    @endif
@endsection

@if ($isCrud)
@push('scripts')
<style>
    .standard-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .standard-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .standard-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .standard-card-head p { margin:5px 0 0; }
    .standard-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .standard-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex-wrap:wrap; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .standard-table-toolbar { padding:16px 20px; }
    .column-picker { position:relative; }
    .btn-column-picker, .btn-filter-toggle { min-height:38px; padding:8px 11px; }
    .btn-column-picker svg, .btn-filter-toggle svg { width:16px; height:16px; }
    .btn-filter-toggle { color:var(--text); background:#fff; }
    .btn-filter-toggle.is-active { color:var(--primary); border-color:color-mix(in srgb, var(--primary) 42%, var(--border)); background:color-mix(in srgb, var(--primary) 10%, var(--surface)); }
    .column-picker-menu { position:absolute; top:calc(100% + 8px); left:0; z-index:80; display:none; min-width:220px; padding:8px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); box-shadow:var(--shadow); }
    .column-picker-menu.is-open { display:grid; gap:4px; }
    .column-picker-menu label { display:flex; align-items:center; gap:8px; min-height:34px; padding:6px 8px; border-radius:6px; color:var(--text); font-weight:800; }
    .column-picker-menu label:hover { background:var(--surface-soft); }
    .column-picker-menu input { width:16px; height:16px; accent-color:var(--primary); }
    .standard-data-table-wrap { max-height:calc(100vh - 340px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .is-hidden-column { display:none !important; }
    .standard-data-table { min-width:980px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .standard-data-table th, .standard-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .standard-data-table th:last-child, .standard-data-table td:last-child { border-right:0; }
    .standard-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .standard-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .standard-filter-head input { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .standard-filter-head input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .standard-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .standard-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .standard-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:110px; }
    .standard-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .standard-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .standard-actions .icon-btn svg { width:17px; height:17px; }
    .standard-actions .action-view { color:#2563eb; }
    .standard-actions .action-edit { color:#f97316; }
    .standard-actions .action-delete { color:#dc2626; }
    .standard-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .standard-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .standard-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    @media (max-width:768px) {
        .standard-card-head, .standard-filter-row, .standard-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .standard-filter-group, .pager-actions, .module-actions { width:100%; flex-wrap:wrap; }
        .standard-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .standard-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .standard-data-table { min-width:920px; }
    }
</style>
<script>
    const endpoint = @json($apiEndpoint);
    const moduleTitle = @json($title);
    let meta = { key: 'id', columns: [], required: [] };
    let rows = [];
    let currentPage = 1;
    let lastPage = 1;
    let paginationMeta = { from: 0, to: 0, total: 0 };
    let searchTimer;
    let visibleColumns = [];
    let lookups = { roles: [] };
    let sortColumn = sessionStorage.getItem(`${endpoint}.sortColumn`) || '';
    let sortDirection = sessionStorage.getItem(`${endpoint}.sortDirection`) || 'asc';

    const labels = {
        tenant_id: 'Tenant',
        branch_id: 'Branch',
        role_id: 'Role',
        uom_id: 'UOM',
        material_type_id: 'Material Type',
        party_type: 'Party Type',
        item_type: 'Item Type',
        location_type: 'Location Type',
        status: 'Status',
    };

    function headline(value) {
        return labels[value] || value.replaceAll('_', ' ').replace(/\b\w/g, char => char.toUpperCase());
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function roleName(value) {
        const role = (lookups.roles || []).find(item => String(item.id) === String(value));
        return role?.name || value || '';
    }

    function moduleName(value) {
        const module = (lookups.modules || []).find(item => String(item.module_id) === String(value));
        if (!module) return value || '';
        return module.module_code ? `${module.module_code} - ${module.module_name}` : module.module_name;
    }

    function displayValue(row, column) {
        if (column === 'role_id') return escapeHtml(roleName(row[column]));
        if (column === 'parent_module_id') return escapeHtml(moduleName(row[column]));
        return escapeHtml(row[column] ?? '');
    }

    function formColumns(readonly = false) {
        const columns = meta.form_columns || meta.columns;
        return readonly ? columns.filter(column => column !== 'password') : columns;
    }

    function isRequired(column, mode = 'create') {
        return meta.required.includes(column) || (mode === 'create' && endpoint.endsWith('/users') && column === 'password');
    }

    function showModuleAlert(message, type = 'success') {
        if (window.ErpToast) {
            window.ErpToast.show(message, type);
            return;
        }

        const alert = document.getElementById('module-alert');
        alert.textContent = message;
        alert.className = `alert-panel alert-${type}`;
        alert.hidden = false;
        window.setTimeout(() => alert.hidden = true, 3200);
    }

    function renderHead() {
        document.getElementById('module-head').innerHTML = `
            <tr>
                ${meta.columns.map(column => `<th data-col="${column}"><button class="sort-head" type="button" data-sort="${column}">${headline(column)} <i data-lucide="chevrons-up-down"></i></button></th>`).join('')}
                <th class="text-end action-col">Actions</th>
            </tr>
            <tr class="standard-filter-head" id="standard-filter-head" hidden>
                ${meta.columns.map(column => `<th data-col="${column}"><input id="filter-${column}" data-column-filter="${column}" type="${fieldType(column)}" ${fieldType(column) === 'number' ? 'step="0.001" min="0"' : ''} placeholder="Search"></th>`).join('')}
                <th class="action-col"></th>
            </tr>
        `;
        const filterHead = document.getElementById('standard-filter-head');
        const filterRow = document.getElementById('standard-filter-row');
        if (filterHead && filterRow) filterHead.hidden = filterRow.hidden;
        renderSortHeaders();
        restoreColumnFilters();
        applyColumnVisibility();
    }

    function renderRows() {
        document.getElementById('module-body').innerHTML = rows.length ? rows.map(row => `
            <tr>
                ${meta.columns.map(column => `<td data-col="${column}">${displayValue(row, column)}</td>`).join('')}
                <td class="text-end action-col">
                    <span class="standard-actions">
                        <button class="icon-btn action-view" data-view="${row[meta.key]}" title="View"><i data-lucide="eye"></i></button>
                        <button class="icon-btn action-edit" data-edit="${row[meta.key]}" title="Edit"><i data-lucide="pencil"></i></button>
                        <button class="icon-btn action-delete" data-delete="${row[meta.key]}" title="Delete"><i data-lucide="trash-2"></i></button>
                    </span>
                </td>
            </tr>
        `).join('') : `<tr><td colspan="${meta.columns.length + 1}" class="text-muted">No ${moduleTitle.toLowerCase()} records found.</td></tr>`;
        applyColumnVisibility();
        if (window.lucide) window.lucide.createIcons();
    }

    function currentColumns() {
        const base = visibleColumns.length ? visibleColumns : meta.columns.slice(0, 6);
        return base.filter(column => meta.columns.includes(column));
    }

    function renderColumnMenu() {
        const menu = document.getElementById('column-menu');
        menu.innerHTML = meta.columns.map(column => `
            <label>
                <input type="checkbox" data-column-toggle="${column}" ${currentColumns().includes(column) ? 'checked' : ''}>
                <span>${headline(column)}</span>
            </label>
        `).join('');
    }

    function renderSortHeaders() {
        document.querySelectorAll('[data-sort]').forEach(button => {
            const active = button.dataset.sort === sortColumn;
            button.classList.toggle('is-active', active);
            const label = button.dataset.label || button.textContent.trim();
            button.dataset.label = label;
            button.innerHTML = `${label} <i data-lucide="${active ? (sortDirection === 'asc' ? 'arrow-up' : 'arrow-down') : 'chevrons-up-down'}"></i>`;
        });
        if (window.lucide) window.lucide.createIcons();
    }

    function pageRange() {
        const pages = [];
        if (lastPage <= 7) {
            for (let index = 1; index <= lastPage; index++) pages.push(index);
            return pages;
        }

        pages.push(1);
        let start = Math.max(2, currentPage - 1);
        let end = Math.min(lastPage - 1, currentPage + 1);
        if (currentPage <= 2) {
            start = 2;
            end = 3;
        } else if (currentPage >= lastPage - 1) {
            start = lastPage - 2;
            end = lastPage - 1;
        }
        if (start > 2) pages.push('...');
        for (let index = start; index <= end; index++) pages.push(index);
        if (end < lastPage - 1) pages.push('...');
        pages.push(lastPage);

        return pages;
    }

    function renderPager() {
        document.getElementById('page-links').innerHTML = pageRange().map(value => value === '...'
            ? '<span class="page-number-ellipsis">...</span>'
            : `<button class="btn-erp btn-page-number ${Number(value) === currentPage ? 'is-active' : ''}" type="button" data-page="${value}">${value}</button>`
        ).join('');
        document.getElementById('page-status').textContent = paginationMeta.total
            ? `Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`
            : 'Showing 0 to 0 of 0 records';
        document.querySelector('[data-action="prev-page"]').disabled = currentPage <= 1;
        document.querySelector('[data-action="next-page"]').disabled = currentPage >= lastPage;
        if (window.lucide) window.lucide.createIcons();
    }

    function applyColumnVisibility() {
        const visible = new Set(currentColumns());
        document.querySelectorAll('.standard-data-table [data-col]').forEach(cell => {
            cell.classList.toggle('is-hidden-column', !visible.has(cell.dataset.col));
        });
    }

    function columnFilterParams() {
        return meta.columns.reduce((params, column) => {
            const value = document.getElementById(`filter-${column}`)?.value;
            if (value) params[column] = value;
            return params;
        }, {});
    }

    function restoreColumnFilters() {
        meta.columns.forEach(column => {
            const input = document.getElementById(`filter-${column}`);
            if (input) input.value = sessionStorage.getItem(`${endpoint}.filter.${column}`) || '';
        });
    }

    function storeColumnFilters() {
        meta.columns.forEach(column => {
            const input = document.getElementById(`filter-${column}`);
            if (input) sessionStorage.setItem(`${endpoint}.filter.${column}`, input.value);
        });
        sessionStorage.setItem(`${endpoint}.sortColumn`, sortColumn);
        sessionStorage.setItem(`${endpoint}.sortDirection`, sortDirection);
    }

    function toggleFilterRow() {
        const row = document.getElementById('standard-filter-row');
        const head = document.getElementById('standard-filter-head');
        const button = document.querySelector('[data-action="toggle-filter-row"]');
        const open = row.hidden;
        row.hidden = !open;
        if (head) head.hidden = !open;
        button.classList.toggle('is-active', open);
        button.setAttribute('aria-pressed', open ? 'true' : 'false');
        button.title = open ? 'Hide filters' : 'Show filters';
    }

    function fieldType(column) {
        if (column === 'password') return 'password';
        if (column.includes('email')) return 'email';
        if (column.includes('mobile')) return 'tel';
        if (column.includes('rate') || column.includes('limit') || column.includes('qty') || column.includes('length') || column.includes('width') || column.includes('height') || column.includes('percent') || column.includes('stock')) return 'number';
        return 'text';
    }

    function fieldControl(column, readonly = false, currentId = null) {
        const disabled = readonly ? 'disabled' : '';
        const options = {
            status: ['Active', 'Inactive'],
            location_type: ['RM', 'WIP', 'FG', 'WASTAGE', 'SCRAP'],
        };

        if (column === 'role_id') {
            return `<select id="field-${column}" name="${column}" ${disabled}><option value="">Select ${headline(column)}</option>${(lookups.roles || []).map(role => `<option value="${escapeHtml(role.id)}">${escapeHtml(role.name)}</option>`).join('')}</select>`;
        }

        if (column === 'parent_module_id') {
            const modules = (lookups.modules || []).filter(module => String(module.module_id) !== String(currentId));
            return `<select id="field-${column}" name="${column}" ${disabled}><option value="">Select ${headline(column)}</option>${modules.map(module => `<option value="${escapeHtml(module.module_id)}">${escapeHtml(module.module_code ? `${module.module_code} - ${module.module_name}` : module.module_name)}</option>`).join('')}</select>`;
        }

        if (options[column]) {
            return `<select id="field-${column}" name="${column}" ${disabled}><option value="">Select ${headline(column)}</option>${options[column].map(option => `<option>${option}</option>`).join('')}</select>`;
        }

        const type = fieldType(column);
        return `<input id="field-${column}" name="${column}" type="${type}" ${type === 'number' ? 'step="0.001"' : ''} ${type === 'password' ? 'autocomplete="new-password"' : ''} ${disabled}>`;
    }

    function formHtml(mode = 'create', row = null) {
        const readonly = mode === 'view';
        return `
            <input type="hidden" id="record-id">
            <div id="form-fields" class="dynamic-form-grid">
                ${formColumns(readonly).map(column => `
            <div class="field">
                <i data-lucide="circle-dot"></i>
                <label>${headline(column)}${isRequired(column, mode) ? ' *' : ''}</label>
                ${fieldControl(column, readonly, row?.[meta.key] ?? null)}
            </div>
        `).join('')}
            </div>
            <div id="modal-validation" class="form-errors" hidden></div>
        `;
    }

    function openRecordModal(mode = 'create', row = null) {
        const readonly = mode === 'view';
        window.ErpModal.open({
            title: `${mode === 'create' ? 'New' : mode === 'edit' ? 'Edit' : 'View'} ${moduleTitle}`,
            subtitle: readonly ? 'Record details are shown without editing.' : 'Changes save through API validation without page reload.',
            body: formHtml(mode, row),
            footer: readonly ? `<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>` : `
                <button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button>
                <button class="btn-erp btn-primary" type="button" data-action="save-modal-record"><i data-lucide="save"></i> Save</button>
            `,
        });

        document.getElementById('record-id').value = row?.[meta.key] || '';
        if (row) {
            formColumns(readonly).forEach(column => {
                const input = document.getElementById(`field-${column}`);
                if (column === 'password') {
                    if (input) input.value = '';
                    return;
                }
                if (input) input.value = row[column] ?? '';
            });
        } else {
            const status = document.getElementById('field-status');
            if (status) status.value = 'Active';
        }
        if (window.lucide) window.lucide.createIcons();
    }

    async function loadRows(page = 1) {
        const response = await window.axios.get(endpoint, {
            params: {
                page,
                search: document.getElementById('module-search').value,
                per_page: document.getElementById('per-page').value,
                sort_by: sortColumn,
                sort_direction: sortDirection,
                ...columnFilterParams(),
            },
        });
        meta = response.data.meta;
        lookups = meta.lookups || { roles: [], modules: [] };
        rows = response.data.data.data || [];
        if (!visibleColumns.length) visibleColumns = meta.columns.slice(0, 6);
        storeColumnFilters();
        currentPage = response.data.data.current_page || 1;
        lastPage = response.data.data.last_page || 1;
        paginationMeta = {
            from: response.data.data.from || 0,
            to: response.data.data.to || 0,
            total: response.data.data.total || 0,
        };
        renderHead();
        renderRows();
        renderColumnMenu();
        renderPager();
    }

    function fillForm(id) {
        const row = rows.find(item => Number(item[meta.key]) === Number(id));
        if (!row) return;
        openRecordModal('edit', row);
    }

    function formPayload() {
        return formColumns(false).reduce((payload, column) => {
            const value = document.getElementById(`field-${column}`)?.value;
            if (value !== undefined && value !== '') payload[column] = value;
            return payload;
        }, {});
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadRows().catch(error => showModuleAlert(error.response?.data?.message || 'Unable to load records.', 'danger'));

        document.getElementById('module-search').addEventListener('input', function () {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => loadRows(1).catch(error => showModuleAlert(error.response?.data?.message || 'Search failed.', 'danger')), 250);
        });

        document.querySelector('[data-action="new-record"]').addEventListener('click', () => openRecordModal('create'));
        document.querySelector('[data-action="reload-records"]').addEventListener('click', () => loadRows(currentPage));
        document.querySelector('[data-action="apply-filters"]').addEventListener('click', () => loadRows(1));
        document.querySelector('[data-action="reset-filters"]').addEventListener('click', () => {
            document.getElementById('module-search').value = '';
            document.getElementById('per-page').value = '10';
            meta.columns.forEach(column => {
                const input = document.getElementById(`filter-${column}`);
                if (input) {
                    input.value = '';
                    sessionStorage.removeItem(`${endpoint}.filter.${column}`);
                }
            });
            sortColumn = '';
            sortDirection = 'asc';
            loadRows(1);
        });
        document.querySelector('[data-action="toggle-filter-row"]').addEventListener('click', toggleFilterRow);
        document.querySelector('[data-action="clear-search"]').addEventListener('click', () => {
            document.getElementById('module-search').value = '';
            loadRows(1).catch(error => showModuleAlert(error.normalizedMessage || 'Search failed.', 'danger'));
        });
        document.getElementById('per-page').addEventListener('change', () => loadRows(1));
        document.querySelector('[data-action="prev-page"]').addEventListener('click', () => currentPage > 1 && loadRows(currentPage - 1));
        document.querySelector('[data-action="next-page"]').addEventListener('click', () => currentPage < lastPage && loadRows(currentPage + 1));
        document.getElementById('page-links').addEventListener('click', event => {
            const target = event.target.closest('[data-page]');
            if (target) loadRows(Number(target.dataset.page));
        });
        document.querySelector('[data-action="export-csv"]').addEventListener('click', () => {
            const columns = currentColumns();
            const csv = [columns.map(headline).join(','), ...rows.map(row => columns.map(column => `"${String(row[column] ?? '').replaceAll('"', '""')}"`).join(','))].join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${moduleTitle.toLowerCase().replaceAll(' ', '-')}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        });
        document.querySelector('[data-action="print-grid"]').addEventListener('click', () => window.print());
        document.querySelector('[data-action="columns-menu"]').addEventListener('click', function () {
            const menu = document.getElementById('column-menu');
            menu.classList.toggle('is-open');
            this.setAttribute('aria-expanded', menu.classList.contains('is-open') ? 'true' : 'false');
        });
        document.getElementById('module-body').addEventListener('click', async function (event) {
            const viewId = event.target.closest('[data-view]')?.dataset.view;
            const editId = event.target.closest('[data-edit]')?.dataset.edit;
            const deleteId = event.target.closest('[data-delete]')?.dataset.delete;
            if (viewId) {
                const row = rows.find(item => Number(item[meta.key]) === Number(viewId));
                if (row) openRecordModal('view', row);
            }
            if (editId) fillForm(editId);
            if (deleteId) {
                window.ErpModal.open({
                    title: `Delete ${moduleTitle}`,
                    subtitle: 'This action cannot be undone.',
                    body: `<p>Delete this ${moduleTitle.toLowerCase()} record?</p>`,
                    footer: `
                        <button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button>
                        <button class="btn-erp btn-danger" type="button" data-confirm-delete="${deleteId}"><i data-lucide="trash-2"></i> Delete</button>
                    `,
                    size: 'sm',
                });
            }
        });

        document.getElementById('module-head').addEventListener('click', function (event) {
            const column = event.target.closest('[data-sort]')?.dataset.sort;
            if (!column) return;
            if (sortColumn === column) sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            loadRows(1);
        });

        document.getElementById('module-head').addEventListener('input', function (event) {
            const input = event.target.closest('[data-column-filter]');
            if (!input) return;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => loadRows(1).catch(error => showModuleAlert(error.normalizedMessage || 'Filter failed.', 'danger')), 300);
        });

        document.getElementById('column-menu').addEventListener('change', function (event) {
            const column = event.target.closest('[data-column-toggle]')?.dataset.columnToggle;
            if (!column) return;
            if (event.target.checked) visibleColumns.push(column);
            else visibleColumns = visibleColumns.filter(item => item !== column);
            applyColumnVisibility();
            renderColumnMenu();
        });

        document.addEventListener('click', async function (event) {
            const deleteId = event.target.closest('[data-confirm-delete]')?.dataset.confirmDelete;
            if (deleteId) {
                try {
                    await window.axios.delete(`${endpoint}/${deleteId}`);
                    window.ErpModal.close();
                    await loadRows(currentPage);
                    showModuleAlert(`${moduleTitle} deleted.`);
                } catch (error) {
                    showModuleAlert(error.normalizedMessage || 'Delete failed.', 'danger');
                }
            }

            if (event.target.closest('[data-action="save-modal-record"]')) {
            const id = document.getElementById('record-id').value;
            try {
                if (id) {
                    await window.axios.put(`${endpoint}/${id}`, formPayload());
                } else {
                    await window.axios.post(endpoint, formPayload());
                }
                window.ErpApi?.clearLookupCache?.();
                window.ErpModal.close();
                await loadRows(currentPage);
                showModuleAlert(`${moduleTitle} saved.`);
            } catch (error) {
                const box = document.getElementById('modal-validation');
                if (box) {
                    box.textContent = error.normalizedMessage || 'Save failed.';
                    box.hidden = false;
                } else {
                    showModuleAlert(error.normalizedMessage || 'Save failed.', 'danger');
                }
            }
            }
        });

    });
</script>
@endpush
@endif
