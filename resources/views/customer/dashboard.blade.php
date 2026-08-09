@extends('layouts.app')

@section('title', 'My Account')

@include('customer.partials.styles')

@section('content')
<section class="customer-account">
    <div class="account-shell">
        <header class="account-heading">
            <div>
                <small>Customer account</small>
                <h1>Welcome, {{ auth()->user()->name }}</h1>
                <p>Review your latest orders, delivery progress, account details and invoices.</p>
            </div>
        </header>

        @include('customer.partials.navigation')

        <div class="account-stats">
            <article class="account-stat"><small>Total orders</small><strong>{{ number_format($stats['orders']) }}</strong></article>
            <article class="account-stat"><small>Processing</small><strong>{{ number_format($stats['processing']) }}</strong></article>
            <article class="account-stat"><small>Shipped</small><strong>{{ number_format($stats['shipped']) }}</strong></article>
            <article class="account-stat"><small>Completed</small><strong>{{ number_format($stats['completed']) }}</strong></article>
        </div>

        <section class="account-panel">
            <header class="account-panel-heading">
                <h2>Recent orders</h2>
                <a href="{{ route('customer.orders.index') }}">View all orders</a>
            </header>
            @if ($recentOrders->isEmpty())
                <div class="account-empty"><i class="fa-solid fa-bag-shopping"></i><h3>No orders yet</h3><p>Your orders will appear here after checkout.</p></div>
            @else
                <div class="account-table-wrap">
                    <table class="account-table">
                        <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number ?: 'Order #'.$order->id }}</strong><small>{{ $order->tracking_number ?: 'Tracking pending' }}</small></td>
                                <td>{{ $order->created_at?->format('d M Y') }}</td>
                                <td>{{ number_format($order->items_count) }}</td>
                                <td>{{ strtoupper($order->currency ?: 'USD') }} {{ number_format((float) $order->total, 2) }}</td>
                                <td><span class="account-status {{ $order->order_status }}">{{ str($order->order_status ?: 'pending')->headline() }}</span></td>
                                <td><a class="account-action" href="{{ route('customer.orders.show', $order->id) }}">Details <i class="fa-solid fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</section>
@endsection
