@extends('layouts.app')

@section('title', 'Stock Ledger - Timber Inventory')

@section('content')
    <section class="erp-card ledger-list-card">
        <div class="ledger-card-head">
            <div>
                <h1>Stock Ledger</h1>
                <p class="text-muted">Complete read-only inventory movement history.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-ledger" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
            </div>
        </div>

        <div class="ledger-filter-row" id="ledger-filter-row" hidden>
            <div class="ledger-filter-group">
                <label class="ledger-filter-field"><span>From</span><input id="date_from" type="date" title="From date"></label>
                <label class="ledger-filter-field"><span>To</span><input id="date_to" type="date" title="To date"></label>
                <label class="ledger-filter-field"><span>Item</span><select id="item_id"></select></label>
                <label class="ledger-filter-field"><span>Reference</span><select id="reference_type"><option value="">All References</option><option>GRN</option><option>Production</option><option>Production Consumption</option><option>Production Output</option><option>Dispatch</option><option>Wastage</option><option>Reuse</option><option>Adjustment</option></select></label>
                <div class="ledger-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
                <select id="material_type_id" hidden></select>
                <select id="branch_id" hidden></select>
                <select id="location_id" hidden></select>
                <input id="reference_number" type="search" hidden>
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
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="ledger-filter-row ledger-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search item or reference">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive ledger-data-table-wrap">
            <table class="table ledger-data-table">
                <thead>
                    <tr>
                        <th data-col="transaction_date"><button class="sort-head" data-sort="transaction_date" type="button">Date <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="reference_type"><button class="sort-head" data-sort="reference_type" type="button">Ref Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="reference_id"><button class="sort-head" data-sort="reference_id" type="button">Ref # <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item"><button class="sort-head" data-sort="item_name" type="button">Item <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item_code"><button class="sort-head" data-sort="item_code" type="button">Item Code<i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="material_type"><button class="sort-head" data-sort="material_type_name" type="button">Material Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="location"><button class="sort-head" data-sort="location_name" type="button">Location <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="stock_type"><button class="sort-head" data-sort="stock_type" type="button">Stock Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="qty_in" class="text-end"><button class="sort-head sort-end" data-sort="qty_in" type="button">In Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="qty_out" class="text-end"><button class="sort-head sort-end" data-sort="qty_out" type="button">Out Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="running_balance" class="text-end"><button class="sort-head sort-end" data-sort="running_balance" type="button">Running Balance <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="created_by"><button class="sort-head" data-sort="created_by" type="button">Created By <i data-lucide="chevrons-up-down"></i></button></th>
                    </tr>
                    <tr class="ledger-filter-head" id="ledger-filter-head" hidden>
                        <th data-col="transaction_date"><input id="col-ledger-date" type="date"></th>
                        <th data-col="reference_type"><select id="col-reference-type"><option value="">All</option><option>GRN</option><option>Production</option><option>Production Consumption</option><option>Production Output</option><option>Dispatch</option><option>Wastage</option><option>Reuse</option><option>Adjustment</option></select></th>
                        <th data-col="reference_id"><input id="col-reference" type="search" placeholder="Search"></th>
                        <th data-col="item"><input id="col-item" type="search" placeholder="Search"></th>
                        <th data-col="item_code"><input id="col-item_code" type="search" placeholder="Search"></th>
                        <th data-col="material_type"><select id="col-material-type"></select></th>
                        <th data-col="location"><select id="col-location"></select></th>
                        <th data-col="stock_type"></th>
                        <th data-col="qty_in"><input id="col-qty-in-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="qty_out"><input id="col-qty-out-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="running_balance"><input id="col-balance-min" type="number" step="0.001" placeholder="Min"></th>
                        <th data-col="created_by"><input id="col-created-by" type="search" placeholder="Search"></th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr class="skeleton-row"><td colspan="11"></td></tr>
                    <tr class="skeleton-row"><td colspan="11"></td></tr>
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
    .ledger-list-card { overflow: visible; border-radius: 10px; box-shadow: 0 8px 22px rgba(15, 23, 42, .1), 0 22px 46px rgba(15, 23, 42, .07); }
    .ledger-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .ledger-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .ledger-card-head p { margin:5px 0 0; }
    .ledger-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .ledger-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .ledger-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .ledger-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .ledger-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .ledger-filter-group select, .ledger-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:36px; padding:7px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .ledger-table-toolbar { padding:16px 20px; }
    .ledger-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .ledger-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .ledger-table-toolbar .module-search input { padding-right:38px; }
    .column-picker { position:relative; }
    .btn-column-picker { min-height:38px; padding:8px 11px; }
    .btn-column-picker svg { width:16px; height:16px; }
    .btn-filter-toggle { min-height:38px; padding:8px 11px; color:var(--text); background:#fff; }
    .btn-filter-toggle svg { width:16px; height:16px; }
    .btn-filter-toggle.is-active { color:var(--primary); border-color:color-mix(in srgb, var(--primary) 42%, var(--border)); background:color-mix(in srgb, var(--primary) 10%, var(--surface)); }
    .column-picker-menu { position:absolute; top:calc(100% + 8px); left:0; z-index:80; display:none; min-width:220px; padding:8px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); box-shadow:var(--shadow); }
    .column-picker.is-open .column-picker-menu { display:grid; gap:4px; }
    .column-picker-menu label { display:flex; align-items:center; gap:8px; min-height:34px; padding:6px 8px; border-radius:6px; color:var(--text); font-weight:800; }
    .column-picker-menu label:hover { background:var(--surface-soft); }
    .column-picker-menu input { width:16px; height:16px; accent-color:var(--primary); }
    .is-hidden-column { display:none !important; }
    .ledger-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .ledger-data-table { min-width:1320px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .ledger-data-table th, .ledger-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .ledger-data-table th:last-child, .ledger-data-table td:last-child { border-right:0; }
    .ledger-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .ledger-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .ledger-filter-head input, .ledger-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .ledger-filter-head input:focus, .ledger-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .ledger-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .ledger-data-table tbody tr { transition:background .16s ease, box-shadow .16s ease; }
    .ledger-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.sort-end { justify-content:flex-end; }
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
        .ledger-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .ledger-filter-group select, .ledger-filter-group input, .ledger-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .ledger-table-toolbar .module-search { width:100%; }
        .ledger-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .ledger-data-table { min-width:1180px; }
    }
</style>
<script>
const endpoint='/v1/stock-ledger';
const columnOptions=[
    ['transaction_date','Date'],
    ['reference_type','Ref Type'],
    ['reference_id','Ref #'],
    ['item','Item'],
    ['item_code','Item Code'],
    ['material_type','Material Type'],
    ['location','Location'],
    ['stock_type','Stock Type'],
    ['qty_in','In Qty'],
    ['qty_out','Out Qty'],
    ['running_balance','Running Balance'],
    ['created_by','Created By'],
];
const defaultVisibleColumns=['transaction_date','reference_type','reference_id','item','item_code','location','stock_type','qty_in','qty_out','running_balance'];
const columnStorageKey='stockLedger.visibleColumns.v2';
let page=1,last=1,timer,ledgerRows=[],sortBy=sessionStorage.getItem('stockLedger.sortBy')||'transaction_date',sortDirection=sessionStorage.getItem('stockLedger.sortDirection')||'desc',visibleColumns=readVisibleColumns(),paginationMeta={from:0,to:0,total:0};

function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns;}
const opts=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${r[l]||r[k]}</option>`).join('');
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
async function boot(){const [items,types,locs]=await Promise.all([lookup('/v1/items'),lookup('/v1/material-types'),lookup('/v1/locations')]);item_id.innerHTML=opts(items,'item_id','item_name','All Items');material_type_id.innerHTML=opts(types,'material_type_id','material_type_name','All Types');location_id.innerHTML=opts(locs,'location_id','location_name','All Locations');colMaterialType.innerHTML=opts(types,'material_type_id','material_type_name','All');colLocation.innerHTML=opts(locs,'location_id','location_name','All');renderColumnPicker();restoreState();applyColumnVisibility();load()}
function field(id){return document.getElementById(id)}
function params(){return {page,per_page:per_page.value,search:search.value,date_from:date_from.value,date_to:date_to.value,item_id:item_id.value,material_type_id:colMaterialType.value||material_type_id.value,location_id:colLocation.value||location_id.value,reference_type:colReferenceType.value||reference_type.value,reference_number:reference_number.value,ledger_date:colLedgerDate.value,reference_search:colReference.value,item_search:colItem.value,qty_in_min:colQtyInMin.value,qty_out_min:colQtyOutMin.value,balance_min:colBalanceMin.value,created_by:colCreatedBy.value,sort_by:sortBy,sort_direction:sortDirection}}
async function load(p=page){page=p;storeState();rows.innerHTML='<tr class="skeleton-row"><td colspan="11"></td></tr><tr class="skeleton-row"><td colspan="11"></td></tr>';const r=await axios.get(endpoint,{params:params()});const data=r.data.data;page=data.current_page;last=data.last_page;paginationMeta={from:data.from||0,to:data.to||0,total:data.total||0};ledgerRows=data.data||[];renderRows();renderSortHeaders();renderPager()}
function renderRows(){rows.innerHTML=ledgerRows.length?ledgerRows.map(x=>`<tr><td data-col="transaction_date">${x.transaction_date}</td><td data-col="reference_type">${x.reference_type||x.transaction_type}</td><td data-col="reference_id">${x.reference_id||''}</td><td data-col="item">${x.item_name}</td><td data-col="item_code">${x.item_code}</td><td data-col="material_type">${x.material_type_name||''}</td><td data-col="location">${x.location_name||''}</td><td data-col="stock_type">${x.stock_type||'Fresh'}</td><td data-col="qty_in" class="text-end">${x.qty_in}</td><td data-col="qty_out" class="text-end">${x.qty_out}</td><td data-col="running_balance" class="text-end">${x.running_balance}</td><td data-col="created_by">${x.created_by_name||''}</td></tr>`).join(''):'<tr><td colspan="11" class="text-muted">No ledger entries found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.ledger-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('ledger-filter-row'),head=document.getElementById('ledger-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;if(head)head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function storeState(){sessionStorage.setItem('stockLedger.sortBy',sortBy);sessionStorage.setItem('stockLedger.sortDirection',sortDirection)}
function restoreState(){per_page.value=sessionStorage.getItem('stockLedger.perPage')||'10'}
function resetFilters(){[date_from,date_to,item_id,material_type_id,branch_id,location_id,reference_type,reference_number,search,colLedgerDate,colReferenceType,colReference,colItem,colMaterialType,colLocation,colQtyInMin,colQtyOutMin,colBalanceMin,colCreatedBy].forEach(el=>el.value='');per_page.value='10';sortBy='transaction_date';sortDirection='desc';load(1)}
function csvExport(){const valueMap={transaction_date:x=>x.transaction_date,reference_type:x=>x.reference_type||x.transaction_type,reference_id:x=>x.reference_id||'',item:x=>x.item_name,material_type:x=>x.material_type_name||'',location:x=>x.location_name||'',stock_type:x=>x.stock_type||'Fresh',qty_in:x=>x.qty_in,qty_out:x=>x.qty_out,running_balance:x=>x.running_balance,created_by:x=>x.created_by_name||''};const activeColumns=columnOptions.filter(([key])=>visibleColumns.includes(key));const esc=value=>`"${String(value??'').replaceAll('"','""')}"`;const csv=[activeColumns.map(([,label])=>esc(label)).join(','),...ledgerRows.map(row=>activeColumns.map(([key])=>esc(valueMap[key](row))).join(','))].join('\n');const b=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='stock-ledger.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.pageStatus=field('page-status');window.pageLinks=field('page-links');window.columnPickerMenu=field('column-picker-menu');window.colLedgerDate=field('col-ledger-date');window.colReferenceType=field('col-reference-type');window.colReference=field('col-reference');window.colItem=field('col-item');window.colMaterialType=field('col-material-type');window.colLocation=field('col-location');window.colQtyInMin=field('col-qty-in-min');window.colQtyOutMin=field('col-qty-out-min');window.colBalanceMin=field('col-balance-min');window.colCreatedBy=field('col-created-by');boot();document.querySelector('[data-action="reload-ledger"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[per_page,date_from,date_to,item_id,reference_type,colLedgerDate,colReferenceType,colMaterialType,colLocation].forEach(e=>e.addEventListener('change',()=>load(1)));[search,colReference,colItem,colQtyInMin,colQtyOutMin,colBalanceMin,colCreatedBy].forEach(e=>e.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));});
</script>
@endpush
