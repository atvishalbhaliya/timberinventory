@extends('layouts.app')

@section('title', 'Dispatch Challan - Timber Inventory')

@section('content')
    <section class="erp-card dispatch-list-card">
        <div class="dispatch-card-head">
            <div>
                <h1>Dispatch Challan</h1>
                <p class="text-muted">Create dispatch challans and keep team movement in sync.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" data-action="new" title="New challan" hidden><i data-lucide="plus"></i> New Challan</button>
            </div>
        </div>

        <div class="dispatch-filter-row" id="dispatch-filter-row" hidden>
            <div class="dispatch-filter-group">
                <label class="dispatch-filter-field"><span>From</span><input id="date_from" type="date"></label>
                <label class="dispatch-filter-field"><span>To</span><input id="date_to" type="date"></label>
                <label class="dispatch-filter-field"><span>Customer</span><select id="customer_id"></select></label>
                <label class="dispatch-filter-field"><span>Location</span><select id="source_location_id"></select></label>
                <div class="dispatch-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar dispatch-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="dispatch-filter-row" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search challan, customer, vehicle">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive dispatch-data-table-wrap">
            <table class="table dispatch-data-table">
                <thead>
                    <tr>
                        <th><button class="sort-head" data-sort="challan_no" type="button">Challan No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="challan_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="customer_name" type="button">Customer <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="source_location_name" type="button">Location <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="vehicle_no" type="button">Vehicle <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="total_qty" type="button">Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="created_by_name" type="button">Created By <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr class="skeleton-row"><td colspan="8"></td></tr>
                    <tr class="skeleton-row"><td colspan="8"></td></tr>
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
                <button class="btn-erp" type="button" data-action="export"><i data-lucide="file-spreadsheet"></i> Export CSV</button>
                <button class="btn-erp" type="button" data-action="print"><i data-lucide="printer"></i> Print</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<style>
    .dispatch-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .dispatch-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .dispatch-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .dispatch-card-head p { margin:5px 0 0; }
    .dispatch-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .dispatch-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .dispatch-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .dispatch-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .dispatch-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .dispatch-filter-group select, .dispatch-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .dispatch-table-toolbar { padding:16px 20px; }
    .dispatch-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .dispatch-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .dispatch-table-toolbar .module-search input { padding-right:38px; }
    .dispatch-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .dispatch-data-table { min-width:1120px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .dispatch-data-table th, .dispatch-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .dispatch-data-table th:last-child, .dispatch-data-table td:last-child { border-right:0; }
    .dispatch-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .dispatch-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .dispatch-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .dispatch-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:160px; }
    .dispatch-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .dispatch-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .dispatch-actions .action-view { color:#2563eb; }
    .dispatch-actions .action-edit { color:#f97316; }
    .dispatch-actions .action-post { color:#16a34a; }
    .dispatch-actions .action-cancel { color:#dc2626; }
    .dispatch-actions .action-delete { color:#7c3aed; }
    .dispatch-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .dispatch-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .dispatch-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .dispatch-modal-body { display:grid; gap:14px; padding-bottom:86px; }
    .dispatch-header-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; }
    .dispatch-header-grid .field { min-width:0; }
    .dispatch-remarks { grid-column:span 2; }
    .dispatch-lines-wrap { margin:0; }
    .dispatch-lines-wrap .table { min-width:860px; }
    .line-input, .line-select, .line-textarea {
        width:100%;
        min-height:38px;
        padding:8px 10px;
        border:1px solid var(--border);
        border-radius:var(--radius);
        background:var(--surface);
        color:var(--text);
    }
    .line-textarea { min-height:84px; resize:vertical; }
    .line-number { text-align:right; }
    .dispatch-summary-sticky {
        position:sticky;
        bottom:0;
        z-index:3;
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:16px;
        padding:14px 16px;
        border:1px solid var(--border);
        border-radius:var(--radius);
        background:var(--surface);
        box-shadow:var(--shadow-soft);
    }
    .dispatch-summary-sticky span { color:var(--muted); font-weight:800; text-transform:uppercase; font-size:12px; }
    .dispatch-summary-sticky strong { font-size:22px; }
    @media (max-width:1200px) { .dispatch-header-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } .dispatch-remarks { grid-column:span 2; } }
    @media (max-width:860px) { .dispatch-header-grid { grid-template-columns:1fr; } .dispatch-remarks { grid-column:span 1; } }
    @media (max-width:768px) {
        .dispatch-card-head, .dispatch-filter-row, .dispatch-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .dispatch-filter-group, .dispatch-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .dispatch-filter-field { flex:1 1 100%; max-width:none; }
        .dispatch-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .dispatch-filter-group select, .dispatch-filter-group input, .dispatch-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .dispatch-table-toolbar .module-search { width:100%; }
        .dispatch-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .dispatch-data-table { min-width:1140px; }
    }
</style>
<script>
const endpoint = '/v1/dispatch/challans';
const columnOptions = ['challan_no', 'challan_date', 'customer_name', 'vehicle_no', 'total_qty', 'created_by_name', 'created_at'];
const defaultVisible = ['challan_no', 'challan_date', 'customer_name', 'vehicle_no', 'total_qty', 'created_by_name'];
let page = 1;
let lastPage = 1;
let searchTimer;
let rowsData = [];
let visibleColumns = [...defaultVisible];
let sortBy = sessionStorage.getItem('dispatch.sortBy') || 'challan_date';
let sortDirection = sessionStorage.getItem('dispatch.sortDirection') || 'desc';
let lines = [{ item_id: '', team_id: '', qty: '' }];
let lookups = { parties: [], locations: [], teams: [], models: [] };
let activeMode = 'create';
let activeId = null;
let rowsEl;
let pageLinksEl;
let pageStatusEl;
let filterRowEl;
const storedUser = (() => {
    try {
        return JSON.parse(localStorage.getItem('user') || '{}');
    } catch {
        return {};
    }
})();
const hasPermission = (permission) => storedUser.role_name === 'Super Admin' || (storedUser.permissions || []).includes(permission);
const canManageDispatch = hasPermission('dispatch.manage') && hasPermission('masters.view');
const blankLine = () => ({ item_id: '', team_id: '', qty: '' });

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));

const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const qty = (value) => Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 3 });
const optionList = (rows, valueKey, labelKey, placeholder) => `<option value="">${placeholder}</option>` + (Array.isArray(rows) ? rows : []).map((row) => `<option value="${escapeHtml(row[valueKey])}">${escapeHtml(row[labelKey] || row[valueKey])}</option>`).join('');

async function lookup(endpoint, params = {}) {
    return window.ErpApi?.cachedList
        ? window.ErpApi.cachedList(endpoint, params)
        : (await axios.get(endpoint, { params: { per_page: 100, ...params } })).data.data.data || [];
}

async function loadLookups() {
    const [parties, locations, teams, models] = await Promise.all([
        lookup('/v1/parties'),
        lookup('/v1/locations'),
        lookup('/v1/teams'),
        lookup('/v1/items', { item_type: 'Finish Product' }),
    ]);
    lookups = { parties, locations, teams, models };
    document.getElementById('customer_id').innerHTML = optionList(parties, 'party_id', 'party_name', 'All Customers');
    document.getElementById('source_location_id').innerHTML = optionList(locations, 'location_id', 'location_name', 'All Locations');
}

function params() {
    sessionStorage.setItem('dispatch.sortBy', sortBy);
    sessionStorage.setItem('dispatch.sortDirection', sortDirection);
    return {
        page,
        per_page: per_page.value,
        search: search.value,
        date_from: date_from.value,
        date_to: date_to.value,
        customer_id: customer_id.value,
        source_location_id: source_location_id.value,
        sort_by: sortBy,
        sort_direction: sortDirection,
    };
}

function pageRange() {
    const pages = [];
    if (lastPage <= 7) {
        for (let i = 1; i <= lastPage; i++) pages.push(i);
        return pages;
    }
    pages.push(1);
    let start = Math.max(2, page - 1);
    let end = Math.min(lastPage - 1, page + 1);
    if (page <= 2) { start = 2; end = 3; }
    else if (page >= lastPage - 1) { start = lastPage - 2; end = lastPage - 1; }
    if (start > 2) pages.push('...');
    for (let i = start; i <= end; i++) pages.push(i);
    if (end < lastPage - 1) pages.push('...');
    pages.push(lastPage);
    return pages;
}

function renderPager() {
    pageLinksEl.innerHTML = pageRange().map((value) => value === '...'
        ? '<span class="page-number-ellipsis">...</span>'
        : `<button class="btn-erp btn-page-number ${Number(value) === page ? 'is-active' : ''}" type="button" data-page="${value}">${value}</button>`).join('');
    pageStatusEl.textContent = rowsData.length ? `Showing ${rowsData.length} record(s)` : 'Showing 0 records';
    document.querySelector('[data-action="prev-page"]').disabled = page <= 1;
    document.querySelector('[data-action="next-page"]').disabled = page >= lastPage;
    lucide?.createIcons();
}

function renderRows() {
    rowsEl.innerHTML = rowsData.length ? rowsData.map((row) => `
        <tr>
            <td>${escapeHtml(row.challan_no || '')}</td>
            <td>${escapeHtml(row.challan_date || '')}</td>
            <td>${escapeHtml(row.customer_name || '')}</td>
            <td>${escapeHtml(row.source_location_name || '')}</td>
            <td>${escapeHtml(row.vehicle_no || '')}</td>
            <td class="text-end">${qty(row.total_qty)}</td>
            <td>${escapeHtml(row.created_by_name || '')}</td>
            <td class="text-end">
                <span class="dispatch-actions">
                    <button class="icon-btn action-view" type="button" data-view="${row.challan_id}" title="View"><i data-lucide="eye"></i></button>
                    ${canManageDispatch ? `<button class="icon-btn action-edit" type="button" data-edit="${row.challan_id}" title="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn action-delete" type="button" data-delete="${row.challan_id}" title="Delete"><i data-lucide="trash-2"></i></button>` : ''}
                </span>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="8" class="text-muted">No dispatch challans found.</td></tr>';
    lucide?.createIcons();
}

function renderLines(readonly = false) {
    const tbody = document.getElementById('modal-lines');
    if (!tbody) return;
    tbody.innerHTML = lines.map((line, index) => `
        <tr>
            <td><select class="line-select" data-line="${index}" data-field="item_id" ${readonly ? 'disabled' : ''}>${optionList(lookups.models, 'item_id', 'item_name', 'Select finish product')}</select></td>
            <td><select class="line-select" data-line="${index}" data-field="team_id" ${readonly ? 'disabled' : ''}>${optionList(lookups.teams, 'team_id', 'team_name', 'Select team')}</select></td>
            <td><input class="line-input line-number" data-line="${index}" data-field="qty" type="number" min="0" step="0.001" value="${escapeHtml(line.qty || '')}" ${readonly ? 'disabled' : ''}></td>
            <td class="text-end">${readonly ? '' : `<button class="icon-btn" type="button" data-remove-line="${index}" title="Remove" ${lines.length === 1 ? 'disabled' : ''}><i data-lucide="trash-2"></i></button>`}</td>
        </tr>
    `).join('');
    lines.forEach((line, index) => {
        ['item_id', 'team_id'].forEach((field) => {
            const input = document.querySelector(`[data-line="${index}"][data-field="${field}"]`);
            if (input) input.value = line[field] || '';
        });
    });
    document.getElementById('modal-total-qty').textContent = qty(lines.reduce((sum, line) => sum + Number(line.qty || 0), 0));
    lucide?.createIcons();
}

function appendLine(readonly = false) {
    lines.push(blankLine());
    renderLines(readonly);
}

function modalHtml(readonly = false) {
    return `
        <div class="dispatch-modal-body">
            <div class="dispatch-header-grid">
                <div class="field"><i data-lucide="hash"></i><label>Challan No</label><input id="modal-challan-no" type="text" ${readonly ? 'disabled' : ''}></div>
                <div class="field"><i data-lucide="calendar"></i><label>Challan Date *</label><input id="modal-challan-date" type="date" ${readonly ? 'disabled' : ''}></div>
                <div class="field"><i data-lucide="users"></i><label>Customer</label><select id="modal-customer" ${readonly ? 'disabled' : ''}>${optionList(lookups.parties, 'party_id', 'party_name', 'Select customer')}</select></div>
                <div class="field"><i data-lucide="map-pin"></i><label>Location</label><select id="modal-source-location" ${readonly ? 'disabled' : ''}>${optionList(lookups.locations, 'location_id', 'location_name', 'Select location')}</select></div>
                <div class="field"><i data-lucide="truck"></i><label>Vehicle No</label><input id="modal-vehicle" type="text" ${readonly ? 'disabled' : ''}></div>
                <div class="field"><i data-lucide="contact-round"></i><label>Driver Name</label><input id="modal-driver" type="text" ${readonly ? 'disabled' : ''}></div>
                <div class="field dispatch-remarks"><i data-lucide="map"></i><label>Destination</label><input id="modal-destination" type="text" ${readonly ? 'disabled' : ''}></div>
            </div>
            <div class="table-toolbar" style="padding:0;">
                <div>
                    <h3 class="erp-card-title">Team Dispatch Lines</h3>
                    <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Choose finish product, team, and quantity for each line.</p>
                </div>
                ${readonly ? '' : canManageDispatch ? '<button class="btn-erp btn-primary" type="button" data-action="add-line"><i data-lucide="plus"></i> Add Line</button>' : ''}
            </div>
            <div class="table-responsive dispatch-lines-wrap">
                <table class="table">
                    <thead>
                        <tr>
                         <th>Finish Product</th>
                            <th>Team</th>
                           
                            <th class="text-end">Qty</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="modal-lines"></tbody>
                </table>
            </div>
            <div id="modal-errors" class="form-errors" hidden></div>
            <div class="dispatch-summary-sticky"><span>Total Qty</span><strong id="modal-total-qty">0.000</strong></div>
        </div>
    `;
}

function openModal(mode = 'create', row = null) {
    activeMode = mode;
    activeId = row?.challan_id || null;
    lines = [blankLine()];
    window.ErpModal.open({
        title: mode === 'create' ? 'New Dispatch Challan' : mode === 'edit' ? 'Edit Dispatch Challan' : 'View Dispatch Challan',
        subtitle: mode === 'view' ? 'Read-only dispatch challan details.' : 'Create and manage team dispatch entries.',
        size: 'xl',
        body: modalHtml(mode === 'view'),
        footer: mode === 'view'
            ? '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>'
            : canManageDispatch
                ? '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save"><i data-lucide="save"></i> Save</button>'
                : '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>',
    });

    if (mode === 'create') {
        document.getElementById('modal-challan-date').valueAsDate = new Date();
        renderLines(false);
        previewNumber();
        return;
    }

    if (row) fillModal(row);
}

async function previewNumber() {
    if (activeMode !== 'create') return;
    try {
        const response = await axios.get(`${endpoint}/next-number`);
        document.getElementById('modal-challan-no').value = response.data.data.challan_no || '';
    } catch {
        document.getElementById('modal-challan-no').value = '';
    }
}

function fillModal(row) {
    axios.get(`${endpoint}/${row.challan_id}`).then((response) => {
        const challan = response.data.data.header;
        const details = response.data.data.details || [];
        document.getElementById('modal-challan-no').value = challan.challan_no || '';
        document.getElementById('modal-challan-date').value = challan.challan_date || '';
        document.getElementById('modal-customer').value = challan.customer_id || '';
        document.getElementById('modal-source-location').value = challan.source_location_id || '';
        document.getElementById('modal-vehicle').value = challan.vehicle_no || '';
        document.getElementById('modal-driver').value = challan.driver_name || '';
        document.getElementById('modal-destination').value = challan.destination || '';
        lines = details.length ? details.map((detail) => ({
            item_id: detail.item_id || '',
            team_id: detail.team_id || '',
            qty: detail.qty || '',
        })) : [{ item_id: '', team_id: '', qty: '' }];
        renderLines(activeMode === 'view');
    });
}

function formPayload() {
    return {
        challan_no: document.getElementById('modal-challan-no').value,
        challan_date: document.getElementById('modal-challan-date').value,
        customer_id: document.getElementById('modal-customer').value || null,
        source_location_id: document.getElementById('modal-source-location').value || null,
        vehicle_no: document.getElementById('modal-vehicle').value,
        driver_name: document.getElementById('modal-driver').value,
        destination: document.getElementById('modal-destination').value,
        team_details: lines.map((line) => ({
            item_id: line.item_id,
            team_id: line.team_id,
            qty: line.qty,
        })),
    };
}

async function load(p = 1) {
    page = p;
    rowsEl.innerHTML = '<tr class="skeleton-row"><td colspan="8"></td></tr><tr class="skeleton-row"><td colspan="8"></td></tr>';
    const response = await axios.get(endpoint, { params: params() });
    const payload = response.data.data;
    rowsData = payload.data || [];
    page = payload.current_page || page;
    lastPage = payload.last_page || 1;
    renderRows();
    renderPager();
}

function resetFilters() {
    [date_from, date_to, customer_id, source_location_id, search].forEach((input) => { input.value = ''; });
    per_page.value = '10';
    sortBy = 'challan_date';
    sortDirection = 'desc';
    load(1);
}

async function saveModal() {
    const box = document.getElementById('modal-errors');
    box.hidden = true;
    try {
        if (activeMode === 'edit') await axios.put(`${endpoint}/${activeId}`, formPayload());
        else await axios.post(endpoint, formPayload());
        window.ErpModal.close();
        await load(page);
        window.ErpToast?.show('Dispatch challan saved.');
    } catch (error) {
        box.textContent = error.normalizedMessage || 'Unable to save dispatch challan.';
        box.hidden = false;
    }
}

async function doDelete(id) {
    if (!confirm('Delete this draft challan?')) return;
    await axios.delete(`${endpoint}/${id}`);
    await load(page);
    window.ErpToast?.show('Dispatch challan deleted.');
}

function csvExport() {
    const headers = ['Challan No', 'Date', 'Customer', 'Source Location', 'Vehicle', 'Qty', 'Created By'];
    const csv = [headers.join(','), ...rowsData.map((row) => [
        row.challan_no,
        row.challan_date,
        row.customer_name,
        row.source_location_name,
        row.vehicle_no,
        row.total_qty,
        row.created_by_name,
    ].map((value) => `"${String(value ?? '').replaceAll('"', '""')}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'dispatch-challans.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

document.addEventListener('DOMContentLoaded', async () => {
    rowsEl = document.getElementById('rows');
    pageLinksEl = document.getElementById('page-links');
    pageStatusEl = document.getElementById('page-status');
    filterRowEl = document.getElementById('dispatch-filter-row');
    document.querySelector('[data-action="new"]').hidden = !canManageDispatch;
    if (canManageDispatch) await loadLookups();
    await load(1).catch((error) => window.ErpToast?.show(error.normalizedMessage || 'Unable to load dispatch challans.', 'danger'));

    document.querySelector('[data-action="new"]').addEventListener('click', () => openModal('create'));
    document.querySelector('[data-action="reload"]').addEventListener('click', () => load(page));
    document.querySelector('[data-action="toggle-filter-row"]').addEventListener('click', function () {
        filterRowEl.hidden = !filterRowEl.hidden;
        this.classList.toggle('is-active', !filterRowEl.hidden);
        this.setAttribute('aria-pressed', filterRowEl.hidden ? 'false' : 'true');
    });
    document.querySelector('[data-action="apply-filters"]').addEventListener('click', () => load(1));
    document.querySelector('[data-action="reset-filters"]').addEventListener('click', resetFilters);
    document.querySelector('[data-action="clear-search"]').addEventListener('click', () => { search.value = ''; load(1); });
    document.querySelector('[data-action="prev-page"]').addEventListener('click', () => page > 1 && load(page - 1));
    document.querySelector('[data-action="next-page"]').addEventListener('click', () => page < lastPage && load(page + 1));
    pageLinksEl.addEventListener('click', (event) => {
        const target = event.target.closest('[data-page]');
        if (target) load(Number(target.dataset.page));
    });
    document.querySelector('[data-action="export"]').addEventListener('click', csvExport);
    document.querySelector('[data-action="print"]').addEventListener('click', () => window.print());
    document.querySelectorAll('[data-sort]').forEach((button) => button.addEventListener('click', function () {
        if (sortBy === this.dataset.sort) sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        else { sortBy = this.dataset.sort; sortDirection = 'asc'; }
        load(1);
    }));

    rowsEl.addEventListener('click', async (event) => {
        const viewId = event.target.closest('[data-view]')?.dataset.view;
        const editId = event.target.closest('[data-edit]')?.dataset.edit;
        const deleteId = event.target.closest('[data-delete]')?.dataset.delete;
        if (viewId) openModal('view', rowsData.find((row) => String(row.challan_id) === String(viewId)));
        if (editId) openModal('edit', rowsData.find((row) => String(row.challan_id) === String(editId)));
        if (deleteId) await doDelete(deleteId).catch((error) => window.ErpToast?.show(error.normalizedMessage || 'Unable to delete challan.', 'danger'));
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-action="save"]')) saveModal();
        if (event.target.closest('[data-action="add-line"]')) appendLine(activeMode === 'view');
        const removeIndex = event.target.closest('[data-remove-line]')?.dataset.removeLine;
        if (removeIndex !== undefined && lines.length > 1) {
            lines.splice(Number(removeIndex), 1);
            renderLines(activeMode === 'view');
        }
    });

    document.addEventListener('input', (event) => {
        const input = event.target.closest('#modal-lines [data-line][data-field]');
        if (!input) return;
        lines[Number(input.dataset.line)][input.dataset.field] = input.value;
        document.getElementById('modal-total-qty').textContent = qty(lines.reduce((sum, line) => sum + Number(line.qty || 0), 0));
    });

    document.addEventListener('change', (event) => {
        const input = event.target.closest('#modal-lines [data-line][data-field]');
        if (!input) return;
        lines[Number(input.dataset.line)][input.dataset.field] = input.value;
    });

    [per_page, date_from, date_to, customer_id, source_location_id].forEach((input) => input.addEventListener('change', () => load(1)));
    [search].forEach((input) => input.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => load(1), 250);
    }));
});
</script>
@endpush
