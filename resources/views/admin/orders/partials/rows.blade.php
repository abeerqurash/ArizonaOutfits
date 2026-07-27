@forelse ($orders as $order)

    @php
        $customerName =
            $order->billing_name
            ?: $order->shipping_name
            ?: $order->user?->name
            ?: 'Guest customer';

        $customerEmail =
            $order->billing_email
            ?: $order->shipping_email
            ?: $order->user?->email
            ?: 'No email';

        $paymentStatus =
            $order->payment_status ?: 'pending';

        $orderStatus =
            $order->order_status ?: 'pending';

        $currency =
            strtoupper($order->currency ?: 'USD');
    @endphp

    <tr data-order-row="{{ $order->id }}">

        <td class="admin-orders-checkbox-column">

            <input
                type="checkbox"
                class="admin-order-checkbox js-order-checkbox"
                value="{{ $order->id }}"
                aria-label="Select order {{
                    $order->order_number ?: '#' . $order->id
                }}"
            >

        </td>

        <td>
            <span class="admin-orders-number">

                <strong>
                    {{ $order->order_number ?: '#' . $order->id }}
                </strong>

                <small>
                    ID: {{ $order->id }}
                </small>

            </span>
        </td>

        <td class="admin-orders-customer">

            <strong>{{ $customerName }}</strong>

            <small>{{ $customerEmail }}</small>

        </td>

        <td>
            {{ number_format((int) $order->items_count) }}
        </td>

        <td class="admin-orders-money">
            {{ $currency }}
            {{ number_format((float) $order->total, 2) }}
        </td>

        <td>
            <span
                class="admin-status admin-status-{{
                    strtolower($paymentStatus)
                }}"
            >
                {{ ucfirst($paymentStatus) }}
            </span>
        </td>

        <td>
            <span
                class="admin-status admin-status-{{
                    strtolower($orderStatus)
                }}"
            >
                {{ ucfirst($orderStatus) }}
            </span>
        </td>

        <td class="admin-orders-tracking">
            {{ $order->tracking_number ?: '—' }}
        </td>

        <td class="admin-orders-date">

            {{ $order->created_at?->format('M d, Y') }}

            <br>

            <small>
                {{ $order->created_at?->format('g:i A') }}
            </small>

        </td>

        <td>
            <a
                href="{{ route('admin.orders.show', $order) }}"
                class="admin-orders-view-button"
            >
                View
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </td>

    </tr>

@empty

    <tr>
        <td
            colspan="10"
            class="admin-orders-empty"
        >

            <div class="admin-orders-empty-state">

                <span class="admin-orders-empty-icon">
                    <i class="fa-solid fa-box-open"></i>
                </span>

                <strong>No orders found</strong>

                <p>
                    No orders match your current search and filters.
                </p>

            </div>

        </td>
    </tr>

@endforelse