@extends('layouts.app')

@section('title', 'GRN - Timber Inventory')

@section('content')
    <section class="erp-card grn-list-card">
        <div class="grn-card-head" id="grn-card-head">
            <div>
                <h1>GRN Management</h1>
                <p class="text-muted">Manage Goods Receipt Notes efficiently</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-grns" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" type="button" data-action="new-grn" data-manage-only><i data-lucide="plus"></i> Add GRN</button>
            </div>
        </div>

        <section class="grn-editor-card" id="grn-editor-card" hidden>
            <div class="grn-editor-head">
                <div>
                    <h2 id="grn-editor-title">New GRN</h2>
                    <p class="text-muted" id="grn-editor-subtitle">Create or update a goods receipt without leaving the list page.</p>
                </div>
                <div class="module-actions">
                    <button class="btn-erp" type="button" data-action="close-grn-form"><i data-lucide="x"></i> Cancel</button>
                    <button class="btn-erp btn-primary" type="button" data-action="save-grn"><i data-lucide="save"></i> Save Draft</button>
                </div>
            </div>
            <div id="grn-editor-body"></div>
        </section>

        <div id="grn-list-content">
            <div class="grn-filter-row" id="grn-filter-row" hidden>
                <div class="grn-filter-group">
                    <label class="grn-filter-field">
                        <span>Supplier</span>
                        <select id="filter-party"></select>
                    </label>
                    <label class="grn-filter-field">
                        <span>Status</span>
                        <select id="filter-status">
                            <option value="">All Status</option>
                            <option value="Draft">Draft</option>
                            <option value="Posted">Posted</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </label>
                    <div class="grn-filter-actions">
                        <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                        <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                    </div>
                    <select id="filter-branch" hidden></select>
                </div>
            </div>

            <div class="table-toolbar grn-table-toolbar">
                <div class="data-grid-controls">
                    <select id="per-page">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="column-picker">
                        <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                        <div class="column-picker-menu" id="column-picker-menu"></div>
                    </div>
                    <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="grn-filter-row grn-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
                </div>
                <div class="module-search">
                    <i data-lucide="search"></i>
                    <input id="grn-search" type="search" placeholder="Search GRN, supplier, status">
                    <button class="search-clear" type="button" data-action="clear-grn-search" title="Clear search"><i data-lucide="x"></i></button>
                </div>
            </div>

            <div class="table-responsive grn-data-table-wrap">
                <table class="table grn-data-table">
                    <thead>
                        <tr>
                            <th data-col="grn_no"><button class="sort-head" data-sort="grn_no" type="button">GRN Number <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="grn_date"><button class="sort-head" data-sort="grn_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="supplier"><button class="sort-head" data-sort="supplier_name" type="button">Supplier <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="location"><button class="sort-head" data-sort="location" type="button">Location <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="item_count" class="text-end"><button class="sort-head sort-end" data-sort="item_count" type="button">Item Count <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="total_qty" class="text-end"><button class="sort-head sort-end" data-sort="total_qty" type="button">Total Qty <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="total_amount" class="text-end"><button class="sort-head sort-end" data-sort="total_amount" type="button">Total Amount <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="status"><button class="sort-head" data-sort="status" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="created_by"><button class="sort-head" data-sort="created_by" type="button">Created By <i data-lucide="chevrons-up-down"></i></button></th>
                            <th data-col="created_at"><button class="sort-head" data-sort="created_at" type="button">Created Date <i data-lucide="chevrons-up-down"></i></button></th>
                            <th class="text-end action-col">Actions</th>
                        </tr>
                        <tr class="grn-filter-head" id="grn-filter-head" hidden>
                            <th data-col="grn_no"><input id="col-grn-no" type="search" placeholder="Search"></th>
                            <th data-col="grn_date"><input id="col-grn-date" type="date"></th>
                            <th data-col="supplier"><select id="col-party"></select></th>
                            <th data-col="location"><select id="col-location"></select></th>
                            <th data-col="item_count"></th>
                            <th data-col="total_qty"></th>
                            <th data-col="total_amount"><div class="amount-filter"><input id="col-amount-min" type="number" min="0" step="0.01" placeholder="Min"><input id="col-amount-max" type="number" min="0" step="0.01" placeholder="Max"></div></th>
                            <th data-col="status"><select id="col-status"><option value="">All</option><option>Draft</option><option>Posted</option><option>Cancelled</option><option>Pending</option></select></th>
                            <th data-col="created_by"><input id="col-created-by" type="search" placeholder="Search"></th>
                            <th data-col="created_at"></th>
                            <th class="action-col"></th>
                        </tr>
                    </thead>
                    <tbody id="grn-body">
                        <tr class="skeleton-row"><td colspan="11"></td></tr>
                        <tr class="skeleton-row"><td colspan="11"></td></tr>
                        <tr class="skeleton-row"><td colspan="11"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-row">
                <div class="pager-actions">
                    <button class="btn-erp btn-page-nav" data-action="prev-page" type="button" title="Previous"><i data-lucide="chevron-left"></i></button>
                    <span id="grn-page-links" class="page-number-list"></span>
                    <button class="btn-erp btn-page-nav" data-action="next-page" type="button" title="Next"><i data-lucide="chevron-right"></i></button>
                    <span id="grn-page-status" class="page-record-status text-muted"></span>
                </div>
                <div class="module-actions">
                    <button class="btn-erp" type="button" data-action="export-csv"><i data-lucide="file-spreadsheet"></i> Export CSV</button>
                    <button class="btn-erp" type="button" data-action="print-list"><i data-lucide="printer"></i> Print</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<style>
    .grn-list-card {
        overflow: visible;
        border-radius: 10px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .1), 0 22px 46px rgba(15, 23, 42, .07);
    }
    .grn-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 20px 16px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface));
    }
    .grn-card-head h1 { margin: 0; font-size: 22px; line-height: 1.2; font-weight: 800; }
    .grn-card-head p { margin: 5px 0 0; }
    .grn-editor-card {
        display: grid;
        gap: 16px;
        margin: 0 0 16px;
        padding: 20px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
    }
    .grn-editor-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }
    .grn-editor-head h2 { margin: 0; font-size: 18px; font-weight: 800; }
    .grn-editor-head p { margin: 4px 0 0; }
    .grn-filter-row {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }
    .grn-filter-group {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 10px;
        flex: 1 1 auto;
        flex-wrap: wrap;
        max-width: 1320px;
    }
    .grn-filter-field {
        display: grid;
        gap: 5px;
        flex: 1 1 170px;
        min-width: 150px;
        max-width: 220px;
        margin: 0;
    }
    .grn-filter-field span {
        color: var(--muted);
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        text-transform: uppercase;
    }
    .grn-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }
    .grn-filter-group select {
        width: 100%;
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface-soft);
        color: var(--text);
        font-weight: 700;
    }
    .filter-placeholder {
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        padding: 0 12px;
        border: 1px dashed var(--border);
        border-radius: var(--radius);
        color: var(--muted);
        font-weight: 800;
    }
    .btn-filter {
        min-height: 36px;
        padding: 7px 12px;
        font-size: 13px;
        line-height: 1;
    }
    .btn-filter svg { width: 16px; height: 16px; }
    .btn-filter-secondary {
        background: var(--surface-soft);
        color: var(--muted);
    }
    .grn-table-toolbar { padding: 16px 20px; }
    .grn-table-toolbar .module-search { width: clamp(180px, 22vw, 260px); }
    .grn-table-toolbar .module-search > svg { color: #cbd5e1; stroke: #cbd5e1; }
    .grn-table-toolbar .module-search input { padding-right: 38px; }
    .column-picker { position: relative; }
    .btn-column-picker {
        min-height: 38px;
        padding: 8px 11px;
    }
    .btn-column-picker svg { width: 16px; height: 16px; }
    .btn-filter-toggle {
        min-height: 38px;
        padding: 8px 11px;
        color: var(--text);
        background: #fff;
    }
    .btn-filter-toggle svg { width: 16px; height: 16px; }
    .btn-filter-toggle.is-active {
        color: var(--primary);
        border-color: color-mix(in srgb, var(--primary) 42%, var(--border));
        background: color-mix(in srgb, var(--primary) 10%, var(--surface));
    }
    .column-picker-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 80;
        display: none;
        min-width: 220px;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        box-shadow: var(--shadow);
    }
    .column-picker.is-open .column-picker-menu {
        display: grid;
        gap: 4px;
    }
    .column-picker-menu label {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 6px 8px;
        border-radius: 6px;
        color: var(--text);
        font-weight: 800;
    }
    .column-picker-menu label:hover { background: var(--surface-soft); }
    .column-picker-menu input { width: 16px; height: 16px; accent-color: var(--primary); }
    .is-hidden-column { display: none !important; }
    .grn-data-table-wrap {
        max-height: calc(100vh - 360px);
        min-height: 320px;
        margin: 0 20px 16px;
        overflow: auto;
    }
    .grn-data-table {
        min-width: 1320px;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
    }
    .grn-data-table th,
    .grn-data-table td {
        border-right: 1px solid color-mix(in srgb, var(--border) 54%, transparent);
        border-bottom: 1px solid color-mix(in srgb, var(--border) 54%, transparent);
    }
    .grn-data-table th:last-child,
    .grn-data-table td:last-child {
        border-right: 0;
    }
    .grn-data-table th {
        top: 0;
        z-index: 4;
        height: 44px;
        padding: 0 12px;
        vertical-align: middle;
        white-space: nowrap;
        letter-spacing: 0;
    }
    .grn-filter-head th {
        top: 44px;
        z-index: 3;
        height: 46px;
        padding: 6px 10px;
        background: color-mix(in srgb, var(--surface-soft) 74%, var(--surface));
    }
    .grn-filter-head input, .grn-filter-head select {
        width: 100%;
        min-height: 32px;
        padding: 0 8px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--surface);
        color: var(--text);
        font-size: 12px;
        outline: none;
    }
    .grn-filter-head input:focus, .grn-filter-head select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .1);
    }
    .amount-filter {
        display: grid;
        grid-template-columns: minmax(70px, 1fr) minmax(70px, 1fr);
        gap: 6px;
    }
    .grn-data-table td {
        height: 54px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .grn-data-table tbody tr {
        transition: background .16s ease, box-shadow .16s ease;
    }
    .grn-data-table tbody tr:hover {
        box-shadow: inset 3px 0 0 var(--primary);
    }
    .sort-head {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        min-height: 34px;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
    }
    .sort-head svg { width: 15px; height: 15px; opacity: .7; }
    .sort-head.sort-end { justify-content: flex-end; }
    .sort-head.is-active { color: var(--primary); }
    .sort-head.is-active svg { opacity: 1; }
    .grn-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
        min-width: 178px;
    }
    .grn-actions .icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        background: color-mix(in srgb, currentColor 10%, var(--surface));
        border-color: color-mix(in srgb, currentColor 26%, var(--border));
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }
    .grn-actions .icon-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(15, 23, 42, .12);
    }
    .grn-actions .icon-btn svg { width: 17px; height: 17px; }
    .grn-actions .action-view { color: #2563eb; }
    .grn-actions .action-edit { color: #f97316; }
    .grn-actions .action-post { color: #16a34a; }
    .grn-actions .action-print { color: #7c3aed; }
    .grn-actions .action-delete { color: #dc2626; }
    .grn-list-card .pagination-row {
        justify-content: space-between;
        padding-inline: 20px;
    }
    .grn-data-table .action-col {
        position: static;
        right: auto;
        z-index: auto;
        min-width: 196px;
        width: 196px;
        box-shadow: none;
    }
    .grn-list-card .pager-actions {
        justify-content: flex-start;
        margin-left: 0;
    }
    .grn-list-card .pager-actions .btn-erp {
        min-height: 36px;
        padding: 7px 10px;
    }
    .grn-list-card .btn-page-nav {
        width: 36px;
        min-width: 36px;
        padding: 0;
        justify-content: center;
    }
    .grn-list-card .page-number-list {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    .grn-list-card .btn-page-number {
        min-width: 34px;
        min-height: 34px;
        padding: 0 9px;
        justify-content: center;
    }
    .grn-list-card .btn-page-number.is-active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }
    .grn-list-card .page-number-ellipsis {
        padding: 0 4px;
        color: var(--muted);
        font-weight: 800;
    }
    .grn-list-card .page-record-status {
        margin-left: 8px;
        font-weight: 700;
    }
    .badge-muted { background: #e5e7eb; color: #4b5563; }
    .badge-pending { background: #ffedd5; color: #ea580c; }
    .grn-modal-body { display: grid; gap: 14px; padding-bottom: 86px; }
    .grn-header-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .grn-remarks { grid-column: span 2; }
    .grn-branch-field.is-hidden { display: none; }
    .grn-line-wrap { margin: 0; }
    .grn-line-wrap .table { min-width: 820px; }
    .line-input, .line-select {
        width: 100%;
        min-height: 38px;
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        color: var(--text);
    }
    .line-number { text-align: right; }
    .grn-summary-sticky {
        position: sticky;
        bottom: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 16px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        box-shadow: var(--shadow-soft);
    }
    .grn-summary-sticky span { color: var(--muted); font-weight: 800; text-transform: uppercase; font-size: 12px; }
    .grn-summary-sticky strong { font-size: 22px; }
    .grn-view-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .grn-view-fact { padding: 10px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface-soft); }
    .grn-view-fact span { display: block; color: var(--muted); font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .grn-view-fact strong { display: block; margin-top: 4px; }
    @media (max-width: 1200px) { .grn-header-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .grn-remarks { grid-column: span 2; } }
    @media (max-width: 860px) { .grn-header-grid, .grn-view-grid { grid-template-columns: 1fr; } .grn-remarks { grid-column: span 1; } }
    @media (max-width: 768px) {
        .grn-card-head, .grn-filter-row, .grn-table-toolbar, .pagination-row {
            align-items: stretch;
            flex-direction: column;
        }
        .grn-filter-group, .grn-filter-actions, .pager-actions, .module-actions {
            width: 100%;
            flex: 1 1 auto;
            flex-wrap: wrap;
        }
        .grn-filter-field {
            flex: 1 1 100%;
            max-width: none;
        }
        .grn-filter-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .grn-filter-group select, .filter-placeholder, .grn-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp {
            width: 100%;
        }
        .grn-table-toolbar .module-search { width: 100%; }
        .grn-data-table-wrap {
            max-height: none;
            min-height: 0;
            margin-inline: 12px;
        }
        .grn-data-table { min-width: 1180px; }
        .grn-actions .icon-btn { width: 34px; height: 34px; }
    }
</style>
<script>
    const grnEndpoint = '/v1/grns';
    const authUser = JSON.parse(localStorage.getItem('user') || '{}');
    const permissions = authUser.permissions || [];
    const canManageGrn = permissions.includes('purchase-grn.manage') || authUser.role_name === 'Super Admin';
    const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 2 });
    const columnOptions = [
        ['grn_no', 'GRN Number'],
        ['grn_date', 'Date'],
        ['supplier', 'Supplier'],
        ['location', 'Location'],
        ['item_count', 'Item Count'],
        ['total_qty', 'Total Qty'],
        ['total_amount', 'Total Amount'],
        ['status', 'Status'],
        ['created_by', 'Created By'],
        ['created_at', 'Created Date'],
    ];
    const columnStorageKey = 'grn.visibleColumns';
    const defaultVisibleColumns = ['grn_no', 'grn_date', 'supplier', 'status', 'total_amount'];

    let grns = [];
    let currentPage = Number(sessionStorage.getItem('grn.page') || 1);
    let lastPage = 1;
    let paginationMeta = { from: 0, to: 0, total: 0 };
    let sortBy = sessionStorage.getItem('grn.sortBy') || 'grn_date';
    let sortDirection = sessionStorage.getItem('grn.sortDirection') || 'desc';
    let searchTimer;
    let lookups = { parties: [], branches: [], items: [], uoms: [], locations: [] };
    let branchMode = { show: true, value: '' };
    let lines = [];
    let activeId = null;
    let activeMode = 'create';
    let visibleColumns = readVisibleColumns();

    function readVisibleColumns() {
        try {
            const stored = JSON.parse(localStorage.getItem(columnStorageKey) || 'null');
            if (Array.isArray(stored) && stored.length) {
                return stored.map(column => column === 'party' ? 'supplier' : column);
            }
        } catch {}

        return defaultVisibleColumns;
    }

    function toast(message, type = 'success') {
        window.ErpToast ? window.ErpToast.show(message, type) : alert(message);
    }

    function badge(status) {
        const map = { Draft: 'badge-muted', Posted: 'badge-success', Cancelled: 'badge-danger', Pending: 'badge-pending' };
        return `<span class="badge ${map[status] || 'badge-primary'}">${status}</span>`;
    }

    function dateOnly(value) {
        if (!value) return '';
        return String(value).slice(0, 10);
    }

    function optionList(rows, key, label, placeholder) {
        return `<option value="">${placeholder}</option>` + rows.map(row => `<option value="${row[key]}">${row[label] || row[key]}</option>`).join('');
    }

    async function loadLookup(endpoint) {
        if (window.ErpApi?.cachedList) return window.ErpApi.cachedList(endpoint);
        const response = await window.axios.get(endpoint, { params: { per_page: 100 } });
        return response.data.data.data || [];
    }

    async function loadLookups() {
        const [parties, branches, items, uoms, locations] = await Promise.all([
            loadLookup('/v1/parties'),
            loadLookup('/v1/branches'),
            loadLookup('/v1/items'),
            loadLookup('/v1/uoms'),
            loadLookup('/v1/locations'),
        ]);

        lookups = {
            parties: parties.filter(row => ['Supplier', 'Both'].includes(row.party_type)),
            branches,
            items,
            uoms,
            locations,
        };

        branchMode = authUser.branch_id
            ? { show: false, value: String(authUser.branch_id) }
            : branches.length === 1
                ? { show: false, value: String(branches[0].branch_id) }
                : { show: true, value: '' };

        document.getElementById('filter-branch').innerHTML = optionList(branches, 'branch_id', 'branch_name', 'All Branches');
        document.getElementById('filter-party').innerHTML = optionList(lookups.parties, 'party_id', 'party_name', 'All Suppliers');
        document.getElementById('col-party').innerHTML = optionList(lookups.parties, 'party_id', 'party_name', 'All');
        document.getElementById('col-location').innerHTML = optionList(locations, 'location_id', 'location_name', 'All');
        if (!branchMode.show) {
            document.getElementById('filter-branch').value = branchMode.value;
            document.getElementById('filter-branch').hidden = true;
        }
    }

    function storeState() {
        sessionStorage.setItem('grn.page', currentPage);
        sessionStorage.setItem('grn.search', document.getElementById('grn-search').value);
        sessionStorage.setItem('grn.status', document.getElementById('filter-status').value);
        sessionStorage.setItem('grn.party', document.getElementById('filter-party').value);
        sessionStorage.setItem('grn.branch', document.getElementById('filter-branch').value);
        sessionStorage.setItem('grn.perPage', document.getElementById('per-page').value);
        sessionStorage.setItem('grn.sortBy', sortBy);
        sessionStorage.setItem('grn.sortDirection', sortDirection);
        ['col-grn-no', 'col-grn-date', 'col-party', 'col-location', 'col-status', 'col-amount-min', 'col-amount-max', 'col-created-by'].forEach(id => {
            sessionStorage.setItem(`grn.${id}`, document.getElementById(id)?.value || '');
        });
    }

    function restoreState() {
        document.getElementById('grn-search').value = sessionStorage.getItem('grn.search') || '';
        document.getElementById('filter-status').value = sessionStorage.getItem('grn.status') || '';
        document.getElementById('filter-party').value = sessionStorage.getItem('grn.party') || '';
        document.getElementById('filter-branch').value = sessionStorage.getItem('grn.branch') || document.getElementById('filter-branch').value;
        document.getElementById('per-page').value = sessionStorage.getItem('grn.perPage') || '10';
        ['col-grn-no', 'col-grn-date', 'col-party', 'col-location', 'col-status', 'col-amount-min', 'col-amount-max', 'col-created-by'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = sessionStorage.getItem(`grn.${id}`) || '';
        });
    }

    async function loadGrns(page = currentPage) {
        storeState();
        document.getElementById('grn-body').innerHTML = '<tr class="skeleton-row"><td colspan="11"></td></tr><tr class="skeleton-row"><td colspan="11"></td></tr><tr class="skeleton-row"><td colspan="11"></td></tr>';
        const response = await window.axios.get(grnEndpoint, {
            params: {
                page,
                search: document.getElementById('grn-search').value,
                branch_id: branchMode.show ? document.getElementById('filter-branch').value : branchMode.value,
                per_page: document.getElementById('per-page').value,
                sort_by: sortBy,
                sort_direction: sortDirection,
                grn_no: document.getElementById('col-grn-no').value,
                grn_date: document.getElementById('col-grn-date').value,
                location_id: document.getElementById('col-location').value,
                supplier_id: document.getElementById('col-party').value || document.getElementById('filter-party').value,
                status: document.getElementById('col-status').value || document.getElementById('filter-status').value,
                amount_min: document.getElementById('col-amount-min').value,
                amount_max: document.getElementById('col-amount-max').value,
                created_by: document.getElementById('col-created-by').value,
            },
        });
        grns = response.data.data.data || [];
        currentPage = response.data.data.current_page || 1;
        lastPage = response.data.data.last_page || 1;
        paginationMeta = {
            from: response.data.data.from || 0,
            to: response.data.data.to || 0,
            total: response.data.data.total || 0,
        };
        renderRows();
        renderSortHeaders();
        renderPager();
    }

    function pageRange() {
        const pages = [];
        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) pages.push(i);
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
        for (let i = start; i <= end; i++) pages.push(i);
        if (end < lastPage - 1) pages.push('...');
        pages.push(lastPage);
        return pages;
    }

    function renderPager() {
        document.getElementById('grn-page-links').innerHTML = pageRange().map(value => value === '...'
            ? '<span class="page-number-ellipsis">...</span>'
            : `<button class="btn-erp btn-page-number ${Number(value) === currentPage ? 'is-active' : ''}" type="button" data-page="${value}">${value}</button>`
        ).join('');
        document.getElementById('grn-page-status').textContent = paginationMeta.total
            ? `Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`
            : 'Showing 0 to 0 of 0 records';
        document.querySelector('[data-action="prev-page"]').disabled = currentPage <= 1;
        document.querySelector('[data-action="next-page"]').disabled = currentPage >= lastPage;
        lucide?.createIcons();
    }

    function renderRows() {
        document.getElementById('grn-body').innerHTML = grns.length ? grns.map(row => `
            <tr>
                <td data-col="grn_no"><strong>${row.grn_no}</strong></td>
                <td data-col="grn_date">${row.grn_date}</td>
                <td data-col="supplier">${row.supplier_name || row.party_name || ''}</td>
                <td data-col="location">${row.location_names || row.location_name || ''}</td>
                <td data-col="item_count" class="text-end">${row.item_count || 0}</td>
                <td data-col="total_qty" class="text-end">${Number(row.total_qty || 0).toLocaleString('en-IN')}</td>
                <td data-col="total_amount" class="text-end">${money.format(Number(row.total_amount || 0))}</td>
                <td data-col="status">${badge(row.status)}</td>
                <td data-col="created_by">${row.created_by_name || ''}</td>
                <td data-col="created_at">${dateOnly(row.created_at)}</td>
                <td class="text-end action-col">
                    <span class="grn-actions">
                        <button class="icon-btn action-view" data-view="${row.grn_id}" title="View" aria-label="View"><i data-lucide="eye"></i></button>
                        ${row.status === 'Draft' && canManageGrn ? `<button class="icon-btn action-edit" data-edit="${row.grn_id}" title="Edit" aria-label="Edit"><i data-lucide="pencil"></i></button>` : ''}
                        ${row.status === 'Draft' && canManageGrn ? `<button class="icon-btn action-post" data-post="${row.grn_id}" title="Post" aria-label="Post"><i data-lucide="check"></i></button>` : ''}
                        <button class="icon-btn action-print" data-print="${row.grn_id}" title="Print" aria-label="Print"><i data-lucide="printer"></i></button>
                        ${row.status === 'Draft' && canManageGrn ? `<button class="icon-btn action-delete" data-delete="${row.grn_id}" title="Delete" aria-label="Delete"><i data-lucide="trash-2"></i></button>` : ''}
                    </span>
                </td>
            </tr>
        `).join('') : '<tr><td colspan="11" class="text-muted">No GRN records found.</td></tr>';
        applyColumnVisibility();
        if (window.lucide) window.lucide.createIcons();
    }

    function renderSortHeaders() {
        document.querySelectorAll('[data-sort]').forEach(button => {
            const active = button.dataset.sort === sortBy;
            button.classList.toggle('is-active', active);
            const label = button.dataset.label || button.textContent.trim();
            button.dataset.label = label;
            button.innerHTML = `${label} <i data-lucide="${active ? (sortDirection === 'asc' ? 'arrow-up' : 'arrow-down') : 'chevrons-up-down'}"></i>`;
        });
        if (window.lucide) window.lucide.createIcons();
    }

    function resetFilters() {
        document.getElementById('grn-search').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-party').value = '';
        document.getElementById('filter-branch').value = branchMode.show ? '' : branchMode.value;
        document.getElementById('per-page').value = '10';
        ['col-grn-no', 'col-grn-date', 'col-party', 'col-location', 'col-status', 'col-amount-min', 'col-amount-max', 'col-created-by'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
        sortBy = 'grn_date';
        sortDirection = 'desc';
        loadGrns(1);
    }

    function toggleFilterRow() {
        const row = document.getElementById('grn-filter-row');
        const head = document.getElementById('grn-filter-head');
        const button = document.querySelector('[data-action="toggle-filter-row"]');
        const open = row.hidden;
        row.hidden = !open;
        if (head) head.hidden = !open;
        button.classList.toggle('is-active', open);
        button.setAttribute('aria-pressed', open ? 'true' : 'false');
        button.title = open ? 'Hide filters' : 'Show filters';
    }

    function renderColumnPicker() {
        const menu = document.getElementById('column-picker-menu');
        menu.innerHTML = columnOptions.map(([key, label]) => `
            <label>
                <input type="checkbox" value="${key}" ${visibleColumns.includes(key) ? 'checked' : ''}>
                <span>${label}</span>
            </label>
        `).join('');
    }

    function applyColumnVisibility() {
        const visible = new Set(visibleColumns);
        document.querySelectorAll('.grn-data-table [data-col]').forEach(cell => {
            cell.classList.toggle('is-hidden-column', !visible.has(cell.dataset.col));
        });
    }

    function updateColumnVisibility(column, checked) {
        if (checked) {
            visibleColumns = [...new Set([...visibleColumns, column])];
        } else {
            visibleColumns = visibleColumns.filter(item => item !== column);
        }

        localStorage.setItem(columnStorageKey, JSON.stringify(visibleColumns));
        applyColumnVisibility();
    }

    function nextLine(row = {}) {
        return {
            item_id: row.item_id || '',
            uom_id: row.uom_id || '',
            location_id: row.location_id || '',
            qty: row.qty || row.accepted_qty || '',
            rate: row.rate ?? '',
        };
    }

    function lineTotal(line) {
        return Number(line.qty || 0) * Number(line.rate || 0);
    }

    function totalAmount() {
        return lines.reduce((sum, line) => sum + lineTotal(line), 0);
    }

    function grnFormHtml(readonly = false) {
        return `
            <div class="grn-modal-body">
                <div class="grn-header-grid">
                    <div class="field">
                        <i data-lucide="hash"></i>
                        <label>GRN Number</label>
                        <input id="modal-grn-no" type="text" readonly>
                    </div>
                    <div class="field">
                        <i data-lucide="calendar"></i>
                        <label>GRN Date *</label>
                        <input id="modal-grn-date" type="date" ${readonly ? 'disabled' : ''}>
                    </div>
                    <div class="field">
                        <i data-lucide="users"></i>
                        <label>Supplier *</label>
                        <select id="modal-party" ${readonly ? 'disabled' : ''}>${optionList(lookups.parties, 'party_id', 'party_name', 'Select supplier')}</select>
                    </div>
                    <div class="field grn-branch-field ${branchMode.show ? '' : 'is-hidden'}">
                        <i data-lucide="building-2"></i>
                        <label>Branch *</label>
                        <select id="modal-branch" ${readonly ? 'disabled' : ''}>${optionList(lookups.branches, 'branch_id', 'branch_name', 'Select branch')}</select>
                    </div>
                    <div class="field grn-remarks">
                        <i data-lucide="message-square"></i>
                        <label>Remarks</label>
                        <input id="modal-remarks" type="text" ${readonly ? 'disabled' : ''}>
                    </div>
                </div>
                <div class="table-toolbar" style="padding:0;">
                    <div>
                        <h3 class="erp-card-title">Item Details</h3>
                        <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Item, UOM, location, quantity, and rate.</p>
                    </div>
                    ${readonly ? '' : '<button class="btn-erp" type="button" data-action="add-grn-row"><i data-lucide="plus"></i> Add Row</button>'}
                </div>
                <div class="table-responsive grn-line-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>UOM</th>
                                <th>Location</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="modal-lines"></tbody>
                    </table>
                </div>
                <div id="modal-grn-errors" class="form-errors" hidden></div>
                <div class="grn-summary-sticky"><span>Total Amount</span><strong id="modal-total-amount">₹0.00</strong></div>
            </div>
        `;
    }

    function renderModalLines(readonly = false) {
        document.getElementById('modal-lines').innerHTML = lines.map((line, index) => `
            <tr>
                <td><select class="line-select" data-line="${index}" data-field="item_id" ${readonly ? 'disabled' : ''}>${optionList(lookups.items, 'item_id', 'item_name', 'Search item')}</select></td>
                <td><select class="line-select" data-line="${index}" data-field="uom_id" ${readonly ? 'disabled' : ''}>${optionList(lookups.uoms, 'uom_id', 'uom_name', 'UOM')}</select></td>
                <td><select class="line-select" data-line="${index}" data-field="location_id" ${readonly ? 'disabled' : ''}>${optionList(lookups.locations, 'location_id', 'location_name', 'Select location')}</select></td>
                <td><input class="line-input line-number" data-line="${index}" data-field="qty" type="number" min="0" step="0.001" value="${line.qty}" ${readonly ? 'disabled' : ''}></td>
                <td><input class="line-input line-number" data-line="${index}" data-field="rate" type="number" min="0" step="0.01" value="${line.rate}" ${readonly ? 'disabled' : ''}></td>
                <td class="text-end">${readonly ? '' : `<button class="icon-btn" type="button" data-remove-line="${index}" title="Remove" ${lines.length === 1 ? 'disabled' : ''}><i data-lucide="trash-2"></i></button>`}</td>
            </tr>
        `).join('');
        lines.forEach((line, index) => ['item_id', 'uom_id', 'location_id'].forEach(field => {
            const input = document.querySelector(`[data-line="${index}"][data-field="${field}"]`);
            if (input) input.value = line[field] || '';
        }));
        document.getElementById('modal-total-amount').textContent = money.format(totalAmount());
        if (window.lucide) window.lucide.createIcons();
    }

    async function previewNumber(branchId = branchMode.value) {
        const response = await window.axios.get(`${grnEndpoint}/next-number`, { params: { branch_id: branchId } });
        return response.data.data.grn_no;
    }

    function showGrnEditor(mode) {
        activeMode = mode;
        const editor = document.getElementById('grn-editor-card');
        const cardHead = document.getElementById('grn-card-head');
        const listContent = document.getElementById('grn-list-content');
        document.getElementById('grn-editor-title').textContent = mode === 'create' ? 'New GRN' : 'Edit GRN';
        document.getElementById('grn-editor-subtitle').textContent = mode === 'create'
            ? 'Create a new goods receipt note and stay on the page.'
            : 'Update the draft goods receipt note and keep the list visible.';
        document.getElementById('grn-editor-body').innerHTML = grnFormHtml(false);
        if (cardHead) cardHead.hidden = true;
        if (listContent) listContent.hidden = true;
        editor.hidden = false;
        editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (window.lucide) window.lucide.createIcons();
    }

    function hideGrnEditor() {
        const cardHead = document.getElementById('grn-card-head');
        const listContent = document.getElementById('grn-list-content');
        document.getElementById('grn-editor-card').hidden = true;
        document.getElementById('grn-editor-body').innerHTML = '';
        if (cardHead) cardHead.hidden = false;
        if (listContent) listContent.hidden = false;
        activeId = null;
        activeMode = 'create';
    }

    async function loadGrnEditorForCreate() {
        showGrnEditor('create');
        lines = [nextLine()];
        if (branchMode.show) {
            document.getElementById('modal-branch').addEventListener('change', async function () {
                document.getElementById('modal-grn-no').value = await previewNumber(this.value);
            });
            if (lookups.branches.length) {
                document.getElementById('modal-branch').value = lookups.branches[0].branch_id;
            }
        }
        document.getElementById('modal-grn-date').valueAsDate = new Date();
        document.getElementById('modal-grn-no').value = await previewNumber(branchMode.show ? document.getElementById('modal-branch').value : branchMode.value);
        renderModalLines(false);
    }

    async function loadGrnEditorForEdit(id) {
        activeId = id;
        showGrnEditor('edit');
        const response = await window.axios.get(`${grnEndpoint}/${id}`);
        fillGrnModal(response.data.data, false);
    }

    function fillGrnModal(grn, readonly = false) {
        document.getElementById('modal-grn-no').value = grn.grn_no || '';
        document.getElementById('modal-grn-date').value = grn.grn_date || '';
        document.getElementById('modal-party').value = grn.supplier_id || '';
        if (branchMode.show && document.getElementById('modal-branch')) document.getElementById('modal-branch').value = grn.branch_id || '';
        document.getElementById('modal-remarks').value = grn.remarks || '';
        lines = (grn.details || []).map(nextLine);
        if (!lines.length) lines = [nextLine()];
        renderModalLines(readonly);
    }

    async function openGrnModal(mode, id = null) {
        if (mode === 'create') {
            await loadGrnEditorForCreate();
            return;
        }

        if (mode === 'edit') {
            await loadGrnEditorForEdit(id);
            return;
        }

        hideGrnEditor();
        activeMode = mode;
        activeId = id;
        const readonly = mode === 'view';

        window.ErpModal.open({
            title: mode === 'create' ? 'New GRN' : mode === 'edit' ? 'Edit GRN' : 'View GRN',
            subtitle: 'Compact goods receipt entry',
            size: 'xl',
            body: grnFormHtml(readonly),
            footer: readonly
                ? '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>'
                : '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-grn"><i data-lucide="save"></i> Save</button>',
        });

        const response = await window.axios.get(`${grnEndpoint}/${id}`);
        fillGrnModal(response.data.data, readonly);
    }

    function modalPayload() {
        const branchId = branchMode.show ? document.getElementById('modal-branch').value : branchMode.value;
        return {
            branch_id: branchId,
            supplier_id: document.getElementById('modal-party').value,
            grn_date: document.getElementById('modal-grn-date').value,
            remarks: document.getElementById('modal-remarks').value,
            warehouse_location_id: lines[0]?.location_id || null,
            details: lines.map(line => ({
                item_id: line.item_id,
                uom_id: line.uom_id,
                location_id: line.location_id,
                qty: line.qty,
                rate: line.rate,
            })),
        };
    }

    async function saveModalGrn() {
        const box = document.getElementById('modal-grn-errors');
        box.hidden = true;
        try {
            if (activeMode === 'edit') await window.axios.put(`${grnEndpoint}/${activeId}`, modalPayload());
            else await window.axios.post(grnEndpoint, modalPayload());
            window.ErpApi?.clearLookupCache?.();
            hideGrnEditor();
            window.ErpModal.close?.();
            await loadGrns(currentPage);
            toast('GRN saved.');
        } catch (error) {
            box.textContent = error.normalizedMessage || 'Unable to save GRN.';
            box.hidden = false;
        }
    }

    function csvExport() {
        const valueMap = {
            grn_no: row => row.grn_no,
            grn_date: row => row.grn_date,
            supplier: row => row.supplier_name || row.party_name || '',
            location: row => row.location_names || row.location_name || '',
            item_count: row => row.item_count || 0,
            total_qty: row => row.total_qty || 0,
            total_amount: row => row.total_amount || 0,
            status: row => row.status,
            created_by: row => row.created_by_name || '',
            created_at: row => dateOnly(row.created_at),
        };
        const activeColumns = columnOptions.filter(([key]) => visibleColumns.includes(key));
        const escapeCsv = value => `"${String(value ?? '').replaceAll('"', '""')}"`;
        const csv = [
            activeColumns.map(([, label]) => escapeCsv(label)).join(','),
            ...grns.map(row => activeColumns.map(([key]) => escapeCsv(valueMap[key](row))).join(',')),
        ].join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'grns.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    }

    document.addEventListener('DOMContentLoaded', async function () {
        document.querySelectorAll('[data-manage-only]').forEach(element => element.hidden = !canManageGrn);
        renderColumnPicker();
        applyColumnVisibility();
        await loadLookups();
        restoreState();
        await loadGrns(currentPage).catch(error => toast(error.normalizedMessage || 'Unable to load GRNs.', 'danger'));

        document.querySelector('[data-action="new-grn"]').addEventListener('click', () => openGrnModal('create'));
        document.querySelector('[data-action="reload-grns"]').addEventListener('click', () => loadGrns(currentPage));
        document.querySelector('[data-action="close-grn-form"]').addEventListener('click', () => hideGrnEditor());
        document.querySelector('[data-action="clear-grn-search"]').addEventListener('click', () => {
            document.getElementById('grn-search').value = '';
            loadGrns(1);
        });
        document.querySelector('[data-action="reset-filters"]').addEventListener('click', resetFilters);
        document.querySelector('[data-action="apply-filters"]').addEventListener('click', () => loadGrns(1));
        document.querySelector('[data-action="toggle-filter-row"]').addEventListener('click', toggleFilterRow);
        document.querySelector('[data-action="toggle-columns"]').addEventListener('click', function () {
            const picker = this.closest('.column-picker');
            picker.classList.toggle('is-open');
            this.setAttribute('aria-expanded', picker.classList.contains('is-open') ? 'true' : 'false');
        });
        document.getElementById('column-picker-menu').addEventListener('change', function (event) {
            const checkbox = event.target.closest('input[type="checkbox"]');
            if (!checkbox) return;
            updateColumnVisibility(checkbox.value, checkbox.checked);
        });
        ['col-party', 'col-location', 'col-status', 'col-grn-date'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => loadGrns(1));
        });
        ['col-grn-no', 'col-amount-min', 'col-amount-max', 'col-created-by'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(() => loadGrns(1), 300);
            });
        });
        document.addEventListener('click', function (event) {
            if (event.target.closest('.column-picker')) return;
            const picker = document.querySelector('.column-picker');
            picker?.classList.remove('is-open');
            document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('[data-sort]').forEach(button => button.addEventListener('click', function () {
            if (sortBy === this.dataset.sort) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = this.dataset.sort;
                sortDirection = 'asc';
            }
            loadGrns(1);
        }));
        document.getElementById('grn-search').addEventListener('input', function () {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => loadGrns(1), 250);
        });
        document.getElementById('per-page').addEventListener('change', () => loadGrns(1));
        document.querySelector('[data-action="prev-page"]').addEventListener('click', () => currentPage > 1 && loadGrns(currentPage - 1));
        document.querySelector('[data-action="next-page"]').addEventListener('click', () => currentPage < lastPage && loadGrns(currentPage + 1));
        document.getElementById('grn-page-links').addEventListener('click', event => {
            const target = event.target.closest('[data-page]');
            if (target) loadGrns(Number(target.dataset.page));
        });
        document.querySelector('[data-action="export-csv"]').addEventListener('click', csvExport);
        document.querySelector('[data-action="print-list"]').addEventListener('click', () => window.print());

        document.getElementById('grn-body').addEventListener('click', async function (event) {
            const viewId = event.target.closest('[data-view]')?.dataset.view;
            const editId = event.target.closest('[data-edit]')?.dataset.edit;
            const postId = event.target.closest('[data-post]')?.dataset.post;
            const printId = event.target.closest('[data-print]')?.dataset.print;
            const deleteId = event.target.closest('[data-delete]')?.dataset.delete;
            try {
                if (viewId) await openGrnModal('view', viewId);
                if (editId) await openGrnModal('edit', editId);
                if (printId) {
                    await openGrnModal('view', printId);
                    window.setTimeout(() => window.print(), 150);
                }
                if (postId) {
                    await window.axios.post(`${grnEndpoint}/${postId}/post`);
                    await loadGrns(currentPage);
                    toast('GRN posted.');
                }
                if (deleteId) {
                    window.ErpModal.open({
                        title: 'Delete GRN',
                        subtitle: 'Only draft GRNs can be deleted.',
                        size: 'sm',
                        body: '<p>Delete this draft GRN?</p>',
                        footer: `<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-danger" type="button" data-confirm-delete="${deleteId}"><i data-lucide="trash-2"></i> Delete</button>`,
                    });
                }
            } catch (error) {
                toast(error.normalizedMessage || 'Action failed.', 'danger');
            }
        });

        document.addEventListener('click', async function (event) {
            if (event.target.closest('[data-action="save-grn"]')) await saveModalGrn();
            const deleteId = event.target.closest('[data-confirm-delete]')?.dataset.confirmDelete;
            if (deleteId) {
                try {
                    await window.axios.delete(`${grnEndpoint}/${deleteId}`);
                    window.ErpModal.close();
                    await loadGrns(currentPage);
                    toast('GRN deleted.');
                } catch (error) {
                    toast(error.normalizedMessage || 'Delete failed.', 'danger');
                }
            }
            if (event.target.closest('[data-action="add-grn-row"]')) {
                lines.push(nextLine());
                renderModalLines(false);
            }
            const removeIndex = event.target.closest('[data-remove-line]')?.dataset.removeLine;
            if (removeIndex !== undefined && lines.length > 1) {
                lines.splice(Number(removeIndex), 1);
                renderModalLines(false);
            }
        });

        document.addEventListener('input', function (event) {
            const input = event.target.closest('#modal-lines [data-line][data-field]');
            if (!input) return;
            lines[Number(input.dataset.line)][input.dataset.field] = input.value;
            document.getElementById('modal-total-amount').textContent = money.format(totalAmount());
        });

        document.addEventListener('change', function (event) {
            const input = event.target.closest('#modal-lines [data-line][data-field]');
            if (!input) return;
            const index = Number(input.dataset.line);
            lines[index][input.dataset.field] = input.value;
            if (input.dataset.field === 'item_id') {
                const item = lookups.items.find(row => String(row.item_id) === String(input.value));
                if (item?.uom_id) lines[index].uom_id = item.uom_id;
                renderModalLines(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
            const input = event.target.closest('#modal-lines [data-line][data-field]');
            if (!input) return;
            event.preventDefault();
            const next = Number(input.dataset.line) + (event.key === 'ArrowDown' ? 1 : -1);
            document.querySelector(`#modal-lines [data-line="${next}"][data-field="${input.dataset.field}"]`)?.focus();
        });
    });
</script>
@endpush
