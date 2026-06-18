@extends('layouts.app')

@section('title', $title.' - Timber Inventory')

@section('content')
    <section class="erp-card report-card">
        <div class="report-card-head">
            <div>
                <h1>{{ $title }}</h1>
                <p class="text-muted">{{ $description }}</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload" type="button"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp" data-action="export" type="button"><i data-lucide="file-spreadsheet"></i> Export CSV</button>
                <button class="btn-erp" data-action="print" type="button"><i data-lucide="printer"></i> Print</button>
            </div>
        </div>

        <div class="report-metrics" id="report-metrics"></div>

        <div class="report-filter-row" id="report-filter-row">
            <div class="report-filter-group" id="report-filters"></div>
            <div class="report-filter-actions">
                <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
            </div>
        </div>

        <div class="table-toolbar report-toolbar">
            <div class="data-grid-controls">
                <select id="per_page">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search report">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive report-table-wrap">
            <table class="table report-table">
                <thead id="report-head"></thead>
                <tbody id="report-body">
                    <tr class="skeleton-row"><td></td></tr>
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
        </div>
    </section>
@endsection

@push('scripts')
<style>
    .report-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .report-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .report-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .report-card-head p { margin:5px 0 0; }
    .report-metrics { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; padding:16px 20px 4px; }
    .report-metric { display:grid; gap:6px; padding:14px 16px; border:1px solid var(--border); border-radius:14px; background:var(--surface); box-shadow:0 8px 18px rgba(15,23,42,.05); }
    .report-metric span { color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    .report-metric strong { font-size:22px; line-height:1.1; }
    .report-filter-row { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; padding:12px 20px 8px; flex-wrap:wrap; }
    .report-filter-group { display:flex; align-items:flex-end; gap:10px; flex:1 1 auto; flex-wrap:wrap; }
    .report-filter-field { display:grid; gap:5px; min-width:170px; flex:1 1 170px; max-width:230px; }
    .report-filter-field span { color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; line-height:1; }
    .report-filter-field select, .report-filter-field input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .report-filter-actions { display:flex; align-items:flex-end; gap:10px; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .report-toolbar { padding:8px 20px 16px; }
    .report-toolbar .module-search { width:clamp(200px, 24vw, 280px); }
    .report-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .report-toolbar .module-search input { padding-right:38px; }
    .report-table-wrap { max-height:calc(100vh - 380px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .report-table { min-width:1220px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .report-table th, .report-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .report-table th:last-child, .report-table td:last-child { border-right:0; }
    .report-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .report-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .report-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .report-list-empty { text-align:center; color:var(--muted); padding:28px 12px; }
    .report-status-pill { display:inline-flex; align-items:center; justify-content:center; min-height:28px; padding:0 10px; border-radius:999px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    .report-status-pill.is-posted { color:#166534; background:#dcfce7; }
    .report-status-pill.is-draft { color:#92400e; background:#ffedd5; }
    .report-status-pill.is-cancelled { color:#991b1b; background:#fee2e2; }
    .report-status-pill.is-pending { color:#9a3412; background:#ffedd5; }
    .report-status-pill.is-settled { color:#166534; background:#dcfce7; }
    .report-status-pill.is-partial { color:#1d4ed8; background:#dbeafe; }
    @media (max-width: 992px) {
        .report-metrics { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .report-card-head, .report-filter-row, .report-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .report-filter-group, .report-filter-actions, .pager-actions, .module-actions { width:100%; flex-wrap:wrap; }
        .report-filter-field { max-width:none; flex:1 1 100%; }
        .report-filter-field select, .report-filter-field input, .report-filter-actions .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .report-toolbar .module-search { width:100%; }
        .report-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .report-table { min-width:1080px; }
        .report-metrics { grid-template-columns:1fr; padding-inline:12px; }
    }
</style>
<script>
const reportKey = @json($reportKey);
const reportTitle = @json($title);
const reportDescription = @json($description);
let rows = [];
let page = 1;
let lastPage = 1;
let searchTimer;
let sortBy = '';
let sortDirection = 'asc';
let filters = {};
let lookups = {};

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));
const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const qty = (value) => Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 3 });
const monthName = (value) => ({ 1: 'January', 2: 'February', 3: 'March', 4: 'April', 5: 'May', 6: 'June', 7: 'July', 8: 'August', 9: 'September', 10: 'October', 11: 'November', 12: 'December' }[Number(value)] || '');
const reports = {
    inventory: {
        endpoint: '/v1/stock-summary',
        searchPlaceholder: 'Search item or code',
        filters: [
            { key: 'item_type', label: 'Item Type', type: 'select', placeholder: 'All Types', options: ['Raw Material'] },
            { key: 'stock_status', label: 'Stock Status', type: 'select', placeholder: 'All Statuses', options: ['available', 'low', 'out'] },
            { key: 'location_id', label: 'Location', type: 'lookup', placeholder: 'All Locations', source: '/v1/locations', valueKey: 'location_id', labelKey: 'location_name' },
        ],
        columns: [
            { key: 'item_code', label: 'Item Code', align: 'left' },
            { key: 'item_name', label: 'Item Name', align: 'left' },
            { key: 'item_type', label: 'Item Type', align: 'left' },
            { key: 'location_name', label: 'Location', align: 'left' },
            { key: 'available_qty', label: 'Available Qty', align: 'right', format: qty },
            { key: 'avg_rate', label: 'Avg Rate', align: 'right', format: money },
            { key: 'stock_value', label: 'Stock Value', align: 'right', format: money },
            { key: 'last_movement_date', label: 'Last Movement', align: 'left' },
        ],
        metrics: (payload) => {
            const m = payload.metrics || {};
            return [
                { label: 'Total Items', value: payload.total || m.total_items || 0 },
                { label: 'Available Stock', value: qty(m.available_stock || 0) },
                { label: 'Low Stock', value: m.low_stock_items || 0 },
                { label: 'Out of Stock', value: m.out_of_stock_items || 0 },
            ];
        },
        rowBadge: () => '',
    },
    production: {
        endpoint: '/v1/production',
        searchPlaceholder: 'Search production, BOM, team',
        filters: [
            { key: 'status', label: 'Status', type: 'select', placeholder: 'All Statuses', options: ['Draft', 'Posted', 'Cancelled'] },
            { key: 'date_from', label: 'From', type: 'date' },
            { key: 'date_to', label: 'To', type: 'date' },
        ],
        columns: [
            { key: 'production_no', label: 'Production No', align: 'left' },
            { key: 'production_date', label: 'Date', align: 'left' },
            { key: 'bom_no', label: 'BOM', align: 'left' },
            { key: 'team_name', label: 'Team', align: 'left' },
            { key: 'produced_item_name', label: 'Produced Item', align: 'left' },
            { key: 'produced_qty', label: 'Qty', align: 'right', format: qty },
            { key: 'status', label: 'Status', align: 'left', badge: true },
        ],
        metrics: (payload) => {
            const posted = rows.filter((row) => row.status === 'Posted').length;
            const draft = rows.filter((row) => row.status === 'Draft').length;
            const cancelled = rows.filter((row) => row.status === 'Cancelled').length;
            return [
                { label: 'Total Records', value: payload.total || rows.length || 0 },
                { label: 'Produced Qty', value: qty(rows.reduce((sum, row) => sum + Number(row.produced_qty || 0), 0)) },
                { label: 'Posted', value: posted },
                { label: 'Draft / Cancelled', value: `${draft} / ${cancelled}` },
            ];
        },
        rowBadge: (value) => statusBadge(value),
    },
    dispatch: {
        endpoint: '/v1/dispatch/challans',
        searchPlaceholder: 'Search challan, customer, vehicle',
        filters: [
            { key: 'date_from', label: 'From', type: 'date' },
            { key: 'date_to', label: 'To', type: 'date' },
            { key: 'customer_id', label: 'Customer', type: 'lookup', placeholder: 'All Customers', source: '/v1/parties', valueKey: 'party_id', labelKey: 'party_name', params: { party_type: 'Customer' } },
        ],
        columns: [
            { key: 'challan_no', label: 'Challan No', align: 'left' },
            { key: 'challan_date', label: 'Date', align: 'left' },
            { key: 'customer_name', label: 'Customer', align: 'left' },
            { key: 'vehicle_no', label: 'Vehicle', align: 'left' },
            { key: 'destination', label: 'Destination', align: 'left' },
            { key: 'total_qty', label: 'Qty', align: 'right', format: qty },
            { key: 'created_by_name', label: 'Created By', align: 'left' },
        ],
        metrics: (payload) => [
            { label: 'Total Challans', value: payload.total || rows.length || 0 },
            { label: 'Dispatch Qty', value: qty(rows.reduce((sum, row) => sum + Number(row.total_qty || 0), 0)) },
            { label: 'Customers', value: new Set(rows.map((row) => row.customer_name).filter(Boolean)).size },
            { label: 'Current Page', value: `${page} / ${lastPage}` },
        ],
        rowBadge: () => '',
    },
    payment: {
        endpoint: '/v1/team-payments',
        searchPlaceholder: 'Search team or code',
        defaults: {
            payment_month: String(new Date().getMonth() + 1),
            payment_year: String(new Date().getFullYear()),
        },
        filters: [
            { key: 'team_id', label: 'Team', type: 'lookup', placeholder: 'All Teams', source: '/v1/teams', valueKey: 'team_id', labelKey: 'team_name' },
            { key: 'payment_month', label: 'Month', type: 'select', placeholder: 'All Months', options: [
                { value: 1, label: 'January' }, { value: 2, label: 'February' }, { value: 3, label: 'March' }, { value: 4, label: 'April' },
                { value: 5, label: 'May' }, { value: 6, label: 'June' }, { value: 7, label: 'July' }, { value: 8, label: 'August' },
                { value: 9, label: 'September' }, { value: 10, label: 'October' }, { value: 11, label: 'November' }, { value: 12, label: 'December' },
            ] },
            { key: 'payment_year', label: 'Year', type: 'number', placeholder: 'Year' },
        ],
        columns: [
            { key: 'team_name', label: 'Team', align: 'left' },
            { key: 'payment_month', label: 'Month', align: 'left', format: monthName },
            { key: 'payment_year', label: 'Year', align: 'left' },
            { key: 'dispatch_qty', label: 'Dispatch Qty', align: 'right', format: qty },
            { key: 'gross_amount', label: 'Gross Amount', align: 'right', format: money },
            { key: 'tds_amount', label: 'TDS Amount', align: 'right', format: money },
            { key: 'net_payable', label: 'Net Payable', align: 'right', format: money },
            { key: 'paid_amount', label: 'Paid Amount', align: 'right', format: money },
            { key: 'pending_amount', label: 'Pending Amount', align: 'right', format: money },
            { key: 'updated_by_name', label: 'Updated By', align: 'left' },
        ],
        metrics: (payload) => {
            const settled = rows.filter((row) => Number(row.pending_amount || 0) <= 0).length;
            const pending = rows.filter((row) => Number(row.pending_amount || 0) > 0).length;
            return [
                { label: 'Total Summaries', value: payload.total || rows.length || 0 },
                { label: 'Net Payable', value: money(rows.reduce((sum, row) => sum + Number(row.net_payable || 0), 0)) },
                { label: 'Settled', value: settled },
                { label: 'Pending', value: pending },
            ];
        },
        rowBadge: (value) => statusBadge(Number(value || 0) > 0 ? 'Pending' : 'Settled'),
    },
};

const config = reports[reportKey] || reports.inventory;

const renderSelectOptions = (options, placeholder) => `<option value="">${placeholder}</option>` + options.map((option) => {
    if (typeof option === 'string') return `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`;
    return `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`;
}).join('');

const statusBadge = (value) => {
    const text = String(value || '');
    const key = text.toLowerCase();
    const classes = {
        posted: 'is-posted',
        draft: 'is-draft',
        cancelled: 'is-cancelled',
        pending: 'is-pending',
        settled: 'is-settled',
        partial: 'is-partial',
    };
    const klass = classes[key] || 'is-pending';
    return `<span class="report-status-pill ${klass}">${escapeHtml(text)}</span>`;
};

function lookupValue(row, column) {
    const value = row[column.key];
    if (column.badge) return config.rowBadge(value);
    return escapeHtml(column.format ? column.format(value, row) : value);
}

function buildFilters() {
    const filterHost = document.getElementById('report-filters');
    filterHost.innerHTML = config.filters.map((field) => {
        if (field.type === 'select') {
            return `
                <label class="report-filter-field">
                    <span>${field.label}</span>
                    <select id="${field.key}">
                        ${renderSelectOptions(field.options, field.placeholder || `All ${field.label}`)}
                    </select>
                </label>
            `;
        }
        if (field.type === 'lookup') {
            return `
                <label class="report-filter-field">
                    <span>${field.label}</span>
                    <select id="${field.key}"><option value="">${field.placeholder}</option></select>
                </label>
            `;
        }
        return `
            <label class="report-filter-field">
                <span>${field.label}</span>
                <input id="${field.key}" type="${field.type}" placeholder="${field.placeholder || field.label}">
            </label>
        `;
        }).join('');
}

function applyDefaults() {
    Object.entries(config.defaults || {}).forEach(([key, value]) => {
        const input = document.getElementById(key);
        if (input && !input.value) input.value = value;
    });
}

async function loadLookups() {
    const lookupFields = config.filters.filter((field) => field.type === 'lookup');
    await Promise.all(lookupFields.map(async (field) => {
        try {
            const data = await (window.ErpApi?.cachedList ? window.ErpApi.cachedList(field.source, field.params || {}) : (await axios.get(field.source, { params: { per_page: 100, ...(field.params || {}) } })).data.data.data || []);
            lookups[field.key] = data;
            document.getElementById(field.key).innerHTML = renderSelectOptions(data.map((row) => ({ value: row[field.valueKey], label: row[field.labelKey] })), field.placeholder);
        } catch {
            document.getElementById(field.key).innerHTML = `<option value="">${field.placeholder}</option>`;
        }
    }));
}

function collectFilters() {
    const data = { page, per_page: document.getElementById('per_page').value, sort_by: sortBy, sort_direction: sortDirection };
    config.filters.forEach((field) => {
        const value = document.getElementById(field.key)?.value;
        if (value !== undefined && value !== '') data[field.key] = value;
    });
    if (document.getElementById('search').value) data.search = document.getElementById('search').value;
    return data;
}

function renderMetrics(payload) {
    const metrics = config.metrics(payload);
    document.getElementById('report-metrics').innerHTML = metrics.map((metric) => `
        <div class="report-metric">
            <span>${escapeHtml(metric.label)}</span>
            <strong>${metric.value}</strong>
        </div>
    `).join('');
}

function renderHead() {
    document.getElementById('report-head').innerHTML = `
        <tr>
            ${config.columns.map((column) => `
                <th class="${column.align === 'right' ? 'text-end' : ''}">
                    <button class="sort-head ${column.align === 'right' ? 'sort-end' : ''}" data-sort="${column.key}" type="button">
                        ${column.label} <i data-lucide="chevrons-up-down"></i>
                    </button>
                </th>
            `).join('')}
        </tr>
    `;
    lucide?.createIcons();
}

function renderRows() {
    document.getElementById('report-body').innerHTML = rows.length ? rows.map((row) => `
        <tr>
            ${config.columns.map((column) => `
                <td class="${column.align === 'right' ? 'text-end' : ''}">${lookupValue(row, column)}</td>
            `).join('')}
        </tr>
    `).join('') : `<tr><td colspan="${config.columns.length}" class="report-list-empty">No ${reportTitle.toLowerCase()} found.</td></tr>`;
    lucide?.createIcons();
}

function renderPager() {
    const pages = [];
    if (lastPage <= 7) {
        for (let i = 1; i <= lastPage; i++) pages.push(i);
    } else {
        pages.push(1);
        let start = Math.max(2, page - 1);
        let end = Math.min(lastPage - 1, page + 1);
        if (page <= 2) { start = 2; end = 3; }
        else if (page >= lastPage - 1) { start = lastPage - 2; end = lastPage - 1; }
        if (start > 2) pages.push('...');
        for (let i = start; i <= end; i++) pages.push(i);
        if (end < lastPage - 1) pages.push('...');
        pages.push(lastPage);
    }
    document.getElementById('page-links').innerHTML = pages.map((value) => value === '...'
        ? '<span class="page-number-ellipsis">...</span>'
        : `<button class="btn-erp btn-page-number ${Number(value) === page ? 'is-active' : ''}" type="button" data-page="${value}">${value}</button>`).join('');
    document.getElementById('page-status').textContent = rows.length ? `Showing ${rows.length} record(s)` : 'Showing 0 records';
    document.querySelector('[data-action="prev-page"]').disabled = page <= 1;
    document.querySelector('[data-action="next-page"]').disabled = page >= lastPage;
    lucide?.createIcons();
}

async function load(p = 1) {
    page = p;
    document.getElementById('report-body').innerHTML = `<tr class="skeleton-row"><td colspan="${config.columns.length}"></td></tr><tr class="skeleton-row"><td colspan="${config.columns.length}"></td></tr>`;
    const response = await axios.get(config.endpoint, { params: collectFilters() });
    const payload = response.data.data;
    rows = payload.data || [];
    page = payload.current_page || page;
    lastPage = payload.last_page || 1;
    renderMetrics(payload);
    renderRows();
    renderPager();
}

function csvExport() {
    const headers = config.columns.map((column) => column.label);
    const csv = [headers.join(','), ...rows.map((row) => config.columns.map((column) => `"${String(column.format ? column.format(row[column.key], row) : row[column.key] ?? '').replaceAll('"', '""')}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${reportKey}-report.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('per_page').value = '10';
    config.filters.forEach((field) => {
        const input = document.getElementById(field.key);
        if (input) input.value = '';
    });
    load(1);
}

document.addEventListener('DOMContentLoaded', async () => {
    buildFilters();
    applyDefaults();
    document.getElementById('search').placeholder = config.searchPlaceholder;
    await loadLookups();
    renderHead();
    await load(1).catch((error) => window.ErpToast?.show(error.normalizedMessage || `Unable to load ${reportTitle.toLowerCase()}.`, 'danger'));

    document.querySelector('[data-action="reload"]').addEventListener('click', () => load(page));
    document.querySelector('[data-action="export"]').addEventListener('click', csvExport);
    document.querySelector('[data-action="print"]').addEventListener('click', () => window.print());
    document.querySelector('[data-action="apply-filters"]').addEventListener('click', () => load(1));
    document.querySelector('[data-action="reset-filters"]').addEventListener('click', resetFilters);
    document.querySelector('[data-action="clear-search"]').addEventListener('click', () => {
        document.getElementById('search').value = '';
        load(1);
    });
    document.querySelector('[data-action="prev-page"]').addEventListener('click', () => page > 1 && load(page - 1));
    document.querySelector('[data-action="next-page"]').addEventListener('click', () => page < lastPage && load(page + 1));
    document.getElementById('page-links').addEventListener('click', (event) => {
        const target = event.target.closest('[data-page]');
        if (target) load(Number(target.dataset.page));
    });
    document.getElementById('report-head').addEventListener('click', (event) => {
        const column = event.target.closest('[data-sort]')?.dataset.sort;
        if (!column) return;
        if (sortBy === column) sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        else {
            sortBy = column;
            sortDirection = 'asc';
        }
        load(1);
    });
    [document.getElementById('search'), document.getElementById('per_page'), ...config.filters.map((field) => document.getElementById(field.key)).filter(Boolean)].forEach((input) => {
        input.addEventListener('change', () => load(1));
    });
    document.getElementById('search').addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => load(1), 250);
    });
});
</script>
@endpush
