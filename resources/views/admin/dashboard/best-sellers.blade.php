<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Product performance
            </span>

            <h3>
                Best Sellers
            </h3>
        </div>

    </div>

    <div class="admin-section-links">

        @forelse ($bestSellingProducts as $product)

        <a
            href="{{ route(
                                'admin.products.edit',
                                $product
                            ) }}">
            <i class="fa-solid fa-ranking-star"></i>

            <span>
                {{ $product->title }}

                <small>
                    {{ number_format(
                                        $product->purchase_count
                                    ) }}
                    purchases
                </small>
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

        @empty

        <div class="admin-empty-state">

            <span>
                <i class="fa-solid fa-chart-line"></i>
            </span>

            <h4>
                No sales data yet
            </h4>

            <p>
                Best-selling products will appear here.
            </p>

        </div>

        @endforelse

    </div>

</div>