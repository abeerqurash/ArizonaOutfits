
(function () {
    const box = document.getElementById('shop-filter-box');
    const form = document.getElementById('shop-filter-form');
    if (!box || !form) return;
    const summary = box.querySelector('summary');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let expanded = false;
    let animation = null;
    let contentAnimation = null;
    box.classList.add('has-smooth-filter');
    function cancelAnimations() {
        if (animation) animation.cancel();
        if (contentAnimation) contentAnimation.cancel();
        animation = contentAnimation = null;
    }
    function closeInstantly() {
        expanded = false;
        cancelAnimations();
        box.removeAttribute('open');
        box.classList.remove('is-animating', 'is-closing');
        summary.setAttribute('aria-expanded', 'false');
    }
    function toggle() {
        const start = box.getBoundingClientRect().height;
        expanded = !expanded;
        cancelAnimations();
        summary.setAttribute('aria-expanded', String(expanded));
        if (reduced.matches || typeof box.animate !== 'function') {
            box.open = expanded;
            box.classList.remove('is-animating', 'is-closing');
            return;
        }
        box.open = true;
        const styles = getComputedStyle(box);
        const closedHeight = summary.getBoundingClientRect().height
            + parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom)
            + parseFloat(styles.borderTopWidth) + parseFloat(styles.borderBottomWidth);
        const end = expanded ? box.getBoundingClientRect().height : closedHeight;
        box.classList.add('is-animating');
        box.classList.toggle('is-closing', !expanded);
        const current = box.animate([{ height: start + 'px' }, { height: end + 'px' }], {
            duration: 360, easing: 'cubic-bezier(.22,1,.36,1)'
        });
        animation = current;
        contentAnimation = form.animate(expanded
            ? [{ opacity: 0, transform: 'translateY(-8px)' }, { opacity: 1, transform: 'translateY(0)' }]
            : [{ opacity: 1 }, { opacity: 0 }],
            { duration: expanded ? 280 : 160, easing: 'ease-out', fill: 'both' });
        current.onfinish = function () {
            if (animation !== current) return;
            box.open = expanded;
            box.classList.remove('is-animating', 'is-closing');
            cancelAnimations();
        };
    }
    summary.addEventListener('click', function (event) { event.preventDefault(); toggle(); });
    form.addEventListener('submit', closeInstantly);
    window.addEventListener('pageshow', closeInstantly);
    closeInstantly();
})();
