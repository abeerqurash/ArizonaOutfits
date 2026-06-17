@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

<div class="container">

    <h1>Checkout</h1>

    @guest
        <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
    @endguest

    <form action="{{ route('checkout.place') }}" method="POST">
        @csrf

        <div class="checkout-layout">

            <div class="checkout-left">

                <h3>Billing Details</h3>

                <input type="text" name="billing_name" required>
                <input type="email" name="billing_email" placeholder="Email" required>
                <input type="text" name="billing_phone" placeholder="Phone" required>
                <textarea name="billing_address" required></textarea>
                <input type="text" name="billing_city" placeholder="City" required>
                <input type="text" name="billing_country" placeholder="Country" required>

                <label>
                    <input type="checkbox" id="different-shipping">
                    Ship to different address?
                </label>

                <div id="shipping-fields" style="display:none;">
                    <h3>Shipping Details</h3>

                    <input type="text" name="shipping_name" placeholder="Shipping Name">
                    <input type="email" name="shipping_email" placeholder="Shipping Email">
                    <input type="text" name="shipping_phone" placeholder="Shipping Phone">
                    <textarea name="shipping_address" placeholder="Shipping Address"></textarea>
                    <input type="text" name="shipping_city" placeholder="Shipping City">
                    <input type="text" name="shipping_country" placeholder="Shipping Country">
                </div>

            </div>

            <div class="checkout-right">

                <h3>Your Order</h3>

                @php $subtotal = 0; @endphp

                @foreach($cart as $item)
                    @php
                        $lineTotal = $item['price'] * $item['quantity'];
                        $subtotal += $lineTotal;
                    @endphp

                    <p>
                        {{ $item['title'] }} × {{ $item['quantity'] }}
                        <strong>${{ number_format($lineTotal, 2) }}</strong>
                    </p>
                @endforeach

                <hr>

                <p>Subtotal: ${{ number_format($subtotal, 2) }}</p>
                <p>Shipping: $0.00</p>
                <h3>Total: ${{ number_format($subtotal, 2) }}</h3>

                <h4>Payment Method</h4>

                <label>
                    <input type="radio" name="payment_method" value="card" checked>
                    Card Payment
                </label>

                <br>

                <label>
                    <input type="checkbox" name="terms" value="1" required>
                    I have read and agree to the terms and conditions.
                </label>

                <button type="submit">Place Order</button>

            </div>

        </div>

    </form>

</div>

<script>
document.getElementById('different-shipping').addEventListener('change', function () {
    document.getElementById('shipping-fields').style.display = this.checked ? 'block' : 'none';
});
</script>

@endsection