@extends('layouts.app')

@section('title', 'Team Ledger - Timber Inventory')

@section('content')
    <section class="erp-card ledger-list-card">
        <div class="ledger-card-head">
            <div>
                <h1>Team Ledger</h1>
                <p class="text-muted">Track production and dispatch movements for each team.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
            </div>
        </div>

        <div class="ledger-filter-row" id="ledger-filter-row" hidden>
            <div class="ledger-filter-group">
                <label class="ledger-filter-field"><span>From</span><input id="date_from" type="date"></label>
                <label class="ledger-filter-field"><span>To</span><input id="date_to" type="date"></label>
                <label class="ledger-filter-field"><span>Team</span><select id="team_id"></select></label>
                <label class="ledger-filter-field"><span>Type</span><select id="transaction_type"><option value="">All</option><option>Production</option><option>Dispatch</option></select></label>
                <div class="ledger-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar ledger-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="ledger-filter-row" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search team or Finished Item">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive ledger-data-table-wrap">
            <table class="table ledger-data-table">
                <thead>
                    <tr>
                        <th><button class="sort-head" data-sort="transaction_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="team_name" type="button">Team <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="pallet_model_name" type="button">Finished Item <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="transaction_type" type="button">Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="qty" type="button">Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <!-- <th class="text-end"><button class="sort-head sort-end" data-sort="amount" type="button">Amount <i data-lucide="chevrons-up-down"></i></button></th> -->
                        <th><button class="sort-head" data-sort="created_by_name" type="button">Created By <i data-lucide="chevrons-up-down"></i></button></th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr class="skeleton-row"><td colspan="7"></td></tr>
                    <tr class="skeleton-row"><td colspan="7"></td></tr>
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
                <button class="btn-erp" data-action="export"><i data-lucide="file-spreadsheet"></i> Export CSV</button>
                <button class="btn-erp" data-action="print"><i data-lucide="printer"></i> Print</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<style>
    .ledger-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .ledger-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .ledger-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .ledger-card-head p { margin:5px 0 0; }
    .ledger-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .ledger-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .ledger-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .ledger-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .ledger-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .ledger-filter-group select, .ledger-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .ledger-table-toolbar { padding:16px 20px; }
    .ledger-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .ledger-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .ledger-table-toolbar .module-search input { padding-right:38px; }
    .ledger-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .ledger-data-table { min-width:1220px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .ledger-data-table th, .ledger-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .ledger-data-table th:last-child, .ledger-data-table td:last-child { border-right:0; }
    .ledger-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .ledger-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .ledger-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .ledger-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .ledger-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .ledger-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    @media (max-width:768px) {
        .ledger-card-head, .ledger-filter-row, .ledger-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .ledger-filter-group, .ledger-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .ledger-filter-field { flex:1 1 100%; max-width:none; }
        .ledger-filter-group select, .ledger-filter-group input, .ledger-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .ledger-table-toolbar .module-search { width:100%; }
        .ledger-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .ledger-data-table { min-width:980px; }
    }
</style>
<script>
const endpoint = '/v1/team-ledger';
let page = 1;
let lastPage = 1;
let searchTimer;
let rowsData = [];
let sortBy = sessionStorage.getItem('teamLedger.sortBy') || 'transaction_date';
let sortDirection = sessionStorage.getItem('teamLedger.sortDirection') || 'desc';
let rowsEl;
let pageLinksEl;
let pageStatusEl;
let filterRowEl;

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));
const qty = (value) => Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 3 });
const optionList = (rows, valueKey, labelKey, placeholder) => `<option value="">${placeholder}</option>` + rows.map((row) => `<option value="${escapeHtml(row[valueKey])}">${escapeHtml(row[labelKey] || row[valueKey])}</option>`).join('');

async function loadLookups() {
    try {
        const teams = await (window.ErpApi?.cachedList ? window.ErpApi.cachedList('/v1/teams') : (await axios.get('/v1/teams', { params: { per_page: 100 } })).data.data.data || []);
        document.getElementById('team_id').innerHTML = optionList(teams, 'team_id', 'team_name', 'All Teams');
    } catch {
        document.getElementById('team_id').innerHTML = '<option value="">All Teams</option>';
    }
}

function params() {
    sessionStorage.setItem('teamLedger.sortBy', sortBy);
    sessionStorage.setItem('teamLedger.sortDirection', sortDirection);
    return {
        page,
        per_page: per_page.value,
        search: search.value,
        date_from: date_from.value,
        date_to: date_to.value,
        team_id: team_id.value,
        transaction_type: transaction_type.value,
        sort_by: sortBy,
        sort_direction: sortDirection,
    };
}

function renderRows() {
    rowsEl.innerHTML = rowsData.length ? rowsData.map((row) => `
        <tr>
            <td>${escapeHtml(row.transaction_date || '')}</td>
            <td>${escapeHtml(row.team_name || '')}</td>
            <td>${escapeHtml(row.pallet_model_name || '')}</td>
            <td><span class="badge">${escapeHtml(row.transaction_type || '')}</span></td>
            <td class="text-end">${qty(row.qty)}</td>
            <td>${escapeHtml(row.created_by_name || '')}</td>
        </tr>
    `).join('') : '<tr><td colspan="7" class="text-muted">No ledger entries found.</td></tr>';
    lucide?.createIcons();
}
//             <td class="text-end">${Number(row.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>

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
    pageLinksEl.innerHTML = pages.map((value) => value === '...'
        ? '<span class="page-number-ellipsis">...</span>'
        : `<button class="btn-erp btn-page-number ${Number(value) === page ? 'is-active' : ''}" type="button" data-page="${value}">${value}</button>`).join('');
    pageStatusEl.textContent = rowsData.length ? `Showing ${rowsData.length} record(s)` : 'Showing 0 records';
    document.querySelector('[data-action="prev-page"]').disabled = page <= 1;
    document.querySelector('[data-action="next-page"]').disabled = page >= lastPage;
    lucide?.createIcons();
}

async function load(p = 1) {
    page = p;
    rowsEl.innerHTML = '<tr class="skeleton-row"><td colspan="6"></td></tr><tr class="skeleton-row"><td colspan="6"></td></tr>';
    const response = await axios.get(endpoint, { params: params() });
    const payload = response.data.data;
    rowsData = payload.data || [];
    page = payload.current_page || page;
    lastPage = payload.last_page || 1;
    renderRows();
    renderPager();
}

function resetFilters() {
    [date_from, date_to, team_id, transaction_type, search].forEach((input) => { input.value = ''; });
    per_page.value = '10';
    sortBy = 'transaction_date';
    sortDirection = 'desc';
    load(1);
}

function csvExport() {
    const headers = ['Date', 'Team', 'Pallet Model', 'Type', 'Qty', 'Amount', 'Created By'];
    const csv = [headers.join(','), ...rowsData.map((row) => [
        row.transaction_date,
        row.team_name,
        row.pallet_model_name,
        row.transaction_type,
        row.qty,
        // row.amount,
        row.created_by_name,
    ].map((value) => `"${String(value ?? '').replaceAll('"', '""')}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'team-ledger.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

document.addEventListener('DOMContentLoaded', async () => {
    rowsEl = document.getElementById('rows');
    pageLinksEl = document.getElementById('page-links');
    pageStatusEl = document.getElementById('page-status');
    filterRowEl = document.getElementById('ledger-filter-row');
    await loadLookups();
    await load(1).catch((error) => window.ErpToast?.show(error.normalizedMessage || 'Unable to load team ledger.', 'danger'));

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
    rowsEl = document.getElementById('rows');
    [per_page, date_from, date_to, team_id, transaction_type].forEach((input) => input.addEventListener('change', () => load(1)));
    search.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => load(1), 250);
    });
});
</script>
@endpush
