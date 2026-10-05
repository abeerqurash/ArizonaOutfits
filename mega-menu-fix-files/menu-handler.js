
(function () {
    const navbarElement = document.querySelector('.navbar');
    if (!navbarElement) return;
    const trigger = navbarElement.querySelector('.menu-button');
    const panel = navbarElement.querySelector('.mega-menu');
    if (!trigger || !panel || trigger.dataset.arizonaMenuBound) return;
    trigger.dataset.arizonaMenuBound = 'true';
    function setOpen(open) {
        trigger.classList.toggle('open', open);
        panel.classList.toggle('active', open);
        trigger.setAttribute('aria-expanded', String(open));
        trigger.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        panel.setAttribute('aria-hidden', String(!open));
    }
    setOpen(false);
    // Capture handles the click before the older page-specific bubble listeners.
    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        setOpen(!panel.classList.contains('active'));
    }, true);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel.classList.contains('active')) {
            setOpen(false);
            trigger.focus();
        }
    });
    document.addEventListener('click', function (event) {
        if (panel.classList.contains('active') && !navbarElement.contains(event.target)) setOpen(false);
    });
    panel.addEventListener('click', function (event) {
        if (event.target.closest('a[href]')) setOpen(false);
    });
})();
