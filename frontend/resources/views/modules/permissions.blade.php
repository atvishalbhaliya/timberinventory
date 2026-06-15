@extends('layouts.app')

@section('title', 'Permissions - Timber Inventory')

@section('content')
    <div class="erp-page-title">
        <div>
            <h1>Permissions</h1>
            <p class="text-muted">Create and maintain permission keys used by roles and navigation.</p>
        </div>
        <div class="module-actions">
            <button class="btn-erp btn-primary" data-action="new-permission"><i data-lucide="plus"></i> Add Permission</button>
        </div>
    </div>

    <section class="erp-card">
        <div class="table-toolbar">
            <div class="data-grid-controls">
                <label class="text-muted">Show</label>
                <select id="per_page"><option selected>10</option><option>25</option><option>50</option><option>100</option></select>
                <span class="text-muted">entries</span>
                <select id="main_module"><option value="">All Modules</option></select>
                <select id="action_filter"><option value="">All Actions</option></select>
            </div>
            <div class="module-search"><i data-lucide="search"></i><input id="search" type="search" placeholder="Search permissions"><button class="search-clear" data-action="clear-search" type="button"><i data-lucide="x"></i></button></div>
        </div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Permission Name</th><th>Module</th><th>Sub Module</th><th>Action</th><th>Description</th><th class="text-end action-col">Actions</th></tr></thead><tbody id="rows"></tbody></table></div>
        <div class="list-footer">
            <div class="module-actions"><button class="btn-erp" data-action="export"><i data-lucide="file-spreadsheet"></i> Export CSV</button><button class="btn-erp" onclick="window.print()"><i data-lucide="printer"></i> Print</button></div>
            <div class="pager-actions"><button class="btn-erp" data-action="prev-page">Previous</button><span id="page-status" class="text-muted"></span><button class="btn-erp" data-action="next-page">Next</button></div>
        </div>
    </section>

    <style>
        .list-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 16px 16px;flex-wrap:wrap}
        .action-col{position:sticky;right:0;background:var(--surface-soft);min-width:112px}
        .permission-modal-shell{display:grid;gap:14px}
        .permission-modal-note{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:12px 14px;border:1px solid var(--border);border-radius:var(--radius);background:var(--surface-soft);color:var(--muted);font-size:12px;line-height:1.5}
        .permission-modal-note strong{display:block;color:var(--text);font-size:13px;font-weight:900}
        .permission-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .permission-form-grid label{display:grid;gap:6px;font-weight:800;font-size:12px;color:var(--muted)}
        .permission-form-grid input,.permission-form-grid select{width:100%;border:1px solid var(--border);border-radius:var(--radius);padding:10px 12px;background:var(--surface);color:var(--text)}
        .permission-form-grid input[readonly]{background:var(--surface-soft);color:var(--muted)}
        .permission-form-grid .field-wide{grid-column:1/-1}
        .permission-key-wrap{display:flex;gap:8px;align-items:center}
        .permission-key-wrap input{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;letter-spacing:.02em}
        @media (max-width: 720px) {
            .permission-form-grid{grid-template-columns:1fr}
            .permission-modal-note{flex-direction:column}
        }
    </style>
@endsection

@push('scripts')
<script>
let permissions=[],page=1,last=1,timer;
const endpoint='/v1/admin/permissions';
const mainModules=['Inventory','Masters','Production','Dispatch','Reports','Administration','Finance','Settings'];
const actions=['View','Create','Edit','Delete','Approve','Post','Cancel','Export','Import','Print','Custom'];
let subModules = [];

async function loadSubModules() {
    try {
        const res = await axios.get('/v1/admin/modules');
        
        // Adjust according to your API response structure
        subModules = res.data.data.map(x => x.module_name);
        //  console.log(subModules);
        // if (document.getElementById('p_sub')) {
        //     p_sub.innerHTML = opts(subModules, 'Select sub module');
        // }
    } catch (err) {
        console.error(err);
        ErpToast?.show('Failed to load sub modules');
    }
}const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
const slug=v=>String(v||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
function opts(a,p=''){return `<option value="">${p}</option>`+a.map(x=>`<option>${esc(x)}</option>`).join('')}
function params(){return{page,per_page:per_page.value,search:search.value,main_module:main_module.value,action:action_filter.value}}
async function load(p=page){page=p;rows.innerHTML='<tr class="skeleton-row"><td colspan="6"></td></tr>';const r=await axios.get(endpoint,{params:params()});const d=r.data.data;permissions=d.data||[];last=d.last_page||1;rows.innerHTML=permissions.map(x=>`<tr><td><strong>${esc(x.name)}</strong></td><td>${esc(x.main_module)}</td><td>${esc(x.sub_module)}</td><td>${esc(x.action)}</td><td>${esc(x.description)}</td><td class="text-end action-col"><button class="icon-btn" data-edit="${x.id}" title="Edit"><i data-lucide="pencil"></i></button><button class="icon-btn" data-delete="${x.id}" title="Delete"><i data-lucide="trash-2"></i></button></td></tr>`).join('')||'<tr><td colspan="6" class="text-muted">No permissions found.</td></tr>';pageStatus.textContent=`Showing page ${page} of ${last}`;lucide?.createIcons()}
function modal(row=null){ErpModal.open({title:row?'Edit Permission':'Add Permission',subtitle:'Choose the module parts first, then review the generated permission key.',size:'lg',body:`<div class="permission-modal-shell"><div class="permission-modal-note"><div><strong>Permission key</strong><span>The key is generated from Sub Module + Action and must stay unique.</span></div><div class="text-muted">Example: <code>stock-ledger.view</code></div></div><div class="permission-form-grid"><input type="hidden" id="permission_id" value="${row?.id||''}"><label><span>Main Module</span><select id="p_main" required>${opts(mainModules,'Select module')}</select></label><label><span>Sub Module</span><select id="p_sub" required>${opts(subModules,'Select sub module')}</select></label><label><span>Action</span><select id="p_action" required>${opts(actions,'Select action')}</select></label><label><span>Permission Key</span><div class="permission-key-wrap"><input id="p_name" readonly><button class="btn-erp" type="button" data-action="copy-permission-key">Copy</button></div></label><label class="field-wide"><span>Description</span><input id="p_description" value="${esc(row?.description||'')}" maxlength="255" placeholder="Optional description"></label></div><div id="modal-validation" class="form-errors" hidden></div></div>`,footer:'<button class="btn-erp" data-modal-close>Cancel</button><button class="btn-erp btn-primary" data-action="save-permission">Save</button>'});p_main.value=row?.main_module||'';p_sub.value=row?.sub_module||'';p_action.value=row?.action||'';const gen=()=>p_name.value=`${slug(p_sub.value)}.${slug(p_action.value)}`;p_sub.onchange=gen;p_action.onchange=gen;p_name.value=row?.name||'';if(!row)gen()}
document.addEventListener('DOMContentLoaded',async ()=>{ await loadSubModules();main_module.innerHTML=opts(mainModules,'All Modules');action_filter.innerHTML=opts(actions,'All Actions');load();search.oninput=()=>{clearTimeout(timer);timer=setTimeout(()=>load(1),250)};per_page.onchange=()=>load(1);main_module.onchange=()=>load(1);action_filter.onchange=()=>load(1);document.addEventListener('click',async e=>{if(e.target.closest('[data-action="new-permission"]'))modal();if(e.target.closest('[data-action="clear-search"]')){search.value='';load(1)}if(e.target.closest('[data-action="copy-permission-key"]')){await navigator.clipboard?.writeText(p_name.value||'').catch(()=>{});ErpToast?.show('Permission key copied.')}if(e.target.closest('[data-action="prev-page"]')&&page>1)load(page-1);if(e.target.closest('[data-action="next-page"]')&&page<last)load(page+1);const id=e.target.closest('[data-edit]')?.dataset.edit;if(id)modal(permissions.find(x=>x.id===Number(id)));const del=e.target.closest('[data-delete]')?.dataset.delete;if(del)ErpModal.open({title:'Delete Permission',body:'<p>Delete this permission?</p>',footer:`<button class="btn-erp" data-modal-close>Cancel</button><button class="btn-erp btn-danger" data-confirm-delete="${del}">Delete</button>`,size:'sm'});if(e.target.closest('[data-confirm-delete]')){await axios.delete(`${endpoint}/${e.target.closest('[data-confirm-delete]').dataset.confirmDelete}`);ErpModal.close();load(page)}if(e.target.closest('[data-action="save-permission"]')){try{const id=permission_id.value;const payload={name:p_name.value,main_module:p_main.value,sub_module:p_sub.value,action:p_action.value,description:p_description.value,guard_name:'api'};id?await axios.put(`${endpoint}/${id}`,payload):await axios.post(endpoint,payload);ErpModal.close();load(page);ErpToast?.show('Permission saved.')}catch(err){modalValidation=document.getElementById('modal-validation');modalValidation.textContent=err.normalizedMessage||'Save failed.';modalValidation.hidden=false}}if(e.target.closest('[data-action="export"]')){const csv=['Permission Name,Module,Sub Module,Action,Description',...permissions.map(x=>[x.name,x.main_module,x.sub_module,x.action,x.description].map(v=>`"${String(v??'').replaceAll('"','""')}"`).join(','))].join('\\n');const b=new Blob([csv],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='permissions.csv';a.click()}})})
</script>
@endpush
