@extends('layouts.app')

@section('title', 'Production Entry - Timber Inventory')

@section('content')
    <section class="erp-card production-list-card">
        <div class="production-card-head">
            <div>
                <h1>Production Entry</h1>
                <p class="text-muted">Draft, post, and cancel production with stock impact.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-production" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" type="button" data-action="new-production" data-manage-only><i data-lucide="plus"></i> Add Production</button>
            </div>
        </div>

        <div class="production-filter-row" id="production-filter-row" hidden>
            <div class="production-filter-group">
                <label class="production-filter-field">
                    <span>Date From</span>
                    <input id="filter-date-from" type="date">
                </label>
                <label class="production-filter-field">
                    <span>Date To</span>
                    <input id="filter-date-to" type="date">
                </label>
                <label class="production-filter-field">
                    <span>BOM</span>
                    <select id="filter-bom"></select>
                </label>
                <label class="production-filter-field">
                    <span>Team</span>
                    <select id="filter-team"></select>
                </label>
                <label class="production-filter-field">
                    <span>Produced Item</span>
                    <select id="filter-produced-item"></select>
                </label>
                <label class="production-filter-field">
                    <span>Status</span>
                    <select id="filter-status"><option value="">All Status</option><option>Draft</option><option>Posted</option><option>Cancelled</option></select>
                </label>
                <div class="production-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar production-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="production-filter-row production-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search production, BOM, team">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive production-data-table-wrap">
            <table class="table production-data-table">
                <thead>
                    <tr>
                        <th data-col="production_no"><button class="sort-head" data-sort="production_no" type="button">Production No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="date"><button class="sort-head" data-sort="production_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="bom"><button class="sort-head" data-sort="bom_no" type="button">BOM <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="team"><button class="sort-head" data-sort="team_name" type="button">Team <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item"><button class="sort-head" data-sort="produced_item_name" type="button">Produced Item <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="qty" class="text-end"><button class="sort-head sort-end" data-sort="produced_qty" type="button">Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="status"><button class="sort-head" data-sort="status" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="production-filter-head" id="production-filter-head" hidden>
                        <th data-col="production_no"><input id="col-production-no" type="search" placeholder="Search"></th>
                        <th data-col="date"><input id="col-date" type="date"></th>
                        <th data-col="bom"><input id="col-bom" type="search" placeholder="Search"></th>
                        <th data-col="team"><input id="col-team" type="search" placeholder="Search"></th>
                        <th data-col="item"><input id="col-produced-item" type="search" placeholder="Search"></th>
                        <th data-col="qty"><input id="col-qty-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="status"><select id="col-status"><option value="">All</option><option>Draft</option><option>Posted</option><option>Cancelled</option></select></th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="rows"><tr class="skeleton-row"><td colspan="8"></td></tr></tbody>
            </table>
        </div>

        <div class="pagination-row">
            <div class="pager-actions">
                <button class="btn-erp btn-page-nav" data-action="prev-page" type="button" title="Previous"><i data-lucide="chevron-left"></i></button>
                <span id="page-links" class="page-number-list"></span>
                <button class="btn-erp btn-page-nav" data-action="next-page" type="button" title="Next"><i data-lucide="chevron-right"></i></button>
                <span id="page-status" class="page-record-status text-muted"></span>
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
    .production-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .production-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .production-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .production-card-head p { margin:5px 0 0; }
    .production-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .production-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .production-filter-field { display:grid; gap:5px; flex:1 1 150px; min-width:140px; max-width:190px; margin:0; }
    .production-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .production-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .production-filter-group select, .production-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .production-table-toolbar { padding:16px 20px; }
    .production-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .production-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .production-table-toolbar .module-search input { padding-right:38px; }
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
    .production-data-table-wrap { max-height:calc(100vh - 340px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .production-data-table { min-width:1080px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .production-data-table th, .production-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .production-data-table th:last-child, .production-data-table td:last-child { border-right:0; }
    .production-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; color:var(--muted); font-size:12px; font-weight:900; text-transform:uppercase; }
    .production-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .production-filter-head input, .production-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .production-filter-head input:focus, .production-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .production-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .production-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .production-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:178px; }
    .production-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .production-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .production-actions .icon-btn svg { width:17px; height:17px; }
    .production-actions .action-view { color:#2563eb; }
    .production-actions .action-edit { color:#f97316; }
    .production-actions .action-post { color:#16a34a; }
    .production-actions .action-cancel { color:#dc2626; }
    .production-actions .action-delete { color:#dc2626; }
    .production-data-table .action-col { position:static; right:auto; z-index:auto; min-width:190px; width:190px; box-shadow:none; }
    .production-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .production-list-card .pager-actions { justify-content:flex-start; margin-left:0; }
    .production-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .production-list-card .btn-page-nav { width:36px; min-width:36px; padding:0; justify-content:center; }
    .production-list-card .page-number-list { display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap; }
    .production-list-card .btn-page-number { min-width:34px; min-height:34px; padding:0 9px; justify-content:center; }
    .production-list-card .btn-page-number.is-active { background:var(--primary); border-color:var(--primary); color:#fff; }
    .production-list-card .page-number-ellipsis { padding:0 4px; color:var(--muted); font-weight:800; }
    .production-list-card .page-record-status { margin-left:8px; font-weight:700; }
    .badge-muted { background:#e5e7eb; color:#4b5563; }
    .production-modal-body { display:grid; gap:14px; padding-bottom:70px; }
    .production-form-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .production-form-grid .field-wide { grid-column:span 2; }
    .production-line-wrap { margin:0; }
    .production-line-wrap .table { min-width:1180px; }
    .line-input, .line-select { width:100%; min-height:38px; padding:8px 10px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); color:var(--text); }
    .line-number { text-align:right; }
    .production-status-strip { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:var(--surface-soft); }
    .production-status-strip span { color:var(--muted); font-weight:800; }
    .production-audit-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .production-audit-fact { padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--surface-soft); }
    .production-audit-fact span { display:block; color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; }
    .production-audit-fact strong { display:block; margin-top:4px; font-size:13px; word-break:break-word; }
    @media (max-width:1100px) { .production-form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:768px) {
        .production-card-head, .production-filter-row, .production-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .production-filter-group, .production-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .production-filter-field { flex:1 1 100%; max-width:none; }
        .production-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .production-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .production-table-toolbar .module-search { width:100%; }
        .production-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .production-data-table { min-width:940px; }
        .production-form-grid { grid-template-columns:1fr; }
        .production-form-grid .field-wide { grid-column:span 1; }
        .production-audit-grid { grid-template-columns:1fr; }
    }
</style>
<script>
const endpoint='/v1/production';
const authUser=JSON.parse(localStorage.getItem('user')||'{}');
const permissions=authUser.permissions||[];
const isSuperAdmin=authUser.role_name==='Super Admin';
const canManageProduction=permissions.includes('production.manage')||isSuperAdmin;
const canPostProduction=permissions.includes('production.post')||isSuperAdmin;
const canCancelProduction=permissions.includes('production.cancel')||isSuperAdmin;
const columnOptions=[['production_no','Production No'],['date','Date'],['bom','BOM'],['team','Team'],['item','Produced Item'],['qty','Qty'],['status','Status']];
const defaultVisibleColumns=['production_no','date','bom','team','item','qty','status'];
const columnStorageKey='production.visibleColumns';
let page=1,last=1,timer,productionRows=[],boms=[],items=[],teams=[],locations=[],uoms=[],sortBy=sessionStorage.getItem('production.sortBy')||'production_date',sortDirection=sessionStorage.getItem('production.sortDirection')||'desc',activeMode='create',activeId=null,visibleColumns=readVisibleColumns(),posting=false,paginationMeta={from:0,to:0,total:0};
const esc=v=>(v??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
const opt=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${esc(r[l]||r[k])}</option>`).join('');
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns;}
async function lookup(e){const params={per_page:100};if(e==='/v1/boms')params.status='Active';return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e,params) : (await axios.get(e,{params})).data.data.data||[]}
async function boot(){document.querySelectorAll('[data-manage-only]').forEach(el=>el.hidden=!canManageProduction);[boms,items,teams,locations,uoms]=await Promise.all([lookup('/v1/boms'),lookup('/v1/items'),lookup('/v1/teams'),lookup('/v1/locations'),lookup('/v1/uoms')]);filterBom.innerHTML=opt(boms,'bom_id','bom_name','All BOMs');filterTeam.innerHTML=opt(teams,'team_id','team_name','All Teams');filterProducedItem.innerHTML=opt(items,'item_id','item_name','All Produced Items');renderColumnPicker();restoreState();applyColumnVisibility();load()}
function params(){return{page,per_page:perPage.value,search:search.value,status:colStatus.value||filterStatus.value,date_from:filterDateFrom.value,date_to:filterDateTo.value,bom_id:filterBom.value,team_id:filterTeam.value,produced_item_id:filterProducedItem.value,production_no:colProductionNo.value,production_date:colDate.value,bom_search:colBom.value,team_search:colTeam.value,produced_item_search:colProducedItem.value,qty_min:colQtyMin.value,sort_by:sortBy,sort_direction:sortDirection}}
async function load(p=page){page=p;storeState();rows.innerHTML='<tr class="skeleton-row"><td colspan="8"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};productionRows=d.data||[];renderRows();renderSortHeaders();renderPager()}
function badge(status){const cls=status==='Posted'?'badge-success':status==='Cancelled'?'badge-danger':'badge-warning';return`<span class="badge ${cls}">${esc(status)}</span>`}
function renderRows(){rows.innerHTML=productionRows.length?productionRows.map(x=>`<tr><td data-col="production_no"><strong>${esc(x.production_no)}</strong></td><td data-col="date">${esc(x.production_date)}</td><td data-col="bom">${esc(x.bom_no||x.bom_name)}</td><td data-col="team">${esc(x.team_name)}</td><td data-col="item">${esc(x.produced_item_name)}</td><td data-col="qty" class="text-end">${Number(x.produced_qty||0).toLocaleString(undefined,{maximumFractionDigits:3})}</td><td data-col="status">${badge(x.status)}</td><td class="text-end action-col"><span class="production-actions"><button class="icon-btn action-view" data-view="${x.production_id}" title="View" aria-label="View"><i data-lucide="eye"></i></button>${canManageProduction&&x.status==='Draft'?`<button class="icon-btn action-edit" data-edit="${x.production_id}" title="Edit" aria-label="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn action-delete" data-delete="${x.production_id}" title="Delete" aria-label="Delete"><i data-lucide="trash-2"></i></button>`:''}${canPostProduction&&x.status==='Draft'?`<button class="icon-btn action-post" data-post="${x.production_id}" title="Post" aria-label="Post"><i data-lucide="check-circle"></i></button>`:''}${canCancelProduction&&x.status==='Posted'?`<button class="icon-btn action-cancel" data-cancel="${x.production_id}" title="Cancel" aria-label="Cancel"><i data-lucide="ban"></i></button>`:''}</span></td></tr>`).join(''):'<tr><td colspan="8" class="text-muted">No production entries found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.production-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('production-filter-row'),head=document.getElementById('production-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;if(head)head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function storeState(){sessionStorage.setItem('production.search',search.value);sessionStorage.setItem('production.status',filterStatus.value);sessionStorage.setItem('production.perPage',perPage.value);sessionStorage.setItem('production.dateFrom',filterDateFrom.value);sessionStorage.setItem('production.dateTo',filterDateTo.value);sessionStorage.setItem('production.bom',filterBom.value);sessionStorage.setItem('production.team',filterTeam.value);sessionStorage.setItem('production.producedItem',filterProducedItem.value);sessionStorage.setItem('production.sortBy',sortBy);sessionStorage.setItem('production.sortDirection',sortDirection);['col-production-no','col-date','col-bom','col-team','col-produced-item','col-qty-min','col-status'].forEach(id=>sessionStorage.setItem(`production.${id}`,document.getElementById(id).value))}
function restoreState(){search.value=sessionStorage.getItem('production.search')||'';filterStatus.value=sessionStorage.getItem('production.status')||'';perPage.value=sessionStorage.getItem('production.perPage')||'10';filterDateFrom.value=sessionStorage.getItem('production.dateFrom')||'';filterDateTo.value=sessionStorage.getItem('production.dateTo')||'';filterBom.value=sessionStorage.getItem('production.bom')||'';filterTeam.value=sessionStorage.getItem('production.team')||'';filterProducedItem.value=sessionStorage.getItem('production.producedItem')||'';['col-production-no','col-date','col-bom','col-team','col-produced-item','col-qty-min','col-status'].forEach(id=>{const el=document.getElementById(id);el.value=sessionStorage.getItem(`production.${id}`)||''})}
function resetFilters(){[search,filterStatus,filterDateFrom,filterDateTo,filterBom,filterTeam,filterProducedItem,colProductionNo,colDate,colBom,colTeam,colProducedItem,colQtyMin,colStatus].forEach(el=>el.value='');perPage.value='10';sortBy='production_date';sortDirection='desc';load(1)}
function selectedBom(){return boms.find(b=>String(b.bom_id)===String(document.getElementById('modal-bom-id')?.value))}
function auditFact(label,value){return`<div class="production-audit-fact"><span>${label}</span><strong>${esc(value||'-')}</strong></div>`}
function auditSection(x={}){return`<div class="production-audit-grid">${auditFact('Created By',x.created_by)}${auditFact('Created At',x.created_at)}${auditFact('Updated By',x.updated_by)}${auditFact('Updated At',x.updated_at)}${auditFact('Posted By',x.posted_by)}${auditFact('Posted At',x.posted_at)}${auditFact('Cancelled By',x.cancelled_by)}${auditFact('Cancelled At',x.cancelled_at)}</div>`}
function productionModalBody(readonly=false){return`<div class="production-modal-body"><div class="production-status-strip"><span id="modal-status-label">Draft</span><span id="modal-reference-label"></span></div><div class="production-form-grid"><div class="field"><i data-lucide="hash"></i><label>Production No</label><input id="modal-production-no" type="text" ${readonly?'disabled':'readonly'}></div><div class="field"><i data-lucide="calendar"></i><label>Date *</label><input id="modal-production-date" type="date" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="workflow"></i><label>BOM *</label><select id="modal-bom-id" ${readonly?'disabled':''}>${opt(boms,'bom_id','bom_name','Select BOM')}</select></div><div class="field"><i data-lucide="package-check"></i><label>Produced Item *</label><select id="modal-produced-item-id" disabled>${opt(items,'item_id','item_name','Produced Item')}</select></div><div class="field"><i data-lucide="users"></i><label>Team *</label><select id="modal-team-id" ${readonly?'disabled':''}>${opt(teams,'team_id','team_name','Team')}</select></div><div class="field"><i data-lucide="map-pin"></i><label>FG Location *</label><select id="modal-fg-location-id" ${readonly?'disabled':''}>${opt(locations,'location_id','location_name','FG Location')}</select></div><div class="field"><i data-lucide="scale"></i><label>Produced Qty *</label><input id="modal-produced-qty" type="number" step="0.001" min="0.001" value="1" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="indian-rupee"></i><label>Cost</label><input id="modal-production-cost" type="number" step="0.01" min="0" value="0" ${readonly?'disabled':''}></div><div class="field field-wide"><i data-lucide="message-square"></i><label>Remarks</label><input id="modal-remarks" type="text" ${readonly?'disabled':''}></div></div><div class="table-toolbar" style="padding:0;"><h3 class="erp-card-title">Material Consumption</h3>${readonly?'':'<button class="btn-erp" type="button" data-action="load-bom-lines"><i data-lucide="refresh-cw"></i> Load BOM</button>'}</div><div class="table-responsive production-line-wrap"><table class="table"><thead><tr><th>Item</th><th>UOM</th><th>Location</th><th class="text-end">Current Stock</th><th class="text-end">Required</th><th class="text-end">Consumed</th><th class="text-end">Wastage</th><th>Remarks</th></tr></thead><tbody id="modal-consumption-rows"></tbody></table></div><div id="modal-audit-section" hidden><div class="table-toolbar" style="padding:0;"><h3 class="erp-card-title">Audit Trail</h3></div><div id="modal-audit-grid"></div></div><div id="modal-errors" class="form-errors" hidden></div></div>`}
function num(v){return Number(v||0).toLocaleString(undefined,{maximumFractionDigits:3})}
async function refreshLineStock(tr){const itemId=tr.querySelector('[name=item_id]').value,locationId=tr.querySelector('[name=location_id]').value,stockEl=tr.querySelector('[data-current-stock]');if(!itemId||!locationId){stockEl.textContent='0';return}try{const r=await axios.get(`${endpoint}/current-stock`,{params:{item_id:itemId,location_id:locationId}});stockEl.textContent=num(r.data.data?.available_qty||0)}catch{stockEl.textContent='0'}}
function consumptionLine(row={},readonly=false){const tr=document.createElement('tr');tr.innerHTML=`<td><select class="line-select" name="item_id" required disabled>${opt(items,'item_id','item_name','Item')}</select></td><td><select class="line-select" name="uom_id" disabled>${opt(uoms,'uom_id','uom_name','UOM')}</select></td><td><select class="line-select" name="location_id" required ${readonly?'disabled':''}>${opt(locations,'location_id','location_name','Location')}</select></td><td class="text-end" data-current-stock>${num(row.current_stock)}</td><td><input class="line-input line-number" name="required_qty" type="number" step="0.001" min="0" value="${row.required_qty??0}" disabled></td><td><input class="line-input line-number" name="consumed_qty" type="number" step="0.001" min="0.001" value="${row.consumed_qty??row.required_qty??1}" required disabled></td><td><input class="line-input line-number" name="wastage_qty" type="number" step="0.001" min="0" value="${row.wastage_qty??Math.max(Number(row.consumed_qty||0)-Number(row.required_qty||0),0)}" disabled></td><td><input class="line-input" name="remarks" value="${esc(row.remarks)}" ${readonly?'disabled':''}></td>`;document.getElementById('modal-consumption-rows').appendChild(tr);tr.querySelector('[name=item_id]').value=row.item_id||'';tr.querySelector('[name=uom_id]').value=row.uom_id||'';tr.querySelector('[name=location_id]').value=row.location_id||'';if(!readonly)tr.querySelector('[name=location_id]').addEventListener('change',()=>refreshLineStock(tr));refreshLineStock(tr)}
async function fetchBomData(){const bomId=document.getElementById('modal-bom-id').value;if(!bomId)return null;const r=await axios.get(`${endpoint}/bom/${bomId}/materials`,{params:{produced_qty:document.getElementById('modal-produced-qty').value||1}});return r.data.data}
function applyBomHeader(data){if(!data)return;const item=document.getElementById('modal-produced-item-id'),bom=data.bom;if(data.produced_item_id)item.value=data.produced_item_id;if(bom?.version_no)document.getElementById('modal-reference-label').textContent=`Version: ${bom.version_no}`}
async function applyBomSelection(){const data=await fetchBomData();applyBomHeader(data)}
async function loadBomLines(){const bomId=document.getElementById('modal-bom-id').value;if(!bomId){window.ErpToast?.show('Select a BOM first.','danger');return}const data=await fetchBomData();applyBomHeader(data);document.getElementById('modal-consumption-rows').innerHTML='';(data?.materials||[]).forEach(row=>consumptionLine(row,false));}
async function openProductionModal(mode,id=null){activeMode=mode;activeId=id;let readonly=mode==='view';window.ErpModal.open({title:mode==='create'?'Add Production':mode==='edit'?'Edit Production':'Display Production',subtitle:'Production master and material consumption rows',size:'xl',body:productionModalBody(readonly),footer:readonly?'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>':'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-production"><i data-lucide="save"></i> Save Draft</button>'});if(mode==='create'){document.getElementById('modal-production-date').value=new Date().toISOString().slice(0,10);document.getElementById('modal-produced-qty').value=1;document.getElementById('modal-production-cost').value=0;const r=await axios.get(endpoint+'/next-number');document.getElementById('modal-production-no').value=r.data.data.production_no;return}const r=await axios.get(`${endpoint}/${id}`);const x=r.data.data;readonly=mode==='view'||x.status!=='Draft';document.getElementById('modal-status-label').innerHTML=badge(x.status);document.getElementById('modal-reference-label').textContent=x.posted_at?`Posted: ${x.posted_at}`:x.cancelled_at?`Cancelled: ${x.cancelled_at}`:'';document.getElementById('modal-production-no').value=x.production_no;document.getElementById('modal-production-date').value=x.production_date;document.getElementById('modal-bom-id').value=x.bom_id;document.getElementById('modal-produced-item-id').value=x.produced_item_id;document.getElementById('modal-team-id').value=x.team_id;document.getElementById('modal-fg-location-id').value=x.fg_location_id;document.getElementById('modal-produced-qty').value=x.produced_qty;document.getElementById('modal-production-cost').value=x.production_cost;document.getElementById('modal-remarks').value=x.remarks||'';document.getElementById('modal-audit-section').hidden=false;document.getElementById('modal-audit-grid').innerHTML=auditSection(x);(x.consumptions||[]).forEach(row=>consumptionLine(row,readonly));if(readonly){document.querySelectorAll('#modal-production-date,#modal-bom-id,#modal-produced-item-id,#modal-team-id,#modal-fg-location-id,#modal-produced-qty,#modal-production-cost,#modal-remarks').forEach(el=>el.disabled=true)}}
function modalPayload(){return{production_no:document.getElementById('modal-production-no').value,production_date:document.getElementById('modal-production-date').value,bom_id:document.getElementById('modal-bom-id').value,produced_item_id:document.getElementById('modal-produced-item-id').value,team_id:document.getElementById('modal-team-id').value,fg_location_id:document.getElementById('modal-fg-location-id').value,produced_qty:document.getElementById('modal-produced-qty').value,production_cost:document.getElementById('modal-production-cost').value,remarks:document.getElementById('modal-remarks').value,consumptions:[...document.querySelectorAll('#modal-consumption-rows tr')].map(tr=>({item_id:tr.querySelector('[name=item_id]').value,uom_id:tr.querySelector('[name=uom_id]').value,location_id:tr.querySelector('[name=location_id]').value,required_qty:tr.querySelector('[name=required_qty]').value,consumed_qty:tr.querySelector('[name=consumed_qty]').value,wastage_qty:tr.querySelector('[name=wastage_qty]').value,remarks:tr.querySelector('[name=remarks]').value}))}}
async function saveProduction(){const box=document.getElementById('modal-errors');box.hidden=true;try{if(activeMode==='edit')await axios.put(`${endpoint}/${activeId}`,modalPayload());else await axios.post(endpoint,modalPayload());window.ErpApi?.clearLookupCache?.();window.ErpModal.close();await load(page);window.ErpToast?.show('Production saved.')}catch(error){box.textContent=error.normalizedMessage||'Unable to save production.';box.hidden=false}}
function setProcessing(button,label='Processing'){posting=true;button.disabled=true;button.innerHTML=`<i data-lucide="loader"></i> ${label}`;lucide?.createIcons()}
function clearProcessing(){posting=false}
function confirmPost(id){window.ErpModal.open({title:'Post Production',subtitle:'Posting updates Fresh stock, output, wastage stock-out, and team ledger.',size:'sm',body:'<p>Post this production entry?</p>',footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-confirm-post="${id}"><i data-lucide="check-circle"></i> Post</button>`})}
function confirmCancel(id){window.ErpModal.open({title:'Cancel Production',subtitle:'Cancellation reverses posted production stock movement.',size:'md',body:'<div class="field"><i data-lucide="message-square"></i><label>Cancellation Reason</label><input id="cancel-reason" type="text"></div><div id="cancel-errors" class="form-errors" hidden></div>',footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button><button class="btn-erp btn-danger" type="button" data-confirm-cancel="${id}"><i data-lucide="ban"></i> Cancel Production</button>`})}
function confirmDelete(id){window.ErpModal.open({title:'Delete Production',subtitle:'Only draft production entries can be deleted.',size:'sm',body:'<p>Delete this production entry?</p>',footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-danger" type="button" data-confirm-delete="${id}"><i data-lucide="trash-2"></i> Delete</button>`})}
function csvExport(){const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const map={production_no:x=>x.production_no,date:x=>x.production_date,bom:x=>x.bom_no||x.bom_name,team:x=>x.team_name,item:x=>x.produced_item_name,qty:x=>x.produced_qty,status:x=>x.status};const csv=[active.map(([,label])=>`"${label}"`).join(','),...productionRows.map(row=>active.map(([key])=>`"${String(map[key](row)??'').replaceAll('"','""')}"`).join(','))].join('\n');const blob=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='production.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.perPage=document.getElementById('per_page');window.filterStatus=document.getElementById('filter-status');window.filterDateFrom=document.getElementById('filter-date-from');window.filterDateTo=document.getElementById('filter-date-to');window.filterBom=document.getElementById('filter-bom');window.filterTeam=document.getElementById('filter-team');window.filterProducedItem=document.getElementById('filter-produced-item');window.colProductionNo=document.getElementById('col-production-no');window.colDate=document.getElementById('col-date');window.colBom=document.getElementById('col-bom');window.colTeam=document.getElementById('col-team');window.colProducedItem=document.getElementById('col-produced-item');window.colQtyMin=document.getElementById('col-qty-min');window.colStatus=document.getElementById('col-status');window.columnPickerMenu=document.getElementById('column-picker-menu');window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');boot();document.querySelector('[data-action="new-production"]').onclick=()=>openProductionModal('create');document.querySelector('[data-action="reload-production"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('change',async e=>{if(e.target?.id==='modal-bom-id')await applyBomSelection()});document.addEventListener('click',async e=>{if(!e.target.closest('.column-picker')){document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')}if(e.target.closest('[data-action="save-production"]'))await saveProduction();if(e.target.closest('[data-action="load-bom-lines"]'))await loadBomLines();const postButton=e.target.closest('[data-confirm-post]');const post=postButton?.dataset.confirmPost;if(post&&!posting){try{setProcessing(postButton,'Posting');await axios.post(`${endpoint}/${post}/post`);window.ErpModal.close();await load(page);window.ErpToast?.show('Production posted.')}catch(error){window.ErpToast?.show(error.normalizedMessage||'Post failed.','danger')}finally{clearProcessing()}}const cancelButton=e.target.closest('[data-confirm-cancel]');const cancel=cancelButton?.dataset.confirmCancel;if(cancel&&!posting){try{setProcessing(cancelButton,'Cancelling');await axios.post(`${endpoint}/${cancel}/cancel`,{reason:document.getElementById('cancel-reason')?.value||''});window.ErpModal.close();await load(page);window.ErpToast?.show('Production cancelled.')}catch(error){const box=document.getElementById('cancel-errors');if(box){box.textContent=error.normalizedMessage||'Cancel failed.';box.hidden=false}else window.ErpToast?.show(error.normalizedMessage||'Cancel failed.','danger')}finally{clearProcessing()}}const deleteButton=e.target.closest('[data-confirm-delete]');const del=deleteButton?.dataset.confirmDelete;if(del&&!posting){try{setProcessing(deleteButton,'Deleting');await axios.delete(`${endpoint}/${del}`);window.ErpModal.close();await load(page);window.ErpToast?.show('Production deleted.')}catch(error){window.ErpToast?.show(error.normalizedMessage||'Delete failed.','danger')}finally{clearProcessing()}}});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[perPage,filterStatus,filterDateFrom,filterDateTo,filterBom,filterTeam,filterProducedItem,colDate,colStatus].forEach(el=>el.addEventListener('change',()=>load(1)));[search,colProductionNo,colBom,colTeam,colProducedItem,colQtyMin].forEach(el=>el.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));rows.addEventListener('click',async e=>{const view=e.target.closest('[data-view]')?.dataset.view;const edit=e.target.closest('[data-edit]')?.dataset.edit;const post=e.target.closest('[data-post]')?.dataset.post;const cancel=e.target.closest('[data-cancel]')?.dataset.cancel;const del=e.target.closest('[data-delete]')?.dataset.delete;if(view)await openProductionModal('view',view);if(edit)await openProductionModal('edit',edit);if(post)confirmPost(post);if(cancel)confirmCancel(cancel);if(del)confirmDelete(del)});});
</script>
@endpush
