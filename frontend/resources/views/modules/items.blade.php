@extends('layouts.app')

@section('title', 'Item Master - Timber Inventory')

@section('content')
    <section class="erp-card item-list-card">
        <div class="item-card-head">
            <div>
                <h1>Item Master</h1>
                <p class="text-muted">Manage raw materials, consumables, and finished goods.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-items" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp" type="button" data-action="import-items" data-manage-only><i data-lucide="upload"></i> Import</button>
                <button class="btn-erp btn-primary" type="button" data-action="new-item" data-manage-only><i data-lucide="plus"></i> Add Item</button>
            </div>
        </div>

        <div class="item-filter-row" id="item-filter-row" hidden>
            <div class="item-filter-group">
                <label class="item-filter-field">
                    <span>Item Type</span>
                    <select id="filter-type"><option value="">All Types</option><option>Raw Material</option><option>Semi Product</option><option>Finish Product</option><option>Wastage</option><option>Scrap</option><option>Consumable</option></select>
                </label>
                <label class="item-filter-field">
                    <span>Material Type</span>
                    <select id="filter-material"></select>
                </label>
                <label class="item-filter-field">
                    <span>UOM</span>
                    <select id="filter-uom"></select>
                </label>
                <label class="item-filter-field">
                    <span>Status</span>
                    <select id="filter-status"><option value="">All Status</option><option>Active</option><option>Inactive</option></select>
                </label>
                <div class="item-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar item-table-toolbar">
            <div class="data-grid-controls">
                <span class="item-page-size">
                    <select id="per_page"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
                </span>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="item-filter-row item-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search item code, name, type">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive item-data-table-wrap">
            <table class="table item-data-table">
                <thead>
                    <tr>
                        <th data-col="item_code"><button class="sort-head" data-sort="item_code" type="button">Code <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item_name"><button class="sort-head" data-sort="item_name" type="button">Name <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="item_type"><button class="sort-head" data-sort="item_type" type="button">Type <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="material_type_id"><button class="sort-head" data-sort="material_type_id" type="button">Material <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="uom_id"><button class="sort-head" data-sort="uom_id" type="button">UOM <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="length_mm" class="text-end"><button class="sort-head sort-end" data-sort="length_mm" type="button">Length <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="width_mm" class="text-end"><button class="sort-head sort-end" data-sort="width_mm" type="button">Width <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="thickness_mm" class="text-end"><button class="sort-head sort-end" data-sort="thickness_mm" type="button">Thickness <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="minimum_stock" class="text-end"><button class="sort-head sort-end" data-sort="minimum_stock" type="button">Min Stock <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="opening_qty" class="text-end"><button class="sort-head sort-end" data-sort="opening_qty" type="button">Opening <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="status"><button class="sort-head" data-sort="status" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="item-filter-head" id="item-filter-head" hidden>
                        <th data-col="item_code"><input id="col-code" type="search" placeholder="Search"></th>
                        <th data-col="item_name"><input id="col-name" type="search" placeholder="Search"></th>
                        <th data-col="item_type"><select id="col-type"><option value="">All</option><option>Raw Material</option><option>Semi Product</option><option>Finish Product</option><option>Wastage</option><option>Scrap</option><option>Consumable</option></select></th>
                        <th data-col="material_type_id"><select id="col-material"></select></th>
                        <th data-col="uom_id"><select id="col-uom"></select></th>
                        <th data-col="length_mm"><input id="col-length-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="width_mm"><input id="col-width-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="thickness_mm"><input id="col-thickness-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="minimum_stock"><input id="col-min-stock" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="opening_qty"><input id="col-opening-min" type="number" min="0" step="0.001" placeholder="Min"></th>
                        <th data-col="status"><select id="col-status"><option value="">All</option><option>Active</option><option>Inactive</option></select></th>
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
    .item-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .item-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .item-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .item-card-head p { margin:5px 0 0; }
    .item-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .item-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .item-filter-field { display:grid; gap:5px; flex:1 1 150px; min-width:140px; max-width:210px; margin:0; }
    .item-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .item-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .item-filter-group select { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .item-table-toolbar { padding:16px 20px; }
    .item-table-toolbar .module-search { width:clamp(190px, 24vw, 280px); }
    .item-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .item-table-toolbar .module-search input { padding-right:38px; }
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
    .item-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .item-data-table { min-width:1320px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .item-data-table th, .item-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .item-data-table th:last-child, .item-data-table td:last-child { border-right:0; }
    .item-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .item-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .item-filter-head input, .item-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .item-filter-head input:focus, .item-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .item-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .item-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .item-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:110px; }
    .item-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .item-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .item-actions .icon-btn svg { width:17px; height:17px; }
    .item-actions .action-view { color:#2563eb; }
    .item-actions .action-edit { color:#f97316; }
    .item-actions .action-delete { color:#dc2626; }
    .item-data-table .action-col { position:static; right:auto; z-index:auto; min-width:126px; width:126px; box-shadow:none; }
    .item-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .item-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .item-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .badge-muted { background:#e5e7eb; color:#4b5563; }
    .item-modal-body { display:grid; gap:14px; padding-bottom:70px; }
    .item-import-body { display:grid; gap:12px; }
    .item-import-body .text-muted { margin:0; font-size:13px; line-height:1.45; }
    .item-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    @media (max-width:900px) { .item-form-grid { grid-template-columns:1fr; } }
    @media (max-width:768px) {
        .item-card-head, .item-filter-row, .item-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .item-filter-group, .item-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .item-filter-field { flex:1 1 100%; max-width:none; }
        .item-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .item-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .item-table-toolbar .module-search { width:100%; }
        .item-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .item-data-table { min-width:1260px; }
    }
</style>
<script>
const endpoint='/v1/items';
const authUser=JSON.parse(localStorage.getItem('user')||'{}');
const canManageItems=(authUser.permissions||[]).includes('masters.manage')||authUser.role_name==='Super Admin';
const columnOptions=[['item_code','Code'],['item_name','Name'],['item_type','Type'],['material_type_id','Material'],['uom_id','UOM'],['length_mm','Length'],['width_mm','Width'],['thickness_mm','Thickness'],['minimum_stock','Min Stock'],['opening_qty','Opening'],['status','Status']];
const defaultVisibleColumns=['item_code','item_name','item_type','material_type_id','uom_id','minimum_stock','status'];
const columnStorageKey='items.visibleColumns';
let page=1,last=1,timer,records=[],materialTypes=[],uoms=[],sortBy=sessionStorage.getItem('items.sortBy.v2')||'created_at',sortDirection=sessionStorage.getItem('items.sortDirection.v2')||'desc',visibleColumns=readVisibleColumns(),activeId=null,activeMode='create',paginationMeta={from:0,to:0,total:0};
const esc=v=>(v??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
const num=v=>v===null||v===undefined||v===''?'':Number(v).toLocaleString(undefined,{maximumFractionDigits:3});
const opt=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${esc(r[l]||r[k])}</option>`).join('');
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns}
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
function materialName(id){return materialTypes.find(row=>String(row.material_type_id)===String(id))?.material_type_name||id||''}
function uomName(id){return uoms.find(row=>String(row.uom_id)===String(id))?.uom_name||id||''}
async function boot(){document.querySelectorAll('[data-manage-only]').forEach(el=>el.hidden=!canManageItems);[materialTypes,uoms]=await Promise.all([lookup('/v1/material-types'),lookup('/v1/uoms')]);filterMaterial.innerHTML=opt(materialTypes,'material_type_id','material_type_name','All Materials');colMaterial.innerHTML=opt(materialTypes,'material_type_id','material_type_name','All');filterUom.innerHTML=opt(uoms,'uom_id','uom_name','All UOMs');colUom.innerHTML=opt(uoms,'uom_id','uom_name','All');renderColumnPicker();restoreState();applyColumnVisibility();load()}
function params(){return{page,per_page:perPage.value,search:search.value,item_type:colType.value||filterType.value,material_type_id:colMaterial.value||filterMaterial.value,uom_id:colUom.value||filterUom.value,status:colStatus.value||filterStatus.value,item_code:colCode.value,item_name:colName.value,length_min:colLengthMin.value,width_min:colWidthMin.value,thickness_min:colThicknessMin.value,minimum_stock_min:colMinStock.value,opening_qty_min:colOpeningMin.value,sort_by:sortBy,sort_direction:sortDirection}}
async function load(p=page){page=p;storeState();rows.innerHTML='<tr class="skeleton-row"><td colspan="12"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};records=d.data||[];renderRows();renderSortHeaders();renderPager()}
function badge(status){return`<span class="badge ${status==='Active'?'badge-success':'badge-muted'}">${esc(status||'Active')}</span>`}
function renderRows(){rows.innerHTML=records.length?records.map(x=>`<tr><td data-col="item_code"><strong>${esc(x.item_code)}</strong></td><td data-col="item_name">${esc(x.item_name)}</td><td data-col="item_type">${esc(x.item_type)}</td><td data-col="material_type_id">${esc(materialName(x.material_type_id))}</td><td data-col="uom_id">${esc(uomName(x.uom_id))}</td><td data-col="length_mm" class="text-end">${num(x.length_mm)}</td><td data-col="width_mm" class="text-end">${num(x.width_mm)}</td><td data-col="thickness_mm" class="text-end">${num(x.thickness_mm)}</td><td data-col="minimum_stock" class="text-end">${num(x.minimum_stock)}</td><td data-col="opening_qty" class="text-end">${num(x.opening_qty)}</td><td data-col="status">${badge(x.status)}</td><td class="text-end action-col"><span class="item-actions"><button class="icon-btn action-view" data-view="${x.item_id}" title="View" aria-label="View"><i data-lucide="eye"></i></button>${canManageItems?`<button class="icon-btn action-edit" data-edit="${x.item_id}" title="Edit" aria-label="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn action-delete" data-delete="${x.item_id}" title="Delete" aria-label="Delete"><i data-lucide="trash-2"></i></button>`:''}</span></td></tr>`).join(''):'<tr><td colspan="12" class="text-muted">No item records found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.item-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('item-filter-row'),head=document.getElementById('item-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function storeState(){sessionStorage.setItem('items.search',search.value);sessionStorage.setItem('items.type',filterType.value);sessionStorage.setItem('items.material',filterMaterial.value);sessionStorage.setItem('items.uom',filterUom.value);sessionStorage.setItem('items.status',filterStatus.value);sessionStorage.setItem('items.perPage',perPage.value);sessionStorage.setItem('items.sortBy.v2',sortBy);sessionStorage.setItem('items.sortDirection.v2',sortDirection);['col-code','col-name','col-type','col-material','col-uom','col-length-min','col-width-min','col-thickness-min','col-min-stock','col-opening-min','col-status'].forEach(id=>sessionStorage.setItem(`items.${id}`,document.getElementById(id).value))}
function restoreState(){search.value=sessionStorage.getItem('items.search')||'';filterType.value=sessionStorage.getItem('items.type')||'';filterMaterial.value=sessionStorage.getItem('items.material')||'';filterUom.value=sessionStorage.getItem('items.uom')||'';filterStatus.value=sessionStorage.getItem('items.status')||'';perPage.value='10';['col-code','col-name','col-type','col-material','col-uom','col-length-min','col-width-min','col-thickness-min','col-min-stock','col-opening-min','col-status'].forEach(id=>{const el=document.getElementById(id);el.value=sessionStorage.getItem(`items.${id}`)||''})}
function resetFilters(){[search,filterType,filterMaterial,filterUom,filterStatus,colCode,colName,colType,colMaterial,colUom,colLengthMin,colWidthMin,colThicknessMin,colMinStock,colOpeningMin,colStatus].forEach(el=>el.value='');perPage.value='10';sortBy='created_at';sortDirection='desc';load(1)}
function modalBody(readonly=false){return`<div class="item-modal-body"><div class="item-form-grid"><div class="field"><i data-lucide="hash"></i><label>Item Code</label><input id="modal-code" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="package"></i><label>Item Name *</label><input id="modal-name" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="layers"></i><label>Item Type *</label><select id="modal-type" ${readonly?'disabled':''}><option>Raw Material</option><option>Semi Product</option><option>Finish Product</option><option>Wastage</option><option>Scrap</option><option>Consumable</option></select></div><div class="field"><i data-lucide="boxes"></i><label>Material Type</label><select id="modal-material" ${readonly?'disabled':''}>${opt(materialTypes,'material_type_id','material_type_name','Material')}</select></div><div class="field"><i data-lucide="ruler"></i><label>UOM</label><select id="modal-uom" ${readonly?'disabled':''}>${opt(uoms,'uom_id','uom_name','UOM')}</select></div><div class="field"><i data-lucide="activity"></i><label>Status</label><select id="modal-status" ${readonly?'disabled':''}><option>Active</option><option>Inactive</option></select></div><div class="field"><i data-lucide="move-horizontal"></i><label>Length MM</label><input id="modal-length" type="number" step="0.001" min="0" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="move-horizontal"></i><label>Width MM</label><input id="modal-width" type="number" step="0.001" min="0" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="move-vertical"></i><label>Thickness MM</label><input id="modal-thickness" type="number" step="0.001" min="0" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="calculator"></i><label>CFT Factor</label><input id="modal-cft" type="number" step="0.001" min="0" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="warehouse"></i><label>Minimum Stock</label><input id="modal-min-stock" type="number" step="0.001" min="0" ${readonly?'disabled':''}></div></div><div id="modal-errors" class="form-errors" hidden></div></div>`}
function fillModal(row){document.getElementById('modal-code').value=row.item_code||'';document.getElementById('modal-name').value=row.item_name||'';document.getElementById('modal-type').value=row.item_type||'Raw Material';document.getElementById('modal-material').value=row.material_type_id||'';document.getElementById('modal-uom').value=row.uom_id||'';document.getElementById('modal-status').value=row.status||'Active';document.getElementById('modal-length').value=row.length_mm||'';document.getElementById('modal-width').value=row.width_mm||'';document.getElementById('modal-thickness').value=row.thickness_mm||'';document.getElementById('modal-cft').value=row.cft_factor||'';document.getElementById('modal-min-stock').value=row.minimum_stock||''}
async function openItemModal(mode,id=null){activeMode=mode;activeId=id;const readonly=mode==='view';window.ErpModal.open({title:mode==='create'?'Add Item':mode==='edit'?'Edit Item':'Display Item',subtitle:'Item master details and stock defaults',size:'xl',body:modalBody(readonly),footer:readonly?'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>':'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-item"><i data-lucide="save"></i> Save</button>'});if(mode==='create'){fillModal({status:'Active',item_type:'Raw Material',minimum_stock:0});lucide?.createIcons();return}const r=await axios.get(`${endpoint}/${id}`);fillModal(r.data.data);lucide?.createIcons()}
function payload(){return{item_code:document.getElementById('modal-code').value,item_name:document.getElementById('modal-name').value,item_type:document.getElementById('modal-type').value,material_type_id:document.getElementById('modal-material').value,uom_id:document.getElementById('modal-uom').value,length_mm:document.getElementById('modal-length').value,width_mm:document.getElementById('modal-width').value,thickness_mm:document.getElementById('modal-thickness').value,cft_factor:document.getElementById('modal-cft').value,minimum_stock:document.getElementById('modal-min-stock').value,status:document.getElementById('modal-status').value}}
async function saveItem(){const box=document.getElementById('modal-errors');box.hidden=true;try{if(activeMode==='edit')await axios.put(`${endpoint}/${activeId}`,payload());else await axios.post(endpoint,payload());window.ErpApi?.clearLookupCache?.();window.ErpModal.close();await load(page);window.ErpToast?.show('Item saved.')}catch(error){box.textContent=error.normalizedMessage||'Unable to save item.';box.hidden=false}}
function deleteItem(id){window.ErpModal.open({title:'Delete Item',subtitle:'Item deletion is blocked by related records in some workflows.',size:'sm',body:'<p>Delete this item?</p>',footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-danger" type="button" data-confirm-delete="${id}"><i data-lucide="trash-2"></i> Delete</button>`})}
function downloadImportTemplate(){const csvRows=[['item_code','item_name','item_type','material_type','uom','length_mm','width_mm','thickness_mm','cft_factor','minimum_stock','status'],['RM-PLY-001','Plywood 18mm','Raw Material','Wood','CFT','2440','1220','18','1','10','Active'],['FG-PALLET-001','Standard Pallet','Finish Product','Finished Goods','PCS','1200','1000','150','1','0','Active']];const csv='\uFEFF'+csvRows.map(row=>row.map(value=>`"${String(value).replaceAll('"','""')}"`).join(',')).join('\r\n');const blob=new Blob([csv],{type:'text/csv;charset=utf-8'});const link=document.createElement('a');const url=URL.createObjectURL(blob);link.href=url;link.download='item-import-template.csv';link.style.display='none';document.body.appendChild(link);link.click();link.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);window.ErpToast?.show('Sample file downloaded.')}
function openImportModal(){window.ErpModal.open({title:'Import Items',subtitle:'Upload bulk items from the sample Excel file',size:'md',body:`<div class="item-import-body"><p class="text-muted">First download the sample Excel file, fill item rows, then upload the saved CSV. Existing item codes will be updated.</p><div class="field"><i data-lucide="file-spreadsheet"></i><label>CSV File *</label><input id="item-import-file" type="file" accept=".csv,text/csv"></div><div id="item-import-errors" class="form-errors" hidden></div></div>`,footer:'<button class="btn-erp" type="button" data-action="download-item-sample"><i data-lucide="download"></i> Sample Excel</button><button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="upload-item-import"><i data-lucide="upload"></i> Upload</button>'});lucide?.createIcons()}
async function uploadItemImport(){const file=document.getElementById('item-import-file')?.files?.[0];const box=document.getElementById('item-import-errors');box.hidden=true;if(!file){box.textContent='Please select the filled item import file.';box.hidden=false;return}const form=new FormData();form.append('file',file);try{const response=await axios.post(`${endpoint}/import`,form,{headers:{'Content-Type':'multipart/form-data'}});window.ErpApi?.clearLookupCache?.();window.ErpModal.close();await load(1);window.ErpToast?.show(response.data.message||'Items imported.')}catch(error){const messages=error.response?.data?.errors?.file;if(Array.isArray(messages)&&messages.length){box.innerHTML=messages.map(message=>`<div>${esc(message)}</div>`).join('')}else{box.textContent=error.normalizedMessage||'Unable to import items.'}box.hidden=false}}
function csvExport(){const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const map={item_code:x=>x.item_code,item_name:x=>x.item_name,item_type:x=>x.item_type,material_type_id:x=>materialName(x.material_type_id),uom_id:x=>uomName(x.uom_id),length_mm:x=>x.length_mm,width_mm:x=>x.width_mm,thickness_mm:x=>x.thickness_mm,minimum_stock:x=>x.minimum_stock,opening_qty:x=>x.opening_qty,status:x=>x.status};const csv=[active.map(([,label])=>`"${label}"`).join(','),...records.map(row=>active.map(([key])=>`"${String(map[key](row)??'').replaceAll('"','""')}"`).join(','))].join('\n');const blob=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='items.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.perPage=document.getElementById('per_page');window.filterType=document.getElementById('filter-type');window.filterMaterial=document.getElementById('filter-material');window.filterUom=document.getElementById('filter-uom');window.filterStatus=document.getElementById('filter-status');window.colCode=document.getElementById('col-code');window.colName=document.getElementById('col-name');window.colType=document.getElementById('col-type');window.colMaterial=document.getElementById('col-material');window.colUom=document.getElementById('col-uom');window.colLengthMin=document.getElementById('col-length-min');window.colWidthMin=document.getElementById('col-width-min');window.colThicknessMin=document.getElementById('col-thickness-min');window.colMinStock=document.getElementById('col-min-stock');window.colOpeningMin=document.getElementById('col-opening-min');window.colStatus=document.getElementById('col-status');window.columnPickerMenu=document.getElementById('column-picker-menu');window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');boot();document.querySelector('[data-action="new-item"]').onclick=()=>openItemModal('create');document.querySelector('[data-action="import-items"]').onclick=openImportModal;document.querySelector('[data-action="reload-items"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[perPage,filterType,filterMaterial,filterUom,filterStatus,colType,colMaterial,colUom,colStatus].forEach(el=>el.addEventListener('change',()=>load(1)));[search,colCode,colName,colLengthMin,colWidthMin,colThicknessMin,colMinStock,colOpeningMin].forEach(el=>el.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));rows.addEventListener('click',async e=>{const view=e.target.closest('[data-view]')?.dataset.view;const edit=e.target.closest('[data-edit]')?.dataset.edit;const del=e.target.closest('[data-delete]')?.dataset.delete;if(view)await openItemModal('view',view);if(edit)await openItemModal('edit',edit);if(del)deleteItem(del)});document.addEventListener('click',async e=>{if(e.target.closest('[data-action="save-item"]'))await saveItem();if(e.target.closest('[data-action="download-item-sample"]'))await downloadImportTemplate();if(e.target.closest('[data-action="upload-item-import"]'))await uploadItemImport();const del=e.target.closest('[data-confirm-delete]')?.dataset.confirmDelete;if(del){try{await axios.delete(`${endpoint}/${del}`);window.ErpModal.close();await load(page);window.ErpToast?.show('Item deleted.')}catch(error){window.ErpToast?.show(error.normalizedMessage||'Delete failed.','danger')}}});});
</script>
@endpush
