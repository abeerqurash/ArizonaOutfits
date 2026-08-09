@if (!$purchaseOrder->isDraft())
    <a
        href="{{ route(
            'admin.purchase-orders.receiving.index',
            $purchaseOrder
        ) }}"
        class="purchase-order-button reorder">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        Receiving & Returns
    </a>
@endif
