@if ($orders->hasPages() || $orders->total() > 0)

    <div class="admin-orders-pagination">

        <div class="admin-orders-pagination-info">

            @if ($orders->total() > 0)
                Page
                {{ number_format($orders->currentPage()) }}
                of
                {{ number_format($orders->lastPage()) }}
            @else
                No results
            @endif

        </div>

        @if ($orders->hasPages())
            <div class="admin-orders-pagination-links">
                {{ $orders->onEachSide(1)->links() }}
            </div>
        @endif

    </div>

@endif