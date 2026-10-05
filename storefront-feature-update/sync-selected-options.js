(() => {
    function reflectSingleVariation(root) {
        const forms = [...root.querySelectorAll('[data-product-form]')];
        if (root.matches?.('[data-product-form]')) forms.push(root);
        forms.forEach(form => {
            const data = form.querySelector('[data-product-variants]');
            if (!data) return;
            let variants;
            try { variants = JSON.parse(data.textContent); } catch { return; }
            if (!Array.isArray(variants) || variants.length !== 1) return;
            form.querySelectorAll('.product-option-select').forEach(select => {
                if (!select.value) return;
                const id = select.dataset.optionId;
                const label = select.selectedOptions[0]?.dataset.label || select.selectedOptions[0]?.textContent;
                const selected = form.querySelector('[data-selected-option="' + id + '"]');
                if (selected && selected.textContent.trim() !== label) selected.textContent = label;
                form.querySelectorAll('.option-value-button, .quick-view-option-value').forEach(button => {
                    if (String(button.dataset.optionId || button.dataset.option) !== String(id)) return;
                    const active = String(button.dataset.valueId || button.dataset.value) === String(select.value);
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', String(active));
                });
            });
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        reflectSingleVariation(document);
        new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
            if (node.nodeType === 1 && (node.matches('[data-product-form]') || node.querySelector('[data-product-form]'))) reflectSingleVariation(node);
        }))).observe(document.body, {childList:true, subtree:true});
    });
})();
