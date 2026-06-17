@extends('layouts.app')

@section('title', 'Cart')

@section('content')

<div class="container">

    <h1>Cart</h1>

    @php
        $subtotal = 0;
    @endphp

    <form action="{{ route('cart.update') }}" method="POST">
        @csrf

        @forelse($cart as $item)
            @php
                $lineTotal = $item['price'] * $item['quantity'];
                $subtotal += $lineTotal;
            @endphp

            <div class="cart-item">

                <img src="{{ asset($item['image'] ?? 'asset/images/no-image.jpg') }}" width="80">

                <h3>{{ $item['title'] }}</h3>

                <p>${{ number_format($item['price'], 2) }}</p>

                <input type="number"
                       name="quantities[{{ $item['product_id'] }}]"
                       value="{{ $item['quantity'] }}"
                       min="1">

                <p>Total: ${{ number_format($lineTotal, 2) }}</p>

                <button formaction="{{ route('cart.remove') }}"
                        name="product_id"
                        value="{{ $item['product_id'] }}">
                    Remove
                </button>

            </div>

        @empty

            <p>Your cart is empty.</p>

        @endforelse

        @if(count($cart))
            <button type="submit">Update Cart</button>
        @endif

    </form>

    @if(count($cart))
        <div class="cart-summary">
            <h3>Cart Summary</h3>

            <p>Subtotal: ${{ number_format($subtotal, 2) }}</p>
            <p>Shipping: $0.00</p>
            <p>Total: ${{ number_format($subtotal, 2) }}</p>

            <a href="{{ route('checkout.index') }}">Proceed To Checkout</a>
        </div>
    @endif

</div>

@endsection