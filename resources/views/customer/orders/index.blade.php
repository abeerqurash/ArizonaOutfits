@extends('layouts.app')
@section('title', 'My Orders')
@include('customer.partials.styles')
@section('content')
<section class="customer-account"><div class="account-shell">
<header class="account-heading"><div><small>Customer account</small><h1>My orders</h1><p>Find an order, follow its status, or download its invoice.</p></div></header>
@include('customer.partials.navigation')
<section class="account-panel">
<form method="GET" action="{{ route('customer.orders.index') }}" class="account-filters"><input type="search" name="search" value="{{ request('search') }}" placeholder="Order or tracking number"><select name="status"><option value="">All statuses</option>@foreach (['pending','processing','shipped','completed','delivered','cancelled','refunded'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ str($status)->headline() }}</option>@endforeach</select><button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>@if(request()->hasAny(['search','status']))<a href="{{ route('customer.orders.index') }}">Reset</a>@endif</form>
@if($orders->isEmpty())<div class="account-empty"><i class="fa-solid fa-box-open"></i><h3>No orders found</h3><p>Try changing your filters or visit the shop.</p></div>@else
<div class="account-table-wrap"><table class="account-table"><thead><tr><th>Order</th><th>Placed</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($orders as $order)<tr><td><strong>{{ $order->order_number ?: 'Order #'.$order->id }}</strong><small>{{ $order->tracking_number ?: 'Tracking pending' }}</small></td><td>{{ $order->created_at?->format('d M Y') }}</td><td>{{ number_format($order->items_count) }}</td><td>{{ strtoupper($order->currency ?: 'USD') }} {{ number_format((float)$order->total,2) }}</td><td><span class="account-status {{ $order->payment_status }}">{{ str($order->payment_status ?: 'pending')->headline() }}</span></td><td><span class="account-status {{ $order->order_status }}">{{ str($order->order_status ?: 'pending')->headline() }}</span></td><td><a class="account-action" href="{{ route('customer.orders.show',$order->id) }}">View</a></td></tr>@endforeach
</tbody></table></div>@if($orders->hasPages())<div class="account-pagination">{{ $orders->links() }}</div>@endif @endif
</section></div></section>
@endsection
