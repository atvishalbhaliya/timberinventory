@extends('layouts.app')

@section('title', 'BOM - Timber Inventory')

@section('content')
    <section class="erp-card bom-list-card">
        <div class="bom-card-head">
            <div>
                <h1>BOM Management</h1>
                <p class="text-muted">Manage versioned material recipes for production.</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-boms" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" type="button" data-action="new-bom" data-manage-only><i data-lucide="plus"></i> Add BOM</button>
            </div>
        </div>

        <div class="bom-filter-row" id="bom-filter-row" hidden>
            <div class="bom-filter-group">
                <label class="bom-filter-field">
                    <span>Finished Model</span>
                    <select id="filter-model"></select>
                </label>
                <label class="bom-filter-field">
                    <span>Status</span>
                    <select id="filter-status"><option value="">All Status</option><option>Active</option><option>Inactive</option></select>
                </label>
                <div class="bom-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar bom-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="bom-filter-row bom-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search BOM, model, version">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive bom-data-table-wrap">
            <table class="table bom-data-table">
                <thead>
                    <tr>
                        <th data-col="bom_no"><button class="sort-head" data-sort="bom_no" type="button">BOM No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="bom_name"><button class="sort-head" data-sort="bom_name" type="button">Name <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="model"><button class="sort-head" data-sort="model_name" type="button">Finished Model <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="version"><button class="sort-head" data-sort="version_no" type="button">Version <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="status"><button class="sort-head" data-sort="status" type="button">Status <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="materials" class="text-end"><button class="sort-head sort-end" data-sort="materials_count" type="button">Materials <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="bom-filter-head" id="bom-filter-head" hidden>
                        <th data-col="bom_no"><input id="col-bom-no" type="search" placeholder="Search"></th>
                        <th data-col="bom_name"><input id="col-bom-name" type="search" placeholder="Search"></th>
                        <th data-col="model"><select id="col-model"></select></th>
                        <th data-col="version"><input id="col-version" type="search" placeholder="Search"></th>
                        <th data-col="status"><select id="col-status"><option value="">All</option><option>Active</option><option>Inactive</option></select></th>
                        <th data-col="materials"><input id="col-materials-min" type="number" min="0" step="1" placeholder="Min"></th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="rows"><tr class="skeleton-row"><td colspan="7"></td></tr></tbody>
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
    .bom-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .bom-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .bom-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .bom-card-head p { margin:5px 0 0; }
    .bom-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .bom-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .bom-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:220px; margin:0; }
    .bom-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .bom-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .bom-filter-group select { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .bom-table-toolbar { padding:16px 20px; }
    .bom-table-toolbar .module-search { width:clamp(180px, 22vw, 260px); }
    .bom-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .bom-table-toolbar .module-search input { padding-right:38px; }
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
    .bom-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .bom-data-table { min-width:980px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .bom-data-table th, .bom-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .bom-data-table th:last-child, .bom-data-table td:last-child { border-right:0; }
    .bom-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .bom-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .bom-filter-head input, .bom-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .bom-filter-head input:focus, .bom-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .bom-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .bom-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.sort-end { justify-content:flex-end; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .bom-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:142px; }
    .bom-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .bom-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .bom-actions .icon-btn svg { width:17px; height:17px; }
    .bom-actions .action-view { color:#2563eb; }
    .bom-actions .action-edit { color:#f97316; }
    .bom-actions .action-delete { color:#dc2626; }
    .bom-data-table .action-col { position:static; right:auto; z-index:auto; min-width:156px; width:156px; box-shadow:none; }
    .bom-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .bom-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .bom-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .badge-muted { background:#e5e7eb; color:#4b5563; }
    .bom-modal-body { display:grid; gap:14px; padding-bottom:70px; }
    .bom-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .bom-form-grid .field-wide { grid-column:span 3; }
    .bom-line-wrap { margin:0; }
    .bom-line-wrap .table { min-width:850px; }
    .line-input, .line-select { width:100%; min-height:38px; padding:8px 10px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface); color:var(--text); }
    .line-number { text-align:right; }
    @media (max-width:900px) { .bom-form-grid { grid-template-columns:1fr; } .bom-form-grid .field-wide { grid-column:span 1; } }
    @media (max-width:768px) {
        .bom-card-head, .bom-filter-row, .bom-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .bom-filter-group, .bom-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .bom-filter-field { flex:1 1 100%; max-width:none; }
        .bom-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .bom-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .bom-table-toolbar .module-search { width:100%; }
        .bom-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .bom-data-table { min-width:920px; }
    }
</style>
<script>
const endpoint='/v1/boms';
const authUser=JSON.parse(localStorage.getItem('user')||'{}');
const canManageBom=(authUser.permissions||[]).includes('bom.manage')||authUser.role_name==='Super Admin';
const columnOptions=[['bom_no','BOM No'],['bom_name','Name'],['model','Finished Model'],['version','Version'],['status','Status'],['materials','Materials']];
const defaultVisibleColumns=['bom_no','bom_name','model','version','status'];
const columnStorageKey='bom.visibleColumns';
let page=1,last=1,timer,boms=[],items=[],uoms=[],models=[],sortBy=sessionStorage.getItem('bom.sortBy')||'bom_no',sortDirection=sessionStorage.getItem('bom.sortDirection')||'asc',visibleColumns=readVisibleColumns(),activeMode='create',activeId=null,materialLines=[],paginationMeta={from:0,to:0,total:0};
const esc=v=>(v??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
const opt=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${r[k]}">${esc(r[l]||r[k])}</option>`).join('');
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns;}
async function lookup(e){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e) : (await axios.get(e,{params:{per_page:100}})).data.data.data||[]}
async function boot(){document.querySelectorAll('[data-manage-only]').forEach(el=>el.hidden=!canManageBom);[items,uoms]=await Promise.all([lookup('/v1/items'),lookup('/v1/uoms')]);models=items.filter(item=>String(item.item_type||'').toLowerCase().startsWith('finish product'));filterModel.innerHTML=opt(models,'item_id','item_name','All Finished Models');colModel.innerHTML=opt(models,'item_id','item_name','All');renderColumnPicker();restoreState();applyColumnVisibility();load()}
function params(){return{page,per_page:perPage.value,search:search.value,status:colStatus.value||filterStatus.value,finished_item_id:colModel.value||filterModel.value,bom_no:colBomNo.value,bom_name:colBomName.value,version_no:colVersion.value,materials_min:colMaterialsMin.value,sort_by:sortBy,sort_direction:sortDirection}}
async function load(p=page){page=p;storeState();rows.innerHTML='<tr class="skeleton-row"><td colspan="7"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};boms=d.data||[];renderRows();renderSortHeaders();renderPager()}
function badge(status){return`<span class="badge ${status==='Active'?'badge-success':'badge-muted'}">${esc(status)}</span>`}
function renderRows(){rows.innerHTML=boms.length?boms.map(x=>`<tr><td data-col="bom_no"><strong>${esc(x.bom_no)}</strong></td><td data-col="bom_name">${esc(x.bom_name)}</td><td data-col="model">${esc(x.model_name)}</td><td data-col="version">${esc(x.version_no)}</td><td data-col="status">${badge(x.status)}</td><td data-col="materials" class="text-end">${x.materials_count||0}</td><td class="text-end action-col"><span class="bom-actions"><button class="icon-btn action-view" data-view="${x.bom_id}" title="View" aria-label="View"><i data-lucide="eye"></i></button>${canManageBom?`<button class="icon-btn action-edit" data-edit="${x.bom_id}" title="Edit" aria-label="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn action-delete" data-delete="${x.bom_id}" title="Delete" aria-label="Delete"><i data-lucide="trash-2"></i></button>`:''}</span></td></tr>`).join(''):'<tr><td colspan="7" class="text-muted">No BOM records found.</td></tr>';applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.bom-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('bom-filter-row'),head=document.getElementById('bom-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function storeState(){sessionStorage.setItem('bom.search',search.value);sessionStorage.setItem('bom.status',filterStatus.value);sessionStorage.setItem('bom.model',filterModel.value);sessionStorage.setItem('bom.perPage',perPage.value);sessionStorage.setItem('bom.sortBy',sortBy);sessionStorage.setItem('bom.sortDirection',sortDirection);['col-bom-no','col-bom-name','col-model','col-version','col-status','col-materials-min'].forEach(id=>sessionStorage.setItem(`bom.${id}`,document.getElementById(id).value))}
function restoreState(){search.value=sessionStorage.getItem('bom.search')||'';filterStatus.value=sessionStorage.getItem('bom.status')||'';filterModel.value=sessionStorage.getItem('bom.model')||'';perPage.value=sessionStorage.getItem('bom.perPage')||'10';['col-bom-no','col-bom-name','col-model','col-version','col-status','col-materials-min'].forEach(id=>{const el=document.getElementById(id);el.value=sessionStorage.getItem(`bom.${id}`)||''})}
function resetFilters(){[search,filterStatus,filterModel,colBomNo,colBomName,colModel,colVersion,colStatus,colMaterialsMin].forEach(el=>el.value='');perPage.value='10';sortBy='bom_no';sortDirection='asc';load(1)}
function uomForItem(itemId){return items.find(item=>String(item.item_id)===String(itemId))?.uom_id||''}
function setLineUom(tr,force=false){const itemId=tr.querySelector('[name=item_id]').value,uom=tr.querySelector('[name=uom_id]');if(force||!uom.value)uom.value=uomForItem(itemId)}
async function suggestVersionForModel(modelId){const version=document.getElementById('modal-version');if(!modelId||activeMode!=='create')return;if(version.dataset.touched==='1')return;try{const r=await axios.get(endpoint,{params:{per_page:100,finished_item_id:modelId}}),rows=r.data.data.data||[];let max=0;rows.forEach(row=>{const m=String(row.version_no||'').match(/^V(\d+)$/i);if(m)max=Math.max(max,Number(m[1]))});version.value=`V${max+1}`}catch{version.value=version.value||'V1'}}
function line(row={},readonly=false){const tr=document.createElement('tr');tr.innerHTML=`<td><select class="line-select" name="item_id" required ${readonly?'disabled':''}>${opt(items,'item_id','item_name','Item')}</select></td><td><select class="line-select" name="uom_id" required ${readonly?'disabled':''}>${opt(uoms,'uom_id','uom_name','UOM')}</select></td><td><input class="line-input line-number" name="required_qty" type="number" step="0.001" min="0.001" value="${row.required_qty??1}" required ${readonly?'disabled':''}></td><td><input class="line-input line-number" name="wastage_percent" type="number" step="0.01" min="0" max="100" value="${row.wastage_percent??0}" ${readonly?'disabled':''}></td><td><input class="line-input" name="remarks" value="${esc(row.remarks)}" ${readonly?'disabled':''}></td><td class="text-end">${readonly?'':`<button class="icon-btn action-delete" type="button" data-remove-line title="Remove"><i data-lucide="trash-2"></i></button>`}</td>`;materialRows.appendChild(tr);tr.querySelector('[name=item_id]').value=row.item_id||'';tr.querySelector('[name=uom_id]').value=row.uom_id||uomForItem(row.item_id)||'';if(!readonly)tr.querySelector('[name=item_id]').addEventListener('change',()=>setLineUom(tr,true));lucide?.createIcons()}
function modalBody(readonly=false){return`<div class="bom-modal-body"><div class="bom-form-grid"><div class="field"><i data-lucide="hash"></i><label>BOM No</label><input id="modal-bom-no" type="text" ${readonly?'disabled':'readonly'}></div><div class="field"><i data-lucide="file-text"></i><label>BOM Name *</label><input id="modal-bom-name" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="box"></i><label>Finished Model *</label><select id="modal-model" ${readonly?'disabled':''}>${opt(models,'item_id','item_name','Select finished product')}</select></div><div class="field"><i data-lucide="git-branch"></i><label>Version *</label><input id="modal-version" type="text" value="V1" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="activity"></i><label>Status</label><select id="modal-status" ${readonly?'disabled':''}><option>Active</option><option>Inactive</option></select></div><div class="field field-wide"><i data-lucide="message-square"></i><label>Revision Note</label><input id="modal-note" type="text" ${readonly?'disabled':''}></div></div><div class="table-toolbar" style="padding:0;"><h3 class="erp-card-title">Materials</h3>${readonly?'':'<button class="btn-erp" type="button" data-action="add-line"><i data-lucide="plus"></i> Add Line</button>'}</div><div class="table-responsive bom-line-wrap"><table class="table"><thead><tr><th>Item</th><th>UOM</th><th class="text-end">Required Qty</th><th class="text-end">Wastage %</th><th>Remarks</th><th class="text-end">Actions</th></tr></thead><tbody id="modal-material-rows"></tbody></table></div><div id="modal-errors" class="form-errors" hidden></div></div>`}
async function openBomModal(mode,id=null){activeMode=mode;activeId=id;const readonly=mode==='view';window.ErpModal.open({title:mode==='create'?'Add BOM':mode==='edit'?'Edit BOM':'Display BOM',subtitle:'BOM master and material rows',size:'xl',body:modalBody(readonly),footer:readonly?'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>':'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-bom"><i data-lucide="save"></i> Save</button>'});window.materialRows=document.getElementById('modal-material-rows');const model=document.getElementById('modal-model'),version=document.getElementById('modal-version');if(!readonly){model.addEventListener('change',()=>suggestVersionForModel(model.value));version.addEventListener('input',()=>version.dataset.touched='1')}if(mode==='create'){version.value='';document.getElementById('modal-status').value='Active';const r=await axios.get(endpoint+'/next-number');document.getElementById('modal-bom-no').value=r.data.data.bom_no;line({},false);return}const r=await axios.get(`${endpoint}/${id}`);const x=r.data.data;document.getElementById('modal-bom-no').value=x.bom_no;document.getElementById('modal-bom-name').value=x.bom_name;model.value=x.finished_item_id||'';version.value=x.version_no;document.getElementById('modal-status').value=x.status;document.getElementById('modal-note').value=x.revision_note||'';(x.materials||[]).forEach(row=>line(row,readonly));if(!(x.materials||[]).length)line({},readonly)}
function modalPayload(){return{bom_no:document.getElementById('modal-bom-no').value,bom_name:document.getElementById('modal-bom-name').value,finished_item_id:document.getElementById('modal-model').value,version_no:document.getElementById('modal-version').value,status:document.getElementById('modal-status').value,revision_note:document.getElementById('modal-note').value,materials:[...materialRows.querySelectorAll('tr')].map(tr=>({item_id:tr.querySelector('[name=item_id]').value,uom_id:tr.querySelector('[name=uom_id]').value,required_qty:tr.querySelector('[name=required_qty]').value,wastage_percent:tr.querySelector('[name=wastage_percent]').value,remarks:tr.querySelector('[name=remarks]').value}))}}
async function saveBom(){const box=document.getElementById('modal-errors');box.hidden=true;try{if(activeMode==='edit')await axios.put(`${endpoint}/${activeId}`,modalPayload());else await axios.post(endpoint,modalPayload());window.ErpApi?.clearLookupCache?.();window.ErpModal.close();await load(page);window.ErpToast?.show('BOM saved.')}catch(error){box.textContent=error.normalizedMessage||'Unable to save BOM.';box.hidden=false}}
async function deleteBom(id){window.ErpModal.open({title:'Delete BOM',subtitle:'BOM deletion is blocked when used by posted production.',size:'sm',body:'<p>Delete this BOM?</p>',footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-danger" type="button" data-confirm-delete="${id}"><i data-lucide="trash-2"></i> Delete</button>`})}
function csvExport(){const valueMap={bom_no:x=>x.bom_no,bom_name:x=>x.bom_name,model:x=>x.model_name,version:x=>x.version_no,status:x=>x.status,materials:x=>x.materials_count||0};const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const csv=[active.map(([,label])=>`"${label}"`).join(','),...boms.map(row=>active.map(([key])=>`"${String(valueMap[key](row)??'').replaceAll('"','""')}"`).join(','))].join('\n');const blob=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='boms.csv';a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.perPage=document.getElementById('per_page');window.filterStatus=document.getElementById('filter-status');window.filterModel=document.getElementById('filter-model');window.colBomNo=document.getElementById('col-bom-no');window.colBomName=document.getElementById('col-bom-name');window.colModel=document.getElementById('col-model');window.colVersion=document.getElementById('col-version');window.colStatus=document.getElementById('col-status');window.colMaterialsMin=document.getElementById('col-materials-min');window.columnPickerMenu=document.getElementById('column-picker-menu');window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');boot();document.querySelector('[data-action="new-bom"]').onclick=()=>openBomModal('create');document.querySelector('[data-action="reload-boms"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[perPage,filterStatus,filterModel,colModel,colStatus].forEach(el=>el.addEventListener('change',()=>load(1)));[search,colBomNo,colBomName,colVersion,colMaterialsMin].forEach(el=>el.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));rows.addEventListener('click',async e=>{const view=e.target.closest('[data-view]')?.dataset.view;const edit=e.target.closest('[data-edit]')?.dataset.edit;const del=e.target.closest('[data-delete]')?.dataset.delete;if(view)await openBomModal('view',view);if(edit)await openBomModal('edit',edit);if(del)await deleteBom(del)});document.addEventListener('click',async e=>{if(e.target.closest('[data-action="save-bom"]'))await saveBom();if(e.target.closest('[data-action="add-line"]'))line({},false);if(e.target.closest('[data-remove-line]'))e.target.closest('tr').remove();const del=e.target.closest('[data-confirm-delete]')?.dataset.confirmDelete;if(del){try{await axios.delete(`${endpoint}/${del}`);window.ErpModal.close();await load(page);window.ErpToast?.show('BOM deleted.')}catch(error){window.ErpToast?.show(error.normalizedMessage||'Delete failed.','danger')}}});});
</script>
@endpush
