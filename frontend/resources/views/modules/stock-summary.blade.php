@extends('layouts.app')

@section('title', 'Stock Summary - Timber Inventory')

@section('content')
    <section class="grid-kpis summary-kpis">
        <article class="erp-kpi-card"><div class="erp-kpi-label">Total Items</div><div class="erp-kpi-value" id="m_total">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Available Stock</div><div class="erp-kpi-value" id="m_stock">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Low Stock</div><div class="erp-kpi-value" id="m_low">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Out Of Stock</div><div class="erp-kpi-value" id="m_out">0</div></article>
    </section>

    <section class="erp-card summary-list-card">
        <div class="summary-card-head">
            <div>
                <h1>Stock Summary</h1>
                <p class="text-muted">Current stock position by item and location.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp btn-primary" data-action="import-stock" title="Import stock"><i data-lucide="upload"></i> Import Stock</button>
                <button class="btn-erp" data-action="reload" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
            </div>
        </div>

        <div class="summary-filter-row" id="summary-filter-row" hidden>
            <div class="summary-filter-group">
                <label class="summary-filter-field"><span>Item</span><select id="item_id"></select></label>
                <label class="summary-filter-field"><span>Item Type</span><select id="item_type"></select></label>
                <label class="summary-filter-field"><span>Material Type</span><select id="material_type_id"></select></label>
                <label class="summary-filter-field"><span>Location</span><select id="location_id"></select></label>
                <div class="summary-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar summary-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page"><option selected>10</option><option>25</option><option>50</option><option>100</option></select>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="summary-filter-row summary-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search item">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive summary-data-table-wrap">
            <table class="table summary-data-table">
                <thead>
                    <tr>
                        <th data-col="item_code"><button class="sort-head" data-sort="item_code" type="button">Item Code <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item_name"><button class="sort-head" data-sort="item_name" type="button">Item Name <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="material_type"><button class="sort-head" data-sort="material_type_name" type="button">Material Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="uom"><button class="sort-head" data-sort="uom_name" type="button">UOM <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="location"><button class="sort-head" data-sort="location_name" type="button">Location <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="stock_type"><button class="sort-head" data-sort="stock_type" type="button">Stock Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="available_qty" class="text-end"><button class="sort-head sort-end" data-sort="available_qty" type="button">Available Qty <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="rate" class="text-end"><button class="sort-head sort-end" data-sort="avg_rate" type="button">Rate <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="value" class="text-end"><button class="sort-head sort-end" data-sort="stock_value" type="button">Value <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="last_movement"><button class="sort-head" data-sort="last_movement_date" type="button">Last Movement <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="status"><button class="sort-head" data-sort="available_qty" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="summary-filter-head" id="summary-filter-head" hidden>
                        <th data-col="item_code"><input id="col-item-code" type="search" placeholder="Search"></th>
                        <th data-col="item_name"><input id="col-item-name" type="search" placeholder="Search"></th>
                        <th data-col="material_type"><select id="col-material-type"></select></th>
                        <th data-col="uom"></th>
                        <th data-col="location"><select id="col-location"></select></th>
                        <th data-col="stock_type"></th>
                        <th data-col="available_qty"><input id="col-qty-min" type="number" step="0.001" placeholder="Min"></th>
                        <th data-col="rate"></th>
                        <th data-col="value"></th>
                        <th data-col="last_movement"><input id="col-last-movement" type="date"></th>
                        <th data-col="status"><select id="col-status"><option value="">All</option><option>Available</option><option>Low Stock</option><option>Out of Stock</option></select></th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="rows"><tr class="skeleton-row"><td colspan="12"></td></tr></tbody>
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
    .summary-kpis { margin-bottom:16px; }
    .summary-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .summary-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .summary-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .summary-card-head p { margin:5px 0 0; }
    .summary-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .summary-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .summary-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .summary-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .summary-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .summary-filter-group select { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .summary-table-toolbar { padding:16px 20px; }
    .summary-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .summary-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .summary-table-toolbar .module-search input { padding-right:38px; }
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
    .summary-data-table-wrap { max-height:calc(100vh - 410px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .summary-data-table { min-width:1280px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .summary-data-table th, .summary-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .summary-data-table th:last-child, .summary-data-table td:last-child { border-right:0; }
    .summary-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; color:var(--muted); font-size:12px; font-weight:900; text-transform:uppercase; }
    .summary-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .summary-filter-head input, .summary-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .summary-filter-head input:focus, .summary-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .summary-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .summary-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .summary-actions { display:inline-flex; justify-content:flex-end; gap:5px; min-width:88px; }
    .summary-actions .icon-btn { width:30px; height:30px; border-radius:6px; color:var(--primary); background:color-mix(in srgb, var(--primary) 10%, var(--surface)); border-color:color-mix(in srgb, var(--primary) 26%, var(--border)); }
    .summary-import-body { display:grid; gap:14px; }
    .summary-import-note { display:grid; gap:6px; padding:12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--muted); font-size:12px; font-weight:700; }
    .summary-history-table { min-width:820px; }
    .summary-history-table th, .summary-history-table td { white-space:nowrap; }
    .summary-history-table td { height:44px; }
    .summary-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .summary-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .summary-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
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
    @media (max-width:768px) {
        .summary-card-head, .summary-filter-row, .summary-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .summary-filter-group, .summary-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .summary-filter-field { flex:1 1 100%; max-width:none; }
        .summary-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .summary-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .summary-table-toolbar .module-search { width:100%; }
        .summary-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .summary-data-table { min-width:1180px; }
    }
</style>
<script>
const endpoint='/v1/stock-summary';
const columnOptions=[['item_code','Item Code'],['item_name','Item Name'],['material_type','Material Type'],['uom','UOM'],['location','Location'],['stock_type','Stock Type'],['available_qty','Available Qty'],['rate','Rate'],['value','Value'],['last_movement','Last Movement'],['status','Status']];
const defaultVisibleColumns=['item_code','item_name','material_type','uom','location','stock_type','available_qty','rate','value','status'];
const columnStorageKey='stockSummary.visibleColumns.v3';
let page=1,last=1,timer,summaryRows=[],visibleColumns=readVisibleColumns(),paginationMeta={from:0,to:0,total:0},sortBy=sessionStorage.getItem('stockSummary.sortBy')||'item_name',sortDirection=sessionStorage.getItem('stockSummary.sortDirection')||'asc';
const opts=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${r[l]||r[k]}</option>`).join('');
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns}
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
function itemTypeOptions(items){const types=[...new Set(items.map(row=>row.item_type).filter(Boolean))].sort();return '<option value="">All Item Types</option>'+types.map(type=>`<option value="${type}">${type}</option>`).join('')}
async function boot(){const [items,types,locs]=await Promise.all([lookup('/v1/items'),lookup('/v1/material-types'),lookup('/v1/locations')]);item_id.innerHTML=opts(items,'item_id','item_name','All Items');item_type.innerHTML=itemTypeOptions(items);material_type_id.innerHTML=opts(types,'material_type_id','material_type_name','All Types');location_id.innerHTML=opts(locs,'location_id','location_name','All Locations');colMaterialType.innerHTML=opts(types,'material_type_id','material_type_name','All');colLocation.innerHTML=opts(locs,'location_id','location_name','All');renderColumnPicker();applyColumnVisibility();load()}
function stockStatus(q,min){if(Number(q)<=0)return'Out of Stock';if(Number(q)<=Number(min))return'Low Stock';return'Available'}
function statusBadge(q,min){const s=stockStatus(q,min);if(s==='Out of Stock')return'<span class="badge badge-danger">Out of Stock</span>';if(s==='Low Stock')return'<span class="badge badge-warning">Low Stock</span>';return'<span class="badge badge-success">Available</span>'}
function storeSort(){sessionStorage.setItem('stockSummary.sortBy',sortBy);sessionStorage.setItem('stockSummary.sortDirection',sortDirection)}
function params(){storeSort();return{page,per_page:per_page.value,search:search.value,item_id:item_id.value,item_type:item_type.value,material_type_id:colMaterialType.value||material_type_id.value,location_id:colLocation.value||location_id.value,sort_by:sortBy,sort_direction:sortDirection}}
function clientFiltered(rows){const code=colItemCode.value.toLowerCase(),name=colItemName.value.toLowerCase(),min=Number(colQtyMin.value||Number.NEGATIVE_INFINITY),date=colLastMovement.value,status=colStatus.value;return rows.filter(x=>(!code||String(x.item_code||'').toLowerCase().includes(code))&&(!name||String(x.item_name||'').toLowerCase().includes(name))&&(!colQtyMin.value||Number(x.available_qty||0)>=min)&&(!date||String(x.last_movement_date||'').slice(0,10)===date)&&(!status||stockStatus(x.available_qty,x.minimum_stock)===status))}
async function load(p=page){page=p;rows.innerHTML='<tr class="skeleton-row"><td colspan="12"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data,m=r.data.metrics;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};m_total.textContent=m.total_items;m_stock.textContent=m.available_stock;m_low.textContent=m.low_stock_items;m_out.textContent=m.out_of_stock_items;summaryRows=clientFiltered(d.data||[]);renderRows();renderSortHeaders();renderPager()}
function money(v){return Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
function qty(v){return Number(v||0).toLocaleString(undefined,{maximumFractionDigits:3})}
function renderRows(){rows.innerHTML=summaryRows.length?summaryRows.map(x=>`<tr><td data-col="item_code">${x.item_code||''}</td><td data-col="item_name">${x.item_name}</td><td data-col="material_type">${x.material_type_name||''}</td><td data-col="uom">${x.uom_name||''}</td><td data-col="location">${x.location_name||''}</td><td data-col="stock_type">${x.stock_type||'Fresh'}</td><td data-col="available_qty" class="text-end">${qty(x.available_qty)}</td><td data-col="rate" class="text-end">${money(x.avg_rate)}</td><td data-col="value" class="text-end">${money(x.stock_value)}</td><td data-col="last_movement">${x.last_movement_date||''}</td><td data-col="status">${statusBadge(x.available_qty,x.minimum_stock)}</td><td class="text-end action-col"><span class="summary-actions"><button class="icon-btn" type="button" data-history-item="${x.item_id}" data-history-location="${x.location_id||''}" data-history-title="${String(x.item_name||'').replaceAll('"','&quot;')}" title="Stock History"><i data-lucide="history"></i></button></span></td></tr>`).join(''):'<tr><td colspan="12" class="text-muted">No stock found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.summary-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('summary-filter-row'),head=document.getElementById('summary-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;if(head)head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function resetFilters(){[search,item_id,item_type,material_type_id,location_id,colItemCode,colItemName,colMaterialType,colLocation,colQtyMin,colLastMovement,colStatus].forEach(el=>el.value='');per_page.value='10';sortBy='item_name';sortDirection='asc';load(1)}
function csvExport(){const valueMap={item_code:x=>x.item_code,item_name:x=>x.item_name,material_type:x=>x.material_type_name,uom:x=>x.uom_name,location:x=>x.location_name,stock_type:x=>x.stock_type||'Fresh',available_qty:x=>x.available_qty,rate:x=>x.avg_rate,value:x=>x.stock_value,last_movement:x=>x.last_movement_date,status:x=>stockStatus(x.available_qty,x.minimum_stock)};const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const esc=v=>`"${String(v??'').replaceAll('"','""')}"`;const csv=[active.map(([,label])=>esc(label)).join(','),...summaryRows.map(row=>active.map(([key])=>esc(valueMap[key](row))).join(','))].join('\n');const b=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='stock-summary.csv';a.click();URL.revokeObjectURL(a.href)}
async function downloadTemplate(){try{const r=await axios.get(endpoint+'/import-template',{responseType:'blob'});const url=URL.createObjectURL(r.data);const a=document.createElement('a');a.href=url;a.download='stock-import-template.csv';a.click();URL.revokeObjectURL(url)}catch(error){window.ErpToast?.show(error.normalizedMessage||'Unable to download sample file.','danger')}}
function openImportModal(){window.ErpModal.open({title:'Import Stock',subtitle:'Add stock quantity to item/location balances',size:'sm',body:`<div class="summary-import-body"><div class="summary-import-note"><strong>Required columns</strong><span>item_code, item_name, location_name, qty, rate, reference.</span><span>Item name is for display only. Import matches stock using item_code and location_name.</span><span>Date and operation are set in background as Stock Import.</span></div><button class="btn-erp" type="button" data-action="download-template"><i data-lucide="download"></i> Sample Excel</button><div class="field"><i data-lucide="file-spreadsheet"></i><label>Stock Import File</label><input id="stock-import-file" type="file" accept=".csv,.xlsx" required></div><div id="stock-import-errors" class="form-errors" hidden></div></div>`,footer:'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-import"><i data-lucide="upload"></i> Import</button>'})}
async function saveImport(){const file=document.getElementById('stock-import-file')?.files?.[0],box=document.getElementById('stock-import-errors');box.hidden=true;if(!file){box.textContent='Select a stock import file.';box.hidden=false;return}const data=new FormData();data.append('file',file);try{const r=await axios.post(endpoint+'/import',data,{headers:{'Content-Type':'multipart/form-data'}});window.ErpModal.close();await load(1);const info=r.data.data;window.ErpToast?.show(`Imported ${info.imported} stock row(s).${info.errors?.length?' Some rows skipped.':''}`)}catch(error){box.textContent=error.normalizedMessage||'Unable to import stock.';box.hidden=false}}
async function openHistory(button){const itemId=button.dataset.historyItem,locationId=button.dataset.historyLocation,title=button.dataset.historyTitle||'Item';window.ErpModal.loading('Stock History','Loading item stock movement...');try{const r=await axios.get(endpoint+'/history',{params:{item_id:itemId,location_id:locationId}});const list=r.data.data||[];window.ErpModal.open({title:'Stock History',subtitle:title,size:'xl',body:`<div class="table-responsive"><table class="table summary-history-table"><thead><tr><th>Date</th><th>Operation</th><th>Location</th><th>Stock Type</th><th>Reference</th><th class="text-end">In</th><th class="text-end">Out</th><th class="text-end">Balance</th></tr></thead><tbody>${list.length?list.map(x=>`<tr><td>${x.transaction_date||''}</td><td>${x.transaction_type||''}</td><td>${x.location_name||''}</td><td>${x.stock_type||'Fresh'}</td><td>${x.reference_type||''}${x.reference_id?' #'+x.reference_id:''}</td><td class="text-end">${Number(x.qty_in||0).toLocaleString(undefined,{maximumFractionDigits:3})}</td><td class="text-end">${Number(x.qty_out||0).toLocaleString(undefined,{maximumFractionDigits:3})}</td><td class="text-end">${Number(x.balance_qty||0).toLocaleString(undefined,{maximumFractionDigits:3})}</td></tr>`).join(''):'<tr><td colspan="8" class="text-muted">No stock history found.</td></tr>'}</tbody></table></div>`,footer:'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>'})}catch(error){window.ErpModal.open({title:'Stock History',subtitle:'Unable to load item movement',size:'sm',body:`<div class="form-errors">${error.normalizedMessage||'Unable to load stock history.'}</div>`,footer:'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>'})}}
document.addEventListener('DOMContentLoaded',()=>{window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');window.columnPickerMenu=document.getElementById('column-picker-menu');window.colItemCode=document.getElementById('col-item-code');window.colItemName=document.getElementById('col-item-name');window.colMaterialType=document.getElementById('col-material-type');window.colLocation=document.getElementById('col-location');window.colQtyMin=document.getElementById('col-qty-min');window.colLastMovement=document.getElementById('col-last-movement');window.colStatus=document.getElementById('col-status');boot();document.querySelector('[data-action="reload"]').onclick=()=>load(page);document.querySelector('[data-action="import-stock"]').onclick=openImportModal;document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('[data-action="download-template"]'))downloadTemplate();if(e.target.closest('[data-action="save-import"]'))saveImport();const history=e.target.closest('[data-history-item]');if(history)openHistory(history);if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[per_page,item_id,item_type,material_type_id,location_id,colMaterialType,colLocation,colLastMovement,colStatus].forEach(e=>e.addEventListener('change',()=>load(1)));[search,colItemCode,colItemName,colQtyMin].forEach(e=>e.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),250)}));});
</script>
@endpush
