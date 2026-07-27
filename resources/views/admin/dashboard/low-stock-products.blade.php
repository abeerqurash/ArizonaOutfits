<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Inventory alerts
            </span>

            <h3>
                Low-Stock Products
            </h3>
        </div>

        <a href="{{ route('admin.products.index') }}">
            Manage inventory
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="admin-table-wrapper">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @forelse ($lowStockProducts as $product)

                @php
                $productStatus = strtolower(
                $product->status ?? 'draft'
                );
                @endphp

                <tr>

                    <td>
                        <strong>
                            {{ $product->title }}
                        </strong>
                    </td>

                    <td>
                        {{ $product->sku ?: 'No SKU' }}
                    </td>

                    <td>
                        <strong>
                            {{ number_format($product->stock) }}
                        </strong>

                        <small>
                            {{
                                                $product->stock <= 0
                                                    ? 'Out of stock'
                                                    : 'Low stock'
                                            }}
                        </small>
                    </td>

                    <td>
                        <span
                            class="admin-badge admin-badge-{{
                                                $productStatus
                                            }}">
                            {{ ucfirst($productStatus) }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                                'admin.products.edit',
                                                $product
                                            ) }}"
                            class="admin-table-action"
                            title="Edit product">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="5">

                        <div class="admin-empty-state">

                            <span>
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </span>

                            <h4>
                                Inventory looks healthy
                            </h4>

                            <p>
                                No products have stock at or below
                                {{ $lowStockThreshold }} units.
                            </p>

                        </div>

                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>