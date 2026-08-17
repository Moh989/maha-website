(() => {
    'use strict';
    const sidebar = document.querySelector('[data-admin-sidebar]');
    document.querySelector('[data-admin-menu]')?.addEventListener('click', () => sidebar?.classList.toggle('open'));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') sidebar?.classList.remove('open'); });
    document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm || 'هل أنت متأكد؟')) event.preventDefault();
    }));
})();
