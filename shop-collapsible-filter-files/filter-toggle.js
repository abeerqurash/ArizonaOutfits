
(function () {
    const filterBox = document.getElementById('shop-filter-box');
    const filterForm = document.getElementById('shop-filter-form');
    if (!filterBox || !filterForm) return;
    const closeFilters = function () { filterBox.removeAttribute('open'); };
    closeFilters();
    filterForm.addEventListener('submit', closeFilters);
    // Also close when the browser restores this page from its back/forward cache.
    window.addEventListener('pageshow', closeFilters);
})();
