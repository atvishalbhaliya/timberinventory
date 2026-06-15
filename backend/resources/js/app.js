import './bootstrap';

const defaults = {
    themeColor: 'blue',
    sidebarTheme: 'dark',
    headerTheme: 'light',
    darkMode: 'light',
    layoutMode: 'standard',
    cardStyle: 'modern',
    sidebarState: 'expanded',
    favorites: ['Dashboard', 'Production'],
    recentMenus: ['Dispatch', 'Stock Ledger', 'GRN'],
};

const storageKey = 'timber-erp-preferences';
const root = document.documentElement;

const readPreferences = () => {
    try {
        return { ...defaults, ...JSON.parse(localStorage.getItem(storageKey) || '{}') };
    } catch {
        return { ...defaults };
    }
};

const writePreferences = (preferences) => {
    localStorage.setItem(storageKey, JSON.stringify(preferences));
    fetch('/preferences/theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            Accept: 'application/json',
        },
        body: JSON.stringify(preferences),
    }).catch(() => {});
};

const applyPreferences = (preferences) => {
    const systemDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
    root.classList.toggle('system-dark', Boolean(systemDark));
    root.dataset.themeColor = preferences.themeColor;
    root.dataset.sidebarTheme = preferences.sidebarTheme;
    root.dataset.headerTheme = preferences.headerTheme;
    root.dataset.darkMode = preferences.darkMode;
    root.dataset.layoutMode = preferences.layoutMode;
    root.dataset.cardStyle = preferences.cardStyle;
    root.dataset.sidebarState = preferences.sidebarState;
    window.dispatchEvent(new CustomEvent('erp-preferences-changed', { detail: preferences }));
};

let preferences = readPreferences();
applyPreferences(preferences);

window.TimberTheme = {
    get: () => ({ ...preferences }),
    set: (patch) => {
        preferences = { ...preferences, ...patch };
        applyPreferences(preferences);
        writePreferences(preferences);
    },
    toggleDark: () => {
        const next = preferences.darkMode === 'dark' ? 'light' : 'dark';
        window.TimberTheme.set({ darkMode: next });
    },
};

window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener('change', () => applyPreferences(preferences));

const closeDropdowns = (except = null) => {
    document.querySelectorAll('[data-dropdown-panel]').forEach((panel) => {
        if (panel !== except) panel.classList.remove('is-open');
    });
};

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-dropdown]');
    if (trigger) {
        const panel = document.querySelector(`[data-dropdown-panel="${trigger.dataset.dropdown}"]`);
        closeDropdowns(panel);
        panel?.classList.toggle('is-open');
        return;
    }

    if (!event.target.closest('[data-dropdown-panel]')) closeDropdowns();

    const action = event.target.closest('[data-action]');
    if (!action) return;

    if (action.dataset.action === 'toggle-sidebar') {
        if (window.innerWidth <= 860) {
            root.dataset.sidebarOpen = root.dataset.sidebarOpen === 'true' ? 'false' : 'true';
        } else {
            const next = preferences.sidebarState === 'collapsed' ? 'expanded' : 'collapsed';
            window.TimberTheme.set({ sidebarState: next });
        }
    }

    if (action.dataset.action === 'theme-panel') document.querySelector('.theme-panel')?.classList.add('is-open');
    if (action.dataset.action === 'close-theme-panel') document.querySelector('.theme-panel')?.classList.remove('is-open');
    if (action.dataset.action === 'toggle-dark') window.TimberTheme.toggleDark();
    if (action.dataset.action === 'close-modal') action.closest('.modal')?.classList.remove('is-open');
});

document.addEventListener('input', (event) => {
    const sidebarSearch = event.target.closest('[data-menu-search]');
    if (sidebarSearch) {
        const term = sidebarSearch.value.toLowerCase();
        document.querySelectorAll('[data-menu-item]').forEach((item) => {
            item.style.display = item.dataset.menuItem.toLowerCase().includes(term) ? '' : 'none';
        });
    }

    const globalSearch = event.target.closest('[data-global-search]');
    if (globalSearch) {
        const panel = document.querySelector('[data-search-results]');
        const term = globalSearch.value.trim();
        panel?.classList.toggle('is-open', term.length > 0);
        panel?.querySelectorAll('[data-search-result]').forEach((item) => {
            item.style.display = item.textContent.toLowerCase().includes(term.toLowerCase()) ? '' : 'none';
        });
    }
});

document.addEventListener('click', (event) => {
    const segment = event.target.closest('[data-segment-value]');
    if (segment) {
        window.TimberTheme.set({ [segment.dataset.segmentName]: segment.dataset.segmentValue });
        return;
    }

    const pin = event.target.closest('[data-pin-menu]');
    if (!pin) return;
    event.preventDefault();
    const label = pin.dataset.pinMenu;
    const favorites = new Set(preferences.favorites || []);
    favorites.has(label) ? favorites.delete(label) : favorites.add(label);
    window.TimberTheme.set({ favorites: [...favorites] });
    pin.classList.toggle('is-pinned', favorites.has(label));
});

window.addEventListener('erp-preferences-changed', ({ detail }) => {
    document.querySelectorAll('[data-segment-value]').forEach((button) => {
        button.classList.toggle('is-selected', detail[button.dataset.segmentName] === button.dataset.segmentValue);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    applyPreferences(preferences);
    if (window.lucide) window.lucide.createIcons();
});
