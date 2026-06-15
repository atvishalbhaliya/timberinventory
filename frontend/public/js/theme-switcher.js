(function () {
    const storageKey = 'suresh-timber-theme';
    const root = document.documentElement;
    const savedTheme = localStorage.getItem(storageKey) || 'light';

    root.setAttribute('data-theme', savedTheme);

    window.toggleErpTheme = function () {
        const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', nextTheme);
        localStorage.setItem(storageKey, nextTheme);
    };
})();
