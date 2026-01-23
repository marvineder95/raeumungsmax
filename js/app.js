function setYear () {
    const el = document.getElementById('year');
    if (!el) return;
    el.textContent = String(new Date().getFullYear());
}

function openDrawer () {
    const drawer = document.getElementById('mobileDrawer');
    const backdrop = document.getElementById('drawerBackdrop');
    const burgerBtn = document.getElementById('burgerBtn');

    if (!drawer || !backdrop || !burgerBtn) return;

    drawer.hidden = false;
    backdrop.hidden = false;
    burgerBtn.setAttribute('aria-expanded', 'true');

    // Prevent body scroll while open
    document.body.style.overflow = 'hidden';
}

function closeDrawer () {
    const drawer = document.getElementById('mobileDrawer');
    const backdrop = document.getElementById('drawerBackdrop');
    const burgerBtn = document.getElementById('burgerBtn');

    if (!drawer || !backdrop || !burgerBtn) return;

    drawer.hidden = true;
    backdrop.hidden = true;
    burgerBtn.setAttribute('aria-expanded', 'false');

    document.body.style.overflow = '';
}

function bindDrawerEvents () {
    const burgerBtn = document.getElementById('burgerBtn');
    const closeBtn = document.getElementById('drawerCloseBtn');
    const backdrop = document.getElementById('drawerBackdrop');
    const drawer = document.getElementById('mobileDrawer');

    if (burgerBtn) {
        burgerBtn.addEventListener('click', function () {
            openDrawer();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            closeDrawer();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closeDrawer();
        });
    }

    // Close drawer when clicking any link inside
    if (drawer) {
        drawer.addEventListener('click', function (e) {
            const target = e.target;
            if (!(target instanceof Element)) return;

            const link = target.closest('a');
            if (link) closeDrawer();
        });
    }

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
}

function init () {
    setYear();
    bindDrawerEvents();
}

document.addEventListener('DOMContentLoaded', function () {
    init();
});