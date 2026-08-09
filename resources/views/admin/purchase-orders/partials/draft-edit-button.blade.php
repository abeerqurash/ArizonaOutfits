@if ($purchaseOrder->isDraft())
    <a
        href="{{ route(
            'admin.purchase-orders.draft.edit',
            $purchaseOrder
        ) }}"
        class="purchase-order-button reorder">
        <i class="fa-regular fa-pen-to-square"></i>
        Edit Draft
    </a>
@endif
