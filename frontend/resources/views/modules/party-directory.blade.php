@extends('layouts.app')

@section('title', $title.' - Timber Inventory')

@section('content')
    <section class="erp-card party-list-card">
        <div class="party-card-head">
            <div>
                <h1>{{ $title }}</h1>
                <p class="text-muted">{{ $description }}</p>
            </div>
            <div class="module-actions">
                <button class="btn-erp" data-action="reload-parties" title="Refresh"><i data-lucide="refresh-cw"></i> Refresh</button>
                <button class="btn-erp btn-primary" type="button" data-action="new-party" data-manage-only><i data-lucide="plus"></i> Add {{ $singular }}</button>
            </div>
        </div>

        <div class="party-filter-row" id="party-filter-row" hidden>
            <div class="party-filter-group">
                <label class="party-filter-field">
                    <span>State</span>
                    <select id="filter-state"></select>
                </label>
                <label class="party-filter-field">
                    <span>GST No</span>
                    <input id="filter-gst" type="search" placeholder="GST">
                </label>
                <div class="party-filter-actions">
                    <button class="btn-erp btn-primary btn-filter" type="button" data-action="apply-filters"><i data-lucide="filter"></i> Apply</button>
                    <button class="btn-erp btn-filter btn-filter-secondary" type="button" data-action="reset-filters"><i data-lucide="filter-x"></i> Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar party-table-toolbar">
            <div class="data-grid-controls">
                <select id="per_page"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
                <div class="column-picker">
                    <button class="btn-erp btn-column-picker" type="button" data-action="toggle-columns" aria-expanded="false"><i data-lucide="columns-3"></i> Columns</button>
                    <div class="column-picker-menu" id="column-picker-menu"></div>
                </div>
                <button class="btn-erp btn-filter-toggle" type="button" data-action="toggle-filter-row" aria-controls="party-filter-row party-filter-head" aria-pressed="false" title="Show filters"><i data-lucide="filter"></i> Filters</button>
            </div>
            <div class="module-search">
                <i data-lucide="search"></i>
                <input id="search" type="search" placeholder="Search code, name, mobile, GST">
                <button class="search-clear" data-action="clear-search" type="button" title="Clear search"><i data-lucide="x"></i></button>
            </div>
        </div>

        <div class="table-responsive party-data-table-wrap">
            <table class="table party-data-table">
                <thead>
                    <tr>
                        <th data-col="party_code"><button class="sort-head" data-sort="party_code" type="button">Code <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="party_name"><button class="sort-head" data-sort="party_name" type="button">Name <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="contact_person"><button class="sort-head" data-sort="contact_person" type="button">Contact <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="mobile"><button class="sort-head" data-sort="mobile" type="button">Mobile <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="email"><button class="sort-head" data-sort="email" type="button">Email <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="state"><button class="sort-head" data-sort="state" type="button">State <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="gst_no"><button class="sort-head" data-sort="gst_no" type="button">GST No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th data-col="pan_no"><button class="sort-head" data-sort="pan_no" type="button">PAN No <i data-lucide="chevrons-up-down"></i></button></th>
                        <th class="text-end action-col">Actions</th>
                    </tr>
                    <tr class="party-filter-head" id="party-filter-head" hidden>
                        <th data-col="party_code"><input id="col-code" type="search" placeholder="Search"></th>
                        <th data-col="party_name"><input id="col-name" type="search" placeholder="Search"></th>
                        <th data-col="contact_person"><input id="col-contact" type="search" placeholder="Search"></th>
                        <th data-col="mobile"><input id="col-mobile" type="search" placeholder="Search"></th>
                        <th data-col="email"><input id="col-email" type="search" placeholder="Search"></th>
                        <th data-col="state"><select id="col-state"></select></th>
                        <th data-col="gst_no"><input id="col-gst" type="search" placeholder="Search"></th>
                        <th data-col="pan_no"><input id="col-pan" type="search" placeholder="Search"></th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="rows"><tr class="skeleton-row"><td colspan="9"></td></tr></tbody>
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
    .party-list-card { overflow:visible; border-radius:10px; box-shadow:0 8px 22px rgba(15,23,42,.1), 0 22px 46px rgba(15,23,42,.07); }
    .party-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 20px 16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 48%, var(--surface)), var(--surface)); }
    .party-card-head h1 { margin:0; font-size:22px; line-height:1.2; font-weight:800; }
    .party-card-head p { margin:5px 0 0; }
    .party-filter-row { display:flex; align-items:flex-end; justify-content:center; gap:10px; padding:16px 20px; border-bottom:1px solid var(--border); background:var(--surface); }
    .party-filter-group { display:flex; align-items:flex-end; justify-content:center; gap:10px; flex:1 1 auto; flex-wrap:wrap; max-width:1320px; }
    .party-filter-field { display:grid; gap:5px; flex:1 1 170px; min-width:150px; max-width:240px; margin:0; }
    .party-filter-field span { color:var(--muted); font-size:11px; font-weight:900; line-height:1; text-transform:uppercase; }
    .party-filter-actions { display:flex; align-items:flex-end; gap:10px; flex:0 0 auto; }
    .party-filter-group select, .party-filter-group input { width:100%; min-height:40px; padding:0 12px; border:1px solid var(--border); border-radius:var(--radius); background:var(--surface-soft); color:var(--text); font-weight:700; }
    .btn-filter { min-height:40px; padding:8px 12px; font-size:13px; line-height:1; }
    .btn-filter svg { width:16px; height:16px; }
    .btn-filter-secondary { background:var(--surface-soft); color:var(--muted); }
    .party-table-toolbar { padding:16px 20px; }
    .party-table-toolbar .module-search { width:clamp(190px, 24vw, 280px); }
    .party-table-toolbar .module-search > svg { color:#cbd5e1; stroke:#cbd5e1; }
    .party-table-toolbar .module-search input { padding-right:38px; }
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
    .party-data-table-wrap { max-height:calc(100vh - 360px); min-height:320px; margin:0 20px 16px; overflow:auto; }
    .party-data-table { min-width:1120px; table-layout:auto; border-collapse:separate; border-spacing:0; }
    .party-data-table th, .party-data-table td { border-right:1px solid color-mix(in srgb, var(--border) 54%, transparent); border-bottom:1px solid color-mix(in srgb, var(--border) 54%, transparent); }
    .party-data-table th:last-child, .party-data-table td:last-child { border-right:0; }
    .party-data-table th { top:0; z-index:4; height:44px; padding:0 12px; vertical-align:middle; white-space:nowrap; letter-spacing:0; }
    .party-filter-head th { top:44px; z-index:3; height:46px; padding:6px 10px; background:color-mix(in srgb, var(--surface-soft) 74%, var(--surface)); }
    .party-filter-head input, .party-filter-head select { width:100%; min-height:32px; padding:0 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:12px; outline:none; }
    .party-filter-head input:focus, .party-filter-head select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(var(--primary-rgb), .1); }
    .party-data-table td { height:54px; vertical-align:middle; white-space:nowrap; }
    .party-data-table tbody tr:hover { box-shadow:inset 3px 0 0 var(--primary); }
    .sort-head { display:inline-flex; align-items:center; gap:6px; width:100%; min-height:34px; padding:0; border:0; background:transparent; color:var(--muted); font-size:12px; font-weight:900; text-align:left; text-transform:uppercase; }
    .sort-head svg { width:15px; height:15px; opacity:.7; }
    .sort-head.is-active { color:var(--primary); }
    .sort-head.is-active svg { opacity:1; }
    .party-actions { display:inline-flex; align-items:center; justify-content:flex-end; gap:5px; min-width:110px; }
    .party-actions .icon-btn { width:30px; height:30px; border-radius:6px; background:color-mix(in srgb, currentColor 10%, var(--surface)); border-color:color-mix(in srgb, currentColor 26%, var(--border)); transition:transform .16s ease, box-shadow .16s ease; }
    .party-actions .icon-btn:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(15,23,42,.12); }
    .party-actions .icon-btn svg { width:17px; height:17px; }
    .party-actions .action-view { color:#2563eb; }
    .party-actions .action-edit { color:#f97316; }
    .party-actions .action-delete { color:#dc2626; }
    .party-data-table .action-col { position:static; right:auto; z-index:auto; min-width:126px; width:126px; box-shadow:none; }
    .party-list-card .pagination-row { justify-content:space-between; padding-inline:20px; }
    .party-list-card .pager-actions { justify-content:flex-start; margin-left:0; flex-wrap:wrap; }
    .party-list-card .pager-actions .btn-erp { min-height:36px; padding:7px 10px; }
    .btn-page-nav { width:36px; justify-content:center; padding:7px !important; }
    .page-number-list { display:inline-flex; align-items:center; gap:4px; flex-wrap:wrap; }
    .page-number-list .btn-page-number { min-width:34px; min-height:34px; padding:6px 9px; justify-content:center; }
    .page-number-list .btn-page-number.is-active { color:#fff; border-color:var(--primary); background:var(--primary); }
    .page-number-ellipsis { display:inline-flex; align-items:center; min-height:34px; padding:0 6px; color:var(--muted); font-weight:800; }
    .page-record-status { margin-left:8px; white-space:nowrap; }
    .party-modal-body { display:grid; gap:14px; padding-bottom:70px; }
    .party-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .party-form-grid .field-wide { grid-column:span 3; }
    @media (max-width:900px) { .party-form-grid { grid-template-columns:1fr; } .party-form-grid .field-wide { grid-column:span 1; } }
    @media (max-width:768px) {
        .party-card-head, .party-filter-row, .party-table-toolbar, .pagination-row { align-items:stretch; flex-direction:column; }
        .party-filter-group, .party-filter-actions, .pager-actions, .module-actions { width:100%; flex:1 1 auto; flex-wrap:wrap; }
        .party-filter-field { flex:1 1 100%; max-width:none; }
        .party-filter-actions { display:grid; grid-template-columns:1fr 1fr; }
        .party-filter-group .btn-erp, .pager-actions .btn-erp, .module-actions .btn-erp { width:100%; }
        .party-table-toolbar .module-search { width:100%; }
        .party-data-table-wrap { max-height:none; min-height:0; margin-inline:12px; }
        .party-data-table { min-width:1060px; }
    }
</style>
<script>
const endpoint='/v1/parties';
const partyType=@json($partyType);
const singular=@json($singular);
const authUser=JSON.parse(localStorage.getItem('user')||'{}');
const canManageParties=(authUser.permissions||[]).includes('masters.manage')||authUser.role_name==='Super Admin';
const columnOptions=[['party_code','Code'],['party_name','Name'],['contact_person','Contact'],['mobile','Mobile'],['email','Email'],['state','State'],['gst_no','GST No'],['pan_no','PAN No']];
const defaultVisibleColumns=['party_code','party_name','contact_person','mobile','state','gst_no'];
const columnStorageKey=`${partyType.toLowerCase()}.visibleColumns`;
let page=1,last=1,timer,records=[],states=[],sortBy=sessionStorage.getItem(`${partyType}.sortBy`)||'party_code',sortDirection=sessionStorage.getItem(`${partyType}.sortDirection`)||'asc',visibleColumns=readVisibleColumns(),activeId=null,activeMode='create',paginationMeta={from:0,to:0,total:0};
const esc=v=>(v??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
const opt=(rows,k,l,p)=>`<option value="">${p}</option>`+rows.map(r=>`<option value="${esc(r[k])}">${esc(r[l]||r[k])}</option>`).join('');
function readVisibleColumns(){try{const stored=JSON.parse(localStorage.getItem(columnStorageKey)||'null');if(Array.isArray(stored)&&stored.length)return stored;}catch{}return defaultVisibleColumns}
async function lookup(e,params={}){return window.ErpApi?.cachedList ? window.ErpApi.cachedList(e,params) : (await axios.get(e,{params:{per_page:100,...params}})).data.data.data||[]}
async function boot(){document.querySelectorAll('[data-manage-only]').forEach(el=>el.hidden=!canManageParties);states=await lookup('/v1/states',{sort_by:'state_name',sort_direction:'asc'});filterState.innerHTML=opt(states,'state_name','state_name','All States');colState.innerHTML=opt(states,'state_name','state_name','All');renderColumnPicker();restoreState();applyColumnVisibility();load()}
function params(){return{page,per_page:perPage.value,search:search.value,party_type:partyType,state:colState.value||filterState.value,gst_no:colGst.value||filterGst.value,party_code:colCode.value,party_name:colName.value,contact_person:colContact.value,mobile:colMobile.value,email:colEmail.value,pan_no:colPan.value,sort_by:sortBy,sort_direction:sortDirection}}
async function load(p=page){page=p;storeState();rows.innerHTML='<tr class="skeleton-row"><td colspan="9"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;page=d.current_page;last=d.last_page;paginationMeta={from:d.from||0,to:d.to||0,total:d.total||0};records=d.data||[];renderRows();renderSortHeaders();renderPager()}
function renderRows(){rows.innerHTML=records.length?records.map(x=>`<tr><td data-col="party_code"><strong>${esc(x.party_code)}</strong></td><td data-col="party_name">${esc(x.party_name)}</td><td data-col="contact_person">${esc(x.contact_person)}</td><td data-col="mobile">${esc(x.mobile)}</td><td data-col="email">${esc(x.email)}</td><td data-col="state">${esc(x.state)}</td><td data-col="gst_no">${esc(x.gst_no)}</td><td data-col="pan_no">${esc(x.pan_no)}</td><td class="text-end action-col"><span class="party-actions"><button class="icon-btn action-view" data-view="${x.party_id}" title="View" aria-label="View"><i data-lucide="eye"></i></button>${canManageParties?`<button class="icon-btn action-edit" data-edit="${x.party_id}" title="Edit" aria-label="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn action-delete" data-delete="${x.party_id}" title="Delete" aria-label="Delete"><i data-lucide="trash-2"></i></button>`:''}</span></td></tr>`).join(''):`<tr><td colspan="9" class="text-muted">No ${singular.toLowerCase()} records found.</td></tr>`;applyColumnVisibility();lucide?.createIcons()}
function renderSortHeaders(){document.querySelectorAll('[data-sort]').forEach(button=>{const active=button.dataset.sort===sortBy;button.classList.toggle('is-active',active);const label=button.dataset.label||button.textContent.trim();button.dataset.label=label;button.innerHTML=`${label} <i data-lucide="${active?(sortDirection==='asc'?'arrow-up':'arrow-down'):'chevrons-up-down'}"></i>`});lucide?.createIcons()}
function pageRange(){const pages=[];if(last<=7){for(let i=1;i<=last;i++)pages.push(i);return pages}pages.push(1);let start=Math.max(2,page-1),end=Math.min(last-1,page+1);if(page<=2){start=2;end=3}else if(page>=last-1){start=last-2;end=last-1}if(start>2)pages.push('...');for(let i=start;i<=end;i++)pages.push(i);if(end<last-1)pages.push('...');pages.push(last);return pages}
function renderPager(){pageLinks.innerHTML=pageRange().map(value=>value==='...'?'<span class="page-number-ellipsis">...</span>':`<button class="btn-erp btn-page-number ${Number(value)===page?'is-active':''}" type="button" data-page="${value}">${value}</button>`).join('');pageStatus.textContent=paginationMeta.total?`Showing ${paginationMeta.from} to ${paginationMeta.to} of ${paginationMeta.total} records`:'Showing 0 to 0 of 0 records';document.querySelector('[data-action="prev-page"]').disabled=page<=1;document.querySelector('[data-action="next-page"]').disabled=page>=last;lucide?.createIcons()}
function renderColumnPicker(){columnPickerMenu.innerHTML=columnOptions.map(([key,label])=>`<label><input type="checkbox" value="${key}" ${visibleColumns.includes(key)?'checked':''}><span>${label}</span></label>`).join('')}
function applyColumnVisibility(){const visible=new Set(visibleColumns);document.querySelectorAll('.party-data-table [data-col]').forEach(cell=>cell.classList.toggle('is-hidden-column',!visible.has(cell.dataset.col)))}
function updateColumnVisibility(column,checked){visibleColumns=checked?[...new Set([...visibleColumns,column])]:visibleColumns.filter(item=>item!==column);localStorage.setItem(columnStorageKey,JSON.stringify(visibleColumns));applyColumnVisibility()}
function toggleFilterRow(){const row=document.getElementById('party-filter-row'),head=document.getElementById('party-filter-head'),button=document.querySelector('[data-action="toggle-filter-row"]'),open=row.hidden;row.hidden=!open;head.hidden=!open;button.classList.toggle('is-active',open);button.setAttribute('aria-pressed',open?'true':'false');button.title=open?'Hide filters':'Show filters'}
function storeState(){sessionStorage.setItem(`${partyType}.search`,search.value);sessionStorage.setItem(`${partyType}.state`,filterState.value);sessionStorage.setItem(`${partyType}.gst`,filterGst.value);sessionStorage.setItem(`${partyType}.perPage`,perPage.value);sessionStorage.setItem(`${partyType}.sortBy`,sortBy);sessionStorage.setItem(`${partyType}.sortDirection`,sortDirection);['col-code','col-name','col-contact','col-mobile','col-email','col-state','col-gst','col-pan'].forEach(id=>sessionStorage.setItem(`${partyType}.${id}`,document.getElementById(id).value))}
function restoreState(){search.value=sessionStorage.getItem(`${partyType}.search`)||'';filterState.value=sessionStorage.getItem(`${partyType}.state`)||'';filterGst.value=sessionStorage.getItem(`${partyType}.gst`)||'';perPage.value=sessionStorage.getItem(`${partyType}.perPage`)||'10';['col-code','col-name','col-contact','col-mobile','col-email','col-state','col-gst','col-pan'].forEach(id=>{const el=document.getElementById(id);el.value=sessionStorage.getItem(`${partyType}.${id}`)||''})}
function resetFilters(){[search,filterState,filterGst,colCode,colName,colContact,colMobile,colEmail,colState,colGst,colPan].forEach(el=>el.value='');perPage.value='10';sortBy='party_code';sortDirection='asc';load(1)}
async function nextCode(){const r=await axios.get('/v1/parties/next-code',{params:{party_type:partyType}});return r.data.data.party_code}
function modalBody(readonly=false){return`<div class="party-modal-body"><div class="party-form-grid"><div class="field"><i data-lucide="hash"></i><label>${singular} Code</label><input id="modal-code" type="text" readonly></div><div class="field"><i data-lucide="building-2"></i><label>${singular} Name *</label><input id="modal-name" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="user"></i><label>Contact Person</label><input id="modal-contact" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="phone"></i><label>Mobile No</label><input id="modal-mobile" type="tel" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="mail"></i><label>Email</label><input id="modal-email" type="email" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="map"></i><label>State</label><select id="modal-state" ${readonly?'disabled':''}>${opt(states,'state_name','state_name','Select State')}</select></div><div class="field"><i data-lucide="file-badge"></i><label>GST No</label><input id="modal-gst" type="text" ${readonly?'disabled':''}></div><div class="field"><i data-lucide="badge-indian-rupee"></i><label>PAN No</label><input id="modal-pan" type="text" ${readonly?'disabled':''}></div><div class="field field-wide"><i data-lucide="map-pin"></i><label>Address</label><input id="modal-address" type="text" ${readonly?'disabled':''}></div><div class="field field-wide"><i data-lucide="message-square"></i><label>Remarks</label><input id="modal-remarks" type="text" ${readonly?'disabled':''}></div></div><div id="modal-errors" class="form-errors" hidden></div></div>`}
function fillModal(row){document.getElementById('modal-code').value=row.party_code||'';document.getElementById('modal-name').value=row.party_name||'';document.getElementById('modal-contact').value=row.contact_person||'';document.getElementById('modal-mobile').value=row.mobile||'';document.getElementById('modal-email').value=row.email||'';document.getElementById('modal-state').value=row.state||'';document.getElementById('modal-gst').value=row.gst_no||'';document.getElementById('modal-pan').value=row.pan_no||'';document.getElementById('modal-address').value=row.address||'';document.getElementById('modal-remarks').value=row.remarks||''}
async function openPartyModal(mode,id=null){activeMode=mode;activeId=id;const readonly=mode==='view';window.ErpModal.open({title:mode==='create'?`Add ${singular}`:mode==='edit'?`Edit ${singular}`:`Display ${singular}`,subtitle:`${singular} account and tax details`,size:'xl',body:modalBody(readonly),footer:readonly?'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Close</button>':'<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-primary" type="button" data-action="save-party"><i data-lucide="save"></i> Save</button>'});if(mode==='create'){fillModal({party_code:await nextCode()});lucide?.createIcons();return}const r=await axios.get(`${endpoint}/${id}`);fillModal(r.data.data);lucide?.createIcons()}
function payload(){return{party_type:partyType,party_name:document.getElementById('modal-name').value,contact_person:document.getElementById('modal-contact').value,mobile:document.getElementById('modal-mobile').value,email:document.getElementById('modal-email').value,address:document.getElementById('modal-address').value,state:document.getElementById('modal-state').value,gst_no:document.getElementById('modal-gst').value,pan_no:document.getElementById('modal-pan').value,remarks:document.getElementById('modal-remarks').value}}
async function saveParty(){const box=document.getElementById('modal-errors');box.hidden=true;try{if(activeMode==='edit')await axios.put(`${endpoint}/${activeId}`,payload());else await axios.post(endpoint,payload());window.ErpApi?.clearLookupCache?.();window.ErpModal.close();await load(page);window.ErpToast?.show(`${singular} saved.`)}catch(error){box.textContent=error.normalizedMessage||`Unable to save ${singular.toLowerCase()}.`;box.hidden=false}}
function deleteParty(id){window.ErpModal.open({title:`Delete ${singular}`,subtitle:'This action cannot be undone.',size:'sm',body:`<p>Delete this ${singular.toLowerCase()}?</p>`,footer:`<button class="btn-erp" type="button" data-modal-close><i data-lucide="x"></i> Cancel</button><button class="btn-erp btn-danger" type="button" data-confirm-delete="${id}"><i data-lucide="trash-2"></i> Delete</button>`})}
async function csvExport(){const r=await axios.get(`${endpoint}/export`,{params:params()});const exportRows=r.data.data||records;const active=columnOptions.filter(([key])=>visibleColumns.includes(key));const map={party_code:x=>x.party_code,party_name:x=>x.party_name,contact_person:x=>x.contact_person,mobile:x=>x.mobile,email:x=>x.email,state:x=>x.state,gst_no:x=>x.gst_no,pan_no:x=>x.pan_no};const csv=[active.map(([,label])=>`"${label}"`).join(','),...exportRows.map(row=>active.map(([key])=>`"${String(map[key](row)??'').replaceAll('"','""')}"`).join(','))].join('\n');const blob=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=`${singular.toLowerCase()}s.csv`;a.click();URL.revokeObjectURL(a.href)}
document.addEventListener('DOMContentLoaded',()=>{window.perPage=document.getElementById('per_page');window.filterState=document.getElementById('filter-state');window.filterGst=document.getElementById('filter-gst');window.colCode=document.getElementById('col-code');window.colName=document.getElementById('col-name');window.colContact=document.getElementById('col-contact');window.colMobile=document.getElementById('col-mobile');window.colEmail=document.getElementById('col-email');window.colState=document.getElementById('col-state');window.colGst=document.getElementById('col-gst');window.colPan=document.getElementById('col-pan');window.columnPickerMenu=document.getElementById('column-picker-menu');window.pageStatus=document.getElementById('page-status');window.pageLinks=document.getElementById('page-links');boot();document.querySelector('[data-action="new-party"]').onclick=()=>openPartyModal('create');document.querySelector('[data-action="reload-parties"]').onclick=()=>load(page);document.querySelector('[data-action="apply-filters"]').onclick=()=>load(1);document.querySelector('[data-action="reset-filters"]').onclick=resetFilters;document.querySelector('[data-action="toggle-filter-row"]').onclick=toggleFilterRow;document.querySelector('[data-action="clear-search"]').onclick=()=>{search.value='';load(1)};document.querySelector('[data-action="prev-page"]').onclick=()=>page>1&&load(page-1);document.querySelector('[data-action="next-page"]').onclick=()=>page<last&&load(page+1);pageLinks.addEventListener('click',e=>{const target=e.target.closest('[data-page]');if(target)load(Number(target.dataset.page))});document.querySelector('[data-action="export"]').onclick=csvExport;document.querySelector('[data-action="print"]').onclick=()=>window.print();document.querySelector('[data-action="toggle-columns"]').onclick=function(){const picker=this.closest('.column-picker');picker.classList.toggle('is-open');this.setAttribute('aria-expanded',picker.classList.contains('is-open')?'true':'false')};columnPickerMenu.addEventListener('change',e=>{const cb=e.target.closest('input[type="checkbox"]');if(cb)updateColumnVisibility(cb.value,cb.checked)});document.addEventListener('click',e=>{if(e.target.closest('.column-picker'))return;document.querySelector('.column-picker')?.classList.remove('is-open');document.querySelector('[data-action="toggle-columns"]')?.setAttribute('aria-expanded','false')});document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',function(){if(sortBy===this.dataset.sort)sortDirection=sortDirection==='asc'?'desc':'asc';else{sortBy=this.dataset.sort;sortDirection='asc'}load(1)}));[perPage,filterState,colState].forEach(el=>el.addEventListener('change',()=>load(1)));[search,filterGst,colCode,colName,colContact,colMobile,colEmail,colGst,colPan].forEach(el=>el.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),300)}));rows.addEventListener('click',async e=>{const view=e.target.closest('[data-view]')?.dataset.view;const edit=e.target.closest('[data-edit]')?.dataset.edit;const del=e.target.closest('[data-delete]')?.dataset.delete;if(view)await openPartyModal('view',view);if(edit)await openPartyModal('edit',edit);if(del)deleteParty(del)});document.addEventListener('click',async e=>{if(e.target.closest('[data-action="save-party"]'))await saveParty();const del=e.target.closest('[data-confirm-delete]')?.dataset.confirmDelete;if(del){try{await axios.delete(`${endpoint}/${del}`);window.ErpModal.close();await load(page);window.ErpToast?.show(`${singular} deleted.`)}catch(error){window.ErpToast?.show(error.normalizedMessage||'Delete failed.','danger')}}});});
</script>
@endpush
