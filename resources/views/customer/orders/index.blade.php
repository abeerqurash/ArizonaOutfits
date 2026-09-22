@extends('customer.layouts.app')

@section('title', 'My Orders')
@section('page-heading', 'My Orders')

@section('content')
<header class="customer-page-heading">
    <div>
        <span>Order history</span>
        <h2>My orders</h2>
        <p>Find an order, follow its delivery status, or open its invoice.</p>
    </div>
</header>

<section class="customer-panel">
    <form method="GET" action="{{ route('customer.orders.index') }}" class="customer-filters">
        <label>
            <span>Search orders</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Order or tracking number">
        </label>

        <label>
            <span>Order status</span>
            <select name="status">
                <option value="">All statuses</option>

                @foreach ([
                    'pending',
                    'confirmed',
                    'processing',
                    'packed',
                    'shipped',
                    'out_for_delivery',
                    'completed',
                    'delivered',
                    'cancelled',
                    'refunded',
                ] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ str($status)->headline() }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
            Search
        </button>

        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('customer.orders.index') }}">Reset</a>
        @endif
    </form>

    @if($orders->isEmpty())
        <div class="customer-empty">
            <span>
                <i class="fa-solid fa-box-open"></i>
            </span>

            <h3>No orders found</h3>
            <p>Try changing your filters or visit the shop.</p>

            <a href="{{ route('products.index') }}">
                Visit shop
            </a>
        </div>
    @else
        <div class="customer-table-wrap">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Placed</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <strong>
                                    {{ $order->order_number ?: 'Order #' . $order->id }}
                                </strong>

                                <small>
                                    {{ $order->tracking_number ?: 'Tracking pending' }}
                                </small>
                            </td>

                            <td>
                                {{ $order->created_at?->format('d M Y') }}
                            </td>

                            <td>
                                {{ number_format($order->items_count) }}
                            </td>

                            <td>
                                <strong>
                                    {{ strtoupper($order->currency ?: 'USD') }}
                                    {{ number_format((float) $order->total, 2) }}
                                </strong>
                            </td>

                            <td>
                                <span class="customer-status {{ $order->payment_status }}">
                                    {{ str($order->payment_status ?: 'pending')->headline() }}
                                </span>
                            </td>

                            <td>
                                <span class="customer-status {{ $order->order_status }}">
                                    {{ str($order->order_status ?: 'pending')->headline() }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="customer-row-action"
                                    href="{{ route('customer.orders.show', $order->id) }}">
                                    View
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="customer-pagination">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</section>
@endsection
