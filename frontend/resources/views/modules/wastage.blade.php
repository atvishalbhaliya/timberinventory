@extends('layouts.app')

@section('title', 'Wastage Management - Timber Inventory')

@section('content')
    <section class="grid-kpis summary-kpis">
        <article class="erp-kpi-card"><div class="erp-kpi-label">Total Items</div><div class="erp-kpi-value" id="m_total">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Wastage Qty</div><div class="erp-kpi-value" id="m_stock">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Low Stock</div><div class="erp-kpi-value" id="m_low">0</div></article>
        <article class="erp-kpi-card"><div class="erp-kpi-label">Out Of Stock</div><div class="erp-kpi-value" id="m_out">0</div></article>
    </section>

    <section class="erp-card summary-list-card">
        <div class="summary-card-head">
            <div>
                <h1>Wastage Summary</h1>
                <p class="text-muted">Wastage stock balance by item and location.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp btn-primary" type="button" data-action="open-adjustment" title="Manage wastage"><i data-lucide="sliders-horizontal"></i> Manage Wastage</button>
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
    .wastage-detail-body { display:grid; gap:14px; }
    .wastage-detail-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .wastage-detail-card { padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:var(--surface-soft); }
    .wastage-detail-card span { display:block; color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; }
    .wastage-detail-card strong { display:block; margin-top:4px; font-size:13px; word-break:break-word; }
    .wastage-detail-action-box { display:grid; gap:12px; padding:14px; border:1px solid var(--border); border-radius:10px; background:var(--surface); }
    .wastage-detail-action-box .field { margin:0; }
    .wastage-reuse-section { display:grid; gap:12px; }
    .wastage-reuse-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .wastage-reuse-head h4 { margin:0; font-size:14px; font-weight:900; }
    .wastage-reuse-wrap { border:1px solid var(--border); border-radius:10px; overflow:auto; background:var(--surface-soft); }
    .wastage-reuse-wrap table { width:100%; min-width:860px; margin:0; border-collapse:separate; border-spacing:0; table-layout:fixed; }
    .wastage-reuse-wrap th,.wastage-reuse-wrap td { padding:10px 12px; border-right:1px solid color-mix(in srgb,var(--border) 54%,transparent); border-bottom:1px solid color-mix(in srgb,var(--border) 54%,transparent); vertical-align:middle; background:var(--surface); }
    .wastage-reuse-wrap th { position:sticky; top:0; z-index:1; background:color-mix(in srgb,var(--surface-soft) 82%,var(--surface)); font-size:12px; font-weight:900; text-transform:uppercase; white-space:nowrap; }
    .wastage-reuse-wrap th:nth-child(1),.wastage-reuse-wrap td:nth-child(1){width:42%}
    .wastage-reuse-wrap th:nth-child(2),.wastage-reuse-wrap td:nth-child(2){width:12%}
    .wastage-reuse-wrap th:nth-child(3),.wastage-reuse-wrap td:nth-child(3){width:14%}
    .wastage-reuse-wrap th:nth-child(4),.wastage-reuse-wrap td:nth-child(4){width:14%}
    .wastage-reuse-wrap th:nth-child(5),.wastage-reuse-wrap td:nth-child(5){width:18%}
    .wastage-reuse-wrap td:last-child,.wastage-reuse-wrap th:last-child { text-align:right; border-right:0; }
    .wastage-reuse-wrap tr:hover td { background:color-mix(in srgb,var(--primary) 4%,var(--surface)); }
    .wastage-reuse-wrap .line-input,.wastage-reuse-wrap .line-select { width:100%; min-height:38px; padding:8px 10px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); color:var(--text); font-size:13px; font-weight:700; outline:none; }
    .wastage-reuse-wrap .line-input:focus,.wastage-reuse-wrap .line-select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb),.1); }
    .wastage-reuse-wrap .line-number { text-align:right; }
    .wastage-reuse-wrap .line-actions { display:flex; justify-content:flex-end; }
    .wastage-reuse-wrap .action-delete { width:32px; height:32px; border-radius:8px; }
    .wastage-reuse-summary { position:sticky; bottom:0; z-index:2; display:flex; justify-content:space-between; gap:12px; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:color-mix(in srgb,var(--primary) 7%,var(--surface-soft)); font-weight:900; box-shadow:0 -8px 18px rgba(15,23,42,.05); }
    .wastage-reuse-summary span { color:var(--muted); font-size:11px; text-transform:uppercase; }
    .wastage-reuse-summary strong { display:block; margin-top:3px; font-size:15px; }
    .detail-help { color:var(--muted); font-size:12px; font-weight:700; line-height:1.45; }
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
        .wastage-detail-grid { grid-template-columns:1fr; }
    }
</style>
<script>
const endpoint='/v1/wastage-summary';
const authUser=JSON.parse(localStorage.getItem('user')||'{}');
const canManage= (authUser.permissions||[]).includes('wastage.manage') || authUser.role_name==='Super Admin';
const columnOptions=[['item_code','Item Code'],['item_name','Item Name'],['material_type','Material Type'],['uom','UOM'],['location','Location'],['stock_type','Stock Type'],['available_qty','Available Qty'],['rate','Rate'],['value','Value'],['last_movement','Last Movement'],['status','Status']];
const defaultVisibleColumns=['item_code','item_name','material_type','uom','location','stock_type','available_qty','rate','value','status'];
const columnStorageKey='wastageSummary.visibleColumns.v1';
let page=1,last=1,timer,summaryRows=[],visibleColumns=readVisibleColumns(),paginationMeta={from:0,to:0,total:0},sortBy=sessionStorage.getItem('wastageSummary.sortBy')||'item_name',sortDirection=sessionStorage.getItem('wastageSummary.sortDirection')||'asc',activeDetailRow=null;
const opts=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${r[l]||r[k]}</option>`).join('');
const esc=v=>(v??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns}
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
function itemTypeOptions(items){const types=[...new Set(items.map(row=>row.item_type).filter(Boolean))].sort();return '<option value="">All Item Types</option>'+types.map(type=>`<option value="${type}">${type}</option>`).join('')}
async function boot(){const [items,types,locs,teams,wastages]=await Promise.all([lookup('/v1/items'),lookup('/v1/material-types'),lookup('/v1/locations'),lookup('/v1/teams'),lookup('/v1/wastage',{per_page:100,status:'Posted'})]);item_id.innerHTML=opts(items,'item_id','item_name','All Items');item_type.innerHTML=itemTypeOptions(items);material_type_id.innerHTML=opts(types,'material_type_id','material_type_name','All Types');location_id.innerHTML=opts(locs,'location_id','location_name','All Locations');colMaterialType.innerHTML=opts(types,'material_type_id','material_type_name','All');colLocation.innerHTML=opts(locs,'location_id','location_name','All');window.reuseItems=items;window.reuseTeams=teams;window.reuseLocations=locs;window.reuseWastages=(wastages||[]).filter(x=>x.wastage_type==='Reusable'&&Number(x.available_qty)>0&&x.location_id);renderColumnPicker();applyColumnVisibility();load()}
function stockStatus(q,min){if(Number(q)<=0)return'Out of Stock';if(Number(q)<=Number(min))return'Low Stock';return'Available'}
function statusBadge(q,min){const s=stockStatus(q,min);if(s==='Out of Stock')return'<span class="badge badge-danger">Out of Stock</span>';if(s==='Low Stock')return'<span class="badge badge-warning">Low Stock</span>';return'<span class="badge badge-success">Available</span>'}
function storeSort(){sessionStorage.setItem('wastageSummary.sortBy',sortBy);sessionStorage.setItem('wastageSummary.sortDirection',sortDirection)}
function params(){storeSort();return{page,per_page:per_page.value,search:search.value,item_id:item_id.value,item_type:item_type.value,material_type_id:colMaterialType.value||material_type_id.value,location_id:colLocation.value||location_id.value,stock_type:'Wastage',sort_by:sortBy,sort_direction:sortDirection}}
function clientFiltered(rows){const code=colItemCode.value.toLowerCase(),name=colItemName.value.toLowerCase(),min=Number(colQtyMin.value||Number.NEGATIVE_INFINITY),date=colLastMovement.value,status=colStatus.value;return rows.filter(x=>(!code||String(x.item_code||'').toLowerCase().includes(code))&&(!name||String(x.item_name||'').toLowerCase().includes(name))&&(!colQtyMin.value||Number(x.available_qty||0)>=min)&&(!date||String(x.last_movement_date||'').slice(0,10)===date)&&(!status||stockStatus(x.available_qty,x.minimum_stock)===status))}
async function load(p=page){page=p;rows.innerHTML='<tr class="skeleton-row"><td colspan="12"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data,m=r.data.metrics;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};m_total.textContent=m.total_items;m_stock.textContent=m.available_stock;m_low.textContent=m.low_stock_items;m_out.textContent=m.out_of_stock_items;summaryRows=clientFiltered(d.data||[]);renderRows();renderSortHeaders();renderPager()}
function money(v){return Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
function qty(v){return Number(v||0).toLocaleString(undefined,{maximumFractionDigits:3})}
function round3(value){return Math.round((Number(value)||0)*1000)/1000}
function itemOptions(){return '<option value="">Select Produced Item</option>'+((window.reuseItems||[])).map(item=>`<option value="${item.item_id}">${esc(item.item_name||item.item_id)}</option>`).join('')}
function teamOptions(){return '<option value="">Select Team</option>'+((window.reuseTeams||[])).map(team=>`<option value="${team.team_id}">${esc(team.team_name||team.team_id)}</option>`).join('')}
function locationOptions(){return '<option value="">Select Destination</option>'+((window.reuseLocations||[])).map(loc=>`<option value="${loc.location_id}">${esc(loc.location_name||loc.location_id)}</option>`).join('')}
function wastageSourceOptions(activeId=null){const rows=(window.reuseWastages||[]).filter(w=>w.wastage_type==='Reusable'&&Number(w.available_qty)>0);const options=['<option value="">Select Reusable Wastage</option>'];rows.forEach(w=>{const label=[w.item_name,w.location_name,`Avl ${qty(w.available_qty)}`].filter(Boolean).join(' / ');options.push(`<option value="${w.wastage_stock_id}" ${String(activeId)===String(w.wastage_stock_id)?'selected':''}>${esc(label)}</option>`)});return options.join('')}
function getSelectedSource(){const selectedId=document.getElementById('detail-source')?.value;return (window.reuseWastages||[]).find(w=>String(w.wastage_stock_id)===String(selectedId))||null}
let detailReuseLines=[];
function detailItemOptions(){return '<option value="">Select Produced Item</option>'+((window.reuseItems||[])).map(item=>`<option value="${item.item_id}">${esc(item.item_name||item.item_id)}</option>`).join('')}
function detailBaseRate(itemId){const item=(window.reuseItems||[]).find(item=>String(item.item_id)===String(itemId));return Number(item?.base_rate??item?.opening_rate??0)}
function detailNextLine(row={}){const qty=Number(row.qty??1);const itemRate=detailBaseRate(row.item_id);const rawRate=row.rate!==undefined?Number(row.rate||0):itemRate;const rate=rawRate>0?rawRate:itemRate;const amount=row.amount!==undefined?Number(row.amount||0):round3((qty>0?qty:1)*(rate>=0?rate:0));return{item_id:row.item_id||'',qty:qty>0?qty:1,rate:rate>=0?rate:0,amount:amount>=0?amount:0}}
function detailTotals(){return detailReuseLines.reduce((totals,line)=>({qty:totals.qty+Number(line.qty||0),amount:totals.amount+Number(line.amount||0)}),{qty:0,amount:0})}
function detailRowHtml(line,index,readonly=false){return`<tr data-detail-line="${index}"><td><select class="line-select" data-detail-line="${index}" data-field="item_id" ${readonly?'disabled':''}>${detailItemOptions()}</select></td><td><input class="line-input line-number" data-detail-line="${index}" data-field="qty" type="number" step="0.001" min="0.001" value="${line.qty||1}" ${readonly?'disabled':''}></td><td><input class="line-input line-number" data-detail-line="${index}" data-field="rate" type="number" step="0.01" min="0" value="${line.rate||0}" ${readonly?'disabled':''}></td><td><input class="line-input line-number" data-detail-line="${index}" data-field="amount" type="number" step="0.01" min="0" value="${line.amount||0}" readonly></td><td class="text-end">${readonly?'':`<div class="line-actions"><button class="icon-btn action-delete" type="button" data-remove-detail-line="${index}" title="Remove"><i data-lucide="trash-2"></i></button></div>`}</td></tr>`}
function renderDetailLines(readonly=false){const tbody=document.getElementById('detail-lines');const rows=document.getElementById('detail-row-count');const qty=document.getElementById('detail-total-qty');const cost=document.getElementById('detail-total-cost');if(!tbody)return;tbody.innerHTML=detailReuseLines.length?detailReuseLines.map((line,index)=>detailRowHtml(line,index,readonly)).join(''):`<tr><td colspan="5" class="text-muted">No produced item rows added.</td></tr>`;detailReuseLines.forEach((line,index)=>{const item=document.querySelector(`#detail-lines [data-detail-line="${index}"][data-field="item_id"]`);if(item)item.value=line.item_id||''});const totals=detailTotals();if(rows)rows.textContent=String(detailReuseLines.length);if(qty)qty.textContent=totals.qty?round3(totals.qty):0;if(cost)cost.textContent=totals.amount?money(totals.amount):0;lucide?.createIcons()}
function syncDetailLine(index){const line=detailReuseLines[index];if(!line)return;line.qty=Number(line.qty||1);line.rate=Number(line.rate||0);line.amount=round3(Number(line.qty||0)*Number(line.rate||0));const amountEl=document.querySelector(`#detail-lines [data-detail-line="${index}"][data-field="amount"]`);if(amountEl)amountEl.value=line.amount;const totals=detailTotals();const qty=document.getElementById('detail-total-qty');const cost=document.getElementById('detail-total-cost');if(qty)qty.textContent=totals.qty?round3(totals.qty):0;if(cost)cost.textContent=totals.amount?money(totals.amount):0}
function detailBody(row){const available=Number(row.available_qty||0);const sources=(window.reuseWastages||[]).filter(w=>w.wastage_type==='Reusable'&&Number(w.available_qty)>0);const defaultSource=sources.find(w=>String(w.item_id)===String(row.item_id)&&String(w.location_id)===String(row.location_id))||sources[0]||null;return `<div class="wastage-detail-body"><div class="wastage-detail-grid"><div class="wastage-detail-card"><span>Item</span><strong>${esc(row.item_name||'-')}</strong></div><div class="wastage-detail-card"><span>Item Code</span><strong>${esc(row.item_code||'-')}</strong></div><div class="wastage-detail-card"><span>Category</span><strong>${esc(row.category||'-')}</strong></div><div class="wastage-detail-card"><span>Material Type</span><strong>${esc(row.material_type_name||'-')}</strong></div><div class="wastage-detail-card"><span>Location</span><strong>${esc(row.location_name||'-')}</strong></div><div class="wastage-detail-card"><span>Stock Type</span><strong>${esc(row.stock_type||'Wastage')}</strong></div><div class="wastage-detail-card"><span>Status</span><strong>${esc(stockStatus(row.available_qty,row.minimum_stock))}</strong></div><div class="wastage-detail-card"><span>Available Qty</span><strong>${qty(row.available_qty)}</strong></div><div class="wastage-detail-card"><span>Rate</span><strong>${money(row.avg_rate)}</strong></div><div class="wastage-detail-card"><span>Value</span><strong>${money(row.stock_value)}</strong></div><div class="wastage-detail-card"><span>Last Movement</span><strong>${esc(row.last_movement_date||'-')}</strong></div><div class="wastage-detail-card"><span>UOM</span><strong>${esc(row.uom_name||'-')}</strong></div></div><div class="wastage-detail-action-box"><div class="field"><i data-lucide="recycle"></i><label>Action</label><select id="detail-action"><option value="scrap">Scrap</option><option value="reuse"${available>0?' selected':''}>Reuse</option></select></div><div class="field"><i data-lucide="layers-3"></i><label>Reusable Wastage *</label><select id="detail-source">${wastageSourceOptions(defaultSource?.wastage_stock_id||null)}</select></div><div id="reuse-panel"><div class="field"><i data-lucide="scale"></i><label>Reuse Qty *</label><input id="detail-qty" type="number" step="0.001" min="0.001" max="${available||0}" value="${available>0?available:1}"></div><div class="wastage-reuse-section"><div class="wastage-reuse-head"><h4>Produced Items *</h4><button class="btn-erp" type="button" data-action="add-detail-line"><i data-lucide="plus"></i> Add Row</button></div><div class="wastage-reuse-wrap"><table class="table"><thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th><th class="text-end">Actions</th></tr></thead><tbody id="detail-lines"></tbody></table></div><div class="wastage-reuse-summary"><div><span>Rows</span><strong id="detail-row-count">0</strong></div><div><span>Total Qty</span><strong id="detail-total-qty">0</strong></div><div><span>Total Cost</span><strong id="detail-total-cost">0</strong></div></div></div><div class="field"><i data-lucide="map-pin"></i><label>Destination *</label><select id="detail-destination">${locationOptions()}</select></div><div class="field"><i data-lucide="users"></i><label>Team</label><select id="detail-team">${teamOptions()}</select></div><div class="field"><i data-lucide="message-square"></i><label>Remarks</label><input id="detail-remarks"></div></div><div class="detail-help" id="detail-note"></div><div id="detail-error" class="form-errors" hidden></div></div></div>`}
function updateDetailActionState(){const action=document.getElementById('detail-action');const source=document.getElementById('detail-source');const panel=document.getElementById('reuse-panel');const qtyInput=document.getElementById('detail-qty');const note=document.getElementById('detail-note');const error=document.getElementById('detail-error');if(!action||!source||!panel||!qtyInput||!note||!error||!activeDetailRow)return;const available=Number(activeDetailRow.available_qty||0);const reuse=action.value==='reuse'&&available>0;panel.hidden=!reuse;error.hidden=true;error.textContent='';if(reuse){const selected=getSelectedSource()||defaultSourceForRow(activeDetailRow);note.textContent=`Saving will use reusable wastage ${selected?selected.item_name+' / '+(selected.location_name||''):'from the selected source'} and deduct up to ${qty(available)}.`;qtyInput.max=String(selected?.available_qty||available||0);if(Number(qtyInput.value||0)<=0)qtyInput.value=String(Math.min(available||1,Number(selected?.available_qty||available||1)));document.getElementById('detail-destination').value=selected?.location_id||activeDetailRow.location_id||document.getElementById('detail-destination').value||'';if(!detailReuseLines.length){detailReuseLines=[detailNextLine({item_id:selected?.item_id||activeDetailRow.item_id||'',qty:Number(qtyInput.value||available||1),rate:detailBaseRate(selected?.item_id||activeDetailRow.item_id||'')})]}renderDetailLines();}else{note.textContent='Scrap keeps this record as wastage. Reuse fields stay hidden unless you select Reuse.'}}
function defaultSourceForRow(row){const rows=(window.reuseWastages||[]).filter(w=>w.wastage_type==='Reusable'&&Number(w.available_qty)>0);return rows.find(w=>String(w.item_id)===String(row.item_id)&&String(w.location_id)===String(row.location_id))||rows[0]||null}
function openDetail(row,forceReuse=false){activeDetailRow=row;detailReuseLines=[];window.ErpModal.open({title:'Wastage Detail',subtitle:'Review balance and post reuse in this popup.',size:'lg',body:detailBody(row),footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button><button class="btn-erp btn-primary" type="button" data-action="detail-save"><i data-lucide="save"></i> Save Reuse</button>`});setTimeout(()=>{const action=document.getElementById('detail-action');if(action&&forceReuse)action.value='reuse';action?.addEventListener('change',updateDetailActionState);document.getElementById('detail-source')?.addEventListener('change',updateDetailActionState);document.getElementById('detail-qty')?.addEventListener('input',updateDetailActionState);updateDetailActionState();},0)}
function renderRows(){rows.innerHTML=summaryRows.length?summaryRows.map((x,index)=>`<tr><td data-col="item_code">${x.item_code||''}</td><td data-col="item_name">${x.item_name}</td><td data-col="material_type">${x.material_type_name||''}</td><td data-col="uom">${x.uom_name||''}</td><td data-col="location">${x.location_name||''}</td><td data-col="stock_type">${x.stock_type||'Wastage'}</td><td data-col="available_qty" class="text-end">${qty(x.available_qty)}</td><td data-col="rate" class="text-end">${money(x.avg_rate)}</td><td data-col="value" class="text-end">${money(x.stock_value)}</td><td data-col="last_movement">${x.last_movement_date||''}</td><td data-col="status">${statusBadge(x.available_qty,x.minimum_stock)}</td><td class="text-end action-col"><span class="summary-actions"><button class="icon-btn" type="button" data-detail-row="${index}" title="View Detail"><i data-lucide="eye"></i></button>${Number(x.available_qty||0)>0?`<button class="icon-btn" type="button" data-reuse-row="${index}" title="Reuse"><i data-lucide="recycle"></i></button>`:''}<button class="icon-btn" type="button" data-history-item="${x.item_id}" data-history-location="${x.location_id||''}" data-history-title="${esc(x.item_name||'')}" title="Stock History"><i data-lucide="history"></i></button></span></td></tr>`).join(''):'<tr><td colspan="12" class="text-muted">No wastage stock found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.summary-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('summary-filter-row'),head=document.getElementById('summary-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;if(head)head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function resetFilters(){[search,item_id,item_type,material_type_id,location_id,colItemCode,colItemName,colMaterialType,colLocation,colQtyMin,colLastMovement,colStatus].forEach(el=>el.value='');per_page.value='10';sortBy='item_name';sortDirection='asc';load(1)}
function csvExport(){const valueMap={item_code:x=>x.item_code,item_name:x=>x.item_name,material_type:x=>x.material_type_name,uom:x=>x.uom_name,location:x=>x.location_name,stock_type:x=>x.stock_type||'Wastage',available_qty:x=>x.available_qty,rate:x=>x.avg_rate,value:x=>x.stock_value,last_movement:x=>x.last_movement_date,status:x=>stockStatus(x.available_qty,x.minimum_stock)};const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const esc=v=>`"${String(v??'').replaceAll('"','""')}"`;const csv=[active.map(([,label])=>esc(label)).join(','),...summaryRows.map(row=>active.map(([key])=>esc(valueMap[key](row))).join(','))].join('\n');const b=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='wastage-management.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');window.columnPickerMenu=document.getElementById('column-picker-menu');window.colItemCode=document.getElementById('col-item-code');window.colItemName=document.getElementById('col-item-name');window.colMaterialType=document.getElementById('col-material-type');window.colLocation=document.getElementById('col-location');window.colQtyMin=document.getElementById('col-qty-min');window.colLastMovement=document.getElementById('col-last-movement');window.colStatus=document.getElementById('col-status');const manageBtn=document.querySelector('[data-action="open-adjustment"]');if(manageBtn&&!canManage)manageBtn.remove();boot();document.querySelector('[data-action="reload"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="open-adjustment"]')?.addEventListener('click',()=>window.location.href='/wastage-adjustment');document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[per_page,item_id,item_type,material_type_id,location_id,colMaterialType,colLocation,colLastMovement,colStatus].forEach(el=>el.addEventListener('change',()=>load(1)));[search,colItemCode,colItemName,colQtyMin].forEach(el=>el.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));rows.addEventListener('click',e=>{const detail=e.target.closest('[data-detail-row]')?.dataset.detailRow, reuse=e.target.closest('[data-reuse-row]')?.dataset.reuseRow;if(detail!==undefined)openDetail(summaryRows[Number(detail)]);if(reuse!==undefined)openDetail(summaryRows[Number(reuse)],true)});document.addEventListener('input',e=>{const input=e.target.closest('[data-detail-line][data-field]');if(!input)return;const index=Number(input.dataset.detailLine);detailReuseLines[index]=detailReuseLines[index]||detailNextLine();detailReuseLines[index][input.dataset.field]=input.value;if(input.dataset.field==='qty'||input.dataset.field==='rate'){syncDetailLine(index)}else if(input.dataset.field==='amount'){detailReuseLines[index].amount=round3(input.value||0)}});document.addEventListener('change',e=>{const input=e.target.closest('[data-detail-line][data-field="item_id"]');if(!input)return;const index=Number(input.dataset.detailLine);detailReuseLines[index]=detailReuseLines[index]||detailNextLine();detailReuseLines[index].item_id=input.value;detailReuseLines[index].rate=detailBaseRate(input.value);syncDetailLine(index)});document.addEventListener('click',e=>{const add=e.target.closest('[data-action="add-detail-line"]');if(add){detailReuseLines.push(detailNextLine());renderDetailLines();return}const remove=e.target.closest('[data-remove-detail-line]');if(remove){const index=Number(remove.dataset.removeDetailLine);if(detailReuseLines.length>1){detailReuseLines.splice(index,1);renderDetailLines()}return}});document.addEventListener('click',async e=>{const save=e.target.closest('[data-action="detail-save"]');if(save){const action=document.getElementById('detail-action')?.value||'scrap';if(action==='scrap'){window.ErpModal.close();return}const error=document.getElementById('detail-error');const consumed=Number(document.getElementById('detail-qty')?.value||0);const destinationId=document.getElementById('detail-destination')?.value;const teamId=document.getElementById('detail-team')?.value||null;const cost=Number(detailTotals().amount||0);const remarks=document.getElementById('detail-remarks')?.value||'';const available=Number(activeDetailRow?.available_qty||0);const selectedSource=(getSelectedSource()||defaultSourceForRow(activeDetailRow));if(!(consumed>0)){if(error){error.hidden=false;error.textContent='Please enter a reuse quantity.'}return}if(consumed>available){if(error){error.hidden=false;error.textContent=`Reuse quantity cannot exceed ${qty(available)}.`}return}if(!destinationId){if(error){error.hidden=false;error.textContent='Please select destination.'}return}if(detailReuseLines.length===0||detailReuseLines.some(line=>!line.item_id)){if(error){error.hidden=false;error.textContent='Please add at least one produced item row.'}return}try{if(!selectedSource){if(error){error.hidden=false;error.textContent='No reusable wastage source was found for this item and location.'}return}const payload={reuse_date:new Date().toISOString().slice(0,10),source_wastage_stock_id:selectedSource.wastage_stock_id,consumed_qty:consumed,produced_item_id:detailReuseLines[0]?.item_id||selectedSource.item_id||activeDetailRow.item_id,destination_location_id:destinationId,team_id:teamId,produced_qty:detailTotals().qty,production_cost:cost,remarks,details:detailReuseLines.map((line,index)=>({line_no:index+1,item_id:line.item_id,qty:line.qty,rate:line.rate,amount:line.amount}))};await axios.post('/v1/wastage-reuse',payload);window.ErpModal.close();window.ErpToast?.show('Wastage reuse saved.');await load(page)}catch(err){if(error){error.hidden=false;error.textContent=err.normalizedMessage||'Unable to save reuse.'}}}});});
</script>
@endpush
