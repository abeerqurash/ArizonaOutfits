<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const supplierSelect = document.getElementById('supplier_id');
        const itemRows = Array.from(
            document.querySelectorAll('.purchase-order-table tbody tr')
        );

        if (!supplierSelect || itemRows.length === 0) {
            return;
        }

        const pricingUrlTemplate = @json(
            route(
                'admin.suppliers.products.pricing',
                ['supplier' => '__SUPPLIER_ID__']
            )
        );

        let requestNumber = 0;

        itemRows.forEach(function (row) {
            const quantityInput = row.querySelector('.purchase-quantity-input');
            const costInput = row.querySelector('.purchase-unit-cost-input');
            const skuCell = row.children[1] || null;

            if (quantityInput) {
                quantityInput.dataset.originalQuantity = quantityInput.value;
            }

            if (costInput) {
                costInput.dataset.originalCost = costInput.value;
            }

            if (skuCell) {
                skuCell.dataset.originalSku = skuCell.textContent.trim();
            }
        });

        function removePricingNotice() {
            document.getElementById('supplierPricingNotice')?.remove();
        }

        function showPricingNotice(message, type) {
            removePricingNotice();

            const notice = document.createElement('div');
            notice.id = 'supplierPricingNotice';
            notice.setAttribute('role', 'status');
            notice.style.cssText = [
                'margin-top:12px',
                'padding:11px 13px',
                'border-radius:10px',
                'font-size:13px',
                'font-weight:650',
                type === 'error'
                    ? 'background:#fff1f2;color:#be123c;border:1px solid #fecdd3'
                    : 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe'
            ].join(';');
            notice.textContent = message;

            supplierSelect.closest('.purchase-order-field')?.appendChild(notice);
        }

        function resetRows() {
            itemRows.forEach(function (row) {
                const quantityInput = row.querySelector('.purchase-quantity-input');
                const costInput = row.querySelector('.purchase-unit-cost-input');
                const skuCell = row.children[1] || null;

                row.querySelector('[data-supplier-price-badge]')?.remove();

                if (quantityInput) {
                    quantityInput.value = quantityInput.dataset.originalQuantity || '1';
                }

                if (costInput) {
                    costInput.value = costInput.dataset.originalCost || '0.00';
                    costInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (skuCell) {
                    skuCell.textContent = skuCell.dataset.originalSku || 'Not assigned';
                }
            });
        }

        function applyPricing(pricing) {
            let applied = 0;
            let quantityAdjustments = 0;

            itemRows.forEach(function (row) {
                const productInput = row.querySelector(
                    'input[name$="[product_id]"]'
                );
                const variantInput = row.querySelector(
                    'input[name$="[product_variant_id]"]'
                );
                const quantityInput = row.querySelector('.purchase-quantity-input');
                const costInput = row.querySelector('.purchase-unit-cost-input');
                const skuCell = row.children[1] || null;
                const productId = productInput?.value || '';
                const variantId = variantInput?.value || '';

                const exactKey = variantId ? 'variant:' + variantId : '';
                const fallbackKey = productId ? 'product:' + productId : '';
                const assignment =
                    (exactKey && pricing[exactKey])
                    || (fallbackKey && pricing[fallbackKey])
                    || null;

                if (!assignment) {
                    return;
                }

                applied += 1;

                if (costInput) {
                    costInput.value = Number(assignment.unit_cost || 0).toFixed(2);
                    costInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (skuCell && assignment.supplier_sku) {
                    skuCell.textContent = assignment.supplier_sku;
                }

                if (quantityInput) {
                    const currentQuantity = Math.max(
                        1,
                        Number.parseInt(quantityInput.value || '1', 10)
                    );
                    const minimumQuantity = Math.max(
                        1,
                        Number.parseInt(
                            assignment.minimum_order_quantity || 1,
                            10
                        )
                    );

                    if (currentQuantity < minimumQuantity) {
                        quantityInput.value = minimumQuantity;
                        quantityInput.dispatchEvent(
                            new Event('input', { bubbles: true })
                        );
                        quantityAdjustments += 1;
                    }
                }

                const productCell = row.children[0] || null;
                if (productCell) {
                    const badge = document.createElement('span');
                    badge.dataset.supplierPriceBadge = '1';
                    badge.textContent = exactKey && pricing[exactKey]
                        ? 'Exact supplier variant price'
                        : 'Supplier default price';
                    badge.style.cssText = [
                        'display:inline-flex',
                        'margin-top:6px',
                        'padding:3px 7px',
                        'border-radius:999px',
                        'background:#dbeafe',
                        'color:#1d4ed8',
                        'font-size:10px',
                        'font-weight:800'
                    ].join(';');
                    productCell.appendChild(badge);
                }
            });

            if (applied === 0) {
                showPricingNotice(
                    'This supplier has no active prices for the selected items. General product costs are still shown.',
                    'info'
                );
                return;
            }

            const quantityMessage = quantityAdjustments > 0
                ? ' ' + quantityAdjustments + ' quantity value(s) were raised to the supplier minimum.'
                : '';

            showPricingNotice(
                applied + ' supplier-specific price(s) applied.' + quantityMessage,
                'info'
            );
        }

        async function loadSupplierPricing() {
            const supplierId = supplierSelect.value;
            const currentRequest = ++requestNumber;

            resetRows();
            removePricingNotice();

            if (!supplierId) {
                return;
            }

            showPricingNotice('Loading supplier-specific prices…', 'info');

            try {
                const response = await fetch(
                    pricingUrlTemplate.replace('__SUPPLIER_ID__', supplierId),
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }
                );

                if (!response.ok) {
                    throw new Error('Pricing request failed.');
                }

                const payload = await response.json();

                if (currentRequest !== requestNumber) {
                    return;
                }

                removePricingNotice();
                applyPricing(payload.pricing || {});
            } catch (error) {
                if (currentRequest !== requestNumber) {
                    return;
                }

                resetRows();
                showPricingNotice(
                    'Supplier pricing could not be loaded. General product costs remain available.',
                    'error'
                );
            }
        }

        supplierSelect.addEventListener('change', loadSupplierPricing);

        if (supplierSelect.value) {
            loadSupplierPricing();
        }
    });
</script>
