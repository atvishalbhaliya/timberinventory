import './bootstrap';

const defaults = {
    themePreset: 'timber',
    themeColor: 'timber',
    sidebarTheme: 'dark',
    headerTheme: 'light',
    darkMode: 'light',
    layoutMode: 'standard',
    cardStyle: 'material',
    sidebarState: 'expanded',
    borderRadius: 'comfortable',
    fontFamily: 'inter',
};

const key = 'timber-frontend-preferences';
const sidebarDefaultMigrationKey = 'timber-sidebar-dark-default-v1';
const root = document.documentElement;
const hasStoredPrefs = localStorage.getItem(key) !== null;

const readPrefs = () => {
    try {
        return { ...defaults, ...JSON.parse(localStorage.getItem(key) || '{}') };
    } catch {
        return { ...defaults };
    }
};

let prefs = readPrefs();

if (!localStorage.getItem(sidebarDefaultMigrationKey) && prefs.sidebarTheme === 'light') {
    prefs = { ...prefs, sidebarTheme: 'dark' };
    localStorage.setItem(sidebarDefaultMigrationKey, '1');
}

const applyPrefs = () => {
    root.classList.toggle('system-dark', Boolean(window.matchMedia?.('(prefers-color-scheme: dark)').matches));
    root.dataset.themeColor = prefs.themeColor;
    root.dataset.themePreset = prefs.themePreset;
    root.dataset.sidebarTheme = prefs.sidebarTheme;
    root.dataset.headerTheme = prefs.headerTheme;
    root.dataset.darkMode = prefs.darkMode;
    root.dataset.layoutMode = prefs.layoutMode;
    root.dataset.cardStyle = prefs.cardStyle;
    root.dataset.sidebarState = prefs.sidebarState === 'collapsed' ? 'mini' : prefs.sidebarState;
    root.dataset.borderRadius = prefs.borderRadius;
    root.dataset.fontFamily = prefs.fontFamily;
    window.dispatchEvent(new CustomEvent('erp-theme-change', { detail: prefs }));
};

const savePrefs = () => {
    localStorage.setItem(key, JSON.stringify(prefs));
    localStorage.setItem('theme', prefs.darkMode === 'dark' ? 'dark' : 'light');
    fetch('/preferences/theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            Accept: 'application/json',
        },
        body: JSON.stringify(prefs),
    }).catch(() => {});
};

window.ErpTheme = {
    get: () => ({ ...prefs }),
    set: (patch) => {
        prefs = { ...prefs, ...patch };
        applyPrefs();
        savePrefs();
    },
    toggleDark: () => window.ErpTheme.set({ darkMode: prefs.darkMode === 'dark' ? 'light' : 'dark' }),
};

applyPrefs();
if (!hasStoredPrefs) savePrefs();
window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener('change', applyPrefs);

const closePanels = (except = null) => {
    document.querySelectorAll('[data-floating-panel], .dropdown-menu').forEach((panel) => {
        if (panel !== except) panel.classList.remove('is-open', 'show');
    });
};

const sectionIcons = {
    Dashboard: 'layout-dashboard',
    Master: 'database',
    Inventory: 'boxes',
    Production: 'factory',
    Dispatch: 'truck',
    Reports: 'bar-chart-3',
    Administration: 'settings',
};

const routePermissions = {
    '/stock-ledger': 'stock-ledger.view',
    '/stock-summary': 'stock-summary.view',
    '/stock-verification': 'stock-verification.view',
    '/wastage': 'wastage.view',
    '/wastage-reuse': 'wastage-reuse.view',
    '/dispatch-challan': 'dispatch.view',
    '/team-ledger': 'accounts.view',
    '/team-payments': 'accounts.view',
    '/inventory-reports': 'reports.view',
    '/production-reports': 'reports.view',
    '/dispatch-reports': 'reports.view',
    '/payment-reports': 'reports.view',
    '/roles': 'roles.view',
    '/permissions': 'permissions.view',
    '/modules': 'modules.view',
};

const readStoredUser = () => {
    try {
        return JSON.parse(localStorage.getItem('user') || '{}');
    } catch {
        return {};
    }
};

const userHasPermission = (user, permission) => (
    user.role_name === 'Super Admin' || (user.permissions || []).includes(permission)
);

const refreshStoredUser = async () => {
    try {
        const response = await window.axios.get('/v1/auth/me');
        const currentUser = response.data.data || {};
        localStorage.setItem('user', JSON.stringify(currentUser));
        localStorage.removeItem('timber-sidebar-navigation-v1');
        localStorage.removeItem('timber-sidebar-navigation-v2');
        localStorage.removeItem('timber-sidebar-navigation-v3');
        localStorage.removeItem('timber-sidebar-navigation-v4');
        return currentUser;
    } catch {
        return readStoredUser();
    }
};

const canAccessCurrentRoute = async () => {
    const requiredPermission = routePermissions[window.location.pathname];
    if (!requiredPermission) return true;

    const storedUser = readStoredUser();
    if (userHasPermission(storedUser, requiredPermission)) return true;

    const refreshedUser = await refreshStoredUser();
    return userHasPermission(refreshedUser, requiredPermission);
};

const enforceRoutePermission = async () => {
    if (await canAccessCurrentRoute()) return;

    document.querySelector('.erp-content')?.replaceChildren();
    window.ErpToast?.show('You do not have permission to access this page.', 'danger');
    window.location.href = '/dashboard';
};

const activePath = (path) => window.location.pathname === path || (window.location.pathname === '/' && path === '/dashboard');
const oldNavCacheKeys = ['timber-sidebar-navigation-v1', 'timber-sidebar-navigation-v2', 'timber-sidebar-navigation-v3', 'timber-sidebar-navigation-v4'];
const navCacheKey = 'timber-sidebar-navigation-v5';
const navCacheTtlMs = 5 * 60 * 1000;
let lastRenderedNav = '';

const navSignature = (user = {}) => JSON.stringify({
    role_id: user.role_id || null,
    role_name: user.role_name || null,
    permissions: Array.isArray(user.permissions) ? [...user.permissions].sort() : [],
});

const renderSidebar = (items = []) => {
    const nav = document.querySelector('[data-sidebar-nav]');
    if (!nav) return;

    const signature = JSON.stringify(items);
    if (signature === lastRenderedNav && nav.dataset.rendered === 'true') {
        nav.querySelectorAll('.sidebar-item').forEach((item) => {
            const path = item.getAttribute('href');
            if (path) item.classList.toggle('active', activePath(path));
        });
        return;
    }
    lastRenderedNav = signature;

    const grouped = items.reduce((groups, item) => {
        const section = item.section || 'Navigation';
        groups[section] = groups[section] || [];
        groups[section].push(item);
        return groups;
    }, {});

    nav.innerHTML = Object.entries(grouped).map(([section, sectionItems]) => {
        if (section === 'Dashboard' && sectionItems.length === 1) {
            const item = sectionItems[0];
            return `
                <a href="${item.path}" class="sidebar-item sidebar-parent ${activePath(item.path) ? 'active' : ''}" title="${item.title}" data-menu-item="${item.title}">
                    <i data-lucide="${item.icon || sectionIcons[section] || 'circle'}"></i>
                    <span class="sidebar-item-label">${item.title}</span>
                </a>
            `;
        }

        const isOpen = sectionItems.some((item) => activePath(item.path));
        return `
            <section class="sidebar-group ${isOpen ? 'is-open is-active' : ''}" data-sidebar-group>
                <button type="button" class="sidebar-item sidebar-parent" data-sidebar-group-trigger title="${section}" data-menu-item="${section}">
                    <i data-lucide="${sectionIcons[section] || 'folder'}"></i>
                    <span class="sidebar-item-label">${section}</span>
                    <i class="sidebar-chevron" data-lucide="chevron-down"></i>
                </button>
                <div class="sidebar-submenu">
                    ${sectionItems.map((item) => `
                        <a href="${item.path}" class="sidebar-item sidebar-child ${activePath(item.path) ? 'active' : ''}" title="${item.title}" data-menu-item="${item.title}">
                            <i data-lucide="${item.icon || 'circle'}"></i>
                            <span class="sidebar-item-label">${item.title}</span>
                        </a>
                    `).join('')}
                </div>
            </section>
        `;
    }).join('') || '<div class="sidebar-loading">No navigation available.</div>';
    nav.dataset.rendered = 'true';

    if (window.lucide) window.lucide.createIcons();
};

const loadSidebarNavigation = async () => {
    const nav = document.querySelector('[data-sidebar-nav]');
    if (!nav || !localStorage.getItem('auth_token')) return;

    oldNavCacheKeys.forEach((cacheKey) => localStorage.removeItem(cacheKey));
    const currentUserSignature = navSignature(readStoredUser());

    try {
        const cached = JSON.parse(localStorage.getItem(navCacheKey) || 'null');
        if (cached?.items?.length && cached.signature === currentUserSignature) {
            renderSidebar(cached.items);
            if (Date.now() - (cached.cachedAt || 0) < navCacheTtlMs) return;
        }
    } catch {
        localStorage.removeItem(navCacheKey);
    }

    try {
        const response = await window.axios.get('/v1/dashboard/navigation');
        const items = response.data.data.items || [];
        localStorage.setItem(navCacheKey, JSON.stringify({
            items,
            cachedAt: Date.now(),
            signature: currentUserSignature,
        }));
        renderSidebar(items);
    } catch (error) {
        if (!nav.dataset.rendered) nav.innerHTML = '<div class="sidebar-loading">Navigation unavailable.</div>';
    }
};

const lookupCacheKey = (endpoint, params = {}) => {
    const cleanParams = Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .sort(([left], [right]) => left.localeCompare(right));

    return `timber-lookup:${endpoint}:${JSON.stringify(cleanParams)}`;
};

window.ErpApi = {
    async cachedList(endpoint, params = {}, ttlMs = 5 * 60 * 1000) {
        const requestParams = { per_page: 100, ...params };
        const key = lookupCacheKey(endpoint, requestParams);

        try {
            const cached = JSON.parse(sessionStorage.getItem(key) || 'null');
            if (cached && Date.now() - cached.cachedAt < ttlMs) return cached.rows || [];
        } catch {
            sessionStorage.removeItem(key);
        }

        const response = await window.axios.get(endpoint, { params: requestParams });
        const rows = response.data?.data?.data || response.data?.data || [];
        sessionStorage.setItem(key, JSON.stringify({ rows, cachedAt: Date.now() }));

        return rows;
    },
    clearLookupCache() {
        Object.keys(sessionStorage)
            .filter((key) => key.startsWith('timber-lookup:'))
            .forEach((key) => sessionStorage.removeItem(key));
    },
};

const nativeAxiosGet = window.axios.get.bind(window.axios);
const inflightGets = new Map();

window.axios.get = (url, config = {}) => {
    const method = (config.method || 'get').toLowerCase();
    if (method !== 'get' || config.signal) return nativeAxiosGet(url, config);

    const signature = `${url}:${JSON.stringify(config.params || {})}`;
    if (inflightGets.has(signature)) return inflightGets.get(signature);

    const request = nativeAxiosGet(url, config).finally(() => inflightGets.delete(signature));
    inflightGets.set(signature, request);

    return request;
};

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-panel-trigger]');
    if (trigger) {
        const panel = document.querySelector(`[data-floating-panel="${trigger.dataset.panelTrigger}"]`);
        closePanels(panel);
        panel?.classList.toggle('is-open');
        return;
    }

    const dropdownTrigger = event.target.closest('[data-dropdown-trigger]');
    if (dropdownTrigger) {
        const menu = dropdownTrigger.nextElementSibling;
        closePanels(menu);
        menu?.classList.toggle('show');
        return;
    }

    if (!event.target.closest('[data-floating-panel], .dropdown')) closePanels();

    const action = event.target.closest('[data-action]');
    if (!action) return;

    if (action.dataset.action === 'toggle-sidebar') {
        if (window.innerWidth < 992) {
            root.dataset.sidebarOpen = root.dataset.sidebarOpen === 'true' ? 'false' : 'true';
        } else {
            window.ErpTheme.set({ sidebarState: root.dataset.sidebarState === 'mini' ? 'expanded' : 'mini' });
        }
    }
    if (action.dataset.action === 'theme-panel') document.querySelector('.theme-panel')?.classList.add('is-open');
    if (action.dataset.action === 'close-theme-panel') document.querySelector('.theme-panel')?.classList.remove('is-open');
    if (action.dataset.action === 'toggle-dark') window.ErpTheme.toggleDark();
});

document.addEventListener('click', (event) => {
    const segment = event.target.closest('[data-segment-value]');
    if (segment) {
        window.ErpTheme.set({ [segment.dataset.segmentName]: segment.dataset.segmentValue });
        return;
    }

    const groupTrigger = event.target.closest('[data-sidebar-group-trigger]');
    if (!groupTrigger) return;
    groupTrigger.closest('[data-sidebar-group]')?.classList.toggle('is-open');
});

document.addEventListener('click', (event) => {
    if (window.innerWidth >= 992 || root.dataset.sidebarOpen !== 'true') return;
    if (event.target.closest('.erp-sidebar, [data-action="toggle-sidebar"]')) return;
    root.dataset.sidebarOpen = 'false';
});

document.addEventListener('click', (event) => {
    if (window.innerWidth >= 992) return;
    if (!event.target.closest('.sidebar-child[href], .sidebar-parent[href]')) return;
    root.dataset.sidebarOpen = 'false';
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) root.dataset.sidebarOpen = 'false';
}, { passive: true });

document.addEventListener('input', (event) => {
    const menuSearch = event.target.closest('[data-menu-search]');
    if (menuSearch) {
        const term = menuSearch.value.toLowerCase();
        document.querySelectorAll('[data-menu-item]').forEach((item) => {
            item.style.display = item.dataset.menuItem.toLowerCase().includes(term) ? '' : 'none';
        });
    }
});

window.addEventListener('erp-theme-change', ({ detail }) => {
    document.querySelectorAll('[data-segment-value]').forEach((button) => {
        button.classList.toggle('is-selected', detail[button.dataset.segmentName] === button.dataset.segmentValue);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    applyPrefs();
    enforceRoutePermission().then(() => loadSidebarNavigation());
    if (window.lucide) window.lucide.createIcons();
});

window.ErpToast = {
    show(message, type = 'success') {
        let stack = document.querySelector('[data-toast-stack]');
        if (!stack) {
            stack = document.createElement('div');
            stack.dataset.toastStack = 'true';
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }

        const toast = document.createElement('div');
        toast.className = `erp-toast toast-${type}`;
        toast.innerHTML = `<i data-lucide="${type === 'danger' ? 'circle-alert' : 'circle-check'}"></i><span>${message}</span>`;
        stack.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();
        window.setTimeout(() => toast.classList.add('is-leaving'), 2800);
        window.setTimeout(() => toast.remove(), 3300);
    },
};

window.ErpModal = {
    open({ title = '', subtitle = '', body = '', footer = '', size = 'lg' } = {}) {
        let modal = document.querySelector('[data-erp-modal]');
        if (!modal) {
            modal = document.createElement('div');
            modal.dataset.erpModal = 'true';
            modal.className = 'erp-modal-shell';
            modal.innerHTML = `
                <div class="erp-modal-backdrop" data-modal-close></div>
                <section class="erp-modal erp-modal-${size}" role="dialog" aria-modal="true">
                    <header class="erp-modal-header">
                        <div>
                            <h2 data-modal-title></h2>
                            <p data-modal-subtitle></p>
                        </div>
                        <button class="icon-btn" type="button" data-modal-close title="Close"><i data-lucide="x"></i></button>
                    </header>
                    <div class="erp-modal-body" data-modal-body></div>
                    <footer class="erp-modal-footer" data-modal-footer></footer>
                </section>
            `;
            document.body.appendChild(modal);
            modal.addEventListener('click', (event) => {
                if (event.target.closest('[data-modal-close]')) window.ErpModal.close();
            });
        }

        modal.querySelector('[data-modal-title]').textContent = title;
        modal.querySelector('[data-modal-subtitle]').textContent = subtitle;
        modal.querySelector('[data-modal-body]').innerHTML = body;
        modal.querySelector('[data-modal-footer]').innerHTML = footer;
        modal.querySelector('.erp-modal').className = `erp-modal erp-modal-${size}`;
        modal.classList.add('is-open');
        document.documentElement.dataset.modalOpen = 'true';
        if (window.lucide) window.lucide.createIcons();
        return modal;
    },
    close() {
        document.querySelector('[data-erp-modal]')?.classList.remove('is-open');
        delete document.documentElement.dataset.modalOpen;
    },
    loading(title = 'Loading', subtitle = 'Please wait...') {
        return window.ErpModal.open({
            title,
            subtitle,
            body: '<div class="skeleton-list"><span></span><span></span><span></span><span></span></div>',
            footer: '',
        });
    },
};

window.addEventListener('scroll', () => {
    document.querySelector('.erp-header')?.classList.toggle('is-scrolled', window.scrollY > 4);
}, { passive: true });
