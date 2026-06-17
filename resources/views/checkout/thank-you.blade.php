@extends('layouts.app')

@section('title', 'Order Thank You')

@section('content')

<div class="container">

    <h1>Thank you for your order</h1>

    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
    <p><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
    <p><strong>Status:</strong> {{ $order->order_status }}</p>

    <h3>Products</h3>

    @foreach($order->items as $item)
        <div>
            <h4>{{ $item->product_title }}</h4>
            <p>Quantity: {{ $item->quantity }}</p>
            <p>Total: ${{ number_format($item->total, 2) }}</p>
        </div>
    @endforeach

    <h3>Billing</h3>

    <p>{{ $order->billing_name }}</p>
    <p>{{ $order->billing_email }}</p>
    <p>{{ $order->billing_phone }}</p>
    <p>{{ $order->billing_address }}</p>

    <h3>Total</h3>

    <p>${{ number_format($order->total, 2) }}</p>

</div>

@endsection