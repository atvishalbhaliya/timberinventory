@extends('layouts.app')

@section('title', 'Party Master - Timber Inventory')

@section('content')
    <div class="erp-page-title">
        <div>
            <h1>Party Master</h1>
            <p class="text-muted">Manage customers and suppliers with system generated party codes.</p>
        </div>
        <div class="module-actions">
            <button class="btn-erp" data-action="refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
            <button class="btn-erp btn-primary" data-action="new-party"><i data-lucide="plus"></i> New Party</button>
        </div>
    </div>

    <section class="erp-card">
        <div class="table-toolbar">
            <div class="data-grid-controls">
                <label class="text-muted">Show</label>
                <select id="per_page">
                    <option selected>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span class="text-muted">entries</span>
                <select id="party_type_filter"><option value="">All Types</option><option>Customer</option><option>Supplier</option></select>
                <select id="state_filter"><option value="">All States</option></select>
                <button class="btn-erp" data-action="clear-filters" type="button"><i data-lucide="x"></i> Clear</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search code, name, contact, mobile, GST">
                <button class="search-clear" data-action="clear-search" type="button"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><button class="btn-erp" data-sort="party_code">Party Code</button></th>
                        <th><button class="btn-erp" data-sort="party_type">Party Type</button></th>
                        <th><button class="btn-erp" data-sort="party_name">Party Name</button></th>
                        <th>Contact Person</th>
                        <th>Mobile No</th>
                        <th><button class="btn-erp" data-sort="state">State</button></th>
                        <th>GST No</th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="rows"><tr><td colspan="8" class="text-muted">Loading parties...</td></tr></tbody>
            </table>
        </div>
        <div class="pagination-row">
            <div class="module-actions"><button class="btn-erp" data-action="export"><i data-lucide="file-spreadsheet"></i> Export CSV</button><button class="btn-erp" onclick="window.print()"><i data-lucide="printer"></i> Print</button></div>
            <div class="pager-actions"><button class="btn-erp" data-action="prev-page"><i data-lucide="chevron-left"></i> Previous</button><span id="page-status" class="text-muted"></span><button class="btn-erp" data-action="next-page">Next <i data-lucide="chevron-right"></i></button></div>
        </div>
    </section>

    <style>
        .filter-panel { border-top: 1px solid var(--border); padding: 14px 16px; background: var(--surface-soft); }
        .filter-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 12px; }
        .filter-grid label, .party-form label { display: grid; gap: 6px; font-size: 12px; font-weight: 800; color: var(--muted); }
        .filter-grid select, .party-form input, .party-form select, .party-form textarea { width: 100%; border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; background: var(--surface); color: var(--text); }
        .party-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .party-form .full { grid-column: 1 / -1; }
        .party-form textarea { min-height: 76px; resize: vertical; }
        @media (max-width: 760px) { .filter-grid, .party-form { grid-template-columns: 1fr; } }
    </style>
@endsection

@push('scripts')
<script>
const endpoint = '/v1/parties';
let rowsData = [];
let states = [];
let page = 1;
let lastPage = 1;
let sortBy = 'party_id';
let sortDirection = 'desc';
let timer = null;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
}

function params() {
    return {
        page,
        per_page: per_page.value,
        search: search.value,
        party_type: party_type_filter.value,
        state: state_filter.value,
        sort_by: sortBy,
        sort_direction: sortDirection,
    };
}

async function loadStates() {
    states = window.ErpApi?.cachedList
        ? await window.ErpApi.cachedList('/v1/states', { per_page: 100, sort_by: 'state_name', sort_direction: 'asc' })
        : (await axios.get('/v1/states', { params: { per_page: 100, sort_by: 'state_name', sort_direction: 'asc' } })).data.data.data || [];
    state_filter.innerHTML = '<option value="">All States</option>' + states.map(state => `<option value="${escapeHtml(state.state_name)}">${escapeHtml(state.state_name)}</option>`).join('');
}

async function nextCode(type) {
    if (!type) return '';
    const response = await axios.get('/v1/parties/next-code', { params: { party_type: type } });
    return response.data.data.party_code;
}

async function load(p = page) {
    page = p;
    rows.innerHTML = '<tr class="skeleton-row"><td colspan="8"></td></tr>';
    const response = await axios.get(endpoint, { params: params() });
    const payload = response.data.data;
    rowsData = payload.data || [];
    page = payload.current_page || 1;
    lastPage = payload.last_page || 1;
    rows.innerHTML = rowsData.length ? rowsData.map(row => `
        <tr>
            <td><strong>${escapeHtml(row.party_code)}</strong></td>
            <td><span class="badge badge-primary">${escapeHtml(row.party_type)}</span></td>
            <td>${escapeHtml(row.party_name)}</td>
            <td>${escapeHtml(row.contact_person)}</td>
            <td>${escapeHtml(row.mobile)}</td>
            <td>${escapeHtml(row.state)}</td>
            <td>${escapeHtml(row.gst_no)}</td>
            <td class="text-end action-col">
                <button class="icon-btn" data-view="${row.party_id}" title="View"><i data-lucide="eye"></i></button>
                <button class="icon-btn" data-edit="${row.party_id}" title="Edit"><i data-lucide="pencil"></i></button>
                <button class="icon-btn" data-delete="${row.party_id}" title="Delete"><i data-lucide="trash-2"></i></button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="8" class="text-muted">No parties found.</td></tr>';
    pageStatus.textContent = `Page ${page} of ${lastPage}`;
    lucide?.createIcons();
}

function formHtml(readonly = false) {
    const disabled = readonly ? 'disabled' : '';
    const stateOptions = states.map(state => `<option value="${escapeHtml(state.state_name)}">${escapeHtml(state.state_name)}</option>`).join('');
    return `
        <input type="hidden" id="party_id">
        <div class="party-form">
            <label><span>Party Code</span><input id="party_code" readonly></label>
            <label><span>Party Type *</span><select id="party_type" ${disabled}><option value="">Select Type</option><option>Customer</option><option>Supplier</option></select></label>
            <label><span>Party Name *</span><input id="party_name" ${disabled}></label>
            <label><span>Contact Person</span><input id="contact_person" ${disabled}></label>
            <label><span>Mobile No</span><input id="mobile" type="tel" ${disabled}></label>
            <label><span>Email</span><input id="email" type="email" ${disabled}></label>
            <label class="full"><span>Address</span><textarea id="address" ${disabled}></textarea></label>
            <label><span>State</span><select id="state" ${disabled}><option value="">Select State</option>${stateOptions}</select></label>
            <label><span>GST No</span><input id="gst_no" ${disabled}></label>
            <label><span>PAN No</span><input id="pan_no" ${disabled}></label>
            <label class="full"><span>Remarks</span><textarea id="remarks" ${disabled}></textarea></label>
        </div>
        <div id="modal-validation" class="form-errors" hidden></div>
    `;
}

async function openModal(mode, row = null) {
    const readonly = mode === 'view';
    ErpModal.open({
        title: `${mode === 'create' ? 'New' : mode === 'edit' ? 'Edit' : 'View'} Party`,
        subtitle: readonly ? 'Party details' : 'Party code is generated by the system.',
        body: formHtml(readonly),
        footer: readonly
            ? '<button class="btn-erp" data-modal-close><i data-lucide="x"></i> Close</button>'
            : '<button class="btn-erp" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" data-action="save-party"><i data-lucide="save"></i> Save</button>',
        size: 'xl',
    });

    if (row) {
        ['party_id','party_code','party_type','party_name','contact_person','mobile','email','address','state','gst_no','pan_no','remarks'].forEach(field => {
            const input = document.getElementById(field);
            if (input) input.value = row[field] ?? '';
        });
    }

    document.getElementById('party_type')?.addEventListener('change', async event => {
        if (!row) party_code.value = await nextCode(event.target.value);
    });

    if (!row) party_type.focus();
}

function payload() {
    return ['party_type','party_name','contact_person','mobile','email','address','state','gst_no','pan_no','remarks'].reduce((data, field) => {
        data[field] = document.getElementById(field).value;
        return data;
    }, {});
}

document.addEventListener('DOMContentLoaded', async () => {
    window.pageStatus = document.getElementById('page-status');
    await loadStates();
    await load();

    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(1), 250); });
    per_page.addEventListener('change', () => load(1));
    party_type_filter.addEventListener('change', () => load(1));
    state_filter.addEventListener('change', () => load(1));

    document.addEventListener('click', async event => {
        if (event.target.closest('[data-action="clear-search"]')) { search.value = ''; await load(1); }
        if (event.target.closest('[data-action="clear-filters"]')) { party_type_filter.value = ''; state_filter.value = ''; await load(1); }
        if (event.target.closest('[data-action="refresh"]')) await load(page);
        if (event.target.closest('[data-action="new-party"]')) await openModal('create');
        if (event.target.closest('[data-action="prev-page"]') && page > 1) await load(page - 1);
        if (event.target.closest('[data-action="next-page"]') && page < lastPage) await load(page + 1);

        const sort = event.target.closest('[data-sort]')?.dataset.sort;
        if (sort) {
            sortDirection = sortBy === sort && sortDirection === 'asc' ? 'desc' : 'asc';
            sortBy = sort;
            await load(1);
        }

        const id = Number(event.target.closest('[data-view],[data-edit],[data-delete]')?.dataset.view || event.target.closest('[data-view],[data-edit],[data-delete]')?.dataset.edit || event.target.closest('[data-view],[data-edit],[data-delete]')?.dataset.delete || 0);
        const row = rowsData.find(item => Number(item.party_id) === id);
        if (event.target.closest('[data-view]') && row) await openModal('view', row);
        if (event.target.closest('[data-edit]') && row) await openModal('edit', row);
        if (event.target.closest('[data-delete]') && row) {
            ErpModal.open({
                title: 'Delete Party',
                subtitle: row.party_code,
                body: `<p>Delete ${escapeHtml(row.party_name)}?</p>`,
                footer: `<button class="btn-erp" data-modal-close>Cancel</button><button class="btn-erp btn-danger" data-confirm-delete="${row.party_id}"><i data-lucide="trash-2"></i> Delete</button>`,
                size: 'sm',
            });
        }
        if (event.target.closest('[data-confirm-delete]')) {
            await axios.delete(`${endpoint}/${event.target.closest('[data-confirm-delete]').dataset.confirmDelete}`);
            ErpModal.close();
            await load(page);
            ErpToast?.show('Party deleted.');
        }
        if (event.target.closest('[data-action="save-party"]')) {
            const id = party_id.value;
            try {
                id ? await axios.put(`${endpoint}/${id}`, payload()) : await axios.post(endpoint, payload());
                window.ErpApi?.clearLookupCache?.();
                ErpModal.close();
                await load(page);
                ErpToast?.show('Party saved.');
            } catch (error) {
                const box = document.getElementById('modal-validation');
                box.textContent = error.normalizedMessage || 'Party save failed.';
                box.hidden = false;
            }
        }
        if (event.target.closest('[data-action="export"]')) {
            const response = await axios.get(`${endpoint}/export`, { params: params() });
            const csv = ['Party Code,Party Type,Party Name,Contact Person,Mobile,State,GST No', ...(response.data.data || []).map(row => [row.party_code,row.party_type,row.party_name,row.contact_person,row.mobile,row.state,row.gst_no].map(value => `"${String(value ?? '').replaceAll('"','""')}"`).join(','))].join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'parties.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        }
    });
});
</script>
@endpush
