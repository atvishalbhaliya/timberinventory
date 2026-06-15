@extends('layouts.app')

@section('title', 'Stock Verification - Timber Inventory')

@section('content')
    <section class="erp-card verification-list-card">
        <div class="verification-card-head">
            <div>
                <h1>Stock Verification</h1>
                <p class="text-muted">Verify physical stock, calculate variance, and approve automatic adjustments.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" data-action="new" data-create><i data-lucide="plus"></i> New Verification</button>
            </div>
        </div>

        <div class="verification-filter-row" id="verification-filter-row" hidden>
            <div class="verification-filter-group">
                <label class="verification-filter-field">
                    <span>Status</span>
                    <select id="filter-status"><option value="">All Status</option><option>Draft</option><option>Submitted</option><option>Completed</option><option>Cancelled</option></select>
                </label>
                <div class="verification-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar verification-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page"><option selected>10</option><option>25</option><option>50</option><option>100</option></select>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="verification-filter-row verification-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search verification">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive verification-data-table-wrap">
            <table class="table verification-data-table">
                <thead>
                    <tr>
                        <th data-col="verification_no"><button class="sort-head" data-sort="verification_no" type="button">Verification No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="verification_date"><button class="sort-head" data-sort="verification_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="location"><button class="sort-head" data-sort="location_name" type="button">Location <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="status"><button class="sort-head" data-sort="status" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="remarks"><button class="sort-head" data-sort="remarks" type="button">Remarks <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="verification-filter-head" id="verification-filter-head" hidden>
                        <th data-col="verification_no"><input id="col-verification-no" type="search" placeholder="Search"></th>
                        <th data-col="verification_date"><input id="col-verification-date" type="date"></th>
                        <th data-col="location"><select id="col-location"></select></th>
                        <th data-col="status"><select id="col-status"><option value="">All</option><option>Draft</option><option>Submitted</option><option>Completed</option><option>Cancelled</option></select></th>
                        <th data-col="remarks"><input id="col-remarks" type="search" placeholder="Search"></th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="rows"><tr class="skeleton-row"><td colspan="6"></td></tr></tbody>
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
    .verification-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .verification-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .verification-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .verification-card-head p { margin:5px 0 0; }
    .verification-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .verification-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .verification-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .verification-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .verification-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .verification-filter-group select { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .verification-table-toolbar { padding:16px 20px; }
    .verification-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .verification-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .verification-table-toolbar .module-search input { padding-right:38px; }
    .column-picker { position:relative; }
    .btn-column-picker, .btn-filter-toggle { min-height:38px; padding:8px 11px; }
    .btn-column-picker svg, .btn-filter-toggle svg { width:16px; height:16px; }
    .btn-filter-toggle { color:var(--text); background:#fff; }
    .btn-filter-toggle.is-active { color:var(--primary); border-color:color-mix(in srgb, var(--primary) 42%, var(--border)); background:color-mix(in srgb, var(--primary) 10%, var(--surface)); }
    .column-picker-menu { position:absolute; top:calc(100% + 8px); left:0; z-index:80; display:none; min-width:220px; padding:8px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); box-shadow:var(--shadow); }
    .column-picker.is-open .column-picker-menu { display:grid; gap:4px; }
    .column-picker-menu label { display:flex; align-items:center; gap:8px; min-height:34px; padding:6px 8px; border-radius:6px; color:var(--text); font-weight:800; }
    .column-picker-menu label:hover { background:var(--surface-soft); }
    .column-picker-menu input { width:16px; height:16px; accent-color:var(--primary); }
    .is-hidden-column { display:none !important; }
    .verification-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .verification-data-table { min-width:980px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .verification-data-table th, .verification-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .verification-data-table th:last-child, .verification-data-table td:last-child { border-right:0; }
    .verification-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; color:var(--muted); font-size:12px; font-weight:900; text-transform:uppercase; }
    .verification-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .verification-filter-head input, .verification-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .verification-filter-head input:focus, .verification-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .verification-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .verification-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .verification-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:156px; }
    .verification-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .verification-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .verification-actions .icon-btn svg { width:17px; height:17px; }
    .verification-actions .action-view { color:#2563eb; }
    .verification-actions .action-edit { color:#f97316; }
    .verification-actions .action-submit, .verification-actions .action-approve { color:#16a34a; }
    .verification-actions .action-cancel { color:#dc2626; }
    .verification-data-table .action-col { position:static; right:auto; z-index:auto; min-width:178px; width:178px; box-shadow:none; }
    .verification-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .verification-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .verification-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .verification-grid { display:grid; gap:14px; padding-bottom:72px; }
    .verification-header { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .verification-lines .table { min-width:980px; }
    .summary-sticky { position:sticky; bottom:0; display:flex; justify-content:flex-end; gap:18px; padding:12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); box-shadow:var(--shadow-soft); }
    .summary-sticky strong { font-size:18px; }
    @media(max-width:860px){.verification-header{grid-template-columns:1fr}.summary-sticky{display:grid;justify-content:stretch}}
    @media (max-width:768px) {
        .verification-card-head, .verification-filter-row, .verification-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .verification-filter-group, .verification-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .verification-filter-field { flex:1 1 100%; max-width:none; }
        .verification-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .verification-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .verification-table-toolbar .module-search { width:100%; }
        .verification-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .verification-data-table { min-width:920px; }
    }
</style>
<script>
const endpoint='/v1/stock-verifications';
const user=JSON.parse(localStorage.getItem('user')||'{}');
const perms=user.permissions||[];
const can=p=>perms.includes(p)||user.role_name==='Super Admin';
const columnOptions=[['verification_no','Verification No'],['verification_date','Date'],['location','Location'],['status','Status'],['remarks','Remarks']];
const defaultVisibleColumns=['verification_no','verification_date','location','status'];
const columnStorageKey='stockVerification.visibleColumns';
let page=1,last=1,timer,lookups={branches:[],locations:[],items:[]},lines=[],activeId=null,activeBranchId='',mode='create',verificationRows=[],visibleColumns=readVisibleColumns(),paginationMeta={from:0,to:0,total:0},sortBy=sessionStorage.getItem('stockVerification.sortBy')||'verification_date',sortDirection=sessionStorage.getItem('stockVerification.sortDirection')||'desc';
const opts=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${r[l]||r[k]}</option>`).join('');
const badge=s=>`<span class="badge ${s==='Completed'?'badge-success':s==='Cancelled'?'badge-danger':s==='Submitted'?'badge-primary':'badge-warning'}">${s}</span>`;
const toast=(m,t='success')=>window.ErpToast?window.ErpToast.show(m,t):alert(m);
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns}
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
async function boot(){[lookups.branches,lookups.locations,lookups.items]=await Promise.all([lookup('/v1/branches'),lookup('/v1/locations'),lookup('/v1/items')]);filterStatus.value='';colLocation.innerHTML=opts(lookups.locations,'location_id','location_name','All');document.querySelector('[data-create]').hidden=!can('stock-verification.create');renderColumnPicker();applyColumnVisibility();load()}
function storeSort(){sessionStorage.setItem('stockVerification.sortBy',sortBy);sessionStorage.setItem('stockVerification.sortDirection',sortDirection)}
function params(){storeSort();return{page,status:colStatus.value||filterStatus.value,search:search.value,per_page:per_page.value,sort_by:sortBy,sort_direction:sortDirection}}
function clientFiltered(rows){const no=colVerificationNo.value.toLowerCase(),date=colVerificationDate.value,location=colLocation.value,remarks=colRemarks.value.toLowerCase();return rows.filter(x=>(!no||String(x.verification_no||'').toLowerCase().includes(no))&&(!date||String(x.verification_date||'').slice(0,10)===date)&&(!location||String(x.location_id)===String(location))&&(!remarks||String(x.remarks||'').toLowerCase().includes(remarks)))}
async function load(p=page){page=p;rows.innerHTML='<tr class="skeleton-row"><td colspan="6"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};verificationRows=clientFiltered(d.data||[]);window.currentVerificationRows=verificationRows;renderRows();renderSortHeaders();renderPager()}
function renderRows(){rows.innerHTML=verificationRows.length?verificationRows.map(x=>`<tr><td data-col="verification_no"><strong>${x.verification_no}</strong></td><td data-col="verification_date">${x.verification_date}</td><td data-col="location">${x.location_name||''}</td><td data-col="status">${badge(x.status)}</td><td data-col="remarks">${x.remarks||''}</td><td class="text-end action-col"><span class="verification-actions"><button class="icon-btn action-view" data-view="${x.verification_id}" title="View"><i data-lucide="eye"></i></button>${x.status==='Draft'&&can('stock-verification.edit')?`<button class="icon-btn action-edit" data-edit="${x.verification_id}" title="Edit"><i data-lucide="pencil"></i></button>`:''}${x.status==='Draft'&&can('stock-verification.submit')?`<button class="icon-btn action-submit" data-submit="${x.verification_id}" title="Submit"><i data-lucide="send"></i></button>`:''}${x.status==='Submitted'&&can('stock-verification.approve')?`<button class="icon-btn action-approve" data-approve="${x.verification_id}" title="Approve"><i data-lucide="check"></i></button>`:''}${['Draft','Submitted'].includes(x.status)&&can('stock-verification.cancel')?`<button class="icon-btn action-cancel" data-cancel="${x.verification_id}" title="Cancel"><i data-lucide="x-circle"></i></button>`:''}</span></td></tr>`).join(''):'<tr><td colspan="6" class="text-muted">No verifications found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.verification-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('verification-filter-row'),head=document.getElementById('verification-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;if(head)head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function resetFilters(){[filterStatus,colVerificationNo,colVerificationDate,colLocation,colStatus,colRemarks,search].forEach(el=>el.value='');per_page.value='10';sortBy='verification_date';sortDirection='desc';load(1)}
function modalHtml(readonly=false){return `<div class="verification-grid"><div class="verification-header"><div class="field"><i data-lucide="hash"></i><label>Verification Number</label><input id="v_no" readonly></div><div class="field"><i data-lucide="calendar"></i><label>Date</label><input id="v_date" type="date" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="map-pin"></i><label>Location</label><select id="v_location" ${readonly?'disabled':''}>${opts(lookups.locations,'location_id','location_name','Select location')}</select></div><div class="field" style="grid-column:span 2"><i data-lucide="message-square"></i><label>Remarks</label><input id="v_remarks" ${readonly?'disabled':''}></div></div><div class="table-toolbar" style="padding:0"><h3 class="erp-card-title">Stock Count</h3>${readonly?'':`<div class="module-actions"><button class="btn-erp" type="button" data-load-stock><i data-lucide="database"></i> Auto Load Stock</button><button class="btn-erp" type="button" data-add-row><i data-lucide="plus"></i> Add Row</button></div>`}</div><div class="table-responsive verification-lines" style="margin:0"><table class="table"><thead><tr><th>Item</th><th>UOM</th><th class="text-end">System Qty</th><th class="text-end">Physical Qty</th><th class="text-end">Variance Qty</th><th>Variance Type</th><th></th></tr></thead><tbody id="v_lines"></tbody></table></div><div id="v_errors" class="form-errors" hidden></div><div class="summary-sticky"><span>Total System: <strong id="t_system">0</strong></span><span>Total Physical: <strong id="t_physical">0</strong></span><span>Total Variance: <strong id="t_variance">0</strong></span></div></div>`}
function newLine(r={}){return{item_id:r.item_id||'',uom_id:r.uom_id||'',location_id:r.location_id||'',system_qty:r.system_qty||0,physical_qty:r.physical_qty||0}}
function variance(l){return Number(l.physical_qty||0)-Number(l.system_qty||0)}
function vtype(v){return v>0?'Excess':v<0?'Shortage':'Matched'}
function renderLines(readonly=false){v_lines.innerHTML=lines.map((l,i)=>{const v=variance(l);return`<tr><td><select class="line-select" data-line="${i}" data-field="item_id" ${readonly?'disabled':''}>${opts(lookups.items,'item_id','item_name','Select item')}</select></td><td><input class="line-input" value="${lookups.items.find(x=>String(x.item_id)===String(l.item_id))?.uom_id||l.uom_id||''}" disabled></td><td><input class="line-input line-number" data-line="${i}" data-field="system_qty" type="number" value="${l.system_qty}" ${readonly?'disabled':''}></td><td><input class="line-input line-number" data-line="${i}" data-field="physical_qty" type="number" min="0" value="${l.physical_qty}" ${readonly?'disabled':''}></td><td class="text-end">${v.toFixed(3)}</td><td>${vtype(v)}</td><td class="text-end">${readonly?'':`<button class="icon-btn" data-remove="${i}" ${lines.length===1?'disabled':''}><i data-lucide="trash-2"></i></button>`}</td></tr>`}).join('');lines.forEach((l,i)=>{const s=document.querySelector(`[data-line="${i}"][data-field="item_id"]`);if(s)s.value=l.item_id});t_system.textContent=lines.reduce((s,l)=>s+Number(l.system_qty||0),0).toFixed(3);t_physical.textContent=lines.reduce((s,l)=>s+Number(l.physical_qty||0),0).toFixed(3);t_variance.textContent=lines.reduce((s,l)=>s+variance(l),0).toFixed(3);lucide?.createIcons()}
function defaultBranchId(){return user.branch_id||activeBranchId||lookups.branches[0]?.branch_id||''}
async function openModal(m,id=null){mode=m;activeId=id;activeBranchId='';const ro=m==='view';lines=[newLine()];ErpModal.open({title:m==='create'?'New Verification':m==='edit'?'Edit Verification':'View Verification',subtitle:'Physical stock verification',size:'xl',body:modalHtml(ro),footer:ro?'<button class="btn-erp" data-modal-close>Close</button>':'<button class="btn-erp" data-modal-close>Cancel</button><button class="btn-erp btn-primary" data-save>Save</button>'});v_date.valueAsDate=new Date();if(id){const r=await axios.get(`${endpoint}/${id}`);const x=r.data.data;activeBranchId=x.branch_id||'';v_no.value=x.verification_no;v_date.value=x.verification_date;v_location.value=x.location_id;v_remarks.value=x.remarks||'';lines=(x.details||[]).map(newLine)}else v_no.value='SV-AUTO';renderLines(ro)}
function payload(){return{branch_id:defaultBranchId(),location_id:v_location.value,verification_date:v_date.value,remarks:v_remarks.value,details:lines.map(l=>({...l,location_id:v_location.value}))}}
async function save(){try{if(mode==='edit')await axios.put(`${endpoint}/${activeId}`,payload());else await axios.post(endpoint,payload());ErpModal.close();await load();toast('Verification saved.')}catch(e){v_errors.textContent=e.normalizedMessage||'Save failed.';v_errors.hidden=false}}
function csvExport(){const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const valueMap={verification_no:x=>x.verification_no,verification_date:x=>x.verification_date,location:x=>x.location_name,status:x=>x.status,remarks:x=>x.remarks};const esc=v=>`"${String(v??'').replaceAll('"','""')}"`;const csv=[active.map(([,label])=>esc(label)).join(','),...verificationRows.map(row=>active.map(([key])=>esc(valueMap[key](row))).join(','))].join('\n');const b=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='stock-verifications.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.filterStatus=document.getElementById('filter-status');window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');window.columnPickerMenu=document.getElementById('column-picker-menu');window.colVerificationNo=document.getElementById('col-verification-no');window.colVerificationDate=document.getElementById('col-verification-date');window.colLocation=document.getElementById('col-location');window.colStatus=document.getElementById('col-status');window.colRemarks=document.getElementById('col-remarks');boot();document.querySelector('[data-action="new"]').onclick=()=>openModal('create');document.querySelector('[data-action="reload"]').onclick=()=>load();document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;filterStatus.onchange=()=>load(1);per_page.onchange=()=>load(1);search.oninput=()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),250)};document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[colVerificationDate,colLocation,colStatus].forEach(e=>e.addEventListener('change',()=>load(1)));[colVerificationNo,colRemarks].forEach(e=>e.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),250)}));rows.onclick=async e=>{const g=n=>e.target.closest(`[data-${n}]`)?.dataset[n];if(g('view'))await openModal('view',g('view'));if(g('edit'))await openModal('edit',g('edit'));for(const a of ['submit','approve','cancel'])if(g(a)){await axios.post(`${endpoint}/${g(a)}/${a}`);await load();toast(`Verification ${a}ed.`)}};document.addEventListener('click',async e=>{if(e.target.closest('[data-save]'))save();if(e.target.closest('[data-add-row]')){lines.push(newLine());renderLines()}const ri=e.target.closest('[data-remove]')?.dataset.remove;if(ri!==undefined&&lines.length>1){lines.splice(Number(ri),1);renderLines()}if(e.target.closest('[data-load-stock]')){const r=await axios.get(endpoint+'/current-stock',{params:{branch_id:defaultBranchId(),location_id:v_location.value}});lines=(r.data.data||[]).map(x=>newLine({...x,physical_qty:x.system_qty}));if(!lines.length)lines=[newLine()];renderLines()}});document.addEventListener('input',e=>{const i=e.target.closest('#v_lines [data-line][data-field]');if(!i)return;lines[Number(i.dataset.line)][i.dataset.field]=i.value;renderLines()});document.addEventListener('change',e=>{const i=e.target.closest('#v_lines [data-line][data-field]');if(!i)return;lines[Number(i.dataset.line)][i.dataset.field]=i.value;renderLines()});});
</script>
@endpush
