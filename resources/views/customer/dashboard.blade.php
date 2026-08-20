@extends('customer.layouts.app')

@section('title', 'My Account')
@section('page-heading', 'Dashboard')

@section('content')
<header class="customer-page-heading">
    <div><span>Account overview</span><h2>Welcome back, {{ auth()->user()->name }}</h2><p>Review your orders, delivery progress, account details and invoices.</p></div>
    <a href="{{ route('products.index') }}" class="customer-primary-button"><i class="fa-solid fa-bag-shopping"></i> Shop products</a>
</header>

<div class="customer-stat-grid">
    <article class="customer-stat-card"><span class="customer-stat-icon blue"><i class="fa-solid fa-bag-shopping"></i></span><div><small>Total orders</small><strong>{{ number_format($stats['orders']) }}</strong><p>All purchases</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon amber"><i class="fa-solid fa-clock"></i></span><div><small>Processing</small><strong>{{ number_format($stats['processing']) }}</strong><p>Being prepared</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon violet"><i class="fa-solid fa-truck-fast"></i></span><div><small>Shipped</small><strong>{{ number_format($stats['shipped']) }}</strong><p>On the way</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon green"><i class="fa-solid fa-circle-check"></i></span><div><small>Completed</small><strong>{{ number_format($stats['completed']) }}</strong><p>Delivered orders</p></div></article>
</div>

<section class="customer-panel">
    <header class="customer-panel-heading"><div><span>Order activity</span><h3>Recent orders</h3></div><a href="{{ route('customer.orders.index') }}">View all orders <i class="fa-solid fa-arrow-right"></i></a></header>
    @if ($recentOrders->isEmpty())
        <div class="customer-empty"><span><i class="fa-solid fa-bag-shopping"></i></span><h3>No orders yet</h3><p>Your purchases will appear here after checkout.</p><a href="{{ route('products.index') }}">Start shopping</a></div>
    @else
        <div class="customer-table-wrap"><table class="customer-table"><thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
        @foreach ($recentOrders as $order)
            <tr><td><strong>{{ $order->order_number ?: 'Order #'.$order->id }}</strong><small>{{ $order->tracking_number ?: 'Tracking pending' }}</small></td><td>{{ $order->created_at?->format('d M Y') }}</td><td>{{ number_format($order->items_count) }}</td><td><strong>{{ strtoupper($order->currency ?: 'USD') }} {{ number_format((float) $order->total, 2) }}</strong></td><td><span class="customer-status {{ $order->order_status }}">{{ str($order->order_status ?: 'pending')->headline() }}</span></td><td><a class="customer-row-action" href="{{ route('customer.orders.show', $order->id) }}">Details <i class="fa-solid fa-chevron-right"></i></a></td></tr>
        @endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
