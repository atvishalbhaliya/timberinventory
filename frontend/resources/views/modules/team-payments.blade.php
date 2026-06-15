@extends('layouts.app')

@section('title', 'Team Payments - Timber Inventory')

@section('content')
    <section class="erp-card payment-list-card">
        <div class="payment-card-head">
            <div>
                <h1>Team Payments</h1>
                <p class="text-muted">Monthly dispatch totals and payable calculations by team.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
            </div>
        </div>

        <div class="payment-filter-row" id="payment-filter-row" hidden>
            <div class="payment-filter-group">
                <label class="payment-filter-field"><span>Team</span><select id="team_id"></select></label>
                <label class="payment-filter-field"><span>Month</span><select id="payment_month"><option value="">All</option><option value="1">January</option><option value="2">February</option><option value="3">March</option><option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select></label>
                <label class="payment-filter-field"><span>Year</span><input id="payment_year" type="number" min="2000" max="2100"></label>
                <div class="payment-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar payment-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="payment-filter-row" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search team">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive payment-data-table-wrap">
            <table class="table payment-data-table">
                <thead>
                    <tr>
                        <th><button class="sort-head" data-sort="team_name" type="button">Team <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="payment_month" type="button">Month <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="payment_year" type="button">Year <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="dispatch_qty" type="button">Dispatch Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="gross_amount" type="button">Gross Amount <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="tds_amount" type="button">TDS Amount <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="net_payable" type="button">Net Payable <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="paid_amount" type="button">Paid Amount <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end"><button class="sort-head sort-end" data-sort="pending_amount" type="button">Pending <i data-lucide="chevrons-up-down"></i></button></th>
                        <th><button class="sort-head" data-sort="updated_by_name" type="button">Updated By <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr class="skeleton-row"><td colspan="10"></td></tr>
                    <tr class="skeleton-row"><td colspan="10"></td></tr>
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
    .payment-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .payment-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .payment-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .payment-card-head p { margin:5px 0 0; }
    .payment-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .payment-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .payment-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .payment-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .payment-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .payment-filter-group select, .payment-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .payment-table-toolbar { padding:16px 20px; }
    .payment-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .payment-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .payment-table-toolbar .module-search input { padding-right:38px; }
    .payment-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .payment-data-table { min-width:1380px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .payment-data-table th, .payment-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .payment-data-table th:last-child, .payment-data-table td:last-child { border-right:0; }
    .payment-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .payment-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .payment-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .payment-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .payment-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .payment-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .payment-modal-body { display:grid; gap:14px; }
    .payment-modal-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
    .payment-modal-grid .field { min-width:0; }
    .payment-history-card { display:grid; gap:10px; }
    .payment-history-card .table-responsive { margin-top:0; }
    @media (max-width:768px) {
        .payment-card-head, .payment-filter-row, .payment-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .payment-filter-group, .payment-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .payment-filter-field { flex:1 1 100%; max-width:none; }
        .payment-filter-group select, .payment-filter-group input, .payment-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .payment-table-toolbar .module-search { width:100%; }
        .payment-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .payment-data-table { min-width:1200px; }
        .payment-modal-grid { grid-template-columns:1fr; }
    }
</style>
<script>
const endpoint = '/v1/team-payments';
let page = 1;
let lastPage = 1;
let rowsData = [];
let searchTimer;
let sortBy = sessionStorage.getItem('teamPayments.sortBy') || 'payment_year';
let sortDirection = sessionStorage.getItem('teamPayments.sortDirection') || 'desc';
let rowsEl;
let pageLinksEl;
let pageStatusEl;
let filterRowEl;
let activePayment = null;
const storedUser = (() => {
    try {
        return JSON.parse(localStorage.getItem('user') || '{}');
    } catch {
        return {};
    }
})();
const hasPermission = (permission) => storedUser.role_name === 'Super Admin' || (storedUser.permissions || []).includes(permission);
const canManagePayments = hasPermission('accounts.manage');

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));
const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const qty = (value) => Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 3 });
const optionList = (rows, valueKey, labelKey, placeholder) => `<option value="">${placeholder}</option>` + rows.map((row) => `<option value="${escapeHtml(row[valueKey])}">${escapeHtml(row[labelKey] || row[valueKey])}</option>`).join('');
const monthName = (value) => ({ 1: 'January', 2: 'February', 3: 'March', 4: 'April', 5: 'May', 6: 'June', 7: 'July', 8: 'August', 9: 'September', 10: 'October', 11: 'November', 12: 'December' }[Number(value)] || '');

async function loadLookups() {
    try {
        const teams = await (window.ErpApi?.cachedList ? window.ErpApi.cachedList('/v1/teams') : (await axios.get('/v1/teams', { params: { per_page: 100 } })).data.data.data || []);
        document.getElementById('team_id').innerHTML = optionList(teams, 'team_id', 'team_name', 'All Teams');
    } catch {
        document.getElementById('team_id').innerHTML = '<option value="">All Teams</option>';
    }
    payment_year.value = payment_year.value || String(new Date().getFullYear());
}

function params() {
    sessionStorage.setItem('teamPayments.sortBy', sortBy);
    sessionStorage.setItem('teamPayments.sortDirection', sortDirection);
    return {
        page,
        per_page: per_page.value,
        search: search.value,
        team_id: team_id.value,
        payment_month: payment_month.value,
        payment_year: payment_year.value,
        sort_by: sortBy,
        sort_direction: sortDirection,
    };
}

function renderRows() {
    rowsEl.innerHTML = rowsData.length ? rowsData.map((row) => `
        <tr>
            <td>${escapeHtml(row.team_name || '')}</td>
            <td>${escapeHtml(monthName(row.payment_month))}</td>
            <td>${escapeHtml(row.payment_year || '')}</td>
            <td class="text-end">${qty(row.dispatch_qty)}</td>
            <td class="text-end">${money(row.gross_amount)}</td>
            <td class="text-end">${money(row.tds_amount)}</td>
            <td class="text-end">${money(row.net_payable)}</td>
            <td class="text-end">${money(row.paid_amount)}</td>
            <td class="text-end">${money(row.pending_amount)}</td>
            <td>${escapeHtml(row.updated_by_name || row.created_by_name || '')}</td>
            <td class="text-end">
                ${canManagePayments && Number(row.pending_amount || 0) > 0 ? `<button class="icon-btn action-pay" type="button" data-pay="${row.payment_id}" title="Add Payment"><i data-lucide="wallet"></i></button>` : ''}
            </td>
        </tr>
    `).join('') : '<tr><td colspan="10" class="text-muted">No payment summaries found.</td></tr>';
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
    rowsEl.innerHTML = '<tr class="skeleton-row"><td colspan="10"></td></tr><tr class="skeleton-row"><td colspan="10"></td></tr>';
    const response = await axios.get(endpoint, { params: params() });
    const payload = response.data.data;
    rowsData = payload.data || [];
    page = payload.current_page || page;
    lastPage = payload.last_page || 1;
    renderRows();
    renderPager();
}

function resetFilters() {
    [team_id, payment_month, payment_year, search].forEach((input) => { input.value = ''; });
    payment_year.value = String(new Date().getFullYear());
    per_page.value = '10';
    sortBy = 'payment_year';
    sortDirection = 'desc';
    load(1);
}

function csvExport() {
    const headers = ['Team', 'Month', 'Year', 'Dispatch Qty', 'Gross Amount', 'TDS Amount', 'Net Payable', 'Paid Amount', 'Pending Amount', 'Updated By'];
    const csv = [headers.join(','), ...rowsData.map((row) => [
        row.team_name,
        monthName(row.payment_month),
        row.payment_year,
        row.dispatch_qty,
        row.gross_amount,
        row.tds_amount,
        row.net_payable,
        row.paid_amount,
        row.pending_amount,
        row.updated_by_name || row.created_by_name || '',
    ].map((value) => `"${String(value ?? '').replaceAll('"', '""')}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'team-payments.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

function paymentHistoryHtml(entries) {
    if (!entries.length) return '<p class="text-muted" style="margin:0;">No payment history yet.</p>';
    return `
        <div class="table-responsive" style="margin-top:14px;">
            <table class="table">
                <thead><tr><th>Date</th><th>Mode</th><th class="text-end">Amount</th><th>Reference</th></tr></thead>
                <tbody>
                    ${entries.map((entry) => `<tr><td>${escapeHtml(entry.payment_date || '')}</td><td>${escapeHtml(entry.payment_mode || '')}</td><td class="text-end">${money(entry.payment_amount)}</td><td>${escapeHtml(entry.reference_no || '')}</td></tr>`).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function paymentModalBody(summary, entries = []) {
    return `
        <div class="payment-modal-body">
            <div class="payment-modal-grid">
                <div class="field"><i data-lucide="users"></i><label>Team</label><input value="${escapeHtml(summary?.team_name || '')}" disabled></div>
                <div class="field"><i data-lucide="calendar"></i><label>Month</label><input value="${escapeHtml(monthName(summary?.payment_month))}" disabled></div>
                <div class="field"><i data-lucide="calendar-range"></i><label>Year</label><input value="${escapeHtml(summary?.payment_year || '')}" disabled></div>
                <div class="field"><i data-lucide="scale"></i><label>Net Payable</label><input value="${money(summary?.net_payable)}" disabled></div>
                <div class="field"><i data-lucide="hand-coins"></i><label>Paid Amount</label><input value="${money(summary?.paid_amount)}" disabled></div>
                <div class="field"><i data-lucide="wallet"></i><label>Pending Amount</label><input value="${money(summary?.pending_amount)}" disabled></div>
                <div class="field"><i data-lucide="calendar-plus"></i><label>Payment Date *</label><input id="payment-date" type="date"></div>
                <div class="field"><i data-lucide="badge-info"></i><label>Payment Mode *</label><select id="payment-mode">
                    <option value="">Select mode</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="UPI">UPI</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Other">Other</option>
                </select></div>
                <div class="field"><i data-lucide="indian-rupee"></i><label>Payment Amount *</label><input id="payment-amount" type="number" step="0.01" min="0.01" value="${Number(summary?.pending_amount || 0).toFixed(2)}"></div>
                <div class="field"><i data-lucide="hash"></i><label>Reference No</label><input id="payment-reference" type="text"></div>
                <div class="field" style="grid-column:1/-1"><i data-lucide="message-square"></i><label>Remarks</label><textarea id="payment-remarks"></textarea></div>
            </div>
            <div class="payment-history-card">
                <h3 class="erp-card-title" style="margin-top:0;">Payment History</h3>
                ${paymentHistoryHtml(entries)}
            </div>
            <div id="payment-errors" class="form-errors" hidden></div>
        </div>
    `;
}

async function openPaymentModal(paymentId) {
    const response = await axios.get(`${endpoint}/${paymentId}`);
    const summary = response.data.data.summary;
    const entries = response.data.data.entries || [];
    activePayment = summary;
    window.ErpModal.open({
        title: 'Add Team Payment',
        subtitle: 'Post a payment against the current pending balance.',
        size: 'lg',
        body: paymentModalBody(summary, entries),
        footer: '<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-payment"><i data-lucide="save"></i> Save Payment</button>',
    });
    document.getElementById('payment-date').valueAsDate = new Date();
    document.getElementById('payment-mode').value = 'Cash';
    lucide?.createIcons();
}

async function savePayment() {
    const box = document.getElementById('payment-errors');
    box.hidden = true;
    try {
        await axios.post(`${endpoint}/${activePayment.payment_id}/payments`, {
            payment_date: document.getElementById('payment-date').value,
            payment_mode: document.getElementById('payment-mode').value,
            payment_amount: document.getElementById('payment-amount').value,
            reference_no: document.getElementById('payment-reference').value,
            remarks: document.getElementById('payment-remarks').value,
        });
        window.ErpModal.close();
        await load(page);
        window.ErpToast?.show('Team payment saved.');
    } catch (error) {
        box.textContent = error.normalizedMessage || 'Unable to save team payment.';
        box.hidden = false;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    rowsEl = document.getElementById('rows');
    pageLinksEl = document.getElementById('page-links');
    pageStatusEl = document.getElementById('page-status');
    filterRowEl = document.getElementById('payment-filter-row');
    await loadLookups();
    await load(1).catch((error) => window.ErpToast?.show(error.normalizedMessage || 'Unable to load team payments.', 'danger'));

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
    rowsEl.addEventListener('click', async (event) => {
        const paymentId = event.target.closest('[data-pay]')?.dataset.pay;
        if (!paymentId) return;
        await openPaymentModal(paymentId).catch((error) => window.ErpToast?.show(error.normalizedMessage || 'Unable to open payment form.', 'danger'));
    });
    document.querySelector('[data-action="export"]').addEventListener('click', csvExport);
    document.querySelector('[data-action="print"]').addEventListener('click', () => window.print());
    document.querySelectorAll('[data-sort]').forEach((button) => button.addEventListener('click', function () {
        if (sortBy === this.dataset.sort) sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        else { sortBy = this.dataset.sort; sortDirection = 'asc'; }
        load(1);
    }));
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-action="save-payment"]')) savePayment();
    });
    [per_page, team_id, payment_month, payment_year].forEach((input) => input.addEventListener('change', () => load(1)));
    search.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => load(1), 250);
    });
});
</script>
@endpush
