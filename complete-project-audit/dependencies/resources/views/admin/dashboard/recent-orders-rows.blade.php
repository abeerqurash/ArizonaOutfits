@forelse ($recentOrders as $order)

    @php
        $paymentStatus = strtolower(
            $order->payment_status ?? 'pending'
        );

        $orderStatus = strtolower(
            $order->order_status ?? 'pending'
        );

        $customerName =
            $order->customer_name
            ?: $order->user?->name
            ?: 'Guest';

        $customerEmail =
            $order->customer_email
            ?: $order->user?->email
            ?: 'Guest order';
    @endphp

    <tr>

        <td>
            <a
                href="{{ route('admin.orders.show', $order) }}"
                class="admin-order-number"
            >
                {{ $order->order_number ?: '#' . $order->id }}
            </a>
        </td>

        <td>
            <div class="admin-customer-cell">

                <span class="admin-table-avatar">
                    {{ strtoupper(substr($customerName, 0, 1)) }}
                </span>

                <div>
                    <strong>
                        {{ $customerName }}
                    </strong>

                    <small>
                        {{ $customerEmail }}
                    </small>
                </div>

            </div>
        </td>

        <td>
            <strong>
                ${{ number_format((float) $order->total, 2) }}
            </strong>
        </td>

        <td>
            <span
                class="admin-badge admin-badge-{{ $paymentStatus }}"
            >
                {{
                    ucfirst(
                        str_replace('_', ' ', $paymentStatus)
                    )
                }}
            </span>
        </td>

        <td>
            <span
                class="admin-badge admin-badge-{{ $orderStatus }}"
            >
                {{
                    ucfirst(
                        str_replace('_', ' ', $orderStatus)
                    )
                }}
            </span>
        </td>

        <td>
            <span class="admin-table-date">
                {{ $order->created_at?->format('M d, Y') }}
            </span>

            <small class="admin-table-time">
                {{ $order->created_at?->format('h:i A') }}
            </small>
        </td>

        <td>
            <a
                href="{{ route('admin.orders.show', $order) }}"
                class="admin-table-action"
                title="View order"
            >
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="7">

            <div class="admin-empty-state">

                <span>
                    <i class="fa-solid fa-bag-shopping"></i>
                </span>

                <h4>
                    No orders found
                </h4>

                <p>
                    No orders were placed during this selected period.
                </p>

            </div>

        </td>
    </tr>

@endforelse